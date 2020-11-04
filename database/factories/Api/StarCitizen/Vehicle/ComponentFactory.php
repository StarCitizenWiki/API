<?php

declare(strict_types=1);

namespace Database\Factories\Api\StarCitizen\Vehicle;

use Illuminate\Database\Eloquent\Factories\Factory;

class ComponentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ComponentFactory::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $types = [
            'radar',
            'computers',
            'fuel_intakes',
            'fuel_tanks',
            'quantum_drives',
            'jump_modules',
            'quantum_fuel_tanks',
            'main_thrusters',
            'maneuvering_thrusters',
            'power_plants',
            'coolers',
            'shield_generators',
            'weapons',
            'missiles',
            'utility_items',
            'turrets',
        ];

        $sizes = [
            'V',
            'TBD',
            'S',
            'M',
            'L',
            '9',
            '8',
            '7',
            '6',
            '5',
            '4',
            '3',
            '2',
            '10',
            '1',
            '-',
        ];

        $manufacturers = [
            'TBD',
            '',
            'Lightning Power Ltd',
            'Seal Corp.',
            'KnightBridge Arms',
            'Thermyte Lightfire',
            'ACOM',
            'Yorm',
            'Behring',
            'Juno Starwerk',
            'Basilisk',
            'Aegis Dynamics',
            'Gorgon Defender Industries',
            'Klaus & Werner',
            'Talon Weapon Systems',
            'Tyler Design',
            'Ascension Astro',
            'Firestorm Kinetics',
            'Gallenson Tactical Systems',
            'Sakura Sun',
            'Wen/Cassel',
            'J-Span',
            'Associated Science & Development',
            'Amon & Reese Co.',
            'Kruger Intergalactic',
            'Joker Engineering',
            'Vanduul',
            'Apocalypse Arms',
            'Consolidated Outland',
            'Max Ox',
            'Nova',
            'Esperia',
            'Banu',
            'Xi\'An',
        ];

        $categories = [
            '',
            'R',
            'M',
            'G',
            'V',
            'F',
        ];

        $classes = [
            'RSIAvionic',
            'RSIPropulsion',
            'RSIThruster',
            'RSIModular',
            'RSIWeapon',
        ];

        return [
            'type' => $this->faker->randomElement($types),
            'name' => $this->faker->name,
            'component_size' => $this->faker->randomElement($sizes),
            'category' => $this->faker->randomElement($categories),
            'manufacturer' => $this->faker->randomElement($manufacturers),
            'component_class' => $this->faker->randomElement($classes),
        ];
    }
}
