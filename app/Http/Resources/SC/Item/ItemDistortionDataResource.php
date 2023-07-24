<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Item;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'item_distortion_data_v2',
    title: 'Item Distortion Data',
    properties: [
        new OA\Property(property: 'decay_rate', type: 'double', nullable: true),
        new OA\Property(property: 'decay_delay', type: 'double', nullable: true),
        new OA\Property(property: 'maximum', type: 'double', nullable: true),
        new OA\Property(property: 'overload_ratio', type: 'double', nullable: true),
        new OA\Property(property: 'warning_ratio', type: 'double', nullable: true),
        new OA\Property(property: 'recovery_ratio', type: 'double', nullable: true),
        new OA\Property(property: 'recovery_time', type: 'double', nullable: true),
    ],
    type: 'object'
)]
class ItemDistortionDataResource extends AbstractBaseResource
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
            'decay_delay' => $this->decay_delay,
            'decay_rate' => $this->decay_rate,
            'maximum' => $this->maximum,
            'overload_ratio' => $this->overload_ratio,
            'warning_ratio' => $this->warning_ratio,
            'recovery_ratio' => $this->recovery_ratio,
            'recovery_time' => $this->recovery_time,
        ];
    }
}
