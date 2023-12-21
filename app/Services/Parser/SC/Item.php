<?php

declare(strict_types=1);

namespace App\Services\Parser\SC;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use JsonException;

/**
 * An item can be any entity like a weapon, food, ship doors, paints, etc.
 */
final class Item extends AbstractCommodityItem
{
    private Collection $manufacturers;

    private array $manufacturerFixes = [
        'Lighting Power Ltd.' => 'Lightning Power Ltd.',
        'MISC' => 'Musashi Industrial & Starflight Concern',
        'Nav-E7' => 'Nav-E7 Gadgets',
        'RSI' => 'Roberts Space Industries',
        'YORM' => 'Yorm',
    ];

    /**
     * @param string $fileName
     * @param Collection $labels
     * @param Collection $manufacturers
     * @throws FileNotFoundException
     * @throws JsonException
     */
    public function __construct(string $fileName, Collection $labels, Collection $manufacturers)
    {
        $items = File::get($fileName);
        $this->item = collect(json_decode($items, true, 512, JSON_THROW_ON_ERROR));
        $this->labels = $labels;
        $this->manufacturers = $manufacturers;
    }

    public function getData(): ?array
    {
        $attachDef = $this->getAttachDef();

        if ($attachDef === null || !Arr::has($this->item, 'Raw.Entity.ClassName')) {
            return null;
        }

        $name = $this->getName($attachDef, '<= PLACEHOLDER =>');

        $manufacturer = $this->getManufacturer($attachDef, $this->manufacturers);

        // Use manufacturer from description if available
        $descriptionData = $this->tryExtractDataFromDescription(
            $this->getDescription($attachDef),
            [
                'Action Figure' => 'Action Figure',
                'All Charge Rates' => 'All Charge Rates',
                'Area of Effect' => 'Area of Effect',
                'Armor' => 'Armor',
                'Attachment Point' => 'Attachment Point',
                'Attachments' => 'Attachments',
                'Badge' => 'Badge',
                'Box' => 'Box',
                'Capacity' => 'Capacity',
                'Carrying Capacity' => 'Carrying Capacity',
                'Catastrophic Charge Rate' => 'Catastrophic Charge Rate',
                'Class' => 'Class',
                'Cluster Modifier' => 'Cluster Modifier',
                'Collection Point Radius' => 'Collection Point Radius',
                'Collection Throughput' => 'Collection Throughput',
                'Core Compatibility' => 'Core Compatibility',
                'Damage Reduction' => 'Damage Reduction',
                'Damage Type' => 'Damage Type',
                'Duration' => 'Duration',
                'Effect' => 'Effect',
                'Effective Range' => 'Effective Range',
                'Effects' => 'Effects',
                'Extraction Laser Power' => 'Extraction Laser Power',
                'Extraction Rate' => 'Extraction Rate',
                'Extraction Throughput' => 'Extraction Throughput',
                'Grade' => 'Grade',
                'HEI' => 'HEI',
                'Inert Materials' => 'Inert Materials',
                'Instability' => 'Instability',
                'Item Type' => 'Item Type',
                'Laser Instability' => 'Laser Instability',
                'Magazine Size' => 'Magazine Size',
                'Magnification' => 'Magnification',
                'Manufacturer' => 'Manufacturer',
                'Maximum Range' => 'Maximum Range',
                'Mining Laser Power' => 'Mining Laser Power',
                'Missiles' => 'Missiles',
                'Model' => 'Model',
                'Module Slots' => 'Module Slots',
                'Module' => 'Module',
                'NDR' => 'NDR',
                'Optimal Charge Rate' => 'Optimal Charge Rate',
                'Optimal Charge Window Rate' => 'Optimal Charge Window Rate',
                'Optimal Charge Window Size' => 'Optimal Charge Window Size',
                'Optimal Charge Window' => 'Optimal Charge Window',
                'Optimal Range' => 'Optimal Range',
                'Overcharge Rate' => 'Overcharge Rate',
                'Plant' => 'Plant',
                'Plaque' => 'Plaque',
                'Poster' => 'Poster',
                'Power Transfer' => 'Power Transfer',
                'Rate Of Fire' => 'Rate Of Fire',
                'Resistance' => 'Resistance',
                'Schematic' => 'Schematic',
                'Shatter Damage' => 'Shatter Damage',
                'Size' => 'Size',
                'Spaceglobe' => 'Spaceglobe',
                'Temp. Rating' => 'Temp. Rating',
                'Throttle Responsiveness Delay' => 'Throttle Responsiveness Delay',
                'Throttle Speed' => 'Throttle Speed',
                'Tracking Signal' => 'Tracking Signal',
                'Trophy' => 'Trophy',
                'Type' => 'Type',
                'Uses' => 'Uses',
            ]
        );

        if (!empty($descriptionData['manufacturer'])) {
            $descriptionData['manufacturer'] = $this->manufacturerFixes[$descriptionData['manufacturer']] ?? $descriptionData['manufacturer'];
        }

        $sizes = $this->get('SAttachableComponentParams.AttachDef.inventoryOccupancyDimensions', []);
        $sizeOverride = $this->get('SAttachableComponentParams.AttachDef.inventoryOccupancyDimensionsUIOverride.Vec3', []);

        return [
            'uuid' => $this->getUUID(),
            'name' => $name,
            'type' => $attachDef['Type'],
            'tags' => $attachDef['Tags'] ?? null,
            'required_tags' => $attachDef['RequiredTags'] ?? null,
            'sub_type' => $attachDef['SubType'],
            'description' => $descriptionData['description'] ?? null,
            'manufacturer_description' => $descriptionData['manufacturer'] ?? null,
            'manufacturer' => $manufacturer,
            'size' => $attachDef['Size'],
            'class_name' => strtolower(Arr::get($this->item, 'Raw.Entity.ClassName')),

            'description_data' => $descriptionData,

            'dimension' => [
                'width' => $sizes['x'] ?? 0,
                'height' => $sizes['z'] ?? 0,
                'length' => $sizes['y'] ?? 0,
            ],

            'dimension_override' => [
                'width' => $sizeOverride['x'] ?? null,
                'height' => $sizeOverride['z'] ?? null,
                'length' => $sizeOverride['y'] ?? null,
            ],

            'volume' => $this->convertToSCU($this->get('SAttachableComponentParams.AttachDef.inventoryOccupancyVolume', []))[1],

            'inventory_container' => $this->getInventoryContainer(),

            'mass' => $this->get('SEntityPhysicsControllerParams.PhysType.SEntityRigidPhysicsControllerParams.Mass', 0),

            'ports' => $this->mapPorts(),
            'port_loadout' => $this->mapPortLoadouts(),
        ] + ItemBaseData::getData($this->item);
    }

    private function convertToSCU(array $volume): array
    {
        $unit = null;
        if (isset($volume['SStandardCargoUnit']['standardCargoUnits'])) {
            $unit = 0;
            $volume = $volume['SStandardCargoUnit']['standardCargoUnits'];
        } elseif (isset($volume['SCentiCargoUnit']['centiSCU'])) {
            $unit = 2;
            $volume = (float)($volume['SCentiCargoUnit']['centiSCU']) * (10 ** -$unit);
        } elseif (isset($volume['SMicroCargoUnit']['microSCU'])) {
            $unit = 6;
            $volume = (float)($volume['SMicroCargoUnit']['microSCU']) * (10 ** -$unit);
        } else {
            $volume = 0;
        }

        return [$unit, $volume];
    }

    private function mapPorts(): array
    {
        if ($this->get('SItemPortContainerComponentParams.Ports') === null) {
            return [];
        }
        $loadout = $this->mapPortLoadouts();

        $out = [];

        foreach ($this->get('SItemPortContainerComponentParams.Ports') as $port) {
            $position = null;
            if (stripos($port['DisplayName'], 'optics') !== false) {
                $position = 'optics';
            } elseif (stripos($port['DisplayName'], 'underbarrel') !== false) {
                $position = 'underbarrel';
            } elseif (stripos($port['DisplayName'], 'barrel') !== false) {
                $position = 'barrel';
            } elseif (stripos($port['DisplayName'], 'magazine') !== false) {
                $position = 'magazine_well';
            }

            $out[$port['Name']] = [
                'name' => $port['Name'],
                'display_name' => $port['DisplayName'],
                'min_size' => $port['MinSize'] ?? null,
                'max_size' => $port['MaxSize'] ?? null,
                'position' => $position,
                'equipped_item_uuid' => $port['EquippedItemUuid'] ?? $loadout[strtolower($port['Name'])] ?? null,
                'tags' => $port['PortTags'] ?? null,
                'required_tags' => $port['RequiredPortTags'] ?? null,
            ];
        }

        return $out;
    }

    private function getInventoryContainer(): array
    {
        $container = $this->get('ResourceContainer.capacity');

        if ($container !== null) {
            [$unit, $scu] = $this->convertToSCU($container);

            return [
                'width' => 0,
                'height' => 0,
                'length' => 0,
                'scu' => $scu,
                'unit' => $unit,
            ];
        }

        return [
            'width' => Arr::get($this->item, 'inventoryContainer.x'),
            'height' => Arr::get($this->item, 'inventoryContainer.z'),
            'length' => Arr::get($this->item, 'inventoryContainer.y'),
            'scu' => Arr::get($this->item, 'inventoryContainer.SCU'),
            'unit' => Arr::get($this->item, 'inventoryContainer.unit', 0),
        ];
    }

    private function getItemUUID(string $className): ?string
    {
        $uuid = \App\Models\SC\Item\Item::query()
            ->where('class_name', strtolower($className))
            ->first(['uuid'])->uuid ?? null;

        if ($uuid === null) {
            try {
                $item = File::get(
                    storage_path(
                        sprintf(
                            'app/api/scunpacked-data/items/%s.json',
                            strtolower($className)
                        )
                    )
                );

                $item = collect(json_decode($item, true, 512, JSON_THROW_ON_ERROR));
                return Arr::get($item, 'Raw.Entity.__ref');
            } catch (FileNotFoundException | JsonException $e) {
                return null;
            }
        }

        return null;
    }

    private function mapPortLoadouts(): Collection
    {
        // phpcs:ignore
        return collect($this->get('SEntityComponentDefaultLoadoutParams.loadout.SItemPortLoadoutManualParams.entries', []))
            ->mapWithKeys(function ($loadout) {
                $itemUuid = null;

                if (!empty($loadout['entityClassName'])) {
                    $itemUuid = $this->getItemUUID($loadout['entityClassName']);
                }

                if ($itemUuid === null) {
                    return [null => null];
                }

                $itemPortName = strtolower($loadout['itemPortName']);

                return [
                    $itemPortName => $itemUuid
                ];
            })
            ->filter();
    }
}
