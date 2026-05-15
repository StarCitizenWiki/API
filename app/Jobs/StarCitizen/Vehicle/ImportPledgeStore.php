<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Vehicle;

use App\Models\StarCitizen\PledgeStore\PledgeStoreProduct;
use App\Models\StarCitizen\PledgeStore\PledgeStoreSku;
use App\Models\StarCitizen\PledgeStore\PledgeStoreSkuHistory;
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

    private const int THROTTLE_MICROSECONDS = 500_000;

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
            usleep(self::THROTTLE_MICROSECONDS);
        }

        $allSkus = $allSkus->unique('id');

        Log::info('Pledge store fetch completed', [
            'total_skus' => $allSkus->count(),
        ]);

        $this->syncProducts();
        $this->syncSkus($allSkus);
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

                continue;
            }

            $changed = $this->detectAndApplyChanges($existing, $attributes);

            if ($changed) {
                $historyCreated++;
            }

            $updated++;
        }

        // Mark delisted SKUs as unavailable
        $delisted = PledgeStoreSku::query()
            ->whereNotIn('cig_id', $seenCigIds)
            ->where('stock_available', true)
            ->get();

        foreach ($delisted as $sku) {
            $sku->stock_available = false;
            $sku->stock_unlimited = false;
            $sku->stock_qty = 0;
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
     * @param  array<string, mixed>  $apiSku
     * @return array<string, mixed>
     */
    private function mapApiSku(array $apiSku): array
    {
        $stock = $apiSku['stock'] ?? [];
        $nativePrice = $apiSku['nativePrice'] ?? [];
        $parentProduct = $apiSku['parentProduct'] ?? [];
        $publicType = $apiSku['publicType'] ?? [];
        $tags = collect($apiSku['tags'] ?? [])->pluck('name')->values()->toArray();
        $ships = $apiSku['ships'] ?? [];

        return [
            'cig_id' => (int) $apiSku['id'],
            'slug' => $apiSku['slug'] ?? null,
            'name' => $apiSku['name'] ?? '',
            'title' => $apiSku['title'] ?? null,
            'subtitle' => $apiSku['subtitle'] ?? null,
            'url' => $apiSku['url'] ?? null,
            'product_id' => isset($apiSku['productId']) ? (int) $apiSku['productId'] : null,
            'public_type_code' => $publicType['code'] ?? null,
            'parent_product_slug' => $parentProduct['slug'] ?? null,
            'parent_product_name' => $parentProduct['name'] ?? null,
            'is_warbond' => (bool) ($apiSku['isWarbond'] ?? false),
            'is_package' => (bool) ($apiSku['isPackage'] ?? false),
            'is_vip' => (bool) ($apiSku['isVip'] ?? false),
            'customizable' => (bool) ($apiSku['customizable'] ?? false),
            'native_price' => (int) ($nativePrice['amount'] ?? 0),
            'native_discounted' => $nativePrice['discounted'] !== null ? (int) $nativePrice['discounted'] : null,
            'discount_description' => $nativePrice['discountDescription'] ?? null,
            'stock_available' => (bool) ($stock['available'] ?? false),
            'stock_unlimited' => (bool) ($stock['unlimited'] ?? false),
            'stock_back_order' => (bool) ($stock['backOrder'] ?? false),
            'stock_qty' => (int) ($stock['qty'] ?? 0),
            'stock_back_order_qty' => (int) ($stock['backOrderQty'] ?? 0),
            'stock_level' => $stock['level'] ?? null,
            'tags' => $tags,
            'ships' => $ships,
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function detectAndApplyChanges(PledgeStoreSku $sku, array $attributes): bool
    {
        $before = $sku->trackedAttributes();

        $sku->fill([
            'slug' => $attributes['slug'],
            'name' => $attributes['name'],
            'title' => $attributes['title'],
            'subtitle' => $attributes['subtitle'],
            'url' => $attributes['url'],
            'product_id' => $attributes['product_id'],
            'public_type_code' => $attributes['public_type_code'],
            'parent_product_slug' => $attributes['parent_product_slug'],
            'parent_product_name' => $attributes['parent_product_name'],
            'is_warbond' => $attributes['is_warbond'],
            'is_package' => $attributes['is_package'],
            'is_vip' => $attributes['is_vip'],
            'customizable' => $attributes['customizable'],
            'stock_unlimited' => $attributes['stock_unlimited'],
            'stock_back_order' => $attributes['stock_back_order'],
            'stock_back_order_qty' => $attributes['stock_back_order_qty'],
            'ships' => $attributes['ships'],
        ]);

        $sku->native_price = $attributes['native_price'];
        $sku->native_discounted = $attributes['native_discounted'];
        $sku->discount_description = $attributes['discount_description'];
        $sku->stock_available = $attributes['stock_available'];
        $sku->stock_qty = $attributes['stock_qty'];
        $sku->stock_level = $attributes['stock_level'];
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
            'native_price' => $sku->native_price,
            'native_discounted' => $sku->native_discounted,
            'discount_description' => $sku->discount_description,
            'stock_available' => $sku->stock_available,
            'stock_level' => $sku->stock_level,
            'stock_qty' => $sku->stock_qty,
            'tags' => $sku->tags,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Pledge store import job failed', [
            'message' => $exception->getMessage(),
        ]);
    }
}
