<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Vehicle;

use App\Models\StarCitizen\PledgeStore\PledgeStoreProduct;
use App\Models\StarCitizen\PledgeStore\PledgeStoreSku;
use App\Models\StarCitizen\PledgeStore\PledgeStoreSkuHistory;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Vehicle;
use App\Services\RsiDownloadClient;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class ImportPledgeStore implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    private const string GRAPHQL_URL = 'https://robertsspaceindustries.com/graphql';

    private const int PAGE_SIZE = 100;

    private const int SKU_CARD_BATCH_SIZE = 100;

    private const int THROTTLE_MICROSECONDS = 500_000;

    public int $throttleUs = self::THROTTLE_MICROSECONDS;

    /**
     * Each entry defines a product filter with facets.
     * Results are deduplicated by cig_id across all queries.
     *
     * @var array<int, array{products: array<int>, facet?: string, types?: array<string>}>
     */
    private const array BROWSE_QUERIES = [
        'game-packages' => [
            'products' => [9, 45, 46],
            'facet' => 'game-packages',
        ],
        'standalone-ships' => [
            'products' => [72],
            'facet' => 'extras-standalone-ships',
        ],
        'ship-upgrades' => [
            'products' => [241, 51],
        ],
        'add-ons' => [
            'products' => [3],
            'facet' => 'extras-add-ons',
        ],
        'paints' => [
            'products' => [268],
            'facet' => 'paints',
        ],
        'gear' => [
            'products' => [289],
            'facet' => 'extras-gear',
        ],
        'packs' => [
            'products' => [270],
        ],
        'subscriptions' => [
            'products' => [24, 29],
        ],
        'rentals' => [
            'products' => [105, 106, 130, 134],
        ],
        'ingame-decorations' => [
            'products' => [32, 33, 34, 58],
        ],
        'ingame-weapons' => [
            'products' => [35, 36, 37, 38, 40, 57, 86, 87, 88, 89, 90, 91, 92, 93, 95, 96, 99, 100, 101, 108, 109, 122, 123, 124, 129, 138, 150, 151, 152, 153, 154, 155, 156, 157, 158, 159, 160, 161],
        ],
        'ingame-components' => [
            'products' => [131, 133],
        ],
        'ingame-skins' => [
            'products' => [102],
        ],
        'merchandise' => [
            'products' => [232, 233, 261, 300, 313, 319, 320, 335, 338, 339, 340, 341, 342, 343, 344, 346, 347, 348, 349],
        ],
        'misc' => [
            'products' => [41, 60, 65, 67],
        ],
    ];

    private CookieJar $cookieJar;

    private string $storePricingCurrency = '';

    private int $storePricingExponent = 2;

    /**
     * Execute the job.
     */
    public function handle(RsiDownloadClient $rsiClient): void
    {
        $this->cookieJar = new CookieJar;

        $client = $rsiClient->base()->withOptions([
            'cookies' => $this->cookieJar,
        ]);

        try {
            $this->authenticate($client);
            $this->fetchStoreContext($client);
        } catch (Throwable $e) {
            Log::error('Pledge store authentication failed', [
                'message' => $e->getMessage(),
            ]);
            $this->fail($e);

            return;
        }

        $allSkus = collect();

        foreach (self::BROWSE_QUERIES as $label => $config) {
            $result = $this->fetchCategory($client, $label, $config);

            if ($result === null) {
                Log::error('Pledge store fetch failed, aborting import', [
                    'category' => $label,
                ]);
                $this->fail(new \RuntimeException("Pledge store fetch failed for category '{$label}', aborting to prevent incorrect delisting."));

                return;
            }

            $allSkus = $allSkus->merge($result);
            usleep($this->throttleUs);
        }

        $allSkus = $allSkus->unique('id');

        Log::info('Pledge store fetch completed', [
            'total_skus' => $allSkus->count(),
        ]);

        $this->syncProducts();
        $this->syncSkus($allSkus);
        $this->syncImages($client, $allSkus->pluck('id')->map(fn ($id) => (string) $id)->unique()->values()->toArray());
    }

    /**
     * @throws RequestException
     */
    private function authenticate(mixed $client): void
    {
        $client->post('https://robertsspaceindustries.com/api/account/v2/setAuthToken')->throw();
        $client->post('https://robertsspaceindustries.com/api/ship-upgrades/setContextToken')->throw();
    }

    /**
     * @throws RequestException
     */
    private function fetchStoreContext(mixed $client): void
    {
        $response = $client->post(self::GRAPHQL_URL, [
            'operationName' => 'PlatformQuery',
            'variables' => ['storeFront' => 'pledge'],
            'query' => 'query PlatformQuery($storeFront: String = "pledge") {
                store(name: $storeFront, browse: true) {
                    context {
                        pricing {
                            currencyCode
                            exponent
                            __typename
                        }
                        __typename
                    }
                    __typename
                }
            }',
        ])->throw();

        $pricing = $response->json('data.store.context.pricing');
        $this->storePricingCurrency = $pricing['currencyCode'] ?? 'USD';
        $this->storePricingExponent = $pricing['exponent'] ?? 2;
    }

    /**
     * @param  array{products: array<int>, facet?: string, types?: array<string>}  $config
     * @return Collection<int, array<string, mixed>>|null Returns null on fetch failure to prevent partial-data delisting.
     */
    private function fetchCategory(mixed $client, string $label, array $config): ?Collection
    {
        $allResources = collect();
        $page = 1;

        do {
            $variables = $this->buildVariables($page, $config);
            $response = $client->post(self::GRAPHQL_URL, [
                'operationName' => 'GetBrowseSkusByFilter',
                'variables' => $variables,
                'query' => self::browseQuery(),
            ]);

            if (! $response->successful()) {
                Log::error("Pledge store fetch failed for {$label} page {$page}", [
                    'status' => $response->status(),
                ]);

                return null;
            }

            $listing = $response->json('data.store.listing', []);
            $totalCount = (int) ($listing['totalCount'] ?? 0);
            $resources = collect($listing['resources'] ?? []);

            $allResources = $allResources->merge($resources);

            Log::debug("Pledge store {$label} page {$page}", [
                'fetched' => $resources->count(),
                'total' => $totalCount,
            ]);

            $page++;
        } while ($allResources->count() < $totalCount && $resources->isNotEmpty());

        Log::info("Pledge store fetched {$label}", [
            'count' => $allResources->count(),
        ]);

        return $allResources;
    }

    /**
     * @param  array{products: array<int>, facet?: string, types?: array<string>}  $config
     * @return array<string, mixed>
     */
    private function buildVariables(int $page, array $config): array
    {
        $query = [
            'page' => $page,
            'limit' => self::PAGE_SIZE,
            'sort' => ['field' => 'weight', 'direction' => 'desc'],
        ];

        if (isset($config['types'])) {
            $query['products'] = [
                'filtersFromTags' => [
                    'tagIdentifiers' => [],
                    'facetIdentifiers' => [$config['facet'] ?? ''],
                ],
                'types' => $config['types'],
            ];
        } else {
            $query['skus'] = [
                'filtersFromTags' => [
                    'tagIdentifiers' => [],
                    'facetIdentifiers' => isset($config['facet']) ? [$config['facet']] : [],
                ],
                'products' => $config['products'],
            ];
        }

        return [
            'storeFront' => 'pledge',
            'query' => $query,
        ];
    }

    private static function browseQuery(): string
    {
        return <<<'QUERY'
query GetBrowseSkusByFilter($query: SearchQuery, $storeFront: String = "pledge") {
  store(browse: true, name: $storeFront) {
    listing: search(query: $query) {
      count
      totalCount
      resources {
        id
        slug
        name
        title
        subtitle
        url
        type
        nativePrice {
          amount
          discounted
          discountDescription
          __typename
        }
        price {
          amount
          discounted
          taxDescription
          discountDescription
          __typename
        }
        stock {
          unlimited
          show
          available
          backOrder
          qty
          backOrderQty
          level
          __typename
        }
        tags {
          name
          __typename
        }
        ... on TySku {
          label
          customizable
          isWarbond
          isPackage
          isVip
          isDirectCheckout
          productId
          ships {
            id
            productionStatus
            __typename
          }
          parentProduct {
            slug
            type
            name
            __typename
          }
          publicType {
            code
            label
            __typename
          }
          __typename
        }
        __typename
      }
      __typename
    }
    __typename
  }
}
QUERY;
    }

    private function syncProducts(): void
    {
        foreach (self::BROWSE_QUERIES as $config) {
            foreach ($config['products'] as $productId) {
                PledgeStoreProduct::query()->updateOrCreate(
                    ['cig_id' => $productId],
                    ['title' => ''],
                );
            }
        }
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $apiSkus
     */
    private function syncSkus(Collection $apiSkus): void
    {
        $apiSkus = $apiSkus->unique('id');

        $existingSkus = PledgeStoreSku::query()
            ->whereIn('cig_id', $apiSkus->pluck('id')->map(fn ($id) => (int) $id)->unique()->values()->toArray())
            ->get()
            ->keyBy('cig_id');

        $vehicleIdMap = $this->buildVehicleIdMap($apiSkus);

        $seenCigIds = [];
        $created = 0;
        $updated = 0;
        $historyCreated = 0;

        foreach ($apiSkus as $apiSku) {
            $cigId = (int) $apiSku['id'];
            $seenCigIds[] = $cigId;

            $attributes = $this->mapApiSku($apiSku);

            $existing = $existingSkus->get($cigId);

            if ($existing === null) {
                $existing = PledgeStoreSku::query()->create($attributes);
                $this->recordHistory($existing);
                $created++;
            } else {
                $changed = $this->detectAndApplyChanges($existing, $attributes);

                if ($changed) {
                    $historyCreated++;
                }

                $updated++;
            }

            $this->syncShipRelations($existing, $apiSku['ships'] ?? [], $vehicleIdMap);
        }

        // Mark delisted SKUs as unavailable
        $delisted = PledgeStoreSku::query()
            ->whereNotIn('cig_id', $seenCigIds)
            ->where('stock_available', true)
            ->get();

        foreach ($delisted as $sku) {
            $sku->stock_available = false;
            // Update the raw data to reflect the delisted state
            $currentData = $sku->data?->toArray() ?? [];
            if (isset($currentData['stock'])) {
                $currentData['stock']['available'] = false;
                $currentData['stock']['unlimited'] = false;
                $currentData['stock']['qty'] = 0;
            } else {
                $currentData['stock'] = ['available' => false, 'unlimited' => false, 'qty' => 0];
            }
            $sku->data = $currentData;
            $sku->save();
            $this->recordHistory($sku);
        }

        Log::info('Pledge store sync completed', [
            'created' => $created,
            'updated' => $updated,
            'history_entries' => $historyCreated,
            'delisted' => $delisted->count(),
        ]);
    }

    /**
     * Build a lookup of shipmatrix vehicle ids indexed by cig_id,
     *
     * @param  Collection<int, array<string, mixed>>  $apiSkus
     * @return array<int, int> ship cig_id => shipmatrix_vehicles.id
     */
    private function buildVehicleIdMap(Collection $apiSkus): array
    {
        $shipCigIds = $apiSkus
            ->flatMap(fn (array $sku) => collect($sku['ships'] ?? [])->pluck('id'))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($shipCigIds === []) {
            return [];
        }

        return Vehicle::query()
            ->whereIn('cig_id', $shipCigIds)
            ->pluck('id', 'cig_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Sync the pledge_store_sku_ship join rows for a SKU from its `ships[]` payload.
     *
     * @param  array<int, array{id: mixed, ...}>  $ships
     * @param  array<int, int>  $vehicleIdMap
     */
    private function syncShipRelations(PledgeStoreSku $sku, array $ships, array $vehicleIdMap): void
    {
        $vehicleIds = collect($ships)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->map(fn (int $cigId) => $vehicleIdMap[$cigId] ?? null)
            ->filter()
            ->all();

        $sku->ships()->sync($vehicleIds);
    }

    /**
     * Extract normalized columns and store full API response in `data`.
     *
     * @param  array<string, mixed>  $apiSku
     * @return array<string, mixed>
     */
    private function mapApiSku(array $apiSku): array
    {
        $stock = $apiSku['stock'] ?? [];
        $nativePrice = $apiSku['nativePrice'] ?? [];
        $tags = collect($apiSku['tags'] ?? [])->pluck('name')->values()->toArray();

        return [
            'cig_id' => (int) $apiSku['id'],
            'name' => $apiSku['name'] ?? '',
            'url' => $apiSku['url'] ?? null,
            'product_id' => isset($apiSku['productId']) ? (int) $apiSku['productId'] : null,
            'is_warbond' => (bool) ($apiSku['isWarbond'] ?? false),
            'is_package' => (bool) ($apiSku['isPackage'] ?? false),
            'native_price' => (int) ($nativePrice['amount'] ?? 0),
            'native_discounted' => $nativePrice['discounted'] !== null ? (int) $nativePrice['discounted'] : null,
            'discount_description' => $nativePrice['discountDescription'] ?? null,
            'stock_available' => (bool) ($stock['available'] ?? false),
            'tags' => $tags,
            'data' => $apiSku,
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function detectAndApplyChanges(PledgeStoreSku $sku, array $attributes): bool
    {
        $before = $sku->trackedAttributes();

        $sku->fill([
            'name' => $attributes['name'],
            'url' => $attributes['url'],
            'product_id' => $attributes['product_id'],
            'is_warbond' => $attributes['is_warbond'],
            'is_package' => $attributes['is_package'],
            'data' => $attributes['data'],
        ]);

        $sku->native_price = $attributes['native_price'];
        $sku->native_discounted = $attributes['native_discounted'];
        $sku->discount_description = $attributes['discount_description'];
        $sku->stock_available = $attributes['stock_available'];
        $sku->tags = $attributes['tags'];

        $after = $sku->trackedAttributes();

        $sku->save();

        if ($before === $after) {
            return false;
        }

        $this->recordHistory($sku);

        return true;
    }

    private function recordHistory(PledgeStoreSku $sku): void
    {
        PledgeStoreSkuHistory::query()->create([
            'pledge_store_sku_id' => $sku->id,
            'data' => $sku->data?->toArray(),
        ]);
    }

    /**
     * Fetch image media keys via the GetSkusCardsList GraphQL query
     * and update the `images` column on each SKU.
     *
     * @param  array<int, string>  $skuIds
     */
    private function syncImages(mixed $client, array $skuIds): void
    {
        $batches = collect($skuIds)->chunk(self::SKU_CARD_BATCH_SIZE);
        $updated = 0;

        foreach ($batches as $batch) {
            $response = $client->post(self::GRAPHQL_URL, [
                'operationName' => 'GetSkusCardsList',
                'variables' => [
                    'storeFront' => 'pledge',
                    'query' => [
                        'skus' => [
                            'ids' => $batch->values()->toArray(),
                            'imageComposer' => [
                                ['name' => 'store', 'size' => 'SIZE_900', 'ratio' => 'RATIO_16_9', 'extension' => 'WEBP'],
                            ],
                        ],
                    ],
                ],
                'query' => self::skuCardsQuery(),
            ]);

            if (! $response->successful()) {
                Log::warning('Pledge store image fetch failed', [
                    'status' => $response->status(),
                ]);

                continue;
            }

            $resources = $response->json('data.store.search.resources', []);

            foreach ($resources as $resource) {
                $mediaKey = $this->extractMediaKey($resource);

                PledgeStoreSku::query()
                    ->where('cig_id', (int) $resource['id'])
                    ->update([
                        'images' => $mediaKey !== null ? ['media_key' => $mediaKey] : null,
                    ]);

                $updated++;
            }

            usleep($this->throttleUs);
        }

        Log::info('Pledge store image sync completed', [
            'updated' => $updated,
            'total' => count($skuIds),
        ]);
    }

    /**
     * Extract the media key from the media.thumbnail.slideshow URL.
     * The URL format is: https://media.robertsspaceindustries.com/{media_key}/slideshow.jpg
     *
     * @param  array<string, mixed>  $resource
     */
    private function extractMediaKey(array $resource): ?string
    {
        $slideshow = $resource['media']['thumbnail']['slideshow']
            ?? $resource['media']['thumbnail']['storeSmall']
            ?? null;

        if ($slideshow === null) {
            return null;
        }

        // Extract key from URL like: https://media.robertsspaceindustries.com/utwlo4yrdqw7g/slideshow.jpg
        if (preg_match('#robertsspaceindustries\.com/([^/]+)/#', $slideshow, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private static function skuCardsQuery(): string
    {
        return <<<'QUERY'
query GetSkusCardsList($query: SearchQuery!, $storeFront: String = "pledge") {
  store(name: $storeFront, browse: true) {
    search(query: $query) {
      count
      resources {
        id
        ... on TySku {
          media {
            thumbnail {
              slideshow
              storeSmall
              __typename
            }
            __typename
          }
          __typename
        }
        __typename
      }
      __typename
    }
    __typename
  }
}
QUERY;
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Pledge store import job failed', [
            'message' => $exception->getMessage(),
        ]);
    }
}
