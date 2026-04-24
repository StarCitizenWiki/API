<?php

declare(strict_types=1);

namespace App\Http\Resources\Game;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'game_version',
    title: 'Game Version',
    properties: [
        new OA\Property(property: 'code', type: 'string', example: '4.7.0-LIVE.11518367'),
        new OA\Property(property: 'channel', type: 'string', example: 'live'),
        new OA\Property(property: 'released_at', type: 'string', format: 'date-time', example: '2024-12-15T10:00:00+00:00'),
        new OA\Property(property: 'is_default', type: 'boolean', example: true),
    ],
    type: 'object'
)]
class GameVersionResource extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return [];
    }

    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'channel' => $this->channel,
            'released_at' => $this->released_at?->toIso8601String(),
            'is_default' => $this->is_default,
        ];
    }
}
