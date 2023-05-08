<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\ItemSpecification\MiningLaser;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;

class MiningLaserModifierResource extends AbstractBaseResource
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
            $this->name => $this->modifier,
        ];
    }
}
