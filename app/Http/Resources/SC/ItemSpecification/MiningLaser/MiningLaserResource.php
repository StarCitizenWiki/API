<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\ItemSpecification\MiningLaser;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;

class MiningLaserResource extends AbstractBaseResource
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
            'power_transfer' => $this->power_transfer,
            'optimal_range' => $this->optimal_range,
            'maximum_range' => $this->maximum_range,
            'extraction_throughput' => $this->extraction_throughput,
            'module_slots' => $this->module_slots,
            'modifiers' => MiningLaserModifierResource::collection($this->whenLoaded('modifiers')),
        ];
    }
}
