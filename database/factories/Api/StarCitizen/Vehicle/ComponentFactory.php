<?php declare(strict_types=1);

/*
 * Copyright (c) 2020
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */


use App\Models\Api\StarCitizen\Vehicle\Component\Component;
use Faker\Generator as Faker;

$factory->define(
    Component::class,
    function (Faker $faker) {
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
            'type' => $faker->randomElement($types),
            'name' => $faker->name,
            'mounts' => $faker->numberBetween(1, 10),
            'component_size' => $faker->randomElement($sizes),
            'category' => $faker->randomElement($categories),
            'size' => $faker->randomElement($sizes),
            'details' => $faker->boolean() ? $faker->text(60) : '',
            'quantity' => $faker->numberBetween(1, 10),
            'manufacturer' => $faker->randomElement($manufacturers),
            'component_class' => $faker->randomElement($classes),
        ];
    }
);

