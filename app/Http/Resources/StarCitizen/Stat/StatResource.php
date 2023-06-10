<?php

declare(strict_types=1);

namespace App\Http\Resources\StarCitizen\Stat;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'stat_v2',
    title: 'RSI Stats',
    description: 'Stats about fans and funds',
    properties: [
        new OA\Property(property: 'funds', type: 'float'),
        new OA\Property(property: 'fans', type: 'float'),
        new OA\Property(property: 'fleet', type: 'float'),
        new OA\Property(property: 'timestamp', type: 'string'),
    ],
    type: 'object'
)]
class StatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'funds' => $this->funds,
            'fans' => $this->fans,
            'fleet' => $this->fleet,
            'timestamp' => optional($this)->created_at,
        ];
    }
}
