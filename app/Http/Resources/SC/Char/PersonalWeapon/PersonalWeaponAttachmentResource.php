<?php

namespace App\Http\Resources\SC\Char\PersonalWeapon;

use App\Http\Resources\AbstractBaseResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'personal_weapon_attachment_item_v2',
    title: 'Personal Weapon Attachment',
    type: 'object',
    allOf: [
        new OA\Schema(ref: '#/components/schemas/item_base_v2'),
        new OA\Schema(ref: '#/components/schemas/personal_weapon_attachment_specification_v2'),
    ],
)]

#[OA\Schema(
    schema: 'personal_weapon_attachment_specification_v2',
    title: 'Personal Weapon Attachments',
    type: 'object',
    allOf: [
        new OA\Schema(
            type: 'object',
            oneOf: [
                new OA\Schema(
                    title: 'Personal Weapon Magazine',
                    properties: [
                        new OA\Property(property: 'personal_weapon_magazine', ref: '#/components/schemas/personal_weapon_magazine_v2'),
                    ],
                    type: 'object'
                ),
                new OA\Schema(
                    title: 'Iron Sight',
                    properties: [
                        new OA\Property(property: 'iron_sight', ref: '#/components/schemas/iron_sight_v2'),
                    ],
                    type: 'object'
                ),
                new OA\Schema(
                    title: 'Barrel Attach',
                    properties: [
                        new OA\Property(property: 'barrel_attach', ref: '#/components/schemas/barrel_attach_v2'),
                    ],
                    type: 'object'
                ),
            ],
        ),
        new OA\Schema(
            properties: [
                new OA\Property(property: 'weapon_modifier', ref: '#/components/schemas/item_weapon_modifier_data_v2'),
            ],
            type: 'object'
        ),
    ]
)]

class PersonalWeaponAttachmentResource extends AbstractBaseResource {}
