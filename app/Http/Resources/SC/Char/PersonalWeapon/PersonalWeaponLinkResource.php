<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Char\PersonalWeapon;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\Manufacturer\ManufacturerLinkResource;
use App\Http\Resources\SC\Shop\ShopResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'personal_weapon_link_v2',
    title: 'Personal Weapon Link',
    description: 'Link information to an Item',
    properties: [
        new OA\Property(property: 'uuid', type: 'string'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'type', type: 'string', nullable: true),
        new OA\Property(property: 'class', type: 'string', nullable: true),
        new OA\Property(property: 'manufacturer', ref: '#/components/schemas/manufacturer_link_v2'),
        new OA\Property(property: 'link', type: 'string'),

        new OA\Property(property: 'updated_at', type: 'string'),
        new OA\Property(property: 'version', type: 'string'),

    ],
    type: 'object'
)]
class PersonalWeaponLinkResource extends AbstractBaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $include = $request->get('include', '');
        if (empty($include)) {
            $include = '';
        }

        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'type' => $this->weapon_type,
            'class' => $this->weapon_class,
            'manufacturer' => new ManufacturerLinkResource($this->manufacturer),
            'link' => $this->makeApiUrl(self::ITEMS_SHOW, $this->uuid),
            $this->mergeWhen(str_contains($include, 'shops'), [
                'shops' => ShopResource::collection($this->shops),
            ]),

            'updated_at' => $this->updated_at,
            'version' => config('api.sc_data_version'),
        ];
    }
}
