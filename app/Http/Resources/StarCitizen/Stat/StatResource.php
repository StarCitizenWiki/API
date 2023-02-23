<?php

declare(strict_types=1);

namespace App\Http\Resources\StarCitizen\Stat;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'stat_v2',
    title: 'RSI Stats',
    description: 'Stats about fans and funds',
    properties: [
        new OA\Property(property: 'funds', type: 'float'),
        new OA\Property(property: 'fans', type: 'float'),
        new OA\Property(property: 'fleet', type: 'float'),
        new OA\Property(property: 'timestamp', type: 'timestamp'),
    ],
    type: 'json'
)]
class StatResource extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return [];
    }

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
