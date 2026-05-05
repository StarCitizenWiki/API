<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle\Builders;

use App\Http\Resources\Game\ItemSpecification\ArmorResource;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use Illuminate\Support\Arr;

/**
 * @internal
 *
 * Pure-function pipeline for building vehicle armor data.
 * Uses a callable for item loading to stay decoupled from Eloquent.
 */
final class VehicleArmorBuilder
{
    /** @var callable(string): Item|null */
    private $itemLoader;

    /**
     * @param  callable(string): Item|null  $itemLoader
     */
    public function __construct(callable $itemLoader)
    {
        $this->itemLoader = $itemLoader;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function buildArmor(array $payload): array
    {
        $armorUuid = Arr::get($payload, 'Armor.UUID');

        if ($armorUuid === null) {
            return [];
        }

        $item = ($this->itemLoader)($armorUuid);

        if ($item === null) {
            return [];
        }

        $itemData = $item->data->first();

        if ($itemData === null) {
            return [];
        }

        return $this->buildArmorFromItemData($armorUuid, $itemData);
    }

    /**
     * Build armor data from resolved ItemData using ArmorResource.
     *
     * Outputs canonical ArmorResource format with deprecated plural key aliases
     * for backward compatibility during transition.
     *
     * @return array<string, mixed>
     */
    public function buildArmorFromItemData(string $uuid, ItemData $itemData): array
    {
        $armor = new ArmorResource($itemData)->resolve(request());
        $armor['uuid'] = $uuid;

        // Deprecated plural aliases for backward compatibility
        $armor['signal_multipliers'] = [
            'cross_section' => $armor['signal_multiplier']['cross_section'] ?? null,
            'infrared' => $armor['signal_multiplier']['infrared'] ?? null,
            'electromagnetic' => $armor['signal_multiplier']['electromagnetic'] ?? null,
        ];
        $armor['damage_multipliers'] = [
            'physical' => $armor['damage_multiplier']['physical'] ?? null,
            'energy' => $armor['damage_multiplier']['energy'] ?? null,
            'distortion' => $armor['damage_multiplier']['distortion'] ?? null,
            'thermal' => $armor['damage_multiplier']['thermal'] ?? null,
            'biochemical' => $armor['damage_multiplier']['biochemical'] ?? null,
            'stun' => $armor['damage_multiplier']['stun'] ?? null,
        ];
        $armor['resistance_multipliers'] = [
            'physical' => $armor['resistance_multiplier']['physical'] ?? null,
            'energy' => $armor['resistance_multiplier']['energy'] ?? null,
            'distortion' => $armor['resistance_multiplier']['distortion'] ?? null,
            'thermal' => $armor['resistance_multiplier']['thermal'] ?? null,
            'biochemical' => $armor['resistance_multiplier']['biochemical'] ?? null,
            'stun' => $armor['resistance_multiplier']['stun'] ?? null,
        ];

        return $armor;
    }
}
