<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Char;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\Weapon\WeaponDamageResource;
use App\Http\Resources\SC\Weapon\WeaponModeResource;
use Illuminate\Http\Request;

class PersonalWeaponResource extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return [
            'modes',
            'damages',
            'ports',
            'shops',
            'shops.items',
        ];
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'class' => $this->weapon_class,
            'magazine_type' => $this->magazineType,
            'magazine_size' => $this->magazine->max_ammo_count ?? null,
            'effective_range' => $this->effective_range ?? null,
            'damage_per_shot' => $this->ammunition->damage ?? null,
            'rof' => $this->rof ?? null,
            'modes' => WeaponModeResource::collection($this->whenLoaded('modes')),
            'damages' => WeaponDamageResource::collection($this->whenLoaded('damages')),
        ];

        if ($this->sub_type !== 'Knife') {
            $data['ammunition'] = [
                'size' => $this->ammunition->size ?? null,
                'lifetime' => $this->ammunition->lifetime ?? null,
                'speed' => $this->ammunition->speed ?? null,
                'range' => $this->ammunition->range ?? null,
            ];
        }

        $baseModel = $this->baseModel;
        if ($baseModel !== null && $baseModel->item->name !== $this->item->name) {
            $data['base_model'] = $this->makeApiUrl(self::UNPACKED_PERSONAL_WEAPONS_SHOW, $baseModel->item_uuid);
        }

        $data += [
            'updated_at' => $this->updated_at,
            'version' => $this->version,
        ];

        return $data;
    }
}
