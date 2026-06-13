<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'salvage_modifier',
    title: 'Salvage Modifier',
    description: 'Deprecated: Use values under "weapon_modifier". Salvage-specific tuning taken from Item.stdItem.SalvageModifier',
    properties: [
        new OA\Property(property: 'salvage_speed_multiplier', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'radius_multiplier', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'extraction_efficiency', type: 'double', nullable: true, deprecated: true),
    ],
    type: 'object'
)]
class SalvageModifierResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);
        $stdItem = $this->extractStdItem($data);
        $salvageModifier = Arr::get($stdItem, 'SalvageModifier', []);

        $speed = Arr::get($salvageModifier, 'SalvageSpeedMultiplier');
        $radius = Arr::get($salvageModifier, 'RadiusMultiplier');
        $efficiency = Arr::get($salvageModifier, 'ExtractionEfficiency');

        return [
            'salvage' => collect([
                'salvage_speed_multiplier' => $speed,
                'radius_multiplier' => $radius,
                'extraction_efficiency' => $efficiency,
            ])->filter(fn ($value) => $value !== null)->all(),
        ];
    }
}
