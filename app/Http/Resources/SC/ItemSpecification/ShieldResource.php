<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\ItemSpecification;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;

class ShieldResource extends AbstractBaseResource
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
            'max_shield_health' => $this->max_shield_health,
            'max_shield_regen' => $this->max_shield_regen,
            'decay_ratio' => $this->decay_ratio,
            'regen_delay' => [
                'downed' => $this->downed_regen_delay,
                'damage' => $this->damage_regen_delay,
            ],
            'max_reallocation' => $this->max_reallocation,
            'reallocation_rate' => $this->reallocation_rate,
            'hardening' => [
                'factor' => $this->shield_hardening_factor,
                'duration' => $this->shield_hardening_duration,
                'cooldown' => $this->shield_hardening_cooldown,
            ],
        ];
    }
}
