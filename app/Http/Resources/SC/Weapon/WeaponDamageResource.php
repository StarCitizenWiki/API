<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Weapon;

use App\Http\Resources\AbstractTranslationResource;
use Illuminate\Http\Request;

class WeaponDamageResource extends AbstractTranslationResource
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
            'name' => $this->name,
            'damage' => $this->damage,
        ];
    }
}
