<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\Weapon;

use App\Models\StarCitizenUnpacked\ShipItem\Weapon\Weapon;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class ShipWeaponTransformer extends TransformerAbstract
{

    protected $defaultIncludes = [
        'modes',
    ];

    /**
     * @param Weapon $weapon
     * @return array
     */
    public function transform(Weapon $weapon): array
    {
        return [
            'speed' => $weapon->speed,
            'range' => $weapon->range,
            'size' => $weapon->size,
            'capacity' => $weapon->capacity,
            'damages' => $this->mapDamages($weapon->damages->groupBy('type'))
        ];
    }

    private function mapDamages(\Illuminate\Support\Collection $damages): array
    {
        return $damages->mapWithKeys(function ($damages, $class) {
            return [
                $class => $damages->mapWithKeys(function ($damage) {
                    return (new WeaponDamageTransformer())->transform($damage);
                })
            ];
        })
            ->toArray();
    }

    public function includeModes(Weapon $weapon): Collection
    {
        return $this->collection($weapon->modes, new ShipWeaponModeTransformer());
    }
}
