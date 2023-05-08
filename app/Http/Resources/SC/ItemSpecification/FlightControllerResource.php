<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\ItemSpecification;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;

class FlightControllerResource extends AbstractBaseResource
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
            'scm_speed' => $this->scm_speed,
            'max_speed' => $this->max_speed,
            'pitch' => $this->pitch,
            'yaw' => $this->yaw,
            'roll' => $this->roll,
        ];
    }
}
