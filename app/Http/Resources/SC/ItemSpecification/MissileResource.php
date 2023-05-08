<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\ItemSpecification;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\Weapon\WeaponDamageResource;
use Illuminate\Http\Request;

class MissileResource extends AbstractBaseResource
{

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'signal_type' => $this->signal_type,
            'lock_time' => $this->lock_time,
            'damage_total' => $this->damage ?? 0,
            'damages' => WeaponDamageResource::collection($this->whenLoaded('damages')),
        ];
    }
}
