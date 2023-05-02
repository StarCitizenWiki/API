<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use JsonException;
use phpDocumentor\Reflection\Utils;

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
        $items = File::get(storage_path(sprintf('app/%s', $fileName)));
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
                'Manufacturer' => 'manufacturer',
            ]
        );

        if (!empty($descriptionData['manufacturer'])) {
            $manufacturer = $descriptionData['manufacturer'];
            $manufacturer = $this->manufacturerFixes[$manufacturer] ?? $manufacturer;
        }

        $sizes = $this->get('SAttachableComponentParams.AttachDef.inventoryOccupancyDimensions', []);
        $sizeOverride = $this->get('SAttachableComponentParams.AttachDef.inventoryOccupancyDimensionsUIOverride.Vec3', []);

        $volume = $this->convertToSCU($this->get('SAttachableComponentParams.AttachDef.inventoryOccupancyVolume', []));

        return [
            'uuid' => $this->getUUID(),
            'name' => $name,
            'type' => $attachDef['Type'],
            'tags' => $attachDef['Tags'],
            'sub_type' => $attachDef['SubType'],
            'manufacturer' => $manufacturer,
            'size' => $attachDef['Size'],
            'class_name' => strtolower(Arr::get($this->item, 'Raw.Entity.ClassName')),

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

            'volume' => $volume,

            'inventory_container' => [
                'width' => Arr::get($this->item, 'inventoryContainer.x'),
                'height' => Arr::get($this->item, 'inventoryContainer.z'),
                'length' => Arr::get($this->item, 'inventoryContainer.y'),
                'scu' => Arr::get($this->item, 'inventoryContainer.SCU'),
            ],

            'ports' => $this->mapPorts(),
        ];
    }

    private function convertToSCU(array $volume): float
    {
        if (isset($volume['SStandardCargoUnit']['standardCargoUnits'])) {
            $volume = $volume['SStandardCargoUnit']['standardCargoUnits'];
        } elseif (isset($volume['SCentiCargoUnit']['centiSCU'])) {
            $volume = (float)($volume['SCentiCargoUnit']['centiSCU']) * (10 ** -2);
        } elseif (isset($volume['SMicroCargoUnit']['microSCU'])) {
            $volume = (float)($volume['SMicroCargoUnit']['microSCU']) * (10 ** -6);
        } else {
            $volume = 0;
        }

        return $volume;
    }

    private function mapPorts(): array
    {
        if ($this->get('SItemPortContainerComponentParams.Ports') === null) {
            return [];
        }

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
            ];
        }

        return $out;
    }
}
