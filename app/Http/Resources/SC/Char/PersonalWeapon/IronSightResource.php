<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Char\PersonalWeapon;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'iron_sight_v2',
    title: 'Iron Sight',
    description: 'FPS Iron Sight',
    properties: [
        new OA\Property(property: 'magnification', type: 'number', nullable: true),
        new OA\Property(property: 'optic_type', type: 'string', nullable: true),
        new OA\Property(property: 'default_range', type: 'number', nullable: true),
        new OA\Property(property: 'max_range', type: 'number', nullable: true),
        new OA\Property(property: 'range_increment', type: 'number', nullable: true),
        new OA\Property(property: 'auto_zeroing_time', type: 'number', nullable: true),
        new OA\Property(property: 'zoom_time_scale', type: 'number', nullable: true),
    ],
    type: 'object'
)]
class IronSightResource extends AbstractBaseResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return array_filter([
            'magnification' => $this->zoom_scale ?? null,
            'optic_type' => $this->optic_type ?? null,
            'default_range' => $this->default_range ?? null,
            'max_range' => $this->max_range ?? null,
            'range_increment' => $this->range_increment ?? null,
            'auto_zeroing_time' => $this->auto_zeroing_time ?? null,
            'zoom_time_scale' => $this->zoom_time_scale ?? null,
        ]);
    }
}
