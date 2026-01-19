<?php

declare(strict_types=1);

namespace App\Http\Resources\Rsi\CommLink;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'comm_link_link',
    title: 'Comm-Link Link',
    description: 'Resource link to a Comm-Link',
    properties: [
        new OA\Property(property: 'api_url', type: 'string'),
    ],
    type: 'object'
)]
class CommLinkLinkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'href' => $this->href,
            'text' => $this->text,
        ];
    }
}
