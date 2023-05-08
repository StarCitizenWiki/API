<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\ItemSpecification;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;

class ThrusterResource extends AbstractBaseResource
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
            'thrust_capacity' => $this->thrust_capacity,
            'min_health_thrust_multiplier' => $this->min_health_thrust_multiplier,
            'fuel_burn_per_10k_newton' => $this->fuel_burn_per_10k_newton,
            'type' => $this->type,
        ];
    }
}
