<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'weapon_attachment_rgb_color',
    title: 'Weapon Attachment RGB Color',
    description: 'RGB color triplet as provided by the game data.',
    properties: [
        new OA\Property(property: 'r', type: 'double', example: 1.0, nullable: true),
        new OA\Property(property: 'g', type: 'double', example: 0.0, nullable: true),
        new OA\Property(property: 'b', type: 'double', example: 0.0, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'weapon_attachment_iron_sight',
    title: 'Weapon Attachment Iron Sight',
    properties: [
        new OA\Property(property: 'default_range', type: 'double', nullable: true),
        new OA\Property(property: 'max_range', type: 'double', nullable: true),
        new OA\Property(property: 'range_increment', type: 'double', nullable: true),
        new OA\Property(property: 'auto_zeroing_time', type: 'double', nullable: true),
        new OA\Property(property: 'zoom_scale', type: 'double', nullable: true),
        new OA\Property(property: 'zoom_time_scale', type: 'double', nullable: true),
        new OA\Property(
            property: 'zoom_time_change',
            description: 'Computed as `1 - zoom_time_scale`.',
            type: 'double',
            nullable: true
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'weapon_attachment_magazine',
    title: 'Weapon Attachment Magazine',
    properties: [
        new OA\Property(property: 'initial_ammo_count', type: 'integer', nullable: true),
        new OA\Property(property: 'max_ammo_count', type: 'integer', nullable: true),
        new OA\Property(property: 'max_restock_count', type: 'integer', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'weapon_attachment_laser_pointer',
    title: 'Weapon Attachment Laser Pointer',
    properties: [
        new OA\Property(property: 'range', type: 'double', nullable: true),
        new OA\Property(property: 'color', ref: '#/components/schemas/weapon_attachment_rgb_color', nullable: true),
        new OA\Property(property: 'color_css', type: 'string', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'weapon_attachment_flashlight_profile',
    title: 'Weapon Attachment Flashlight Profile',
    description: 'Single flashlight profile (typically keyed as `narrow` or `wide`).',
    properties: [
        new OA\Property(property: 'port_name', type: 'string', nullable: true),
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'light_type', type: 'string', nullable: true),
        new OA\Property(property: 'light_radius', type: 'double', nullable: true),
        new OA\Property(property: 'intensity', type: 'double', nullable: true),
        new OA\Property(property: 'color', ref: '#/components/schemas/weapon_attachment_rgb_color', nullable: true),
        new OA\Property(property: 'color_css', type: 'string', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'weapon_attachment_flashlight',
    title: 'Weapon Attachment Flashlight',
    description: 'Flashlight profiles keyed by beam type (`narrow`, `wide`) when present.',
    type: 'object',
    additionalProperties: new OA\AdditionalProperties(ref: '#/components/schemas/weapon_attachment_flashlight_profile')
)]
#[OA\Schema(
    schema: 'weapon_attachment_barrel_attachment',
    title: 'Weapon Attachment Barrel Attachment',
    description: 'Barrel attachment map. Keys reflect the raw barrel fields (snake_cased), plus `attachment_point` and `type` injected by the resource.',
    properties: [
        new OA\Property(property: 'attachment_point', type: 'string', nullable: true),
        new OA\Property(
            property: 'type',
            description: 'Currently mirrors `attachment_point` (overrides any snake-cased barrel `Type` field).',
            type: 'string',
            nullable: true
        ),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'weapon_attachment',
    title: 'Weapon Attachment',
    description: 'Weapon attachment details derived from stdItem.WeaponAttachment. Only non-empty blocks are returned.',
    properties: [
        new OA\Property(property: 'iron_sight', ref: '#/components/schemas/weapon_attachment_iron_sight', nullable: true),
        new OA\Property(property: 'laser_pointer', ref: '#/components/schemas/weapon_attachment_laser_pointer', nullable: true),
        new OA\Property(property: 'flashlight', ref: '#/components/schemas/weapon_attachment_flashlight', nullable: true),
        new OA\Property(property: 'magazine', ref: '#/components/schemas/weapon_attachment_magazine', nullable: true),

        // Conditional: only when Barrel.Type is Compensator or Flash Hider
        new OA\Property(property: 'compensator', ref: '#/components/schemas/weapon_attachment_barrel_attachment', nullable: true),
        new OA\Property(property: 'flash_hider', ref: '#/components/schemas/weapon_attachment_barrel_attachment', nullable: true),
    ],
    type: 'object'
)]
class WeaponAttachmentResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);
        $stdItem = $this->extractStdItem($data);
        $weaponAttachment = Arr::get($stdItem, 'WeaponAttachment', []);

        $ironSight = Arr::get($weaponAttachment, 'IronSight', []);
        $ironSightData = $ironSight !== [] ? [
            'default_range' => Arr::get($ironSight, 'DefaultRange'),
            'max_range' => Arr::get($ironSight, 'MaxRange'),
            'range_increment' => Arr::get($ironSight, 'RangeIncrement'),
            'auto_zeroing_time' => Arr::get($ironSight, 'AutoZeroingTime'),
            'zoom_scale' => Arr::get($ironSight, 'ZoomScale'),
            'zoom_time_scale' => Arr::get($ironSight, 'ZoomTimeScale'),
            'zoom_time_change' => 1 - Arr::get($ironSight, 'ZoomTimeScale', 1),
        ] : null;

        $magazine = Arr::get($weaponAttachment, 'Magazine', []);
        $magazineData = $magazine !== [] ? [
            'initial_ammo_count' => Arr::get($magazine, 'InitialAmmoCount'),
            'max_ammo_count' => Arr::get($magazine, 'MaxAmmoCount'),
            'max_restock_count' => Arr::get($magazine, 'MaxRestockCount'),
        ] : null;

        $laserPointer = Arr::get($weaponAttachment, 'LaserPointer', []);
        $laserPointerData = $laserPointer !== [] ? [
            'range' => Arr::get($laserPointer, 'Range'),
            'color' => Arr::get($laserPointer, 'Color') ? [
                'r' => Arr::get($laserPointer, 'Color.R'),
                'g' => Arr::get($laserPointer, 'Color.G'),
                'b' => Arr::get($laserPointer, 'Color.B'),
            ] : null,
            'color_css' => Arr::get($laserPointer, 'ColorCss'),
        ] : null;

        $flashLightData = collect(Arr::get($weaponAttachment, 'Flashlight', []))
            ->mapWithKeys(fn ($value) => [
                str_contains($value['ClassName'], 'narrow') ? 'narrow' : 'wide' => [
                    'port_name' => Arr::get($value, 'PortName'),
                    'name' => Arr::get($value, 'Name'),
                    'light_type' => Arr::get($value, 'LightType'),
                    'light_radius' => Arr::get($value, 'LightRadius'),
                    'intensity' => Arr::get($value, 'Intensity'),
                    'color' => Arr::get($value, 'Color') ? [
                        'r' => Arr::get($value, 'Color.R'),
                        'g' => Arr::get($value, 'Color.G'),
                        'b' => Arr::get($value, 'Color.B'),
                    ] : null,
                    'color_css' => Arr::get($value, 'ColorCss'),
                ],
            ])
            ->toArray();

        $barrelAttachment = Arr::get($weaponAttachment, 'Barrel', []);
        $barrelAttachmentType = Arr::get($barrelAttachment, 'Type');

        $key = $barrelAttachmentType === 'Compensator'
            ? 'compensator'
            : ($barrelAttachmentType === 'Flash Hider' ? 'flash_hider' : null);

        $out = [
            'iron_sight' => $ironSightData,
            'laser_pointer' => $laserPointerData,
            'flashlight' => $flashLightData,
            'magazine' => $magazineData,
        ];

        if ($key) {
            $out[$key] = collect($barrelAttachment)
                ->mapWithKeys(fn ($value, $key) => [Str::snake($key) => $value])
                ->put('attachment_point', Arr::get($weaponAttachment, 'AttachmentPoint'))
                ->put('type', Arr::get($weaponAttachment, 'AttachmentPoint'))
                ->toArray();
        }

        return array_filter($out, static fn ($value) => ! empty($value));
    }
}
