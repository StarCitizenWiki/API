<?php

declare(strict_types=1);

namespace App\Http\Resources\Rsi\CommLink;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'comm_link_link',
    title: 'Comm-Link Link',
    description: 'An external hyperlink found within a Comm-Link\'s content.',
    properties: [
        new OA\Property(property: 'href', description: 'The URL the link points to.', type: 'string', format: 'uri', example: 'http://robertsspaceindustries.com/forums/'),
        new OA\Property(property: 'text', description: 'Display text of the hyperlink.', type: 'string', example: 'forums'),
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
