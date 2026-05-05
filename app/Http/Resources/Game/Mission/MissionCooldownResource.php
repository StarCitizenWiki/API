<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Mission;

use App\Http\Resources\AbstractBaseResource;
use App\Support\Formatting\FormatDuration;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'mission_cooldown',
    title: 'Mission Cooldown',
    properties: [
        new OA\Property(property: 'label', type: 'string', nullable: true),
        new OA\Property(property: 'personal_seconds', type: 'integer'),
        new OA\Property(property: 'abandoned_seconds', type: 'integer'),
        new OA\Property(property: 'personal_variation_seconds', type: 'integer'),
        new OA\Property(property: 'abandoned_variation_seconds', type: 'integer'),
    ],
    type: 'object'
)]
class MissionCooldownResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        $cooldown = $this->resource;
        $personalSeconds = $cooldown['PersonalSeconds'] ?? null;

        return [
            'label' => is_numeric($personalSeconds) && $personalSeconds > 0
                ? FormatDuration::fromSeconds($personalSeconds)
                : null,
            'personal_seconds' => $personalSeconds,
            'abandoned_seconds' => $cooldown['AbandonedSeconds'] ?? null,
            'personal_variation_seconds' => $cooldown['PersonalVariationSeconds'] ?? null,
            'abandoned_variation_seconds' => $cooldown['AbandonedVariationSeconds'] ?? null,
        ];
    }
}
