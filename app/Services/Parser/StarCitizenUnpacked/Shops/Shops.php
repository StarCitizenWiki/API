<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\Shops;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use JsonException;

final class Shops
{
    private Collection $shops;
    private Collection $mapped;

    /**
     * List of game data shop names to "in-game" names
     *
     * @var array|string[]
     */
    private array $shopNames = [
        'TDD_Orison' => 'Trade & Development Division, Orison',
        'TravelerRentals_Orison' => 'Traveler Rentals, Orison',
        'Orison_Spaceport' => 'August Dunlow Spaceport, Orison',
        'AdminOffice_Orison' => 'Admin Office, Orison',
        'NewBabbage_Spaceport' => 'New Babbage Interstellar Spaceport, New Babbage',
        'Shady Glen Farms' => 'Shady Glen Farms, Wala',

        'stanton_4_shubin_001' => 'Shubin Mining Facility SM0-13, Microtech',
        'stanton_4_shubin_002' => 'Shubin Mining Facility SM0-22, Microtech',
        //'stanton_4_shubin_005' => 'Shubin Mining Facility SM0-22, Microtech',

        'GarrityDefense_PortOlisar' => 'Garrity Defense, Port Olisar',
        'LiveFireWeapons_PortOlisar' => 'Live Fire Weapons, Port Olisar',
        'DumpersDepot_PortOlisar' => 'Dumper\'s Depot, Port Olisar',
        'CasabaOutlet_PortOlisar' => 'Casaba Outlet, Port Olisar',


        // Ore Sales
        'MiningKiosks_RS_Stanton1_L1' => 'Ore Sales, HUR-L1',
        'MiningKiosks_RS_Stanton1_L2' => 'Ore Sales, HUR-L2',
        'MiningKiosks_RS_Stanton2_L1' => 'Ore Sales, CRU-L1',
        'MiningKiosks_RS_Stanton3_L1' => 'Ore Sales, ARC-L1',
        'MiningKiosks_RS_Stanton4_L1' => 'Ore Sales, MIC-L1',

        'RS_RefineryStore_Stanton1_L1' => 'Supply Shop, HUR-L1',
        'RS_RefineryStore_Stanton1_L2' => 'Supply Shop, HUR-L2',
        'RS_RefineryStore_Stanton2_L1' => 'Supply Shop, CRU-L1',
        'RS_RefineryStore_Stanton3_L1' => 'Supply Shop, ARC-L1',
        'RS_RefineryStore_Stanton4_L1' => 'Supply Shop, MIC-L1',
    ];

    /**
     * AssaultRifle constructor.
     * @throws FileNotFoundException
     * @throws JsonException
     */
    public function __construct()
    {
        $items = File::get(storage_path(sprintf('app/api/scunpacked-data/shops.json')));
        $this->shops = collect(json_decode($items, true, 512, JSON_THROW_ON_ERROR));
        $this->addShops();
        $this->mapped = collect();
    }

    public function getData(): Collection
    {
        $this->shops
            ->filter(function (array $shop) {
                return isset($shop['name']) &&
                    (strpos($shop['name'], ',') !== false || isset($this->shopNames[$shop['name']]));
            })
            ->filter(function (array $shop) {
                return strpos($shop['name'], 'IAE Expo') === false;
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
                return isset($inventory['displayName'], $inventory['item_reference']);
            })
            ->map(function (array $inventory) {
                return Inventory::map($inventory);
            })
            ->filter(function ($item) {
                return $item !== null;
            })
            ->filter(function ($item) {
                return !empty($item['name']) && strpos($item['name'], '[PH]') === false;
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
        $refineryShops = [
            'CRU-L1',
            'ARC-L1',
            'HUR-L1',
            'HUR-L2',
            'MIC-L2',
        ];

        $cargoShops = [
            'Port Tressler',
            'Everus Harbor',
            'Baijini Point',
        ];

        $refineryRentals = $this->shops->first(function ($value) {
            return isset($value['name']) && $value['name'] === 'Refinery_Rentals';
        });

        if ($refineryRentals !== null) {
            $refinery = collect($refineryShops)->map(function (string $name) use ($refineryRentals) {
                $refineryRentals['name'] = sprintf('Vantage Rentals, %s', $name);

                $hex = bin2hex($name);
                $refineryRentals['reference'] = substr($refineryRentals['reference'], 0, -12) . substr($hex, 0, 12);

                return $refineryRentals;
            });

            $this->shops = $this->shops->concat($refinery);
        }

        $cargoRentals = $this->shops->first(function ($value) {
            return isset($value['name']) && $value['name'] === 'CargoOffice_Rentals';
        });

        if ($cargoRentals !== null) {
            $cargo = collect($cargoShops)->map(function (string $name) use ($cargoRentals) {
                $cargoRentals['name'] = sprintf('Traveler Rentals, %s', $name);

                $hex = bin2hex($name);
                $cargoRentals['reference'] = substr($cargoRentals['reference'], 0, -12) . substr($hex, 0, 12);

                return $cargoRentals;
            });

            $this->shops = $this->shops->concat($cargo);
        }
    }
}
