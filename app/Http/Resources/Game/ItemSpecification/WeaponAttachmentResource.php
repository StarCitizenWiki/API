<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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
        $description = Arr::get($stdItem, 'DescriptionData', []);
        $weaponAttachment = Arr::get($stdItem, 'WeaponAttachment', []);
        $ammo = Arr::get($stdItem, 'Ammunition', []);

        $ammoData = $ammo !== [] ? [
            'ammunition_uuid' => Arr::get($ammo, 'UUID'),
            'initial_ammo_count' => Arr::get($ammo, 'InitialCapacity'),
            'max_ammo_count' => Arr::get($ammo, 'Capacity'),
        ] : null;

        $ironSight = Arr::get($weaponAttachment, 'IronSight', []);
        $ironSightData = $ironSight !== [] ? [
            'default_range' => Arr::get($ironSight, 'DefaultRange'),
            'max_range' => Arr::get($ironSight, 'MaxRange'),
            'range_increment' => Arr::get($ironSight, 'RangeIncrement'),
            'auto_zeroing_time' => Arr::get($ironSight, 'AutoZeroingTime'),
            'zoom_scale' => Arr::get($ironSight, 'ZoomScale'),
            'zoom_time_scale' => Arr::get($ironSight, 'ZoomTimeScale'),
        ] : null;

        $stdType = Arr::get($stdItem, 'Type');
        $subType = null;
        if (is_string($stdType) && str_contains($stdType, '.')) {
            $parts = explode('.', $stdType);
            $subType = $parts !== [] ? Arr::last($parts) : null;
        }

        return [
            'description' => Arr::get($stdItem, 'DescriptionText', Arr::get($stdItem, 'Description')),
            'name' => Arr::get($stdItem, 'Name'),
            'size' => Arr::get($stdItem, 'Size'),
            'grade' => Arr::get($stdItem, 'Grade'),
            'type' => Arr::get($description, 'Type', $subType),
            'sub_type' => $subType,
            'item_type' => Arr::get($weaponAttachment, 'ItemType', Arr::get($description, 'Item Type')),
            'attachment_point' => Arr::get($weaponAttachment, 'AttachmentPoint', Arr::get($description, 'Attachment Point')),
            'magnification' => Arr::get($weaponAttachment, 'Magnification', Arr::get($description, 'Magnification')),
            'capacity' => Arr::get($description, 'Capacity'),
            'utility_class' => Arr::get($description, 'Class'),
            'ammo' => $ammoData,
            'iron_sight' => $ironSightData,
        ];
    }
}
