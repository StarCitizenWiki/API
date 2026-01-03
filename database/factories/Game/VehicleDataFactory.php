<?php

declare(strict_types=1);

namespace Database\Factories\Game;

use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game\VehicleData>
 */
class VehicleDataFactory extends Factory
{
    protected $model = VehicleData::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory(),
            'game_version_id' => GameVersion::factory(),
            'manufacturer_id' => Manufacturer::factory(),
            'shipmatrix_id' => fake()->numberBetween(1000, 9999),
            'class_name' => fake()->randomElement(['MISC_Reliant', 'ANVL_Hornet_F7CM', 'RSI_Constellation']),
            'name' => fake()->words(2, true),
            'display_name' => fake()->words(2, true),
            'career' => fake()->randomElement(['Transporter', 'Combat', 'Exploration']),
            'role' => fake()->randomElement(['Starter / Light Freight', 'Medium Fighter', 'Expedition']),
            'is_vehicle' => false,
            'is_gravlev' => false,
            'is_spaceship' => true,
            'size' => fake()->numberBetween(1, 6),
            'data' => [
                'UUID' => '3c9af040-d919-4afc-b768-45821a5b913c',
                'ClassName' => 'MISC_Reliant',
                'Name' => 'MISC Reliant Kore',
                'DescriptionData' => [
                    'Manufacturer' => 'Musashi Industrial & Starflight Concern',
                    'Focus' => 'Light Freight',
                ],
                'DescriptionText' => 'With the Reliant Kore, MISC adds to its already impressive lineup of ships, a smaller introductory-class spacecraft.',
                'Career' => 'Transporter',
                'Role' => 'Starter / Light Freight',
                'Manufacturer' => [
                    'UUID' => 'b28a5c61-63a4-478b-8047-5fba545d5b8a',
                    'Name' => 'Musashi Industrial & Starflight Concern',
                ],
                'Size' => 3,
                'IsVehicle' => false,
                'IsGravlev' => false,
                'IsSpaceship' => true,
                'Insurance' => [
                    'ExpeditedCost' => 340,
                    'StandardClaimTime' => 0.675,
                ],
            ],
        ];
    }
}
