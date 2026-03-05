<?php

declare(strict_types=1);

namespace App\Http\Resources\Rsi\CommLink\Image;

use App\Http\Resources\Rsi\CommLink\CommLinkResource;
use Illuminate\Http\Request;

/**
 * Image Transformer
 */
class ImageHashResource extends ImageResource
{
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);

        if (isset($this->similarity)) {
            $data['similarity'] = $this->similarity;
        }

        $data['comm_links'] = $this->whenLoaded(
            'commLinks',
            fn () => CommLinkResource::collection($this->commLinks)->resolve($request)
        );

        return $data;
    }
}
