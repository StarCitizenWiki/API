<?php

declare(strict_types=1);

namespace App\Services\Parser\SC;

use App\Services\Parser\SC\AbstractCommodityItem;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use JsonException;

final class Weapon extends AbstractCommodityItem
{
    public function getData(): ?array
    {
        $attachDef = $this->getAttachDef();

        if ($attachDef === null) {
            return null;
        }

        $description = $this->getDescription($attachDef);

        $data = $this->tryExtractDataFromDescription(str_replace('\\n\\nSize', '\nSize', $description), [
            'Manufacturer' => 'manufacturer',
            'Item Type' => 'type',
            'Class' => 'class',
            'Magazine Size' => 'magazine_size',
            'Effective Range' => 'effective_range',
            'Rate Of Fire' => 'rof',
            'Attachments' => 'attachments',
            'Size' => 'size',
        ]);

        $out = [
            'uuid' => $this->getUUID(),
            'size' => $attachDef['Size'] ?? null,
            'description' => $this->cleanString(trim($data['description'] ?? $description)),

            'weapon_type' => trim($data['type'] ?? 'Unknown Type'),
            'weapon_class' => trim($data['class'] ?? 'Unknown Weapon Class'),
            'effective_range' => $this->buildEffectiveRange($data['effective_range'] ?? null),
            'rof' => $data['rof'] ?? null,
            'capacity' => $this->get('SAmmoContainerComponentParams.maxAmmoCount'),
            'attachments' => $this->buildAttachmentsPart(),
            'ammunition' => $this->buildAmmunitionWeaponPart($this->item),
            'modes' => $this->buildModesPart(),
            'magazine' => $this->buildMagazinePart($this->item),
            'regen_consumption' => $this->buildRegenConsumption($this->item),
        ];

        if (empty($out['capacity']) && !empty($out['regen_consumption'])) {
            $out['capacity'] = floor($out['regen_consumption']['requested_ammo_load'] / $out['regen_consumption']['cost_per_bullet']);
        }


        return $out;
    }

    private function buildAmmunitionWeaponPart(Collection $rawData): array
    {
        if (!$rawData->has('ammo')) {
            return [];
        }

        $damageFilter = function (array $entry) {
            return $entry['damage'] > 0;
        };

        $damage = collect(Arr::get($rawData, 'ammo.projectileParams.BulletProjectileParams.damage'))
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
        $detonation = collect(Arr::get($rawData, 'ammo.projectileParams.BulletProjectileParams.detonationParams.ProjectileDetonationParams.explosionParams.damage'))
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
            'size' => Arr::get($rawData, 'ammo.size') ?? 1,
            'speed' => Arr::get($rawData, 'ammo.speed'),
            'lifetime' => Arr::get($rawData, 'ammo.lifetime'),
            'range' => (float)Arr::get($rawData, 'ammo.speed', 0) * (float)Arr::get($rawData, 'ammo.lifetime', 0),
            'damages' => array_filter([
                'impact' => $damage,
                'detonation' => $detonation,
            ]),
        ];
    }

    private function buildMode(array $mode): array
    {
        if (!isset($mode['name'])) {
            return [];
        }

        $base = [
            'mode' => $mode['name'],
            'localised' => $this->labels->get(substr($mode['localisedName'], 1)),
        ];

        switch (strtolower($mode['name'])) {
            case 'shotgun':
            case 'single':
                $data = [
                    'rounds_per_minute' => $mode['fireRate'],
                    'type' => 'single',
                    'ammo_per_shot' => $mode['launchParams']['SProjectileLauncher']['ammoCost'] ?? 1,
                    'pellets_per_shot' => $mode['launchParams']['SProjectileLauncher']['pelletCount'] ?? 1,
                ];
                break;

            case 'rapid':
                $data = [
                    'rounds_per_minute' => $mode['fireRate'],
                    'type' => 'rapid',
                    'ammo_per_shot' => $mode['launchParams']['SProjectileLauncher']['ammoCost'] ?? 1,
                    'pellets_per_shot' => $mode['launchParams']['SProjectileLauncher']['pelletCount'] ?? 1,
                ];
                break;

            case 'beam':
                $data = [
                    'type' => 'beam',
                ];
                break;

            case 'charge':
                $data = [
                    'rounds_per_minute' => $mode['weaponAction']['SWeaponActionFireSingleParams']['fireRate'] ??
                            $mode['weaponAction']['SWeaponActionFireBurstParams']['fireRate'] ?? null,
                    'type' => 'charged',
                    'ammo_per_shot' => $mode['weaponAction']['SWeaponActionFireSingleParams']['launchParams']['SProjectileLauncher']['ammoCost'] ??
                            $mode['weaponAction']['SWeaponActionFireBurstParams']['launchParams']['SProjectileLauncher']['ammoCost'] ?? null,
                    'pellets_per_shot' => $mode['weaponAction']['SWeaponActionFireSingleParams']['launchParams']['SProjectileLauncher']['pelletCount'] ??
                            $mode['weaponAction']['SWeaponActionFireBurstParams']['launchParams']['SProjectileLauncher']['pelletCount'] ?? null,
                ];
                break;

            case 'looping':
                $sequence = $mode['sequenceEntries'][0]['weaponAction']['SWeaponActionFireSingleParams'] ??
                    $mode['sequenceEntries'][0]['weaponAction']['SWeaponActionFireBurstParams'] ?? [];

                $data = $this->buildMode($sequence) + [
                    'type' => 'sequence',
                ];
                break;

            case 'burst':
                $data = [
                    'rounds_per_minute' => $mode['fireRate'],
                    'type' => 'burst',
                    'ammo_per_shot' => $mode['launchParams']['SProjectileLauncher']['ammoCost'] ?? 1,
                    'pellets_per_shot' => $mode['launchParams']['SProjectileLauncher']['pelletCount'] ?? 1,
                ];
                break;

            default:
                $data = [];
        }

        return $base + $data;
    }

    private function buildModesPart(): array
    {
        $fireActions = $this->get('SCItemWeaponComponentParams.fireActions', []);

        $modes = collect($fireActions)
            ->map(function (array $mode) {
                if (isset($mode['sequenceEntries'])) {
                    $mode['name'] = 'looping';
                }
                return $this->buildMode($mode);
            });

        return $modes->toArray();
    }

    private function buildAttachmentsPart(): array
    {
        $attachments = $this->get('SEntityComponentDefaultLoadoutParams.loadout.SItemPortLoadoutManualParams.entries');

        if (empty($attachments)) {
            return [];
        }

        $mapped = collect($attachments)
            ->map(function (array $component) {
                try {
                    $item = File::get(
                        storage_path(
                            sprintf(
                                'app/api/scunpacked-data/items/%s.json',
                                strtolower($component['entityClassName'])
                            )
                        )
                    );

                    $item = collect(json_decode($item, true, 512, JSON_THROW_ON_ERROR));
                } catch (FileNotFoundException $e) {
                    return null;
                }

                return [
                    'uuid' => Arr::get($item, 'Raw.Entity.__ref'),
                    'port' => $component['itemPortName'],
                ];
            });

        return array_filter($mapped->toArray());
    }

    private function buildEffectiveRange(?string $effectiveRange): ?int
    {
        if ($effectiveRange === null) {
            return null;
        }

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
        $data = Arr::get($rawData, 'magazine.Components.SAmmoContainerComponentParams');

        if (empty($data)) {
            return [];
        }

        return [
            'initial_ammo_count' => $data['initialAmmoCount'] ?? 0,
            'max_ammo_count' => $data['maxAmmoCount'] ?? $data['maxRestockCount'] ?? 0,
        ];
    }

    private function buildRegenConsumption(Collection $rawData)
    {
        $data = Arr::get($rawData, 'Raw.Entity.Components.SCItemWeaponComponentParams.weaponRegenConsumerParams.0');

        if (empty($data)) {
            return [];
        }

        return [
            'requested_regen_per_sec' => $data['requestedRegenPerSec'] ?? null,
            'requested_ammo_load' => $data['requestedAmmoLoad'] ?? null,
            'cooldown' => $data['regenerationCooldown'] ?? null,
            'cost_per_bullet' => $data['regenerationCostPerBullet'] ?? null,
        ];
    }
}
