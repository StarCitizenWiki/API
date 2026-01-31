@props([
    'item',
    'type',
])

@php
    $vehicleSpecCount = collect([
        data_get($item, 'shield'),
        data_get($item, 'power_plant'),
        data_get($item, 'quantum_drive'),
        data_get($item, 'cooler'),
        data_get($item, 'jump_drive'),
        data_get($item, 'counter_measure'),
        data_get($item, 'bomb'),
        data_get($item, 'seat'),
        data_get($item, 'thruster'),
        data_get($item, 'fuel_tank'),
        data_get($item, 'fuel_intake'),
        data_get($item, 'emp'),
        data_get($item, 'quantum_interdiction_generator'),
        data_get($item, 'mining_modifier'),
        data_get($item, 'emission'),
        data_get($item, 'mining_laser'),
        data_get($item, 'self_destruct'),
        data_get($item, 'missile_rack'),
        data_get($item, 'missile'),
        data_get($item, 'tractor_beam'),
        data_get($item, 'cargo_grid'),
        data_get($item, 'resource_network'),
    ])->filter()->count();
@endphp

<details {{ $attributes->merge(['class' => 'collapse collapse-arrow border border-base-300 bg-base-100 shadow']) }} open>
    <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
        <span class="flex items-center gap-2">
            <x-icon name="cpu" class="size-5 text-primary" />
            <span>Vehicle Component Data</span>
            @if ($vehicleSpecCount > 0)
                <span class="badge badge-ghost text-xs">{{ $vehicleSpecCount }}</span>
            @endif
        </span>
    </summary>
    <div class="collapse-content">
        <div class="grid gap-4 lg:gap-6 grid-cols-1 lg:grid-cols-2">
            @if ($type === 'WeaponGun')
                <x-items.vehicle-weapon-card :vehicle-weapon="data_get($item, 'vehicle_weapon')" />
            @endif

            @if (data_get($item, 'tractor_beam'))
                <x-items.tractor-beam-card :tractor-beam="data_get($item, 'tractor_beam')" />
            @endif

            @if (data_get($item, 'ammunition') && !data_get($item, 'tractor_beam'))
                <x-items.ammunition-card :ammunition="data_get($item, 'ammunition')" />
            @endif

            @if ($type === 'Armor')
                <x-items.armor-card :armor="data_get($item, 'armor')" />
            @endif

            @if ($type === 'Missile')
                <x-items.missile-card :missile="data_get($item, 'missile')" />
            @endif

            @if ($type === 'FlightController')
                <x-items.flight-controller-card :flight-controller="data_get($item, 'flight_controller')" />
            @endif

            @if ($type === 'ShieldController')
                <x-items.shield-controller-card :shield-controller="data_get($item, 'shield_controller')" />
            @endif

            @if ($type === 'Radar')
                <x-items.radar-card :radar="data_get($item, 'radar')" />
            @endif

            @if ($type === 'Turret')
                <x-items.turret-card :turret="data_get($item, 'turret')" />
            @endif

            @if ($type === 'Shield')
                <x-items.shield-card :shield="data_get($item, 'shield')" />
            @endif

            @if ($type === 'PowerPlant')
                <x-items.power-plant-card :power-plant="data_get($item, 'power_plant')" />
            @endif

            @if ($type === 'QuantumDrive')
                <x-items.quantum-drive-card :quantum-drive="data_get($item, 'quantum_drive')" />
            @endif

            @if ($type === 'Cooler')
                <x-items.cooler-card :cooler="data_get($item, 'cooler')" />
            @endif

            @if ($type === 'JumpDrive')
                <x-items.jump-drive-card :jump-drive="data_get($item, 'jump_drive')" />
            @endif

            @if (data_get($item, 'counter_measure'))
                <x-items.counter-measure-card :counter-measure="data_get($item, 'counter_measure')" />
            @endif

            @if (data_get($item, 'bomb'))
                <x-items.bomb-card :bomb="data_get($item, 'bomb')" />
            @endif

            @if (data_get($item, 'seat'))
                <x-items.seat-card :seat="data_get($item, 'seat')" />
            @endif

            @if (data_get($item, 'thruster'))
                <x-items.thruster-card :thruster="data_get($item, 'thruster')" />
            @endif

            @if (data_get($item, 'fuel_tank'))
                <x-items.fuel-tank-card :fuel-tank="data_get($item, 'fuel_tank')" />
            @endif

            @if (data_get($item, 'fuel_intake'))
                <x-items.fuel-intake-card :fuel-intake="data_get($item, 'fuel_intake')" />
            @endif

            @if (data_get($item, 'emp'))
                <x-items.emp-card :emp="data_get($item, 'emp')" />
            @endif

            @if (data_get($item, 'quantum_interdiction_generator'))
                <x-items.quantum-interdiction-generator-card :quantum-interdiction-generator="data_get($item, 'quantum_interdiction_generator')" />
            @endif

            @if (data_get($item, 'mining_modifier'))
                <x-items.mining-modifier-card :mining-modifier="data_get($item, 'mining_modifier')" />
            @endif

            @if (data_get($item, 'emission') && data_get($item, 'emission.em_max', 0) >0)
                <x-items.emission-card :emission="data_get($item, 'emission')" />
            @endif

            @if (data_get($item, 'mining_laser'))
                <x-items.mining-laser-card :mining-laser="data_get($item, 'mining_laser')" />
            @endif

            @if (data_get($item, 'self_destruct'))
                <x-items.self-destruct-card :self-destruct="data_get($item, 'self_destruct')" />
            @endif

            @if (data_get($item, 'missile_rack'))
                <x-items.missile-rack-card :missile-rack="data_get($item, 'missile_rack')" />
            @endif

            @if (data_get($item, 'cargo_grid'))
                <x-items.cargo-grid-card :cargo-grid="data_get($item, 'cargo_grid')" />
            @endif

            @if (data_get($item, 'weapon_modifier'))
                <x-items.weapon-modifier-card :weapon-modifier="data_get($item, 'weapon_modifier')" />
            @endif

            @if (data_get($item, 'resource_network'))
                <x-items.resource-network-card :resource-network="data_get($item, 'resource_network')" :item-type="data_get($item, 'type')" />
            @endif
        </div>
    </div>
</details>
