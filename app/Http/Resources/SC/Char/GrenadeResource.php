<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Char;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;

class GrenadeResource extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return [
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
    public function toArray($request): array
    {
        return [
            'area_of_effect' => $this->aoe,
            'damage_type' => $this->damage_type,
            'damage' => $this->damage,
            'updated_at' => $this->updated_at,
            'version' => $this->version,
        ];
    }
}
