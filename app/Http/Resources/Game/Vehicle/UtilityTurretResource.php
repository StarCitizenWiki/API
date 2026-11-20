<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'game_vehicle_utility_turret',
    title: 'Utility Turret Summary',
    properties: [
        new OA\Property(property: 'hardpoint_name', type: 'string', example: 'hardpoint_turret', nullable: true),
        new OA\Property(property: 'turret_class_name', type: 'string', example: 'CNOU_Nomad_Remote_Turret', nullable: true),
        new OA\Property(property: 'turret_type', type: 'string', example: 'Turret.GunTurret', nullable: true),
        new OA\Property(
            property: 'payloads',
            description: 'Installed utility payloads found under the turret. Empty when none are identified.',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'hardpoint_name', type: 'string', example: 'turret_weapon', nullable: true),
                    new OA\Property(property: 'class_name', type: 'string', example: 'GRIN_TractorBeam_S1', nullable: true),
                    new OA\Property(property: 'type', type: 'string', example: 'TractorBeam.UNDEFINED'),
                ],
                type: 'object'
            )
        ),
    ],
    type: 'object'
)]
class UtilityTurretResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $turret = $this->resource;

        return [
            'hardpoint_name' => $turret['HardpointName'] ?? null,
            'turret_class_name' => $turret['TurretClassName'] ?? null,
            'turret_type' => $turret['TurretType'] ?? null,
            'payloads' => array_map(static fn (array $item): array => [
                'hardpoint_name' => $item['HardpointName'] ?? null,
                'class_name' => $item['ClassName'] ?? null,
                'type' => $item['Type'],
            ], $turret['Payloads'] ?? []),
        ];
    }
}
