<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'shield_controller',
    title: 'Shield Controller',
    description: '',
    properties: [
        new OA\Property(property: 'face_type', type: 'string', nullable: true),
        new OA\Property(property: 'max_reallocation', type: 'double', nullable: true),
        new OA\Property(property: 'reconfiguration_cooldown', type: 'double', nullable: true),
        new OA\Property(property: 'max_electrical_charge_damage_rate', type: 'double', nullable: true),
    ],
    type: 'object'
)]
class ShieldControllerResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);

        $item = Arr::get($data, 'stdItem.ShieldController', []);

        return [
            'face_type' => Arr::get($item, 'FaceType'),
            'max_reallocation' => Arr::get($item, 'MaxReallocation'),
            'reconfiguration_cooldown' => Arr::get($item, 'ReconfigurationCooldown'),
            'max_electrical_charge_damage_rate' => Arr::get($item, 'MaxElectricalChargeDamageRate'),
        ];
    }
}
