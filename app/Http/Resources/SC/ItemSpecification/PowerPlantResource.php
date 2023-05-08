<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\ItemSpecification;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;

class PowerPlantResource extends AbstractBaseResource
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
            'power_output' => $this->power_output,
        ];
    }
}
