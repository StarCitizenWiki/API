<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Item;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;

class ItemContainerResource extends AbstractBaseResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $unit = match ($this->unit) {
            2 => 'cSCU',
            6 => 'µSCU',
            default => 'SCU',
        };

        return [
            'width' => $this->width,
            'height' => $this->height,
            'length' => $this->length,
            'dimension' => $this->dimension,
            'scu' => $this->calculated_scu,
            'scu_converted' => $this->original_converted_scu,
            'unit' => $unit,
        ];
    }
}
