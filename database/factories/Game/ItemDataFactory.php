<?php

declare(strict_types=1);

namespace Database\Factories\Game;

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game\ItemData>
 */
class ItemDataFactory extends Factory
{
    protected $model = ItemData::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_id' => Item::factory(),
            'game_version_id' => GameVersion::factory(),
            'manufacturer_id' => Manufacturer::factory(),
            'name' => fake()->words(3, true),
            'class_name' => fake()->lexify('item_????????'),
            'type' => fake()->randomElement(['Armor', 'WeaponAttachment', 'Food', 'Drink', 'WeaponPersonal']),
            'sub_type' => fake()->randomElement(['Helmet', 'Pants', 'Scope', 'Medical']),
            'classification' => fake()->randomElement([
                'FPS.Clothing.Helmet',
                'FPS.Armor.Heavy',
                'FPS.Weapon.Rifle',
            ]),
            'size' => fake()->numberBetween(1, 5),
            'grade' => fake()->numberBetween(1, 5),
            'class' => fake()->randomElement(['Civilian', 'Military', 'Industrial']),
            'base_id' => null,
            'data' => [

            ],
        ];
    }

    public function cooler(): self|Factory
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'Cooler',
            'sub_type' => 'UNDEFINED',
            'classification' => 'Ship.Cooler',
            'data' => [
                'stdItem' => [
                    'Cooler' => [
                        'CoolingRate' => fake()->numberBetween(100000, 1000000),
                        'SuppressionIRFactor' => fake()->numberBetween(0, 1),
                        'SuppressionHeatFactor' => fake()->numberBetween(0, 1),
                    ],
                ],
            ],
        ]);
    }

    public function shield(): self|Factory
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'Shield',
            'sub_type' => 'UNDEFINED',
            'classification' => 'Ship.Shield',
            'data' => json_decode(
                <<<'JSON'
{
                "MaxShieldHealth": 66000,
                "MaxShieldRegen": 5940,
                "DecayRatio": 0.25,
                "ReservePoolInitialHealthRatio": 1,
                "ReservePoolMaxHealthRatio": 1,
                "ReservePoolRegenRateRatio": 1,
                "ReservePoolDrainRateRatio": 2.5,
                "DownedDelay": 12.7,
                "DamagedDelay": 6.33,
                "ElectricalChargeDamageResistance": 0,
                "StunParams": {
                    "MinAlphaDamageRatio": 0.005,
                    "MaxAlphaDamageRatio": 0.15,
                    "MinStunTime": 0,
                    "MaxStunTime": 12
                },
                "Absorption": {
                    "Physical": {
                        "Minimum": 0,
                        "Maximum": 0.45
                    },
                    "Energy": {
                        "Minimum": 1,
                        "Maximum": 1
                    },
                    "Distortion": {
                        "Minimum": 1,
                        "Maximum": 1
                    },
                    "Thermal": {
                        "Minimum": 1,
                        "Maximum": 1
                    },
                    "Biochemical": {
                        "Minimum": 1,
                        "Maximum": 1
                    },
                    "Stun": {
                        "Minimum": 1,
                        "Maximum": 1
                    }
                },
                "Resistance": {
                    "Physical": {
                        "Minimum": 0,
                        "Maximum": 0.25
                    },
                    "Energy": {
                        "Minimum": 0.03,
                        "Maximum": 0.1
                    },
                    "Distortion": {
                        "Minimum": 0.75,
                        "Maximum": 0.95
                    },
                    "Thermal": {
                        "Minimum": 0,
                        "Maximum": 0
                    },
                    "Biochemical": {
                        "Minimum": 0,
                        "Maximum": 0
                    },
                    "Stun": {
                        "Minimum": 0,
                        "Maximum": 0
                    }
                },
                "RegenerationTime": 11.11111111111111
            }
JSON, true, 512, JSON_THROW_ON_ERROR
            ),
        ]);
    }

    public function emp(): self|Factory
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'EMP',
            'sub_type' => 'UNDEFINED',
            'classification' => 'Ship.EMP',
            'data' => [
                'stdItem' => [
                    'Emp' => [
                        'ChargeTime' => fake()->numberBetween(1, 10),
                        'DistortionDamage' => fake()->numberBetween(100, 1000),
                        'EmpRadius' => fake()->numberBetween(100, 1000),
                        'MinEmpRadius' => fake()->numberBetween(10, 100),
                        'PhysRadius' => fake()->numberBetween(100, 1000),
                        'MinPhysRadius' => fake()->numberBetween(10, 100),
                        'Pressure' => fake()->numberBetween(0, 100),
                        'UnleashTime' => fake()->numberBetween(0.1, 1),
                        'CooldownTime' => fake()->numberBetween(0.1, 1),
                    ],
                ],
            ],
        ]);
    }

    public function seat(): self|Factory
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'Seat',
            'sub_type' => 'UNDEFINED',
            'classification' => 'Ship.Seat',
            'data' => [
                'stdItem' => [
                    'Seat' => [
                        'SeatType' => fake()->randomElement(['HOTAS_C_L', 'HOTAS_C_R', 'Pilot', 'Copilot']),
                        'Yaw' => [
                            'Minimum' => fake()->numberBetween(-180, 0),
                            'Maximum' => fake()->numberBetween(0, 180),
                        ],
                        'Pitch' => [
                            'Minimum' => fake()->numberBetween(-90, 0),
                            'Maximum' => fake()->numberBetween(0, 90),
                        ],
                        'SetYawPitchLimits' => fake()->boolean(),
                        'HasEjection' => fake()->boolean(),
                        'Ejection' => [
                            'MaxLinearVelocity' => fake()->numberBetween(1000, 5000),
                            'MaxLinearAcceleration' => fake()->numberBetween(50, 500),
                            'MaxAngularVelocity' => fake()->numberBetween(1000, 5000),
                            'MaxAngularAcceleration' => fake()->numberBetween(50, 500),
                            'EjectionLoopTime' => fake()->randomFloat(1, 0.5, 2),
                        ],
                    ],
                ],
            ],
        ]);
    }
}
