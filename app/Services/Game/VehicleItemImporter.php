<?php

declare(strict_types=1);

namespace App\Services\Game;

use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\ItemDescriptionData;
use App\Models\System\Language;
use App\Services\Parser\SC\Labels;
use Illuminate\Support\Arr;

class VehicleItemImporter
{
    public function __construct(private ?Labels $labels = null) {}

    public function importFromVehiclePayload(
        int $gameVersionId,
        array $vehiclePayload,
        array $rawPayload,
        int $manufacturerId
    ): void {
        $uuid = $this->extractUuid($vehiclePayload, $rawPayload);

        if ($uuid === null) {
            return;
        }

        $item = Item::query()->firstOrCreate(
            ['uuid' => $uuid],
            ['uuid' => $uuid]
        );

        $attachDef = $this->extractAttachDef($rawPayload);
        $name = $this->extractName($vehiclePayload, $attachDef);
        $className = $this->extractClassName($vehiclePayload, $rawPayload);
        $type = $this->extractType($attachDef);
        $subType = $this->extractSubType($attachDef);
        $size = $this->nullableInt($attachDef['Size'] ?? Arr::get($vehiclePayload, 'Size'));
        $grade = $this->nullableInt($attachDef['Grade'] ?? null);
        $manufacturerUuid = $this->extractManufacturerUuid($vehiclePayload, $attachDef);
        $manufacturerCode = $this->extractManufacturerCode($vehiclePayload, $attachDef);
        $descriptionData = $this->extractDescriptionData($vehiclePayload);
        $englishDescription = $this->extractEnglishDescription($vehiclePayload, $rawPayload);

        $itemData = ItemData::query()->updateOrCreate(
            [
                'item_id' => $item->id,
                'game_version_id' => $gameVersionId,
            ],
            [
                'manufacturer_id' => $manufacturerId,
                'name' => $name,
                'class_name' => $className,
                'type' => $type,
                'sub_type' => $subType,
                'classification' => null,
                'size' => $size,
                'grade' => $grade,
                'class' => null,
                'base_id' => null,
                'data' => $this->buildItemPayload(
                    $uuid,
                    $className,
                    $name,
                    $type,
                    $subType,
                    $size,
                    $grade,
                    $manufacturerUuid,
                    $manufacturerCode,
                    $descriptionData,
                    $englishDescription
                ),
            ]
        );

        $this->syncDescriptionData($item, $descriptionData);
        $this->syncTranslations($item, $vehiclePayload, $rawPayload);
    }

    private function extractUuid(array $vehiclePayload, array $rawPayload): ?string
    {
        $candidates = [
            Arr::get($rawPayload, 'Entity.__ref'),
            Arr::get($vehiclePayload, 'UUID'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return trim($candidate);
            }
        }

        return null;
    }

    private function extractAttachDef(array $rawPayload): array
    {
        $attachDef = Arr::get($rawPayload, 'Entity.Components.SAttachableComponentParams.AttachDef');

        return is_array($attachDef) ? $attachDef : [];
    }

    private function extractName(array $vehiclePayload, array $attachDef): string
    {
        $localization = Arr::get($attachDef, 'Localization', []);

        $candidates = [
            Arr::get($localization, 'English.Name'),
            Arr::get($localization, 'Name'),
            Arr::get($vehiclePayload, 'Name'),
            Arr::get($vehiclePayload, 'ClassName'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return $candidate;
            }
        }

        return 'Unknown Vehicle';
    }

    private function extractClassName(array $vehiclePayload, array $rawPayload): ?string
    {
        $candidates = [
            Arr::get($vehiclePayload, 'ClassName'),
            Arr::get($rawPayload, 'Entity.ClassName'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return $candidate;
            }
        }

        return null;
    }

    private function extractType(array $attachDef): ?string
    {
        $type = $attachDef['Type'] ?? null;

        return is_string($type) && trim($type) !== '' ? $type : null;
    }

    private function extractSubType(array $attachDef): ?string
    {
        $subType = $attachDef['SubType'] ?? null;

        return is_string($subType) && trim($subType) !== '' ? $subType : null;
    }

    private function extractManufacturerUuid(array $vehiclePayload, array $attachDef): ?string
    {
        $candidates = [
            Arr::get($attachDef, 'Manufacturer.__ref'),
            Arr::get($vehiclePayload, 'Manufacturer.UUID'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return $candidate;
            }
        }

        return null;
    }

    private function extractManufacturerCode(array $vehiclePayload, array $attachDef): ?string
    {
        $candidates = [
            Arr::get($attachDef, 'Manufacturer.Code'),
            Arr::get($vehiclePayload, 'Manufacturer.Code'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return $candidate;
            }
        }

        return null;
    }

    private function extractDescriptionData(array $vehiclePayload): array
    {
        $descriptionData = Arr::get($vehiclePayload, 'DescriptionData');

        return is_array($descriptionData) ? $descriptionData : [];
    }

    private function buildItemPayload(
        string $uuid,
        ?string $className,
        string $name,
        ?string $type,
        ?string $subType,
        ?int $size,
        ?int $grade,
        ?string $manufacturerUuid,
        ?string $manufacturerCode,
        array $descriptionData,
        ?string $englishDescription
    ): array {
        return [
            'reference' => $uuid,
            'className' => $className,
            'itemName' => $className !== null ? mb_strtolower($className) : null,
            'name' => $name,
            'type' => $type,
            'subType' => $subType,
            'size' => $size,
            'grade' => $grade,
            'stdItem' => [
                'UUID' => $uuid,
                'ClassName' => $className,
                'Size' => $size,
                'Grade' => $grade,
                'Type' => $type,
                'SubType' => $subType,
                'Name' => $name,
                'Description' => $englishDescription,
                'DescriptionText' => $englishDescription,
                'DescriptionData' => $descriptionData,
                'Manufacturer' => [
                    'Code' => $manufacturerCode,
                    'UUID' => $manufacturerUuid,
                ],
            ],
        ];
    }

    private function syncDescriptionData(Item $item, array $descriptionData): void
    {
        if ($descriptionData === []) {
            return;
        }

        foreach ($descriptionData as $name => $value) {
            if (! is_string($name) || $name === '') {
                continue;
            }

            ItemDescriptionData::query()->updateOrCreate(
                [
                    'item_id' => $item->id,
                    'name' => $name,
                ],
                [
                    'value' => is_scalar($value) ? (string) $value : json_encode($value),
                ]
            );
        }
    }

    private function syncTranslations(Item $item, array $vehiclePayload, array $rawPayload): void
    {
        $updated = false;
        $english = $this->extractEnglishDescription($vehiclePayload, $rawPayload);

        if ($english !== null && $english !== '') {
            $item->setTranslation('translation', Language::ENGLISH, $english);
            $updated = true;
        }

        $label = $this->extractDescriptionLabel($rawPayload);

        if ($label === null) {
            return;
        }

        foreach ([Language::CHINESE, Language::GERMAN] as $locale) {
            $translation = $this->getLabels()->getTranslation($locale, $label);

            if ($translation === null || $translation === '') {
                continue;
            }

            $item->setTranslation('translation', $locale, $this->getDescriptionText($translation));
            $updated = true;
        }

        if ($updated) {
            $item->save();
        }
    }

    private function extractDescriptionLabel(array $rawPayload): ?string
    {
        $component = Arr::get($rawPayload, 'Entity.Components.SAttachableComponentParams.AttachDef', []);

        $label = $component['Localization__Description'] ?? Arr::get($component, 'Localization.__Description');

        if (! is_string($label)) {
            return null;
        }

        $label = trim($label);

        if ($label === '' || mb_strtolower($label) === '@loc_empty') {
            return null;
        }

        return ltrim($label, '@');
    }

    private function extractEnglishDescription(array $vehiclePayload, array $rawPayload): ?string
    {
        $component = Arr::get($rawPayload, 'Entity.Components.SAttachableComponentParams.AttachDef', []);
        $localization = $component['Localization'] ?? [];

        $candidates = [
            Arr::get($vehiclePayload, 'DescriptionText'),
            Arr::get($vehiclePayload, 'Description'),
            Arr::get($localization, 'English.Description'),
            Arr::get($localization, 'Description'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return $candidate;
            }
        }

        return null;
    }

    private function getLabels(): Labels
    {
        if ($this->labels === null) {
            $this->labels = new Labels;
        }

        return $this->labels;
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    private function getDescriptionText(string $description): string
    {
        $description = str_replace('\\n \\n', '\\n\\n', $description);

        $description = trim(str_replace('\n', "\n", $description));
        $description = str_replace(['‘', '’', '`', '´', ' '], ['\'', '\'', '\'', '\'', ' '], $description);
        $exploded = explode("\n\n", $description);

        if (count($exploded) === 1) {
            $exploded = explode('\n\n', $exploded[0]);
        }

        $exploded = array_filter($exploded, static function (string $part) {
            return preg_match('/(：|\w:[\s| ])/u', $part) !== 1;
        });

        return trim(implode("\n\n", $exploded));
    }
}
