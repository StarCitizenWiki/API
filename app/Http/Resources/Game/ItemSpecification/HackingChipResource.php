<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'hacking_chip',
    title: 'Hacking Chip',
    description: 'Cryptokey / hacking chip stats pulled from in-game item definitions. Controls charge count, hack duration scaling, failure risk, and access gating tags applied during missions.',
    properties: [
        new OA\Property(
            property: 'max_charges',
            description: 'Number of uses before the chip depletes. Current game data sets this to 3 on all chips.',
            type: 'integer',
            example: 3,
            nullable: true
        ),
        new OA\Property(
            property: 'duration_multiplier',
            description: 'Multiplier applied to the base hack duration. Values below 1.0 speed the hack up; values above 1.0 slow it down. Example 0.5 halves the required time.',
            type: 'double',
            example: 0.5,
            nullable: true
        ),
        new OA\Property(
            property: 'error_chance',
            description: 'Probability (0–1) that a hack attempt fails or produces an error. Higher values indicate more risk. Example 0.9 = 90% error chance.',
            type: 'double',
            example: 0.9,
            nullable: true
        ),
        new OA\Property(
            property: 'access_tag',
            description: 'Gameplay access tag required/assigned when the chip is used (e.g., MissionQuestItem to mark mission-only keys).',
            type: 'string',
            example: 'MissionQuestItem',
            nullable: true
        ),
    ],
    type: 'object'
)]
class HackingChipResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);

        $chip = Arr::get($data, 'stdItem.HackingChip', []);

        return [
            'max_charges' => Arr::get($chip, 'MaxCharges'),
            'duration_multiplier' => Arr::get($chip, 'DurationMultiplier'),
            'error_chance' => Arr::get($chip, 'ErrorChance'),
            'access_tag' => Arr::get($chip, 'AccessTag'),
        ];
    }
}
