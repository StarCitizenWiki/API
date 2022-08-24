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
     * WeaponPersonal constructor
     *
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
                try {
                    $item = File::get(
                        storage_path(
                            sprintf('app/api/scunpacked-data/items/%s.json', $entry['ClassName'])
                        )
                    );
                    $item = collect(json_decode($item, true, 512, JSON_THROW_ON_ERROR));
                } catch (FileNotFoundException $e) {
                    $item = collect();
                }

                return $this->map($entry, $item);
            })
            ->filter(function (array $weapon) {
                return !empty($weapon);
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
            ->filter(function (array $weapon) {
                return !empty($weapon['ammunition']) && !empty($weapon['magazine']);
            })
            ->unique('name');
    }

    private function map(array $weapon, Collection $rawData): array
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

        if ($rawData->isEmpty()) {
            return [];
        }

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
            'attachment_ports' => $this->buildAttachmentPortsPart($rawData),
            'attachments' => $this->buildAttachmentsPart($rawData),
            'ammunition' => $this->buildAmmunitionWeaponPart($rawData, $weapon),
            'modes' => $this->buildModesPart($weapon),
            'magazine' => $this->buildMagazinePart($rawData),
        ];
    }

    private function buildAmmunitionWeaponPart(Collection $rawData, array $weapon): array
    {
        $key = 'ammo';

        if (!$rawData->has($key)) {
            return [];
        }

        $damageFilter = function (array $entry) {
            return $entry['damage'] > 0;
        };

        $damage = collect($rawData->pull('ammo.projectileParams.BulletProjectileParams.damage'))
            ->flatMap(function ($entry) {
                return collect($entry)
                    ->map(function ($damage, $key) {
                        return [
                            'type' => 'impact',
                            'name' => strtolower(str_replace('Damage', '', $key)),
                            'damage' => $damage,
                        ];
                    });
            })
            ->filter($damageFilter)
            ->toArray();

        // phpcs:ignore Generic.Files.LineLength.TooLong
        $detonation = collect($rawData->pull('ammo.projectileParams.BulletProjectileParams.detonationParams.ProjectileDetonationParams.explosionParams.damage'))
            ->flatMap(function ($entry) {
                return collect($entry)
                    ->map(function ($damage, $key) {
                        return [
                            'type' => 'detonation',
                            'name' => strtolower(str_replace('Damage', '', $key)),
                            'damage' => $damage,
                        ];
                    });
            })
            ->filter($damageFilter)
            ->toArray();

        return [
            'size' => $rawData->pull('ammo.size') ?? 1,
            'speed' => $rawData->pull('ammo.speed', 0),
            'lifetime' => $rawData->pull('ammo.lifetime', 0),
            'range' => $weapon['Ammunition']['Range'] ?? 0,
            'damages' => array_filter([
                'impact' => $damage,
                'detonation' => $detonation,
            ]),
        ];
    }

    private function buildModesPart($weapon): array
    {
        if (!isset($weapon['Weapon']['Modes'])) {
            return [];
        }

        $modes = collect($weapon['Weapon']['Modes'])
            ->map(function (array $mode) {

                return [
                    'mode' => $mode['Name'],
                    'localised' => $mode['LocalisedName'],
                    'type' => $mode['FireType'],
                    'rounds_per_minute' => $mode['RoundsPerMinute'],
                    'ammo_per_shot' => $mode['AmmoPerShot'],
                    'pellets_per_shot' => $mode['PelletsPerShot'],
                ];
            });

        return $modes->toArray();
    }

    private function buildAttachmentPortsPart(Collection $rawData): array
    {
        $key = 'Raw.Entity.Components.SItemPortContainerComponentParams.Ports';
        $ports = $rawData->pull($key);

        if (empty($ports)) {
            return [];
        }

        $mapped = collect($ports)->map(function (array $component) {
            return [
                'name' => $component['DisplayName'],
                'position' => str_replace(['_attach', 'ment'], '', $component['Name']),
                'min_size' => $component['MinSize'],
                'max_size' => $component['MaxSize'],
            ];
        });

        return array_filter($mapped->toArray());
    }

    private function buildAttachmentsPart(Collection $rawData): array
    {
        $key = 'Raw.Entity.Components.SEntityComponentDefaultLoadoutParams.loadout.SItemPortLoadoutManualParams.entries';
        $attachments = $rawData->pull($key);

        if (empty($attachments)) {
            return [];
        }

        $mapped = collect($attachments)
            ->map(function (array $component) {
                try {
                    $item = File::get(
                        storage_path(
                            sprintf(
                                'app/api/scunpacked-data/v2/items/%s-raw.json',
                                $component['entityClassName']
                            )
                        )
                    );

                    /** @var Collection $item */
                    $item = collect(json_decode($item, true, 512, JSON_THROW_ON_ERROR));
                } catch (FileNotFoundException $e) {
                    return null;
                }


                return [
                    'uuid' => $item->get('__ref'),
                ];
            });

        return array_filter($mapped->toArray());
    }

    private function buildEffectiveRange(string $effectiveRange): int
    {
        $split = explode('(', $effectiveRange);
        $split = array_map('trim', $split);

        if (count($split) === 1) {
            $value = $split[0];
        } else {
            $value = trim(array_pop($split), ')');
        }

        if (!is_numeric(trim($value, ' km'))) {
            return 0;
        }

        if (strpos($value, 'km') !== false) {
            $value = (int)trim($value, ' km') * 1000;
        }

        return (int)trim((string)$value, ' m');
    }

    private function buildMagazinePart(Collection $rawData)
    {
        $key = 'magazine.Components.SAmmoContainerComponentParams';
        $data = $rawData->pull($key);

        if (empty($data)) {
            return [];
        }

        return [
            'initial_ammo_count' => $data['initialAmmoCount'] ?? 0,
            'max_ammo_count' => $data['initialAmmoCount'] ?? 0,
        ];
    }
}
