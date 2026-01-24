<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders ammunition-card', function () {
    $data = [
        'speed' => 1000,
        'lifetime' => 5,
    ];

    $output = Blade::render('<x-items.ammunition-card :ammunition="$data" />', ['data' => $data]);

    expect($output)->toContain('Ammunition');
});

it('renders armor-card', function () {
    $data = [
        'health' => 1000,
    ];

    $output = Blade::render('<x-items.armor-card :armor="$data" />', ['data' => $data]);

    expect($output)->toContain('Armor');
});

it('renders bomb-card', function () {
    $data = [
        'damage_total' => 1000,
    ];

    $output = Blade::render('<x-items.bomb-card :bomb="$data" />', ['data' => $data]);

    expect($output)->toContain('Bomb');
});

it('renders cargo-grid-card', function () {
    $data = [
        'capacity' => 1000,
    ];

    $output = Blade::render('<x-items.cargo-grid-card :cargoGrid="$data" />', ['data' => $data]);

    expect($output)->toContain('Cargo Grid');
});

it('renders cooler-card', function () {
    $data = [
        'coolant_segment_generation' => 5,
    ];

    $output = Blade::render('<x-items.cooler-card :cooler="$data" />', ['data' => $data]);

    expect($output)->toContain('Cooler');
});

it('renders counter-measure-card', function () {
    $data = [
        'ammo_count' => 10,
    ];

    $output = Blade::render('<x-items.counter-measure-card :counterMeasure="$data" />', ['data' => $data]);

    expect($output)->toContain('Counter Measure');
});

it('renders emission-card', function () {
    $data = [
        'noise' => 50,
    ];

    $output = Blade::render('<x-items.emission-card :emission="$data" />', ['data' => $data]);

    expect($output)->toContain('Emission');
});

it('renders emp-card', function () {
    $data = [
        'duration' => 10,
    ];

    $output = Blade::render('<x-items.emp-card :emp="$data" />', ['data' => $data]);

    expect($output)->toContain('EMP');
});

it('renders flight-controller-card', function () {
    $data = [
        'max_speed' => 200,
    ];

    $output = Blade::render('<x-items.flight-controller-card :flightController="$data" />', ['data' => $data]);

    expect($output)->toContain('Flight Controller');
});

it('renders fuel-intake-card', function () {
    $data = [
        'max_flow_rate' => 100,
    ];

    $output = Blade::render('<x-items.fuel-intake-card :fuelIntake="$data" />', ['data' => $data]);

    expect($output)->toContain('Fuel Intake');
});

it('renders fuel-tank-card', function () {
    $data = [
        'capacity' => 5000,
    ];

    $output = Blade::render('<x-items.fuel-tank-card :fuelTank="$data" />', ['data' => $data]);

    expect($output)->toContain('Fuel Tank');
});

it('renders item-breadcrumbs', function () {
    $output = Blade::render('<x-items.item-breadcrumbs />');

    expect($output)->toContain('All Items');
});

it('renders jump-drive-card', function () {
    $data = [
        'range' => 10000,
    ];

    $output = Blade::render('<x-items.jump-drive-card :jumpDrive="$data" />', ['data' => $data]);

    expect($output)->toContain('Jump Drive');
});

it('renders medical-bed-card', function () {
    $data = [
        'capacity' => 1,
    ];

    $output = Blade::render('<x-items.medical-bed-card :medicalBed="$data" />', ['data' => $data]);

    expect($output)->toContain('Medical Bed');
});

it('renders mining-laser-card', function () {
    $data = [
        'mining_yield' => 100,
    ];

    $output = Blade::render('<x-items.mining-laser-card :miningLaser="$data" />', ['data' => $data]);

    expect($output)->toContain('Mining Laser');
});

it('renders mining-modifier-card', function () {
    $data = [
        'modifier' => 1.5,
    ];

    $output = Blade::render('<x-items.mining-modifier-card :miningModifier="$data" />', ['data' => $data]);

    expect($output)->toContain('Mining Modifier');
});

it('renders missile-card', function () {
    $data = [
        'damage' => 1000,
    ];

    $output = Blade::render('<x-items.missile-card :missile="$data" />', ['data' => $data]);

    expect($output)->toContain('Missile');
});

it('renders missile-rack-card', function () {
    $data = [
        'capacity' => 5,
    ];

    $output = Blade::render('<x-items.missile-rack-card :missileRack="$data" />', ['data' => $data]);

    expect($output)->toContain('Missile Rack');
});

it('renders personal-weapon-card', function () {
    $data = [
        'damage' => 50,
    ];

    $output = Blade::render('<x-items.personal-weapon-card :personalWeapon="$data" />', ['data' => $data]);

    expect($output)->toContain('Personal Weapon');
});

it('renders power-plant-card', function () {
    $data = [
        'power_segment_generation' => 10,
    ];

    $output = Blade::render('<x-items.power-plant-card :powerPlant="$data" />', ['data' => $data]);

    expect($output)->toContain('Power Plant');
});

it('renders quantum-drive-card', function () {
    $data = [
        'drive_speed' => 5000,
    ];

    $output = Blade::render('<x-items.quantum-drive-card :quantumDrive="$data" />', ['data' => $data]);

    expect($output)->toContain('Quantum Drive');
});

it('renders quantum-interdiction-generator-card', function () {
    $data = [
        'interdiction_range' => 5000,
    ];

    $output = Blade::render('<x-items.quantum-interdiction-generator-card :quantumInterdictionGenerator="$data" />', ['data' => $data]);

    expect($output)->toContain('Quantum Interdiction Generator');
});

it('renders radar-card', function () {
    $data = [
        'range' => 10000,
    ];

    $output = Blade::render('<x-items.radar-card :radar="$data" />', ['data' => $data]);

    expect($output)->toContain('Radar');
});

it('renders radiation-resistance-card', function () {
    $data = [
        'resistance' => 50,
    ];

    $output = Blade::render('<x-items.radiation-resistance-card :radiationResistance="$data" />', ['data' => $data]);

    expect($output)->toContain('Radiation Resistance');
});

it('renders resource-network-card', function () {
    $data = [
        'usage' => [
            'power' => [
                'minimum' => 10,
                'maximum' => 20,
            ],
        ],
    ];

    $output = Blade::render('<x-items.resource-network-card :resourceNetwork="$data" :itemType="\'Cooler\'" />', ['data' => $data]);

    expect($output)->toContain('Resource Network');
});

it('renders seat-card', function () {
    $data = [
        'capacity' => 1,
    ];

    $output = Blade::render('<x-items.seat-card :seat="$data" />', ['data' => $data]);

    expect($output)->toContain('Seat');
});

it('renders self-destruct-card', function () {
    $data = [
        'damage' => 100000,
    ];

    $output = Blade::render('<x-items.self-destruct-card :selfDestruct="$data" />', ['data' => $data]);

    expect($output)->toContain('Self Destruct');
});

it('renders shield-card', function () {
    $data = [
        'max_health' => 1000,
    ];

    $output = Blade::render('<x-items.shield-card :shield="$data" />', ['data' => $data]);

    expect($output)->toContain('Shield');
});

it('renders shield-controller-card', function () {
    $data = [
        'shield_count' => 2,
    ];

    $output = Blade::render('<x-items.shield-controller-card :shieldController="$data" />', ['data' => $data]);

    expect($output)->toContain('Shield Controller');
});

it('renders suit-armor-card', function () {
    $data = [
        'health' => 500,
    ];

    $output = Blade::render('<x-items.suit-armor-card :suitArmor="$data" />', ['data' => $data]);

    expect($output)->toContain('Suit Armor');
});

it('renders temperature-resistance-card', function () {
    $data = [
        'resistance' => 100,
    ];

    $output = Blade::render('<x-items.temperature-resistance-card :temperatureResistance="$data" />', ['data' => $data]);

    expect($output)->toContain('Temperature Resistance');
});

it('renders thruster-card', function () {
    $data = [
        'max_thrust' => 1000,
    ];

    $output = Blade::render('<x-items.thruster-card :thruster="$data" />', ['data' => $data]);

    expect($output)->toContain('Thruster');
});

it('renders tractor-beam-card', function () {
    $data = [
        'range' => 100,
    ];

    $output = Blade::render('<x-items.tractor-beam-card :tractorBeam="$data" />', ['data' => $data]);

    expect($output)->toContain('Tractor Beam');
});

it('renders turret-card', function () {
    $data = [
        'capacity' => 1,
    ];

    $output = Blade::render('<x-items.turret-card :turret="$data" />', ['data' => $data]);

    expect($output)->toContain('Turret');
});

it('renders vehicle-weapon-card', function () {
    $data = [
        'damage' => 500,
    ];

    $output = Blade::render('<x-items.vehicle-weapon-card :vehicleWeapon="$data" />', ['data' => $data]);

    expect($output)->toContain('Vehicle Weapon');
});

it('renders weapon-attachment-card', function () {
    $data = [
        'iron_sight' => [
            'default_range' => 100,
        ],
    ];

    $output = Blade::render('<x-items.weapon-attachment-card :weaponAttachment="$data" />', ['data' => $data]);

    expect($output)->toContain('Weapon Attachment');
});

it('renders weapon-modifier-card', function () {
    $data = [
        'modifier' => 1.5,
    ];

    $output = Blade::render('<x-items.weapon-modifier-card :weaponModifier="$data" />', ['data' => $data]);

    expect($output)->toContain('Weapon Modifier');
});
