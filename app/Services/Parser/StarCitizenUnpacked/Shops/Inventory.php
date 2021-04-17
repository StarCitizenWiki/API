<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\Shops;

final class Inventory
{
    public static function map(array $inventory, string $shop): array
    {
        ['name' => $shop, 'position' => $position] = self::parseShopName($shop);

        return [
            'Shop' => $shop,
            'Ort' => $position,
            'Name' => $inventory['displayName'],
            'Preis' => str_replace('.', ',', $inventory['basePrice']),
            'Preisoffset' => $inventory['basePriceOffsetPercentage'] ?? 0,
            'Rabatt' => $inventory['maxDiscountPercentage'] ?? 0,
            'Premium' => $inventory['maxPremiumPercentage'] ?? 0,
            'Inventar' => $inventory['inventory'] ?? 0,
            'Maximalbestand' => $inventory['maxInventory'] ?? 0,
            'Wiederauffüllungsrate' => $inventory['refreshRatePercentagePerMinute'] ?? 0,
            'Typ' => $inventory['type'] ?? 'Unknown Type',
            'Kaufbar' => $inventory['shopSellsThis'] ?? false,
            'Mietbar' => $inventory['shopRentThis'] ?? false,
            'Verkaufbar' => $inventory['shopBuysThis'] ?? false,
            'Spielversion' => config('api.sc_data_version')
        ];
    }

    public static function parseShopName(string $name): array
    {
        $parts = explode(',', $name);
        $parts = array_map('trim', $parts);

        if (count($parts) !== 2) {
            return [
                'name' => 'Unknown Shop Name',
                'position' => 'Unknown Shop Position',
            ];
        }

        return [
            'name' => $parts[0],
            'position' => $parts[1],
        ];
    }
}
