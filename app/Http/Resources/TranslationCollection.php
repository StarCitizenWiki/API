<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TranslationCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        $out = [];
        foreach ($this->collection as $translation) {
            $out[$translation->locale_code] = $translation->translation;
        }

        return $out;
    }
}
