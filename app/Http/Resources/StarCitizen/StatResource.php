<?php

declare(strict_types=1);

namespace App\Http\Resources\StarCitizen;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'stat',
    title: 'RSI Stats',
    description: 'Statistics about Star Citizen crowdfunding: fans, fleet, and funds (USD)',
    properties: [
        new OA\Property(property: 'funds', description: 'Crowdfunding funds raised in USD', type: 'number', format: 'float', example: 962878547.56),
        new OA\Property(property: 'fans', description: 'Number of registered fans', type: 'integer', example: 6433852),
        new OA\Property(property: 'fleet', description: 'Total Star Citizen fleet size', type: 'integer', example: 6433852),
        new OA\Property(property: 'timestamp', description: 'When the snapshot was taken', type: 'string', format: 'date-time', example: '2026-04-13T00:00:00Z', nullable: true),
    ],
    type: 'object'
)]
class StatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'funds' => $this->funds,
            'fans' => $this->fans,
            'fleet' => $this->fleet,
            'timestamp' => $this->created_at,
        ];
    }
}
