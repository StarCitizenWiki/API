<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Item;

use App\Http\Resources\AbstractTranslationResource;
use Illuminate\Http\Request;

class ItemDurabilityDataResource extends AbstractTranslationResource
{
    public static function validIncludes(): array
    {
        return [];
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
            'health' => $this->health,
            'max_lifetime' => $this->max_lifetime,
        ];
    }
}
