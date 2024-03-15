<?php

declare(strict_types=1);

namespace App\Jobs\SC\Import;

use App\Models\SC\Item\Interaction;
use App\Models\SC\Item\ItemPort;
use App\Models\SC\Item\ItemPortType;
use App\Models\SC\Item\ItemSubType;
use App\Models\SC\Item\ItemType;
use App\Models\SC\Item\Tag;
use App\Models\SC\Manufacturer;
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
     */
    public function handle(): void
    {
        $manufacturer = Manufacturer::updateOrCreate([
            'uuid' => $this->data['manufacturer']['uuid'],
        ], [
            'name' => $this->data['manufacturer']['name'],
            'code' => $this->data['manufacturer']['code'],
        ]);

        /** @var \App\Models\SC\Item\Item $itemModel */
        $itemModel = \App\Models\SC\Item\Item::query()->withoutGlobalScopes()->updateOrCreate([
            'uuid' => $this->data['uuid'],
        ], [
            'name' => $this->data['name'],
            'type' => $this->data['type'],
            'sub_type' => $this->data['sub_type'],
            'manufacturer_description' => $this->data['manufacturer_description'],
            'size' => $this->data['size'],
            'class_name' => $this->data['class_name'],
            'mass' => $this->data['mass'],
            'version' => config('api.sc_data_version'),
            'manufacturer_id' => $manufacturer->id,
        ]);

        if (! empty($this->data['description'])) {
            $itemModel->translations()->updateOrCreate([
                'locale_code' => 'en_EN',
            ], [
                'translation' => $this->data['description'],
            ]);
        }

        $data = collect($this->data['description_data'] ?? [])->filter(function ($value, $key) {
            return $key !== 'description';
        })->each(function ($value, $key) use ($itemModel) {
            $itemModel->descriptionData()->updateOrCreate([
                'name' => trim($key),
            ], [
                'value' => trim($value),
            ]);
        })->keys();
        $itemModel->descriptionData()->whereNotIn('name', $data)->delete();

        $this->createDimensionModel($itemModel);
        $this->createContainerModel($itemModel);
        $this->createPorts($itemModel);
        $this->createPowerModel($itemModel);
        $this->createHeatModel($itemModel);
        $this->createDistortionModel($itemModel);
        $this->createDurabilityModel($itemModel);
        $this->addTags($itemModel, $this->data, 'tags');
        $this->addTags($itemModel, $this->data, 'required_tags', true);
        $this->addInteractions($itemModel, $this->data);
    }

    private function createDimensionModel(\App\Models\SC\Item\Item $itemModel): void
    {
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
    }

    private function createContainerModel(\App\Models\SC\Item\Item $itemModel): void
    {
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
    }

    private function createPorts(\App\Models\SC\Item\Item $itemModel): void
    {
        if (! empty($this->data['ports'])) {
            $availablePorts = collect($this->data['ports'])->each(function (array $port) use ($itemModel) {
                /** @var ItemPort $port */
                $portModel = $itemModel->ports()->updateOrCreate([
                    'name' => $port['name'],
                ], [
                    'display_name' => $port['display_name'],
                    'equipped_item_uuid' => $port['equipped_item_uuid'],
                    'min_size' => $port['min_size'],
                    'max_size' => $port['max_size'],
                    'position' => $port['position'],
                ]);

                $this->addTags($portModel, $port, 'tags');
                $this->addTags($portModel, $port, 'required_tags', true);

                $types = collect($port['compatible_types'])
                    ->map(function (array $type) {
                        /** @var ItemType $typeModel */
                        $typeModel = ItemType::query()->firstOrCreate([
                            'type' => $type['type'],
                        ]);

                        $type['id'] = $typeModel->id;

                        return $type;
                    })
                    ->each(function (array $type) use ($portModel) {
                        /** @var ItemPort $portModel */
                        $portModelType = $portModel->compatibleTypes()->updateOrCreate([
                            'item_type_id' => $type['id'],
                        ]);

                        $subTypes = collect($type['sub_types'])
                            ->map(function (string $subType) {
                                /** @var ItemSubType $typeModel */
                                $typeModel = ItemSubType::query()->firstOrCreate([
                                    'sub_type' => $subType,
                                ]);

                                return $typeModel->id;
                            })
                            ->each(function (int $id) use ($portModelType) {
                                /** @var ItemPortType $portModelType */
                                $portModelType->subTypes()->updateOrCreate([
                                    'sub_type_id' => $id,
                                ]);
                            });

                        /** @var ItemPortType $portModelType */
                        $portModelType->subTypes()->whereNotIn('sub_type_id', $subTypes)->delete();
                    })
                    ->pluck('id');

                /** @var ItemPort $portModel */
                $portModel->compatibleTypes()->whereNotIn('item_type_id', $types)->delete();
            })
                ->pluck('name');

            // Remove old ports
            $itemModel->ports()->whereNotIn('name', $availablePorts)->delete();
        }
    }

    private function createPowerModel(\App\Models\SC\Item\Item $itemModel): void
    {
        if (! empty($this->data['power'])) {
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
    }

    private function createHeatModel(\App\Models\SC\Item\Item $itemModel): void
    {
        if (! empty($this->data['heat'])) {
            $itemModel->heatData()->updateOrCreate([
                'item_uuid' => $this->data['uuid'],
            ], [
                'temperature_to_ir' => $this->data['heat']['temperature_to_ir'] ?? null,
                'ir_temperature_threshold' => $this->data['heat']['ir_temperature_threshold'] ?? null,
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
    }

    private function createDistortionModel(\App\Models\SC\Item\Item $itemModel): void
    {
        if (! empty($this->data['distortion'])) {
            $itemModel->distortionData()->updateOrCreate([
                'item_uuid' => $this->data['uuid'],
            ], [
                'decay_delay' => $this->data['distortion']['decay_delay'] ?? null,
                'decay_rate' => $this->data['distortion']['decay_rate'] ?? null,
                'maximum' => $this->data['distortion']['maximum'] ?? null,
                'warning_ratio' => $this->data['distortion']['warning_ratio'] ?? null,
                'overload_ratio' => $this->data['distortion']['overload_ratio'] ?? null,
                'recovery_ratio' => $this->data['distortion']['recovery_ratio'] ?? null,
                'recovery_time' => $this->data['distortion']['recovery_time'] ?? null,
            ]);
        }
    }

    private function createDurabilityModel(\App\Models\SC\Item\Item $itemModel): void
    {
        if (! empty($this->data['durability'])) {
            $itemModel->durabilityData()->updateOrCreate([
                'item_uuid' => $this->data['uuid'],
            ], [
                'health' => $this->data['durability']['health'] ?? null,
                'max_lifetime' => $this->data['durability']['lifetime'] ?? null,
                'salvageable' => $this->data['durability']['salvageable'] ?? null,
                'repairable' => $this->data['durability']['repairable'] ?? null,
            ]);
        }
    }

    private function addTags($model, $data, string $key, bool $isRequiredTag = false): void
    {
        if (empty($data[$key])) {
            return;
        }

        $tags = collect(explode(' ', $data[$key]))
            ->map('trim')
            ->map('strtolower')
            ->map(function ($tag) {
                $tag = Tag::query()->firstOrCreate([
                    'name' => $tag,
                ]);

                return $tag->id;
            });

        $model->tags()->syncWithPivotValues($tags, ['is_required_tag' => $isRequiredTag]);
    }

    private function addInteractions($model, $data): void
    {
        if (empty($data['interactions'])) {
            return;
        }

        $interactions = collect($data['interactions'])
            ->map(function ($interaction) {
                $interaction = Interaction::query()->firstOrCreate([
                    'name' => $interaction,
                ]);

                return $interaction->id;
            });

        $model->interactions()->sync($interactions);
    }
}
