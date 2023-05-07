<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Char;

use App\Http\Resources\AbstractTranslationResource;
use App\Http\Resources\SC\Item\ItemPortResource;
use App\Http\Resources\SC\Item\ItemResource;
use App\Http\Resources\SC\Weapon\WeaponDamageResource;
use App\Http\Resources\SC\Weapon\WeaponModeResource;
use Illuminate\Http\Request;

class ClothingResistanceResource extends AbstractTranslationResource
{
    public static function validIncludes(): array
    {
        return [];
    }
    
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'type' => $this->type,
            'threshold' => $this->threshold,
            'multiplier' => $this->multiplier,
        ];
    }
}
