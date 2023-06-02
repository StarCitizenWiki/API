<?php

declare(strict_types=1);

namespace App\Services\Parser\SC\Shops;

use App\Services\Parser\SC\Labels;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use JsonException;

final class Shops
{
    private Collection $shops;
    private Collection $mapped;
    private Collection $labels;

    /**
     * List of game data shop names to "in-game" names
     *
     * @var array|string[]
     */
    private array $shopNames = [
        'stanton_4_shubin_001' => 'Shubin Mining Facility SM0-13, Microtech',
        'stanton_4_shubin_002' => 'Shubin Mining Facility SM0-22, Microtech',
        //'stanton_4_shubin_005' => 'Shubin Mining Facility SM0-22, Microtech',
        'Skutters_GrimHex' => 'Skutters, GrimHEX',
    ];

    /**
     * Shops that do not exist anymore
     *
     * @var array|string[]
     */
    private array $ignoredShops = [
        'Refining Terminal, GrimHEX',
        'Mining Kiosks, Port Olisar',
    ];

    /**
     * AssaultRifle constructor.
     * @throws FileNotFoundException
     * @throws JsonException
     */
    public function __construct()
    {
        $items = File::get(storage_path('app/api/scunpacked-data/shops.json'));
        $this->shops = collect(json_decode($items, true, 512, JSON_THROW_ON_ERROR));
        $this->addShops();
        $this->mapped = collect();

        $this->labels = (new Labels())->getData();
    }

    public function getData(): Collection
    {
        $this->shops
            ->filter(function (array $shop) {
                return isset($shop['name']) &&
                    (str_contains($shop['name'], ',') || isset($this->shopNames[$shop['name']]));
            })
            ->filter(function (array $shop) {
                return !in_array($shop['name'], $this->ignoredShops, true);
            })
            ->filter(function (array $shop) {
                return isset($shop['inventory']);
            })
            ->each(function (array $shop) {
                $this->mapped->put($this->shopNames[$shop['name']] ?? $shop['name'], [
                    'shop' => $this->mapShop($shop),
                    'inventory' => $this->mapInventory($shop)
                ]);
            });

        return $this->mapped;
    }

    private function mapShop(array $shop): array
    {
        [
            'name' => $name,
            'position' => $position
        ] = self::parseShopName($this->shopNames[$shop['name']] ?? $shop['name']);

        return [
            'uuid' => $shop['reference'],
            'name_raw' => $this->shopNames[$shop['name']] ?? str_replace(
                ['Hurston HUR', 'Microtech MIC'],
                ['HUR', 'MIC'],
                $shop['name']
            ),
            'name' => $name,
            'position' => $position,
            'profit_margin' => $shop['profitMargin'] ?? 0,
        ];
    }

    private function mapInventory(array $shop): Collection
    {
        return collect($shop['inventory'])
            ->filter(function (array $inventory) {
                return isset($inventory['item_reference']);
            })
            ->map(function (array $inventory) {
                if (!isset($inventory['displayName'])) {
                    $key = $this->labels->first(function ($value, $key) use ($inventory) {
                        return sprintf('item_name_%s', $inventory['name']) === strtolower($key);
                    });

                    if ($key === false || $key === null) {
                        return null;
                    }

                    $inventory['displayName'] = $key;
                }

                return $inventory;
            })
            ->filter(function ($inventory) {
                return $inventory !== false && $inventory !== null;
            })
            ->map(function (array $inventory) {
                return Inventory::map($inventory);
            })
            ->filter(function ($item) {
                return $item !== null;
            })
            ->filter(function ($item) {
                return !empty($item['name']) &&
                    !str_contains($item['name'], '[PH]') &&
                    !str_contains($item['name'], 'igp_');
            })
            ->filter(function ($item) {
                return ($item['base_price'] ?? null) !== null;
            });
    }

    /**
     * Splits the shop name by ',' and trims it
     *
     * @param string $name
     * @return array|string[]
     */
    public static function parseShopName(string $name): array
    {
        $parts = explode(',', $name);
        $parts = array_map('trim', $parts);

        if (count($parts) < 2) {
            return [
                'name' => 'Unknown Shop Name',
                'position' => 'Unknown Shop Position',
            ];
        }

        $position = array_pop($parts);

        return [
            'name' => implode(', ', $parts),
            'position' => str_replace(
                ['Hurston HUR', 'Microtech MIC'],
                ['HUR', 'MIC'],
                $position
            ),
        ];
    }

    /**
     * This manually adds some shops that are only present once in shops.json
     * Refinery Shops and Cargo Offices are present on almost all space stations, but are set only once in shops.json
     * This loads the available inventory for the base shop and adds it as a dedicated shop for each space station
     *
     * Note: The Shop UUID is NOT official but manually generated
     */
    private function addShops(): void
    {
        // Vantage Rentals
        $this->concatShopData(
            ['CRU-L1', 'ARC-L1', 'HUR-L1', 'HUR-L2', 'MIC-L2'],
            'Vantage Rentals',
            'Refinery_Rentals'
        );

        // Traveler Rentals
        $this->concatShopData(
            ['Port Tressler', 'Everus Harbor', 'Baijini Point'],
            'Traveler Rentals',
            'CargoOffice_Rentals'
        );

        // Ellroy's
        $this->concatShopData(
            ['CRU-L1', 'Cloudview Center', 'August Dunlow Spaceport'],
            "Ellroy's",
            'Ellroys'
        );

        // Burrito Bar
        $this->concatShopData(
            ['CRU-L1', 'Cloudview Center', 'August Dunlow Spaceport'],
            'Burrito Bar',
            'BurritoBar'
        );

        // Noodle Bar
        $this->concatShopData(
            ['CRU-L1'],
            'Noodle Bar',
            'NoodleBar'
        );

        // Pizza Bar
        $this->concatShopData(
            ['CRU-L1'],
            'Pizza Bar',
            'PizzaBar'
        );

        // Juice Bar
        $this->concatShopData(
            ['CRU-L1'],
            'Juice Bar',
            'JuiceBar'
        );

        // Juice Bar
        $this->concatShopData(
            ['Vision Center'],
            'Coffee Stand',
            'CoffeeStand'
        );
    }

    private function concatShopData(array $toAdd, string $shopName, string $internalShopName): void
    {
        $shop = $this->shops->first(function ($value) use ($internalShopName) {
            return isset($value['name']) && $value['name'] === $internalShopName;
        });

        if ($shop !== null) {
            $newShops = collect($toAdd)->map(function (string $name) use ($shop, $shopName) {
                $shop['name'] = sprintf('%s, %s', $shopName, $name);

                $hex = bin2hex($name);
                $shop['reference'] = substr($shop['reference'], 0, -12) . substr($hex, 0, 12);

                return $shop;
            });

            $this->shops = $this->shops->concat($newShops);
        }
    }
}
