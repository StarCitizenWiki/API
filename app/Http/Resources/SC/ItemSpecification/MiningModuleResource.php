<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\ItemSpecification;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\ItemSpecification\MiningLaser\MiningLaserModifierResource;
use Illuminate\Http\Request;

class MiningModuleResource extends AbstractBaseResource
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
            'type' => $this->type,
            'modifiers' => MiningLaserModifierResource::collection($this->whenLoaded('modifiers')),
        ];
    }
}
