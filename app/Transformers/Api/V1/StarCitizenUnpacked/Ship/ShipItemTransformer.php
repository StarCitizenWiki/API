<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\Ship;

use App\Models\StarCitizenUnpacked\ShipItem\AbstractShipItemSpecification;
use App\Transformers\Api\V1\StarCitizenUnpacked\AbstractCommodityTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\Shop\ShopTransformer;
use League\Fractal\Resource\Collection;

class ShipItemTransformer extends AbstractCommodityTransformer
{
    protected $availableIncludes = [
        'shops'
    ];

    public function transform(AbstractShipItemSpecification $item): array
    {
        $transformed = [
            'uuid' => $item->shipItem->item->uuid,
            'name' => $item->shipItem->item->name,
            'description' => $this->getTranslation($item->shipItem),
            'size' => $item->shipItem->item->size,
            'manufacturer' => $item->shipItem->item->manufacturer,
            'grade' => $item->shipItem->grade,
            'class' => $item->shipItem->class,
            'type' => $item->shipItem->type,
            'durability' => [
                'health' => $item->shipItem->health,
                'lifetime' => $item->shipItem->lifetime,
            ],
            'power' => [
                'base' => $item->shipItem->power_base,
                'draw' => $item->shipItem->power_draw,
            ],
            'thermal' => [
                'energy_base' => $item->shipItem->thermal_energy_base,
                'energy_draw' => $item->shipItem->thermal_energy_draw,
                'cooling_rate' => $item->shipItem->cooling_rate,
            ],
            'version' => config('api.sc_data_version'),
        ];

        $this->addSpecificationData($item, $transformed);

        return $transformed;
    }

    /**
     * @param AbstractShipItemSpecification $item
     * @return Collection
     */
    public function includeShops($item): Collection
    {
        return $this->collection($item->shipItem->item->shops, new ShopTransformer());
    }

    private function addSpecificationData(AbstractShipItemSpecification $item, array &$transformed): void
    {
        switch ($item->shipItem->type) {
            case 'Cooler':
                $transformed['cooler'] = [
                    'cooling_rate' => $item->cooling_rate,
                ];
                break;

            case 'Power Plant':
                $transformed['power_plant'] = [
                    'power_output' => $item->power_output,
                ];
                break;

            case 'Quantum Drive':
                $transformed['quantum_drive'] = [
                    'fuel_rate' => $item->fuel_rate,
                    'jump_range' => $item->jump_range,
                    'jumps' => [
                        'standard' => [
                            'speed' => $item->standard_speed,
                            'cooldown' => $item->standard_cooldown,
                            'stage_1_acceleration' => $item->standard_stage_1_acceleration,
                            'stage_2_acceleration' => $item->standard_stage_2_acceleration,
                            'spool_time' => $item->standard_spool_time,
                        ],
                        'spline' => [
                            'speed' => $item->spline_speed,
                            'cooldown' => $item->spline_cooldown,
                            'stage_1_acceleration' => $item->spline_stage_1_acceleration,
                            'stage_2_acceleration' => $item->spline_stage_2_acceleration,
                            'spool_time' => $item->spline_spool_time,
                        ],
                    ]
                ];
                break;

            case 'Shield Generator':
                $transformed['shield'] = [
                    'health' => $item->health,
                    'regeneration' => $item->regeneration,
                    'delay' => [
                        'downed' => $item->downed_delay,
                        'damage' => $item->damage_delay,
                    ],
                    'absorption' => [
                        'physical' => [
                            'min' => $item->min_physical_absorption,
                            'max' => $item->max_physical_absorption,
                        ],
                        'energy' => [
                            'min' => $item->min_energy_absorption,
                            'max' => $item->max_energy_absorption,
                        ],
                        'distortion' => [
                            'min' => $item->min_distortion_absorption,
                            'max' => $item->max_distortion_absorption,
                        ],
                        'thermal' => [
                            'min' => $item->min_thermal_absorption,
                            'max' => $item->max_thermal_absorption,
                        ],
                        'biochemical' => [
                            'min' => $item->min_biochemical_absorption,
                            'max' => $item->max_biochemical_absorption,
                        ],
                        'stun' => [
                            'min' => $item->min_stun_absorption,
                            'max' => $item->max_stun_absorption,
                        ],
                    ]
                ];
                break;

            default:
                break;
        }
    }
}
