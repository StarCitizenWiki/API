<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Item;

use App\Http\Resources\AbstractTranslationResource;
use Illuminate\Http\Request;

class ItemDimensionResource extends AbstractTranslationResource
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
        $sumDim = $this->dimension->width + $this->dimension->height + $this->dimension->length;
        $sumTrueDim = $this->true_dimension->width + $this->true_dimension->height + $this->true_dimension->length;

        return [
            'width' => $this->dimension->width,
            'height' => $this->dimension->height,
            'length' => $this->dimension->length,
            'volume' => $this->dimension->volume ?? $this->true_dimension->volume,
            $this->mergeWhen($sumDim !== $sumTrueDim, [
                'true_dimension' => [
                    'width' => $this->true_dimension->width,
                    'height' => $this->true_dimension->height,
                    'length' => $this->true_dimension->length,
                ]
            ]),
        ];
    }
}
