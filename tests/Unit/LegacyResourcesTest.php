<?php

declare(strict_types=1);

uses(Tests\TestCase::class);

use App\Http\Resources\Game\Item\ItemDescriptionDataResource;
use App\Http\Resources\Game\Item\ItemDurabilityResource;
use App\Http\Resources\Game\Item\ItemHeatConnectionResource;
use App\Http\Resources\Game\Item\ItemResource;
use App\Http\Resources\Game\ItemSpecification\AmmunitionResource;
use App\Http\Resources\Game\ItemSpecification\BombResource;
use App\Http\Resources\Game\ItemSpecification\CargoGridResource;
use App\Http\Resources\Game\ItemSpecification\CounterMeasureResource;
use App\Http\Resources\Game\ItemSpecification\FoodResource;
use App\Http\Resources\Game\ItemSpecification\FuelIntakeResource;
use App\Http\Resources\Game\ItemSpecification\FuelTankResource;
use App\Http\Resources\Game\ItemSpecification\GrenadeResource;
use App\Http\Resources\Game\ItemSpecification\MiningLaserResource;
use App\Http\Resources\Game\ItemSpecification\MiningModuleResource;
use App\Http\Resources\Game\ItemSpecification\MissileRackResource;
use App\Http\Resources\Game\ItemSpecification\MissileResource;
use App\Http\Resources\Game\ItemSpecification\PersonalWeaponResource;
use App\Http\Resources\Game\ItemSpecification\PowerPlantResource;
use App\Http\Resources\Game\ItemSpecification\QuantumDriveResource;
use App\Http\Resources\Game\ItemSpecification\RadarResource;
use App\Http\Resources\Game\ItemSpecification\ShieldResource;
use App\Http\Resources\Game\ItemSpecification\VehicleWeaponResource;
use App\Http\Resources\Game\ItemSpecification\WeaponAttachmentResource;
use App\Http\Resources\Game\ItemSpecification\WeaponModifierResource;
use App\Http\Resources\Game\Manufacturer\ManufacturerLinkResource;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use Illuminate\Http\Request;

it('exposes legacy description data fields', function () {
    $resource = new ItemDescriptionDataResource((object) ['name' => 'NDR', 'value' => '14']);

    $data = $resource->toArray(new Request);

    expect($data['name'])->toBe('NDR')
        ->and($data['value'])->toBe('14')
        ->and($data['type'])->toBe('14');
});

it('includes manufacturer uuid in links', function () {
    $resource = new ManufacturerLinkResource((object) ['name' => 'Behring', 'code' => 'BEHR', 'uuid' => 'uuid-123']);

    $data = $resource->toArray(new Request);

    expect($data['uuid'])->toBe('uuid-123');
});

it('keeps legacy durability and heat aliases', function () {
    $durability = new ItemDurabilityResource(['Health' => 10, 'Lifetime' => 5, 'Repairable' => 1, 'Salvageable' => 0]);
    $heat = new ItemHeatConnectionResource(['OverclockThresholdMinHeat' => 1, 'OverclockThresholdMaxHeat' => 2]);

    $durabilityData = $durability->toArray(new Request);
    $heatData = $heat->toArray(new Request);

    expect($durabilityData['lifetime'])->toBe(5)
        ->and($heatData['overclock_threshold_min'])->toBe(1)
        ->and($heatData['overclock_threshold_max'])->toBe(2);
});

it('adds v2 ammunition damage and uuid fields', function () {
    $resource = new AmmunitionResource([
        'data' => [
            'stdItem' => [
                'Ammunition' => [
                    'UUID' => 'ammo-uuid',
                    'Speed' => 600,
                    'Lifetime' => 2,
                    'Range' => 1200,
                    'ImpactDamage' => [
                        'Physical' => 11.5,
                    ],
                ],
            ],
        ],
    ]);

    $data = $resource->toArray(new Request);

    expect($data['uuid'])->toBe('ammo-uuid')
        ->and($data['damages']['impact'][0]['damage'])->toBe(11.5);
});

it('adds legacy personal weapon fields', function () {
    $resource = new PersonalWeaponResource([
        'data' => [
            'stdItem' => [
                'DescriptionData' => [
                    'Item Type' => 'Assault Rifle',
                ],
                'Weapon' => [
                    'WeaponClass' => 'Medium',
                    'WeaponType' => 'WeaponGun',
                    'EffectiveRange' => 950,
                    'RateOfFire' => 900,
                    'Capacity' => 50,
                    'Magazine' => [
                        'MaxAmmoCount' => 50,
                        'InitialAmmoCount' => 50,
                    ],
                    'Modes' => [
                        [
                            'Name' => 'Rapid',
                            'LocalisedName' => '[AUTO]',
                            'FireType' => 'rapid',
                            'RoundsPerMinute' => 900,
                            'AmmoPerShot' => 1,
                            'PelletsPerShot' => 1,
                        ],
                    ],
                    'Attachments' => [
                        [
                            'Port' => 'barrel_attachment',
                            'ClassName' => 'gmni_smg_ballistic_01',
                            'UUID' => 'attach-uuid',
                        ],
                    ],
                    'Consumption' => [
                        'RequestedRegenPerSec' => 10,
                        'RequestedAmmoLoad' => 100,
                        'Cooldown' => 1,
                        'CostPerBullet' => 2,
                    ],
                ],
                'Ammunition' => [
                    'ImpactDamage' => [
                        'Physical' => 11.5,
                    ],
                ],
            ],
        ],
    ]);

    $data = $resource->toArray(new Request);

    expect($data['weapon_type'])->toBe('WeaponGun')
        ->and($data['attachments_legacy'][0]['uuid'])->toBe('attach-uuid')
        ->and($data['modes_legacy'][0]['mode'])->toBe('Rapid')
        ->and($data['magazine_legacy']['max_ammo_count'])->toBe(50)
        ->and($data['regen_consumption']['requested_regen_per_sec'])->toBe(10);
});

it('adds legacy mining laser modifiers', function () {
    $resource = new MiningLaserResource([
        'data' => [
            'stdItem' => [
                'Description' => 'Mining laser description.',
                'DescriptionData' => [
                    'Item Type' => 'Mining Laser',
                    'All Charge Rates' => '+10%',
                    'Optimal Charge Rate' => '+5%',
                ],
                'MiningLaser' => [
                    'PowerTransfer' => 2100,
                ],
            ],
        ],
    ]);

    $data = $resource->toArray(new Request);

    expect($data['item_type'])->toBe('Mining Laser')
        ->and($data['description'])->toBe('Mining laser description.')
        ->and($data['modifiers_legacy']['all_charge_rates'])->toBe('+10%');
});

it('adds legacy mining module modifiers', function () {
    $resource = new MiningModuleResource([
        'data' => [
            'stdItem' => [
                'Description' => 'Mining module description.',
                'DescriptionData' => [
                    'Item Type' => 'Mining Module',
                    'Instability' => '-10%',
                ],
                'MiningModule' => [
                    'Type' => 'Active',
                ],
            ],
        ],
    ]);

    $data = $resource->toArray(new Request);

    expect($data['description'])->toBe('Mining module description.')
        ->and($data['modifiers_legacy']['instability'])->toBe('-10%');
});

it('adds grenade legacy fields', function () {
    $resource = new GrenadeResource([
        'data' => [
            'stdItem' => [
                'DescriptionText' => 'Boom.',
                'DescriptionData' => [
                    'Area of Effect' => '5m',
                ],
                'Grenade' => [
                    'AreaOfEffect' => 5,
                ],
            ],
        ],
    ]);

    $data = $resource->toArray(new Request);

    expect($data['description'])->toBe('Boom.')
        ->and($data['aoe'])->toBe('5m');
});

it('adds shield legacy fields', function () {
    $resource = new ShieldResource([
        'data' => [
            'stdItem' => [
                'Shield' => [
                    'MaxShieldHealth' => 100,
                    'MaxShieldRegen' => 10,
                    'DownedDelay' => 5,
                    'DamagedDelay' => 3,
                    'MaxReallocation' => 0.5,
                    'ReallocationRate' => 1.5,
                    'ShieldAbsorption' => [
                        ['Min' => 0.7, 'Max' => 0.9],
                        ['Min' => 0.8, 'Max' => 1.0],
                    ],
                ],
            ],
        ],
    ]);

    $data = $resource->toArray(new Request);

    expect($data['downed_regen_delay'])->toBe(5)
        ->and($data['absorptions']['physical']['min'])->toBe(0.7);
});

it('adds flight controller legacy aliases', function () {
    $resource = new \App\Http\Resources\Game\ItemSpecification\FlightControllerResource([
        'data' => [
            'stdItem' => [
                'Ifcs' => [
                    'scmSpeed' => 200,
                    'boostSpeedForward' => 450,
                    'boostSpeedBackward' => 200,
                    'Afterburner' => [
                        'CapacitorMax' => 20,
                    ],
                ],
            ],
        ],
    ]);

    $data = $resource->toArray(new Request);

    expect($data['scm_boost_forward'])->toBe(450)
        ->and($data['afterburner_capacitor'])->toBe(20);
});

it('adds quantum drive legacy heat and modes', function () {
    $resource = new QuantumDriveResource([
        'data' => [
            'stdItem' => [
                'QuantumDrive' => [
                    'Heat' => [
                        'PreRampUpThermalEnergyDraw' => 10,
                    ],
                    'StandardJump' => [
                        'DriveSpeed' => 100,
                    ],
                    'SplineJump' => [
                        'DriveSpeed' => 50,
                    ],
                ],
            ],
        ],
    ]);

    $data = $resource->toArray(new Request);

    expect($data['pre_ramp_up_thermal_energy_draw'])->toBe(10)
        ->and($data['modes']['normal']['drive_speed'])->toBe(100);
});

it('adds legacy damages for bombs and missiles', function () {
    $bomb = new BombResource([
        'data' => [
            'stdItem' => [
                'Bomb' => [
                    'Damage' => [
                        'Physical' => 10,
                    ],
                ],
            ],
        ],
    ]);

    $missile = new MissileResource([
        'data' => [
            'stdItem' => [
                'Missile' => [
                    'Damage' => [
                        'Energy' => 5,
                    ],
                ],
            ],
        ],
    ]);

    expect($bomb->toArray(new Request)['damages_legacy']['physical'])->toBe(10)
        ->and($missile->toArray(new Request)['damages_legacy']['energy'])->toBe(5);
});

it('adds fuel tank legacy capacity', function () {
    $resource = new FuelTankResource([
        'data' => [
            'stdItem' => [
                'ResourceContainer' => [
                    'Capacity' => [
                        'SCU' => 4.35,
                    ],
                ],
            ],
            'Raw' => [
                'Entity' => [
                    'Components' => [
                        'ItemResourceComponentParams' => [
                            'states' => [
                                'ItemResourceState' => [
                                    'deltas' => [
                                        'ItemResourceDeltaStorage' => [
                                            'generation' => [
                                                'resourceAmountPerSecond' => [
                                                    'SStandardResourceUnit' => [
                                                        'standardResourceUnits' => 1,
                                                    ],
                                                ],
                                            ],
                                            'consumption' => [
                                                'resourceAmountPerSecond' => [
                                                    'SStandardResourceUnit' => [
                                                        'standardResourceUnits' => 1,
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $data = $resource->toArray(new Request);

    expect($data['capacity_legacy'])->toBe(4.35);
});

it('adds weapon modifier flat fields', function () {
    $resource = new WeaponModifierResource([
        'data' => [
            'stdItem' => [
                'WeaponModifier' => [
                    'WeaponStats' => [
                        'Recoil' => [
                            'DecayMultiplier' => 0.8,
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $data = $resource->toArray(new Request);

    expect($data['recoil_decay_multiplier'])->toBe(0.8);
});

it('maps food resource from stdItem data', function () {
    $resource = new FoodResource([
        'data' => [
            'stdItem' => [
                'DescriptionText' => 'Snack.',
                'DescriptionData' => [
                    'NDR' => '14',
                    'Effects' => 'Hypertrophic, Dehydrating',
                ],
                'Food' => [
                    'Type' => 'Food',
                    'OneShotConsume' => 1,
                    'CanBeReclosed' => 0,
                    'DiscardWhenConsumed' => 0,
                ],
            ],
        ],
    ]);

    $data = $resource->toArray(new Request);

    expect($data['nutritional_density_rating'])->toBe('14')
        ->and($data['effects'])->toContain('Hypertrophic')
        ->and($data['one_shot_consume'])->toBeTrue();
});

it('maps counter measure ammo counts', function () {
    $resource = new CounterMeasureResource([
        'data' => [
            'stdItem' => [
                'Ammunition' => [
                    'InitialCapacity' => 8,
                    'Capacity' => 16,
                ],
            ],
        ],
    ]);

    $data = $resource->toArray(new Request);

    expect($data['initial_ammo_count'])->toBe(8)
        ->and($data['max_ammo_count'])->toBe(16);
});

it('maps fuel intake flow rates', function () {
    $resource = new FuelIntakeResource([
        'data' => [
            'stdItem' => [
                'FuelIntake' => [
                    'FlowRates' => [
                        'FuelPushRate' => 3.5,
                        'MinimumRate' => 0.2,
                    ],
                ],
            ],
        ],
    ]);

    $data = $resource->toArray(new Request);

    expect($data['fuel_push_rate'])->toBe(3.5)
        ->and($data['minimum_rate'])->toBe(0.2);
});

it('maps missile rack ports to capacity', function () {
    $resource = new MissileRackResource([
        'data' => [
            'stdItem' => [
                'Ports' => [
                    ['MaxSize' => 4],
                    ['MaxSize' => 4],
                ],
            ],
        ],
    ]);

    $data = $resource->toArray(new Request);

    expect($data['missile_count'])->toBe(2)
        ->and($data['missile_size'])->toBe(4);
});

it('maps power plant output', function () {
    $resource = new PowerPlantResource([
        'data' => [
            'stdItem' => [
                'PowerConnection' => [
                    'PowerDraw' => 42,
                ],
            ],
        ],
    ]);

    $data = $resource->toArray(new Request);

    expect($data['power_output'])->toBe(42);
});

it('maps radar legacy fields', function () {
    $resource = new RadarResource([
        'data' => [
            'Raw' => [
                'Entity' => [
                    'Components' => [
                        'SCItemRadarComponentParams' => [
                            'detectionLifetime' => 10,
                            'altitudeCeiling' => 2000,
                            'enableCrossSectionOcclusion' => 1,
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $data = $resource->toArray(new Request);

    expect($data['detection_lifetime'])->toBe(10)
        ->and($data['enable_cross_section_occlusion'])->toBe(1);
});

it('maps cargo grid dimensions', function () {
    $resource = new CargoGridResource([
        'data' => [
            'stdItem' => [
                'InventoryContainer' => [
                    'x' => 2.5,
                    'y' => 5,
                    'z' => 1.25,
                ],
            ],
        ],
    ]);

    $data = $resource->toArray(new Request);

    expect($data['x'])->toBe(2.5)
        ->and($data['y'])->toBe(5)
        ->and($data['z'])->toBe(1.25);
});

it('maps weapon attachment legacy data', function () {
    $resource = new WeaponAttachmentResource([
        'data' => [
            'stdItem' => [
                'Name' => 'EE04',
                'Size' => 2,
                'Grade' => 1,
                'Type' => 'WeaponAttachment.IronSight',
                'DescriptionText' => 'Scope',
                'DescriptionData' => [
                    'Attachment Point' => 'Optic',
                    'Magnification' => '4x',
                    'Type' => 'Telescopic',
                ],
                'WeaponAttachment' => [
                    'ItemType' => 'Scope',
                    'AttachmentPoint' => 'Optic',
                    'Magnification' => 4,
                    'IronSight' => [
                        'DefaultRange' => 0,
                        'MaxRange' => 500,
                        'RangeIncrement' => 100,
                        'AutoZeroingTime' => 0,
                        'ZoomScale' => 4,
                        'ZoomTimeScale' => 1.25,
                    ],
                ],
            ],
        ],
    ]);

    $data = $resource->toArray(new Request);

    expect($data['attachment_point'])->toBe('Optic')
        ->and($data['iron_sight']['max_range'])->toBe(500);
});

it('maps vehicle weapon legacy data', function () {
    $resource = new VehicleWeaponResource([
        'data' => [
            'stdItem' => [
                'Weapon' => [
                    'Ammunition' => [
                        'Speed' => 760,
                        'Range' => 3800,
                        'Size' => 9,
                        'Capacity' => 100,
                        'ImpactDamage' => [
                            'Energy' => 4670.46,
                        ],
                    ],
                    'Modes' => [
                        [
                            'Name' => 'Single',
                            'LocalisedName' => '[SEMI]',
                            'FireType' => 'single',
                            'RoundsPerMinute' => 60,
                            'AmmoPerShot' => 1,
                            'PelletsPerShot' => 1,
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $data = $resource->toArray(new Request);

    expect($data['damages']['impact']['energy'])->toBe(4670.46)
        ->and($data['modes'][0]['mode'])->toBe('Single');
});

it('adds legacy item fields', function () {
    $itemData = new ItemData([
        'name' => 'Item Name',
        'class_name' => 'item_class',
        'classification' => 'Ship.WeaponGun',
        'size' => 2,
        'type' => 'WeaponGun',
        'sub_type' => 'Cannon',
    ]);

    $itemData->data = [
        'stdItem' => [
            'Mass' => 10,
            'Tags' => ['tag'],
            'RequiredTags' => ['req'],
            'DescriptionText' => 'English desc',
            'InventoryContainer' => [
                'x' => 1,
                'y' => 2,
                'z' => 3,
                'SCU' => 0.1,
                'Unit' => 6,
                'UnitName' => 'µSCU',
            ],
            'InventoryOccupancy' => [
                'UIDimensions' => [
                    'Width' => 0.5,
                    'Length' => 0.5,
                    'Height' => 0.5,
                ],
                'Volume' => [
                    'SCU' => 0.0002,
                ],
            ],
            'Ports' => [
                [
                    'PortName' => 'magazine_attach',
                    'DisplayName' => 'Magazine Slot',
                    'MinSize' => 1,
                    'MaxSize' => 2,
                    'CompatibleTypes' => [
                        ['Type' => 'WeaponAttachment', 'SubTypes' => ['Barrel']],
                    ],
                ],
            ],
            'Distortion' => [
                'DecayRate' => 1,
                'DecayDelay' => 2,
                'Maximum' => 3,
            ],
        ],
        'Raw' => [
            'Entity' => [
                'Components' => [
                    'SEntityComponentDefaultLoadoutParams' => [
                        'loadout' => [
                            'SItemPortLoadoutManualParams' => [
                                'entries' => [
                                    [
                                        'itemPortName' => 'magazine_attach',
                                        'itemUUID' => 'loadout-uuid',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $itemData->setRelation('descriptionData', collect([
        (object) ['name' => 'Manufacturer', 'value' => 'Behring'],
    ]));

    $itemData->setRelation('translations', collect([
        (object) ['locale_code' => 'en_EN', 'translation' => 'English desc'],
        (object) ['locale_code' => 'zh_CN', 'translation' => '中文'],
    ]));

    $itemData->setRelation('manufacturer', (object) ['name' => 'Behring', 'code' => 'BEHR', 'uuid' => 'manu-uuid']);
    $itemData->setRelation('gameVersion', (object) ['code' => '4.4.0']);

    $item = new Item(['uuid' => 'item-uuid']);
    $item->setRelation('data', collect([$itemData]));
    $item->updated_at = '2025-01-01 00:00:00';

    $resource = new ItemResource($item);
    $data = $resource->toArray(new Request);

    expect($data['description_legacy'])->toBe('English desc')
        ->and($data['description_zh'])->toBe('中文')
        ->and($data['description_data_legacy']['Manufacturer'])->toBe('Behring')
        ->and($data['ports_legacy']['magazine_attach']['equipped_item_uuid'])->toBe('loadout-uuid')
        ->and($data['inventory_container']['unit'])->toBe(6)
        ->and($data['dimension_override']['width'])->toBe(0.5)
        ->and($data['volume'])->toBe(0.0002)
        ->and($data['distortion']['decay_rate'])->toBe(1);
});
