<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\Weapon;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class WeaponDamageTransformer extends TransformerAbstract
{
    /**
     * @param Model $mode
     *
     * @return array
     */
    public function transform(Model $mode): array
    {
        return [
            $mode->name => $mode->damage,
        ];
    }
}
