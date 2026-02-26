<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders item component cards', function (string $template, array $data, string $expectedLabel): void {
    $output = Blade::render($template, ['data' => $data]);

    expect($output)->toContain($expectedLabel);
})->with([
    'ammunition-card' => [
        '<x-items.ammunition-card :ammunition="$data" />',
        [
            'speed' => 1000,
            'lifetime' => 5,
        ],
        'Ammunition',
    ],
    'armor-card' => [
        '<x-items.armor-card :armor="$data" />',
        [
            'health' => 1000,
        ],
        'Armor',
    ],
    'bomb-card' => [
        '<x-items.bomb-card :bomb="$data" />',
        [
            'damage_total' => 1000,
        ],
        'Bomb',
    ],
    'cargo-grid-card' => [
        '<x-items.cargo-grid-card :cargoGrid="$data" />',
        [
            'capacity' => 1000,
        ],
        'Cargo Grid',
    ],
    'cooler-card' => [
        '<x-items.cooler-card :cooler="$data" />',
        [
            'coolant_segment_generation' => 5,
        ],
        'Cooler',
    ],
    'counter-measure-card' => [
        '<x-items.counter-measure-card :counterMeasure="$data" />',
        [
            'ammo_count' => 10,
        ],
        'Counter Measure',
    ],
    'emission-card' => [
        '<x-items.emission-card :emission="$data" />',
        [
            'noise' => 50,
        ],
        'Emission',
    ],
    'emp-card' => [
        '<x-items.emp-card :emp="$data" />',
        [
            'duration' => 10,
        ],
        'EMP',
    ],
    'flight-controller-card' => [
        '<x-items.flight-controller-card :flightController="$data" />',
        [
            'max_speed' => 200,
        ],
        'Flight Controller',
    ],
    'fuel-intake-card' => [
        '<x-items.fuel-intake-card :fuelIntake="$data" />',
        [
            'max_flow_rate' => 100,
        ],
        'Fuel Intake',
    ],
    'fuel-tank-card' => [
        '<x-items.fuel-tank-card :fuelTank="$data" />',
        [
            'capacity' => 5000,
        ],
        'Fuel Tank',
    ],
    'jump-drive-card' => [
        '<x-items.jump-drive-card :jumpDrive="$data" />',
        [
            'range' => 10000,
        ],
        'Jump Drive',
    ],
    'medical-bed-card' => [
        '<x-items.medical-bed-card :medicalBed="$data" />',
        [
            'capacity' => 1,
        ],
        'Medical Bed',
    ],
    'mining-laser-card' => [
        '<x-items.mining-laser-card :miningLaser="$data" />',
        [
            'mining_yield' => 100,
        ],
        'Mining Laser',
    ],
    'mining-modifier-card' => [
        '<x-items.mining-modifier-card :miningModifier="$data" />',
        [
            'modifier' => 1.5,
        ],
        'Mining Modifier',
    ],
    'missile-card' => [
        '<x-items.missile-card :missile="$data" />',
        [
            'damage' => 1000,
        ],
        'Missile',
    ],
    'missile-rack-card' => [
        '<x-items.missile-rack-card :missileRack="$data" />',
        [
            'capacity' => 5,
        ],
        'Missile Rack',
    ],
    'personal-weapon-card' => [
        '<x-items.personal-weapon-card :personalWeapon="$data" />',
        [
            'damage' => 50,
        ],
        'Personal Weapon',
    ],
    'power-plant-card' => [
        '<x-items.power-plant-card :powerPlant="$data" />',
        [
            'power_segment_generation' => 10,
        ],
        'Power Plant',
    ],
    'quantum-drive-card' => [
        '<x-items.quantum-drive-card :quantumDrive="$data" />',
        [
            'drive_speed' => 5000,
        ],
        'Quantum Drive',
    ],
    'quantum-interdiction-generator-card' => [
        '<x-items.quantum-interdiction-generator-card :quantumInterdictionGenerator="$data" />',
        [
            'interdiction_range' => 5000,
        ],
        'Quantum Interdiction Generator',
    ],
    'radar-card' => [
        '<x-items.radar-card :radar="$data" />',
        [
            'range' => 10000,
        ],
        'Radar',
    ],
    'radiation-resistance-card' => [
        '<x-items.radiation-resistance-card :radiationResistance="$data" />',
        [
            'resistance' => 50,
        ],
        'Radiation Resistance',
    ],
    'resource-network-card' => [
        '<x-items.resource-network-card :resourceNetwork="$data" :itemType="\'Cooler\'" />',
        [
            'usage' => [
                'power' => [
                    'minimum' => 10,
                    'maximum' => 20,
                ],
            ],
        ],
        'Resource Network',
    ],
    'seat-card' => [
        '<x-items.seat-card :seat="$data" />',
        [
            'capacity' => 1,
        ],
        'Seat',
    ],
    'self-destruct-card' => [
        '<x-items.self-destruct-card :selfDestruct="$data" />',
        [
            'damage' => 100000,
        ],
        'Self Destruct',
    ],
    'shield-card' => [
        '<x-items.shield-card :shield="$data" />',
        [
            'max_health' => 1000,
        ],
        'Shield',
    ],
    'shield-controller-card' => [
        '<x-items.shield-controller-card :shieldController="$data" />',
        [
            'shield_count' => 2,
        ],
        'Shield Controller',
    ],
    'suit-armor-card' => [
        '<x-items.suit-armor-card :suitArmor="$data" />',
        [
            'health' => 500,
        ],
        'Suit Armor',
    ],
    'temperature-resistance-card' => [
        '<x-items.temperature-resistance-card :temperatureResistance="$data" />',
        [
            'resistance' => 100,
        ],
        'Temperature Resistance',
    ],
    'thruster-card' => [
        '<x-items.thruster-card :thruster="$data" />',
        [
            'max_thrust' => 1000,
        ],
        'Thruster',
    ],
    'tractor-beam-card' => [
        '<x-items.tractor-beam-card :tractorBeam="$data" />',
        [
            'range' => 100,
        ],
        'Tractor Beam',
    ],
    'turret-card' => [
        '<x-items.turret-card :turret="$data" />',
        [
            'capacity' => 1,
        ],
        'Turret',
    ],
    'vehicle-weapon-card' => [
        '<x-items.vehicle-weapon-card :vehicleWeapon="$data" />',
        [
            'damage' => 500,
        ],
        'Vehicle Weapon',
    ],
    'weapon-attachment-card' => [
        '<x-items.weapon-attachment-card :weaponAttachment="$data" />',
        [
            'iron_sight' => [
                'default_range' => 100,
            ],
        ],
        'Weapon Attachment',
    ],
    'weapon-modifier-card' => [
        '<x-items.weapon-modifier-card :weaponModifier="$data" />',
        [
            'modifier' => 1.5,
        ],
        'Weapon Modifier',
    ],
]);

it('renders item-breadcrumbs', function (): void {
    $output = Blade::render('<x-items.item-breadcrumbs />');

    expect($output)->toContain('All Items');
});
