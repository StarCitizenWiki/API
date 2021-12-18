<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use JsonException;

final class Item
{
    private Collection $item;
    private Collection $labels;
    private Collection $manufacturers;

    /**
     * AssaultRifle constructor.
     * @param string $fileName
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

        if (!$this->labels->has($nameKey)) {
            return null;
        }

        $attach = $this->item['Raw']['Entity']['Components']['SAttachableComponentParams']['AttachDef'];
        $manufacturer = $this->manufacturers->get($attach['Manufacturer'], []);
        $manufacturer = trim($manufacturer['name'] ?? $manufacturer['code'] ?? 'Unknown Manufacturer');

        $name = $this->cleanName($nameKey);
        if (empty($name)) {
            $name = '<= PLACEHOLDER =>';
        }

        // phpcs:ignore
        $sizes = $this->item->pull('Raw.Entity.Components.SAttachableComponentParams.AttachDef.inventoryOccupancyDimensions', []);
        // phpcs:ignore
        $volume = $this->item->pull('Raw.Entity.Components.SAttachableComponentParams.AttachDef.inventoryOccupancyVolume.SMicroCargoUnit.microSCU', 0);

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
        $name = trim(str_replace(' ', ' ', $this->labels->get($key)));
        return str_replace(['“', '”'], '"', $name);
    }
}
