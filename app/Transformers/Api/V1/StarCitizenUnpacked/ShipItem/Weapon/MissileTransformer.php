<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\Weapon;

use App\Models\StarCitizenUnpacked\ShipItem\Weapon\Missile;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class MissileTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = [
        'damages',
    ];

    /**
     * @param Missile $missile
     * @return array
     */
    public function transform(Missile $missile): array
    {
        return [
            'signal_type' => $missile->signal_type,
            'lock_time' => $missile->lock_time,
            'damage_total' => $missile->damage ?? 0,
        ];
    }

    public function includeDamages(Missile $missile): Collection
    {
        return $this->collection($missile->damages, new MissileDamageTransformer());
    }
}
