<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Item;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;

class ItemDurabilityDataResource extends AbstractBaseResource
{
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
            'repairable' => $this->repairable,
            'salvageable' => $this->salvageable,
        ];
    }
}
