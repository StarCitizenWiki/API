<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Char;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;

class ClothingResistanceResource extends AbstractBaseResource
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
            'type' => $this->type,
            'threshold' => $this->threshold,
            'multiplier' => $this->multiplier,
        ];
    }
}
