<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use JsonException;

final class Clothing extends AbstractCommodityItem
{
    public function getData(): ?array
    {
        $attachDef = $this->getAttachDef();
        $tempResist = $this->get('SCItemClothingParams.TemperatureResistance');

        if ($attachDef === null) {
            return null;
        }

        $name = $this->getName($attachDef, 'Unknown Clothing');

        $description = $this->getDescription($attachDef);

        $data = $this->tryExtractDataFromDescription($description, [
            'Manufacturer' => 'manufacturer',
            'Item Type' => 'type',
            'Damage Reduction' => 'damage_reduction',
            'Carrying Capacity' => 'carrying_capacity',
            'Temp\. Rating' => 'temp_rating',
        ]);

        return [
            'uuid' => $this->getUUID(),
            'description' => $this->cleanString(trim($data['description'] ?? $description)),
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
}
