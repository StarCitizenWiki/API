<?php

declare(strict_types=1);

namespace App\Services\Parser\SC;

final class MiningModule extends AbstractCommodityItem
{
    public function getData(): ?array
    {
        $attachDef = $this->getAttachDef();

        if ($attachDef === null) {
            return null;
        }

        $description = $this->getDescription($attachDef);

        $data = $this->tryExtractDataFromDescription($description, [
            'Item Type' => 'item_type',

            'All Charge Rates' => 'all_charge_rates',
            'Collection Point Radius' => 'collection_point_radius',
            'Instability' => 'instability',
            'Module Slots' => 'module_slots',
            'Module' => 'module',
            'Optimal Charge Rate' => 'optimal_charge_rate',
            'Optimal Charge Window' => 'optimal_charge_window',
            'Overcharge Rate' => 'overcharge_rate',
            'Resistance' => 'resistance',
            'Shatter Damage' => 'shatter_damage',
            'Throttle Responsiveness Delay' => 'throttle_responsiveness_delay',
            'Throttle Speed' => 'throttle_speed',
            'Extraction Rate' => 'extraction_rate',
            'Inert Materials' => 'inert_materials',
        ]);

        return [
            'uuid' => $this->getUUID(),
            'description' => $this->cleanString(trim($data['description'] ?? $description)),
            'type' => $data['item_type'] ?? 'Unknown Type',

            'modifiers' => [
                'all_charge_rates' => $data['all_charge_rates'] ?? null,
                'collection_point_radius' => $data['collection_point_radius'] ?? null,
                'instability' => $data['instability'] ?? null,
                'module' => $data['module'] ?? null,
                'optimal_charge_rate' => $data['optimal_charge_rate'] ?? null,
                'optimal_charge_window' => $data['optimal_charge_window'] ?? null,
                'overcharge_rate' => $data['overcharge_rate'] ?? null,
                'resistance' => $data['resistance'] ?? null,
                'shatter_damage' => $data['shatter_damage'] ?? null,
                'throttle_responsiveness_delay' => $data['throttle_responsiveness_delay'] ?? null,
                'throttle_speed' => $data['throttle_speed'] ?? null,
                'extraction_rate' => $data['extraction_rate'] ?? null,
                'inert_materials' => $data['inert_materials'] ?? null,
            ],
        ];
    }
}
