<?php

declare(strict_types=1);

namespace App\Services\Parser\SC;

final class MiningLaser extends AbstractCommodityItem
{
    public function getData(): ?array
    {
        $attachDef = $this->getAttachDef();
        $laser = $this->get('SEntityComponentMiningLaserParams', []);

        if ($attachDef === null || $laser === null) {
            return null;
        }

        $description = $this->getDescription($attachDef);

        $data = $this->tryExtractDataFromDescription($description, [
            'Item Type' => 'item_type',
            'Optimal Range' => 'optimal_range',
            'Maximum Range' => 'maximum_range',
            'Power Transfer' => 'power_transfer',
            'Collection Throughput' => 'collection_throughput',
            'Extraction Throughput' => 'extraction_throughput',
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
        ]);

        $optimal_range = str_replace('m', '', $data['optimal_range'] ?? '');
        $maximum_range = str_replace('m', '', $data['maximum_range'] ?? '');
        $extraction = str_replace('SCU/s', '', $data['extraction_throughput'] ?? $data['collection_throughput'] ?? '');

        return [
            'uuid' => $this->getUUID(),
            'description' => $this->cleanString(trim($data['description'] ?? $description)),
            'item_type' => $data['item_type'] ?? null,

            'power_transfer' => $data['power_transfer'] ?? null,
            'optimal_range' => empty($optimal_range) ? null : $optimal_range,
            'maximum_range' => empty($maximum_range) ? null : $maximum_range,
            'extraction_throughput' => empty($extraction) ? null : $extraction,
            'module_slots' => $data['module_slots'] ?? null,
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
            ],
        ];
    }
}
