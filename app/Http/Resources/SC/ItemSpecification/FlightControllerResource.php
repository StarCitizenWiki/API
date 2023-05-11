<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\ItemSpecification;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'flight_controller_v2',
    title: 'Flight Controller',
    properties: [
        new OA\Property(property: 'scm_speed', type: 'double', nullable: true),
        new OA\Property(property: 'max_speed', type: 'double', nullable: true),
        new OA\Property(property: 'pitch', type: 'double', nullable: true),
        new OA\Property(property: 'yaw', type: 'double', nullable: true),
        new OA\Property(property: 'roll', type: 'double', nullable: true),
    ],
    type: 'object'
)]
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
