<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use JsonException;

/**
 * An item can be any entity like a weapon, food, ship doors, paints, etc.
 */
final class Item extends AbstractCommodityItem
{
    private Collection $item;
    private Collection $labels;
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
        // phpcs:disable
        if (
        !isset(
            $this->item['Raw']['Entity']['__ref'],
            $this->item['Raw']['Entity']['Components']['SAttachableComponentParams']['AttachDef']['Localization']['Name'],
        )
        ) {
            return null;
        }

        $nameKey = substr(
            $this->item['Raw']['Entity']['Components']['SAttachableComponentParams']['AttachDef']['Localization']['Name'],
            1
        );
        // phpcs:enable

        $attach = $this->item['Raw']['Entity']['Components']['SAttachableComponentParams']['AttachDef'];
        $manufacturer = $this->manufacturers->get($attach['Manufacturer'], []);
        $manufacturer = trim($manufacturer['name'] ?? $manufacturer['code'] ?? 'Unknown Manufacturer');

        // Use manufacturer from description if available
        $descriptionData = $this->tryExtractDataFromDescription(
            $this->labels->get(ltrim($attach['Localization']['Description'] ?? '', '@'), ''),
            [
                'Manufacturer' => 'manufacturer',
            ]
        );

        if (!empty($descriptionData['manufacturer'])) {
            $manufacturer = $descriptionData['manufacturer'];
            $manufacturer = $this->manufacturerFixes[$manufacturer] ?? $manufacturer;
        }

        $name = $this->cleanName($nameKey);
        if (empty($name)) {
            $name = '<= PLACEHOLDER =>';
        }

        // phpcs:ignore
        $sizes = $this->item->pull('Raw.Entity.Components.SAttachableComponentParams.AttachDef.inventoryOccupancyDimensions', []);
        // phpcs:ignore
        $volume = $this->item->pull('Raw.Entity.Components.SAttachableComponentParams.AttachDef.inventoryOccupancyVolume.SMicroCargoUnit.microSCU', 0);

        // Change Cargo type to 'PersonalInventory' if item is in fact not a cargo grid
        if ($attach['Type'] === 'Cargo' && isset($this->item['Raw']['Entity']['Components']['SInventoryParams']['capacity'])) {
            $capacity = $this->item['Raw']['Entity']['Components']['SInventoryParams']['capacity']['SStandardCargoUnit'] ??
                $this->item['Raw']['Entity']['Components']['SInventoryParams']['capacity']['SCentiCargoUnit'] ??
                $this->item['Raw']['Entity']['Components']['SInventoryParams']['capacity']['SMicroCargoUnit'] ?? [];
            $capacity = $capacity['SCU'] ?? $capacity['centiSCU'] ?? $capacity['microSCU'] ?? 1;

            if ($capacity > 1) {
                $attach['Type'] = 'PersonalInventory';
            }
        }

        if ($manufacturer === '@LOC_PLACEHOLDER') {
            $manufacturer = 'Unknown Manufacturer';
        }

        return [
            'uuid' => $this->item['Raw']['Entity']['__ref'],
            'name' => $name,
            'type' => $attach['Type'],
            'sub_type' => $attach['SubType'],
            'manufacturer' => $manufacturer,
            'size' => $attach['Size'],

            'width' => $sizes['x'] ?? 0,
            'height' => $sizes['z'] ?? 0,
            'length' => $sizes['y'] ?? 0,

            'volume' => $volume,
        ];
    }

    private function cleanName(string $key): string
    {
        if (!$this->labels->has($key)) {
            return '<= PLACEHOLDER =>';
        }

        $name = trim(str_replace(' ', ' ', $this->labels->get($key)));
        return str_replace(['“', '”'], '"', $name);
    }
}
