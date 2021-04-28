<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\FpsItems;

use App\Services\Parser\StarCitizenUnpacked\AbstractCommodityItem;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use JsonException;

final class WeaponPersonal extends AbstractCommodityItem
{
    private Collection $items;

    /**
     * AssaultRifle constructor.
     * @throws FileNotFoundException
     * @throws JsonException
     */
    public function __construct()
    {
        $items = File::get(storage_path(sprintf('app/api/scunpacked-data/fps-items.json')));
        $this->items = collect(json_decode($items, true, 512, JSON_THROW_ON_ERROR));
    }

    public function getData(bool $onlyBaseVersions = false, bool $excludeToy = true): Collection
    {
        return $this->items->filter(function (array $entry) {
            return $entry['type'] ?? '' === 'WeaponPersonal';
        })
            ->filter(function (array $entry) {
                return isset($entry['reference']);
            })
            ->map(function (array $entry) {
                $out = $entry['stdItem'] ?? [];
                $out['reference'] = $entry['reference'] ?? null;

                return $out;
            })
            ->filter(function (array $entry) {
                return isset($entry['Description'], $entry['Weapon']) && !empty($entry);
            })
            ->map(function (array $entry) {
                return $this->map($entry);
            })
            ->filter(function (array $weapon) use ($onlyBaseVersions) {
                if ($onlyBaseVersions === true) {
                    return strpos($weapon['name'], '"') === false;
                }

                return true;
            })
            ->filter(function (array $weapon) use ($excludeToy) {
                if ($excludeToy === true) {
                    return strpos($weapon['name'], 'Toy Pistol') === false &&
                        strpos($weapon['name'], 'Multi-Tool') === false;
                }

                return true;
            })
            ->unique('name');
    }

    private function map(array $weapon): array
    {
        /**
         * Name": "Gallant “Stormfall” Rifle
         * Manufacturer: Klaus & Werner
         * Item Type: Assault Rifle
         * Class: Energy (Laser)
         * Magazine Size: 120
         * Rate Of Fire: 450 rpm
         * Effective Range: 50 m
         * Attachments: Optics (S2), Barrel (S2), Underbarrel (S2)
         *
         * Fire Modes: AUTO
         * RoundsPerMinute: 451.0
         * DPS: 46.904
         *
         * Fire Modes: BURST
         * RoundsPerMinute: 700.0
         * DPS: 72.8
         */

        $data = $this->tryExtractDataFromDescription($weapon['Description'], [
            'Manufacturer' => 'manufacturer',
            'Item Type' => 'type',
            'Class' => 'class',
            'Magazine Size' => 'magazine_size',
            'Effective Range' => 'effective_range',
            'Rate Of Fire' => 'rof',
            'Attachments' => 'attachments',
        ]);

        return [
            'uuid' => $weapon['reference'],
            'size' => $weapon['Size'] ?? 0,
            'description' => str_replace(['’', '`', '´'], '\'', trim($data['description'] ?? '')),
            'name' => str_replace(
                [
                    '“',
                    '”',
                    '"',
                    '\'',
                ],
                '"',
                trim($weapon['Name'] ?? 'Unknown Weapon')
            ),
            'manufacturer' => $this->getManufacturer($weapon),
            'weapon_type' => trim($data['type'] ?? 'Unknown Type'),
            'class' => trim($weapon['Classification'] ?? 'Unknown Class'),
            'weapon_class' => trim($data['class'] ?? 'Unknown Weapon Class'),
            'magazine_size' => $data['magazine_size'] ?? 0,
            'effective_range' => $this->buildEffectiveRange($data['effective_range'] ?? '0'),
            'rof' => $data['rof'] ?? 0,
            'attachments' => $this->buildAttachmentsPart($data['attachments'] ?? ''),
            'ammunition' => $this->buildAmmunitionWeaponPart($weapon['Weapon']),
            'modes' => $this->buildModesPart($weapon['Weapon']),
        ];
    }

    private function buildAmmunitionWeaponPart(array $weapon): array
    {
        if (!isset($weapon['Ammunition']['ImpactDamage'])) {
            return [];
        }
        $impactDamage = array_shift($weapon['Ammunition']['ImpactDamage']) ?? 0;

        return [
            'speed' => $weapon['Ammunition']['Speed'] ?? 0,
            'range' => $weapon['Ammunition']['Range'] ?? 0,
            'damage' => $impactDamage,
        ];
    }

    private function buildModesPart(array $weapon): array
    {
        $out = [];

        foreach ($weapon['Modes'] as $mode) {
            $out[] = [
                'name' => trim($mode['LocalisedName'] ?? 'Unnamed Mode', '[]'),
                'rpm' => $mode['RoundsPerMinute'] ?? 0,
                'dps' => array_shift($mode['DamagePerSecond']) ?? 0
            ];
        }

        return $out;
    }

    private function buildAttachmentsPart(string $attachmentLine): array
    {
        $parts = explode(',', $attachmentLine);
        $parts = array_map('trim', $parts);

        $out = [];

        foreach ($parts as $part) {
            $split = explode('(', $part);
            $split = array_map('trim', $split);

            if ($split[0] === '') {
                continue;
            }

            $value = rtrim(array_pop($split), ')');

            if ($value[0] === 'N') {
                continue;
            }

            $out[strtolower($split[0])] = trim($value, 'S');
        }

        return $out;
    }

    private function buildEffectiveRange(string $effectiveRange): string
    {
        $split = explode('(', $effectiveRange);
        $split = array_map('trim', $split);

        if (count($split) === 1) {
            $value = $split[0];
        } else {
            $value = trim(array_pop($split), ')');
        }

        if (!is_numeric(trim($value, ' km'))) {
            return '0';
        }

        return (string)$value;
    }
}
