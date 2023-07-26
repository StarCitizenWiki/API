<?php

declare(strict_types=1);

namespace App\Services\Parser\SC\Shops;

final class Inventory
{
    /**
     * Shop items that do not have an accompanying .json file
     */
    public const EXTRA_TYPES = [
        'Mineral',
        'AgriculturalSupply',
        'Natural',
        'Halogen',
        'Scrap',
        'Vice',
        'Food',
        'ConsumerGood',
        'Waste',
        'Metal',
        'Gas',
        'ProcessedGood',
        'MedicalSupplies',

        'Vehicle',
        'GroundVehicle',
    ];

    public static function map(array $inventory): array
    {
        ['type' => $type, 'subType' => $subType] = self::getType($inventory);

        $data = [
            'uuid' => $inventory['item_reference'],
            'node_uuid' => $inventory['node_reference'],
            'name' => $inventory['displayName'] ?? $inventory['name'] ?? null,
            'type' => $type,
            'sub_type' => $subType,
            'base_price' => $inventory['basePrice'] ?? null,
            'base_price_offset' => $inventory['basePriceOffsetPercentage'] ?? 0,
            'max_discount' => $inventory['maxDiscountPercentage'] ?? 0,
            'max_premium' => $inventory['maxPremiumPercentage'] ?? 0,
            'inventory' => $inventory['inventory'] ?? 0,
            'optimal_inventory' => $inventory['optimalInventoryLevel'] ?? 0,
            'max_inventory' => $inventory['maxInventory'] ?? 0,
            'auto_restock' => $inventory['autoRestock'] ?? false,
            'auto_consume' => $inventory['autoConsume'] ?? false,
            'refresh_rate' => $inventory['refreshRatePercentagePerMinute'] ?? 0,
            'buyable' => $inventory['shopSellsThis'] ?? false,
            'sellable' => $inventory['shopBuysThis'] ?? false,
            'rentable' => $inventory['shopRentThis'] ?? false,
        ];

        if (isset($inventory['rentalTemplates']) && !empty($inventory['rentalTemplates'])) {
            $rentalData = collect($inventory['rentalTemplates'])
                ->filter(function (array $rental) {
                    return isset($rental['RentalDuration']);
                })
                ->mapWithKeys(function (array $rental) {
                    return [
                        "percentage_{$rental['RentalDuration']}" => $rental['PercentageOfSalePrice'],
                    ];
                })
                ->toArray();

            if (!isset($rentalData['percentage_2'])) {
                $data['rental'] = $rentalData;
            }
        }

        return $data;
    }

    private static function getType(array $inventory): array
    {
        $out = [
            'type' => $inventory['type'] ?? null,
            'subType' => $inventory['subType'] ?? null,
        ];

        if (isset($inventory['filename'])) {
            switch (true) {
                case strpos($inventory['filename'], 'Minerals') !== false:
                    $type = 'Mineral';
                    break;
                case strpos($inventory['filename'], 'AgriculturalSupplies') !== false:
                    $type = 'AgriculturalSupply';
                    break;
                case strpos($inventory['filename'], 'Natural') !== false:
                    $type = 'Natural';
                    break;
                case strpos($inventory['filename'], 'Halogens') !== false:
                    $type = 'Halogen';
                    break;
                case strpos($inventory['filename'], 'Scrap') !== false:
                    $type = 'Scrap';
                    break;
                case strpos($inventory['filename'], 'Vice') !== false:
                    $type = 'Vice';
                    break;
                case strpos($inventory['filename'], 'Food') !== false:
                    $type = 'Food';
                    break;
                case strpos($inventory['filename'], 'ConsumerGoods') !== false:
                    $type = 'ConsumerGood';
                    break;
                case strpos($inventory['filename'], 'Waste') !== false:
                    $type = 'Waste';
                    break;
                case strpos($inventory['filename'], 'Metals') !== false:
                    $type = 'Metal';
                    break;
                case strpos($inventory['filename'], 'ProcessedGoods') !== false:
                    $type = 'ProcessedGood';
                    break;
                case strpos($inventory['filename'], 'Gas') !== false:
                    $type = 'Gas';
                    break;
                case strpos($inventory['filename'], 'Spaceships') !== false:
                    $type = 'Vehicle';
                    break;
                case strpos($inventory['filename'], 'GroundVehicles') !== false:
                    $type = 'GroundVehicle';
                    break;
                case strpos($inventory['filename'], 'MedicalSupplies') !== false:
                    $type = 'MedicalSupplies';
                    break;

                default:
                    $type = null;
                    break;
            }

            if ($type === 'Mineral' && strpos($inventory['filename'], 'UnrefinedMinerals') !== false) {
                $subType = 'UnrefinedMineral';
            }

            if ($type === 'Metal' && strpos($inventory['filename'], 'UnrefinedMetals') !== false) {
                $subType = 'UnrefinedMetal';
            }

            return [
                'type' => $type ?? $out['type'],
                'subType' => $out['subType'] ?? $subType ?? null,
            ];
        }

        return $out;
    }
}
