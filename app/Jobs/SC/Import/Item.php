<?php

declare(strict_types=1);

namespace App\Jobs\SC\Import;

use App\Models\SC\Item\ItemPort;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class Item implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        /** @var \App\Models\SC\Item\Item $itemModel */
        $itemModel = \App\Models\SC\Item\Item::updateOrCreate([
            'uuid' => $this->data['uuid'],
        ], [
            'name' => $this->data['name'],
            'type' => $this->data['type'],
            'sub_type' => $this->data['sub_type'],
            'manufacturer' => $this->data['manufacturer'],
            'size' => $this->data['size'],
            'class_name' => $this->data['class_name'],
            'version' => config('api.sc_data_version'),
        ]);

        $itemModel->dimensions()->updateOrCreate([
            'item_uuid' => $this->data['uuid'],
            'override' => 0,
        ], [
            'width' => $this->data['dimension']['width'],
            'height' => $this->data['dimension']['height'],
            'length' => $this->data['dimension']['length'],

            'volume' => $this->data['volume'],
        ]);

        if ($this->data['dimension_override']['width'] !== null) {
            $itemModel->dimensions()->updateOrCreate([
                'item_uuid' => $this->data['uuid'],
                'override' => 1,
            ], [
                'width' => $this->data['dimension_override']['width'],
                'height' => $this->data['dimension_override']['height'],
                'length' => $this->data['dimension_override']['length'],
            ]);
        }

        if ($this->data['inventory_container']['scu'] !== null) {
            $itemModel->container()->updateOrCreate([
                'item_uuid' => $this->data['uuid'],
            ], [
                'width' => $this->data['inventory_container']['width'],
                'height' => $this->data['inventory_container']['height'],
                'length' => $this->data['inventory_container']['length'],
                'scu' => $this->data['inventory_container']['scu'],
                'unit' => $this->data['inventory_container']['unit'],
            ]);
        }

        if (!empty($this->data['ports'])) {
            collect($this->data['ports'])->each(function (array $port) use ($itemModel) {
                /** @var ItemPort $port */
                $itemModel->ports()->updateOrCreate([
                    'name' => $port['name'],
                ], [
                    'display_name' => $port['display_name'],
                    'equipped_item_uuid' => $port['equipped_item_uuid'],
                    'min_size' => $port['min_size'],
                    'max_size' => $port['max_size'],
                    'position' => $port['position'],
                ]);
            });
        }

        if (!empty($this->data['power'])) {
            $itemModel->powerData()->updateOrCreate([
                'item_uuid' => $this->data['uuid'],
            ], [
                'power_base' => $this->data['power']['power_base'] ?? null,
                'power_draw' => $this->data['power']['power_draw'] ?? null,
                'throttleable' => $this->data['power']['throttleable'] ?? null,
                'overclockable' => $this->data['power']['overclockable'] ?? null,
                'overclock_threshold_min' => $this->data['power']['overclock_threshold_min'] ?? null,
                'overclock_threshold_max' => $this->data['power']['overclock_threshold_max'] ?? null,
                'overclock_performance' => $this->data['power']['overclock_performance'] ?? null,
                'overpower_performance' => $this->data['power']['overpower_performance'] ?? null,
                'power_to_em' => $this->data['power']['power_to_em'] ?? null,
                'decay_rate_em' => $this->data['power']['decay_rate_em'] ?? null,
            ]);
        }

        if (!empty($this->data['heat'])) {
            $itemModel->heatData()->updateOrCreate([
                'item_uuid' => $this->data['uuid'],
            ], [
                'temperature_to_ir' => $this->data['heat']['temperature_to_ir'] ?? null,
                'overpower_heat' => $this->data['heat']['overpower_heat'] ?? null,
                'overclock_threshold_min' => $this->data['heat']['overclock_threshold_min'] ?? null,
                'overclock_threshold_max' => $this->data['heat']['overclock_threshold_max'] ?? null,
                'thermal_energy_base' => $this->data['heat']['thermal_energy_base'] ?? null,
                'thermal_energy_draw' => $this->data['heat']['thermal_energy_draw'] ?? null,
                'thermal_conductivity' => $this->data['heat']['thermal_conductivity'] ?? null,
                'specific_heat_capacity' => $this->data['heat']['specific_heat_capacity'] ?? null,
                'mass' => $this->data['heat']['mass'] ?? null,
                'surface_area' => $this->data['heat']['surface_area'] ?? null,
                'start_cooling_temperature' => $this->data['heat']['start_cooling_temperature'] ?? null,
                'max_cooling_rate' => $this->data['heat']['max_cooling_rate'] ?? null,
                'max_temperature' => $this->data['heat']['max_temperature'] ?? null,
                'min_temperature' => $this->data['heat']['min_temperature'] ?? null,
                'overheat_temperature' => $this->data['heat']['overheat_temperature'] ?? null,
                'recovery_temperature' => $this->data['heat']['recovery_temperature'] ?? null,
                'misfire_min_temperature' => $this->data['heat']['misfire_min_temperature'] ?? null,
                'misfire_max_temperature' => $this->data['heat']['misfire_max_temperature'] ?? null,
            ]);
        }

        if (!empty($this->data['distortion'])) {
            $itemModel->distortionData()->updateOrCreate([
                'item_uuid' => $this->data['uuid'],
            ], [
                'decay_rate' => $this->data['distortion']['decay_rate'] ?? null,
                'maximum' => $this->data['distortion']['maximum'] ?? null,
                'overload_ratio' => $this->data['distortion']['overload_ratio'] ?? null,
                'recovery_ratio' => $this->data['distortion']['recovery_ratio'] ?? null,
                'recovery_time' => $this->data['distortion']['recovery_time'] ?? null,
            ]);
        }

        if (!empty($this->data['durability'])) {
            $itemModel->durabilityData()->updateOrCreate([
                'item_uuid' => $this->data['uuid'],
            ], [
                'health' => $this->data['durability']['health'] ?? null,
                'max_lifetime' => $this->data['durability']['max_lifetime'] ?? null,
            ]);
        }
    }
}
