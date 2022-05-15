<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use JsonException;

final class Clothing extends AbstractCommodityItem
{
    private Collection $item;
    private Collection $labels;
    private Collection $manufacturers;

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
        $attachDef = $this->item->pull('Components.SAttachableComponentParams.AttachDef');
        $tempResist = $this->item->pull('Components.SCItemClothingParams.TemperatureResistance');

        if ($attachDef === null || strpos($attachDef['Type'], 'Clothing') === false) {
            return null;
        }

        $name = $this->labels->get(substr($attachDef['Localization']['Name'], 1));
        $name = str_replace(
            [
                '“',
                '”',
                '"',
                '\'',
            ],
            '"',
            trim($name ?? 'Unknown Clothing')
        );

        $description = $this->getDescription($this->item->get('ClassName')) ?? '';

        if (empty($description)) {
            $description = $this->labels->get(substr($attachDef['Localization']['Description'], 1)) ?? '';
        }

        $data = $this->tryExtractDataFromDescription($description, [
            'Manufacturer' => 'manufacturer',
            'Item Type' => 'type',
            'Damage Reduction' => 'damage_reduction',
            'Carrying Capacity' => 'carrying_capacity',
            'Temp\. Rating' => 'temp_rating',
        ]);

        $manufacturer = $this->manufacturers->get($attachDef['Manufacturer'], 'Unknown Manufacturer');
        if ($manufacturer === '@LOC_PLACEHOLDER') {
            $manufacturer = 'Unknown Manufacturer';
        }

        $description = str_replace(['’', '`', '´'], '\'', trim($data['description'] ?? $description));

        return [
            'uuid' => $this->item->get('__ref'),
            'description' => $description,
            'name' => $name,
            'manufacturer' => $manufacturer,
            'temp_resistance_min' => $tempResist['MinResistance'] ?? null,
            'temp_resistance_max' => $tempResist['MaxResistance'] ?? null,
            'type' => $this->getType($attachDef['Type'], $name),
            'carrying_capacity' => $data['carrying_capacity'] ?? null
        ];
    }

    private function getType(string $type, string $name): string
    {
        switch (true) {
            case strpos($name, 'T-Shirt') !== false:
            case strpos($name, 'Shirt') !== false:
                return 'T-Shirt';

            case strpos($name, 'Jacket') !== false:
                return 'Jacket';

            case strpos($name, 'Gloves') !== false:
                return 'Gloves';

            case strpos($name, 'Pants') !== false:
                return 'Pants';

            case strpos($name, 'Bandana') !== false:
                return 'Bandana';

            case strpos($name, 'Beanie') !== false:
                return 'Beanie';

            case strpos($name, 'Boots') !== false:
                return 'Boots';

            case strpos($name, 'Sweater') !== false:
                return 'Sweater';

            case strpos($name, 'Hat') !== false:
                return 'Hat';

            case strpos($name, 'Shoes') !== false:
                return 'Shoes';

            case strpos($name, 'Head Cover') !== false:
                return 'Head Cover';

            case strpos($name, 'Gown') !== false:
                return 'Gown';

            case strpos($name, 'Slippers') !== false:
                return 'Slippers';
        }

        switch (true) {
            case strpos($type, 'Backpack') !== false:
                return 'Backpack';

            case strpos($type, 'Feet') !== false:
                return 'Shoes';

            case strpos($type, 'Hands') !== false:
                return 'Gloves';

            case strpos($type, 'Hat') !== false:
                return 'Hat';

            case strpos($type, 'Legs') !== false:
                return 'Pants';

            case strpos($type, 'Torso_0') !== false:
                return 'Shirt';

            case strpos($type, 'Torso_1') !== false:
                return 'Jacket';
        }

        return 'Unknown Type';
    }

    private function getDescription(?string $classname): ?string
    {
        if ($classname === null) {
            return null;
        }

        try {
            $item = File::get(storage_path(
                sprintf('app/api/scunpacked-data/v2/items/%s.json', strtolower($classname))
            ));

            $item = json_decode($item, true, 512, JSON_THROW_ON_ERROR);
        } catch (FileNotFoundException | JsonException $exception) {
            return null;
        }

        return $item['Description'] ?? null;
    }
}
