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
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $data = parent::toArray($request);

        if (isset($this->similarity)) {
            $data['similarity'] = $this->similarity;
        }

        $data['comm_links'] = CommLinkResource::collection($this->whenLoaded('commLinks'));

        return $data;
    }
}
