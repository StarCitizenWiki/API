<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;

beforeEach(function (): void {
    $this->version = GameVersion::factory()->create([
        'code' => '4.4.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $this->manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);
});

function itemWithSpec(GameVersion $version, Manufacturer $manufacturer, array $attributes): Item
{
    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create($attributes);

    return $item;
}

describe('salvage modifier', function (): void {
    it('merges SalvageModifier into weapon_modifier.salvage when WeaponModifier is absent', function (): void {
        $item = itemWithSpec($this->version, $this->manufacturer, [
            'name' => 'Salvage Head',
            'type' => 'SalvageHead',
            'class_name' => 'salvage_head_01',
            'classification' => 'Misc.Salvage',
            'data' => [
                'stdItem' => [
                    'SalvageModifier' => [
                        'SalvageSpeedMultiplier' => 1.25,
                        'RadiusMultiplier' => 0.8,
                        'ExtractionEfficiency' => 0.5,
                    ],
                ],
            ],
        ]);

        $this->getJson("/api/items/{$item->uuid}")
            ->assertSuccessful()
            ->assertJsonPath('data.weapon_modifier.salvage.salvage_speed_multiplier', 1.25)
            ->assertJsonPath('data.weapon_modifier.salvage.radius_multiplier', 0.8)
            ->assertJsonPath('data.weapon_modifier.salvage.extraction_efficiency', 0.5)
            ->assertJsonPath(
                'meta.deprecated_fields.salvage_modifier.salvage_speed_multiplier',
                'Use weapon_modifier.salvage.salvage_speed_multiplier instead.'
            );
    });

    it('overlays salvage onto weapon_modifier when WeaponModifier is also present', function (): void {
        $item = itemWithSpec($this->version, $this->manufacturer, [
            'name' => 'Salvage Attachment',
            'type' => null,
            'class_name' => 'salvage_attachment_01',
            'classification' => 'Misc.Salvage.Modifier',
            'data' => [
                'stdItem' => [
                    'SalvageModifier' => [
                        'SalvageSpeedMultiplier' => 1.5,
                        'ExtractionEfficiency' => 0.6,
                    ],
                    'WeaponModifier' => [
                        'ActivateOnAttach' => true,
                        'WeaponStats' => [
                            'Base' => ['FireRateMultiplier' => 1.1],
                        ],
                    ],
                ],
            ],
        ]);

        $this->getJson("/api/items/{$item->uuid}")
            ->assertSuccessful()
            ->assertJsonPath('data.weapon_modifier.base.fire_rate_multiplier', 1.1)
            ->assertJsonPath('data.weapon_modifier.salvage.salvage_speed_multiplier', 1.5)
            ->assertJsonPath('data.weapon_modifier.salvage.extraction_efficiency', 0.6);
    });
});

describe('suit armor for clothing', function (): void {
    it('emits suit_armor alongside clothing when an FPS.Clothing item carries SuitArmor data', function (): void {
        $item = itemWithSpec($this->version, $this->manufacturer, [
            'name' => 'Heavy Work Jacket',
            'type' => 'Char_Clothing_Torso_1',
            'class_name' => 'cbd_apparel_torso_01_jacket_01_01',
            'classification' => 'FPS.Clothing.Torso_1',
            'data' => [
                'stdItem' => [
                    'SuitArmor' => ['DamageResistance' => ['Impact' => 0.5]],
                    'TemperatureResistance' => ['Minimum' => -10, 'Maximum' => 40],
                ],
            ],
        ]);

        $this->getJson("/api/items/{$item->uuid}")
            ->assertSuccessful()
            ->assertJsonStructure(['data' => ['clothing', 'suit_armor']])
            ->assertJsonPath('data.suit_armor.slot', 'Torso_1')
            ->assertJsonPath('data.suit_armor.type', 'Jacket')
            ->assertJsonPath('data.suit_armor.garment_type', 'Jacket');
    });

    it('emits suit_armor with null armor fields for clothing without SuitArmor data', function (): void {
        $item = itemWithSpec($this->version, $this->manufacturer, [
            'name' => 'Light Shirt',
            'type' => 'Char_Clothing_Torso_0',
            'class_name' => 'cbd_apparel_torso_00_shirt_01_01',
            'classification' => 'FPS.Clothing.Torso_0',
            'data' => [
                'stdItem' => [],
            ],
        ]);

        // Clothing schema is deprecated; suit_armor is always emitted for clothing items,
        // with derived metadata (slot/type/garment_type) and null armor fields when no
        // SuitArmor data is present.
        $this->getJson("/api/items/{$item->uuid}")
            ->assertSuccessful()
            ->assertJsonStructure(['data' => ['clothing', 'suit_armor']])
            ->assertJsonPath('data.suit_armor.slot', 'Torso_0')
            ->assertJsonPath('data.suit_armor.type', 'T-Shirt')
            ->assertJsonPath('data.suit_armor.garment_type', 'Shirt')
            ->assertJsonPath('data.suit_armor.damage_resistance.impact', null)
            ->assertJsonPath('data.suit_armor.radiation_resistance', null)
            ->assertJsonPath('data.suit_armor.gforce_resistance', null);
    });
});

describe('tractor beam', function (): void {
    it('detects tractor beams by type', function (): void {
        $item = itemWithSpec($this->version, $this->manufacturer, [
            'name' => 'Tractor Beam',
            'type' => 'TractorBeam',
            'class_name' => 'tractor_beam_01',
            'classification' => 'Ship.Utilities.TractorBeam',
            'data' => [
                'stdItem' => [
                    'TractorBeam' => ['MaxForce' => 50000],
                ],
            ],
        ]);

        $this->getJson("/api/items/{$item->uuid}")
            ->assertSuccessful()
            ->assertJsonPath('data.tractor_beam.force.max', 50000);
    });

    it('detects tractor beams by stdItem.TractorBeam presence regardless of type', function (): void {
        $item = itemWithSpec($this->version, $this->manufacturer, [
            'name' => 'Salvage Tow S3',
            'type' => 'SalvageHead',
            'class_name' => 'salvage_tow_03',
            'classification' => 'Ship.Utilities.Salvage',
            'data' => [
                'stdItem' => [
                    'TractorBeam' => ['MaxForce' => 250000],
                ],
            ],
        ]);

        $this->getJson("/api/items/{$item->uuid}")
            ->assertSuccessful()
            ->assertJsonPath('data.tractor_beam.force.max', 250000);
    });
});
