<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizenUnpacked\Import;

use App\Models\StarCitizenUnpacked\Item;
use App\Models\StarCitizenUnpacked\ShipItem\ShipItem as ShipItemModel;
use App\Services\Parser\StarCitizenUnpacked\ShipItems\ShipItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ShipItems implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private array $supportedTypes = [
        'Cooler',
        'Power Plant',
        'Shield Generator',
        'Quantum Drive',
    ];

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $items = new ShipItem();
        } catch (\JsonException | FileNotFoundException $e) {
            $this->fail($e->getMessage());

            return;
        }

        $items->getData()
            ->filter(function (array $item) {
                return in_array($item['type'], $this->supportedTypes, true);
            })
            ->each(function ($item) {
                if (!Item::query()->where('uuid', $item['uuid'])->exists()) {
                    return;
                }

                $shipItem = ShipItemModel::updateOrCreate([
                    'uuid' => $item['uuid'],
                ], [
                    'grade' => $item['grade'],
                    'class' => $item['class'],
                    'type' => $item['type'],
                    'health' => $item['durability']['health'],
                    'lifetime' => $item['durability']['lifetime'],
                    'power_base' => $item['power']['power_base'],
                    'power_draw' => $item['power']['power_draw'],
                    'thermal_energy_base' => $item['thermal']['thermal_energy_base'],
                    'thermal_energy_draw' => $item['thermal']['thermal_energy_draw'],
                    'cooling_rate' => $item['thermal']['cooling_rate'],
                ]);

                $this->createModel($item, $shipItem);

                $shipItem->translations()->updateOrCreate([
                    'locale_code' => 'en_EN',
                ], [
                    'translation' => $item['description'] ?? '',
                ]);
            });
    }

    private function createModel(array $item, ShipItemModel $shipItem): ?Model
    {
        switch ($item['type']) {
            case 'Cooler':
                return $shipItem->itemSpecification()->updateOrCreate([
                    'uuid' => $item['uuid'],
                ], [
                    'cooling_rate' => $item['cooler']['cooling_rate'],
                    'ship_item_id' => $shipItem->id,
                ]);

            case 'Power Plant':
                return $shipItem->itemSpecification()->updateOrCreate([
                    'uuid' => $item['uuid'],
                ], [
                    'power_output' => $item['power_plant']['power_output'],
                    'ship_item_id' => $shipItem->id,
                ]);

            case 'Shield Generator':
                return $shipItem->itemSpecification()->updateOrCreate([
                    'uuid' => $item['uuid'],
                ], [
                    'health' => $item['shield']['health'],
                    'regeneration' => $item['shield']['regeneration'],
                    'downed_delay' => $item['shield']['downed_delay'],
                    'damage_delay' => $item['shield']['damage_delay'],
                    'min_physical_absorption' => $item['shield']['min_physical_absorption'],
                    'max_physical_absorption' => $item['shield']['max_physical_absorption'],
                    'min_energy_absorption' => $item['shield']['min_energy_absorption'],
                    'max_energy_absorption' => $item['shield']['max_energy_absorption'],
                    'min_distortion_absorption' => $item['shield']['min_distortion_absorption'],
                    'max_distortion_absorption' => $item['shield']['max_distortion_absorption'],
                    'min_thermal_absorption' => $item['shield']['min_thermal_absorption'],
                    'max_thermal_absorption' => $item['shield']['max_thermal_absorption'],
                    'min_biochemical_absorption' => $item['shield']['min_biochemical_absorption'],
                    'max_biochemical_absorption' => $item['shield']['max_biochemical_absorption'],
                    'min_stun_absorption' => $item['shield']['min_stun_absorption'],
                    'max_stun_absorption' => $item['shield']['max_stun_absorption'],
                    'ship_item_id' => $shipItem->id,
                ]);

            case 'Quantum Drive':
                return $shipItem->itemSpecification()->updateOrCreate([
                    'uuid' => $item['uuid'],
                ], [
                    'fuel_rate' => $item['quantum_drive']['fuel_rate'],
                    'jump_range' => $item['quantum_drive']['jump_range'],
                    'standard_speed' => $item['quantum_drive']['standard_speed'],
                    'standard_cooldown' => $item['quantum_drive']['standard_cooldown'],
                    'standard_stage_1_acceleration' => $item['quantum_drive']['standard_stage_1_acceleration'],
                    'standard_stage_2_acceleration' => $item['quantum_drive']['standard_stage_2_acceleration'],
                    'standard_spool_time' => $item['quantum_drive']['standard_spool_time'],
                    'spline_speed' => $item['quantum_drive']['spline_speed'],
                    'spline_cooldown' => $item['quantum_drive']['spline_cooldown'],
                    'spline_stage_1_acceleration' => $item['quantum_drive']['spline_stage_1_acceleration'],
                    'spline_stage_2_acceleration' => $item['quantum_drive']['spline_stage_2_acceleration'],
                    'spline_spool_time' => $item['quantum_drive']['spline_spool_time'],
                    'ship_item_id' => $shipItem->id,
                ]);

            default:
                return null;
        }
    }
}
