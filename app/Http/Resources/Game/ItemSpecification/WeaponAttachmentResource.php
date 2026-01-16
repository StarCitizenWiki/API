<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'weapon_attachment',
    title: 'Weapon Attachment',
    description: 'Weapon attachment details derived from stdItem.WeaponAttachment and description data.',
    properties: [
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'size', type: 'integer', nullable: true),
        new OA\Property(property: 'grade', type: 'integer', nullable: true),
        new OA\Property(property: 'type', type: 'string', nullable: true),
        new OA\Property(property: 'sub_type', type: 'string', nullable: true),
        new OA\Property(property: 'item_type', type: 'string', nullable: true),
        new OA\Property(property: 'attachment_point', type: 'string', nullable: true),
        new OA\Property(property: 'magnification', type: 'string', nullable: true),
        new OA\Property(property: 'capacity', type: 'string', nullable: true),
        new OA\Property(property: 'utility_class', type: 'string', nullable: true),
        new OA\Property(
            property: 'ammo',
            properties: [
                new OA\Property(property: 'ammunition_uuid', type: 'string', nullable: true),
                new OA\Property(property: 'initial_ammo_count', type: 'integer', nullable: true),
                new OA\Property(property: 'max_ammo_count', type: 'integer', nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'iron_sight',
            properties: [
                new OA\Property(property: 'default_range', type: 'number', nullable: true),
                new OA\Property(property: 'max_range', type: 'number', nullable: true),
                new OA\Property(property: 'range_increment', type: 'number', nullable: true),
                new OA\Property(property: 'auto_zeroing_time', type: 'number', nullable: true),
                new OA\Property(property: 'zoom_scale', type: 'number', nullable: true),
                new OA\Property(property: 'zoom_time_scale', type: 'number', nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
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
                ->push([
                    'attachment_point' => Arr::get($weaponAttachment, 'AttachmentPoint'),
                    'type' => Arr::get($weaponAttachment, 'AttachmentPoint'),
                ])
                ->toArray();
        }

        return array_filter($out, static fn ($value) => ! empty($value));
    }
}
