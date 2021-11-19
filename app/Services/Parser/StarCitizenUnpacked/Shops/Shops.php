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
        /*        'Orison_CrusaderTour',
                'Covalex-Orison',
                'Orison_Hospital',
                'NewBab_Hospital',
                'Makau_Orison',*/
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
        $this->mapped = collect();
    }

    public function getData(): Collection
    {
        $this->shops
            ->filter(function (array $shop) {
                return isset($shop['name']) && (strpos($shop['name'], ',') !== false || isset($this->shopNames[$shop['name']]));
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
        ['name' => $name, 'position' => $position] = self::parseShopName($this->shopNames[$shop['name']] ?? $shop['name']);

        return [
            'uuid' => $shop['reference'],
            'name_raw' => $this->shopNames[$shop['name']] ?? $shop['name'],
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
            'position' => $position,
        ];
    }
}
