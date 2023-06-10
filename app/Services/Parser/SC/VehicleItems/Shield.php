<?php

declare(strict_types=1);

namespace App\Services\Parser\SC\VehicleItems;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class Shield extends AbstractItemSpecification
{
    public static function getData(Collection $item): ?array
    {
        $data = self::get($item, 'SCItemShieldGeneratorParams');

        if ($data === null) {
            return null;
        }

        return [
            'max_shield_health' => Arr::get($data, 'MaxShieldHealth'),
            'max_shield_regen' => Arr::get($data, 'MaxShieldRegen'),
            'decay_ratio' => Arr::get($data, 'DecayRatio'),
            'downed_regen_delay' => Arr::get($data, 'DownedRegenDelay'),
            'damage_regen_delay' => Arr::get($data, 'DamagedRegenDelay'),
            'max_reallocation' => Arr::get($data, 'MaxReallocation'),
            'reallocation_rate' => Arr::get($data, 'ReallocationRate'),

            'absorptions' => [
                'physical' => ([
                    'min' => $data['ShieldAbsorption'][0]['Min'],
                    'max' => $data['ShieldAbsorption'][0]['Max'],
                ]),
                'energy' => ([
                    'min' => $data['ShieldAbsorption'][1]['Min'],
                    'max' => $data['ShieldAbsorption'][1]['Max'],
                ]),
                'distortion' => ([
                    'min' => $data['ShieldAbsorption'][2]['Min'],
                    'max' => $data['ShieldAbsorption'][2]['Max'],
                ]),
                'thermal' => ([
                    'min' => $data['ShieldAbsorption'][3]['Min'],
                    'max' => $data['ShieldAbsorption'][3]['Max'],
                ]),
                'biochemical' => ([
                    'min' => $data['ShieldAbsorption'][4]['Min'],
                    'max' => $data['ShieldAbsorption'][4]['Max'],
                ]),
                'stun' => ([
                    'min' => $data['ShieldAbsorption'][5]['Min'],
                    'max' => $data['ShieldAbsorption'][5]['Max'],
                ]),
            ],
        ];
    }
}
