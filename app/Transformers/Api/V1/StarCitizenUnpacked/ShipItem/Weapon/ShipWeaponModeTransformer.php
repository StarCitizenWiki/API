<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\Weapon;

use App\Models\StarCitizenUnpacked\ShipItem\Weapon\WeaponMode;
use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;

class ShipWeaponModeTransformer extends TransformerAbstract
{
    /**
     * @param WeaponMode $mode
     *
     * @return array
     */
    public function transform(WeaponMode $mode): array
    {
        $damages = $mode->damages->groupBy('type');

        return array_filter([
            'name' => $mode->name,
            'localized_name' => $mode->localized_name,
            'rpm' => $mode->rounds_per_minute,
            'ammo_per_shot' => $mode->ammo_per_shot,
            'pellets_per_shot' => $mode->pellets_per_shot,
            'damages' => $this->mapDamages($damages)
        ]);
    }

    private function mapDamages(Collection $damages): array
    {
        return $damages->mapWithKeys(function ($damages, $class) {
            return [
                sprintf('per_%s', $class) => $damages->mapWithKeys(function ($damage) {
                    return (new WeaponDamageTransformer())->transform($damage);
                })
            ];
        })
            ->toArray();
    }
}
