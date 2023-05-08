<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Item;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;

class ItemPortResource extends AbstractBaseResource
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
            'name' => $this->name,
            'display_name' => $this->display_name,
            'position' => $this->position,
            'sizes' => [
                'min' => $this->min_size,
                'max' => $this->max_size,
            ],
            $this->mergeWhen($this->item !== null, [
                'equipped_item' => new ItemResource($this->item),
            ]),
        ];
    }
}
