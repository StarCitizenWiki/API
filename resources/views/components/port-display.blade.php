@props([
    'port',
    'depth' => 0,
])

@php
    $indentClass = $depth > 0 ? 'ml-6 mt-3 pl-4 border-l-2 border-base-200' : '';
@endphp

<div class="port-entry {{ $indentClass }}">
    <div class="collapse collapse-arrow border border-base-200 bg-base-100">
        <input type="checkbox"/>
        <div class="collapse-title text-sm font-semibold">
            @if ($depth > 0)
                <span class="text-base-content/50 mr-2">↳</span>
            @endif
            {{ \Illuminate\Support\Str::of($port['name'])->lower()->replace('hardpoint_', '')->headline() ?? 'Port' }}
            @if (! empty($port['position']))
                <span class="ml-2 text-xs font-normal text-base-content/60">{{ $port['position'] }}</span>
            @endif
        </div>
        <div class="collapse-content">
            <dl class="grid gap-3 sm:grid-cols-2">
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                        Port Name
                    </dt>
                    <dd class="text-sm">{{ $port['name'] ?? '-' }}</dd>
                </div>

                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                        Size
                    </dt>
                    <dd class="text-sm">
                        @if (! empty($port['sizes']))
                            {{ $port['sizes']['min'] ?? '-' }}
                            - {{ $port['sizes']['max'] ?? '-' }}
                        @else
                            -
                        @endif
                    </dd>
                </div>

                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                        Type + Sub Type
                    </dt>
                    <dd class="text-sm">{{ $port['type'] ?? '-' }} / {{ $port['subtype'] ?? '-' }}</dd>
                </div>

                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                        Health
                    </dt>
                    <dd class="text-sm">{{ $port['health'] ?? '-' }}</dd>
                </div>
                <div class="space-y-1 sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                        Equipped Item
                    </dt>
                    <dd class="text-sm">
                        @if (! empty($port['equipped_item']))
                            <div class="flex items-center gap-2">
                                <span>{{ $port['equipped_item']['name'] ?? '-' }}</span>
                                @if (! empty($port['equipped_item']['uuid']))
                                    <a href="{{ route('web.items.show', $port['equipped_item']['uuid']) }}"
                                       class="link link-primary">View</a>
                                @endif
                            </div>
                            <div class="mt-4">
                                @php
                                    $equippedType = data_get($port['equipped_item'], 'type');
                                @endphp
                                @if ($equippedType === 'WeaponPersonal')
                                    <x-items.personal-weapon-card :personal-weapon="data_get($port['equipped_item'], 'personal_weapon')" />
                                @endif

                                @if ($equippedType === 'Armor')
                                    <x-items.armor-card :armor="data_get($port['equipped_item'], 'armor')" />
                                @endif

                                @if ($equippedType === 'WeaponGun')
                                    <x-items.vehicle-weapon-card :vehicle-weapon="data_get($port['equipped_item'], 'vehicle_weapon')" />
                                @endif

                                @if ($equippedType === 'WeaponAttachment')
                                    <x-items.weapon-attachment-card :weapon-attachment="$port['equipped_item']" />
                                @endif

                                @if (data_get($port['equipped_item'], 'shield'))
                                    <x-items.shield-card :shield="data_get($port['equipped_item'], 'shield')" />
                                @endif

                                @if (data_get($port['equipped_item'], 'quantum_drive'))
                                    <x-items.quantum-drive-card :quantum-drive="data_get($port['equipped_item'], 'quantum_drive')" />
                                @endif

                                @if (data_get($port['equipped_item'], 'jump_drive'))
                                    <x-items.jump-drive-card :jump-drive="data_get($port['equipped_item'], 'jump_drive')" />
                                @endif

                                @if (data_get($port['equipped_item'], 'power_plant'))
                                    <x-items.power-plant-card :power-plant="data_get($port['equipped_item'], 'power_plant')" />
                                @endif

                                @if (data_get($port['equipped_item'], 'cooler'))
                                    <x-items.cooler-card :cooler="data_get($port['equipped_item'], 'cooler')" />
                                @endif

                                @if (data_get($port['equipped_item'], 'counter_measure'))
                                    <x-items.counter-measure-card :counter-measure="data_get($port['equipped_item'], 'counter_measure')" />
                                @endif

                                @if (data_get($port['equipped_item'], 'bomb'))
                                    <x-items.bomb-card :bomb="data_get($port['equipped_item'], 'bomb')" />
                                @endif

                                @if (data_get($port['equipped_item'], 'seat'))
                                    <x-items.seat-card :seat="data_get($port['equipped_item'], 'seat')" />
                                @endif

                                @if (data_get($port['equipped_item'], 'thruster'))
                                    <x-items.thruster-card :thruster="data_get($port['equipped_item'], 'thruster')" />
                                @endif

                                @if (data_get($port['equipped_item'], 'fuel_tank'))
                                    <x-items.fuel-tank-card :fuel-tank="data_get($port['equipped_item'], 'fuel_tank')" />
                                @endif

                                @if (data_get($port['equipped_item'], 'fuel_intake'))
                                    <x-items.fuel-intake-card :fuel-intake="data_get($port['equipped_item'], 'fuel_intake')" />
                                @endif

                                @if (data_get($port['equipped_item'], 'emp'))
                                    <x-items.emp-card :emp="data_get($port['equipped_item'], 'emp')" />
                                @endif

                                @if (data_get($port['equipped_item'], 'quantum_interdiction_generator'))
                                    <x-items.quantum-interdiction-generator-card :quantum-interdiction-generator="data_get($port['equipped_item'], 'quantum_interdiction_generator')" />
                                @endif

                                @if (data_get($port['equipped_item'], 'mining_modifier'))
                                    <x-items.mining-modifier-card :mining-modifier="data_get($port['equipped_item'], 'mining_modifier')" />
                                @endif

                                @if (data_get($port['equipped_item'], 'emission'))
                                    <x-items.emission-card :emission="data_get($port['equipped_item'], 'emission')" />
                                @endif

                                @if (data_get($port['equipped_item'], 'mining_laser'))
                                    <x-items.mining-laser-card :mining-laser="data_get($port['equipped_item'], 'mining_laser')" />
                                @endif

                                @if (data_get($port['equipped_item'], 'self_destruct'))
                                    <x-items.self-destruct-card :self-destruct="data_get($port['equipped_item'], 'self_destruct')" />
                                @endif

                                @if (data_get($port['equipped_item'], 'missile_rack'))
                                    <x-items.missile-rack-card :missile-rack="data_get($port['equipped_item'], 'missile_rack')" />
                                @endif

                                @if (data_get($port['equipped_item'], 'tractor_beam'))
                                    <x-items.tractor-beam-card :tractor-beam="data_get($port['equipped_item'], 'tractor_beam')" />
                                @endif

                                @if ($equippedType === 'FlightController')
                                    <x-items.flight-controller-card :flight-controller="data_get($port['equipped_item'], 'flight_controller')" />
                                @endif

                                @if ($equippedType === 'ShieldController')
                                    <x-items.shield-controller-card :shield-controller="data_get($port['equipped_item'], 'shield_controller')" />
                                @endif

                                @if ($equippedType === 'Radar')
                                    <x-items.radar-card :radar="data_get($port['equipped_item'], 'radar')" />
                                @endif

                                @if ($equippedType === 'Turret')
                                    <x-items.turret-card :turret="data_get($port['equipped_item'], 'turret')" />
                                @endif

                                @if (data_get($port['equipped_item'], 'cargo_grid'))
                                    <x-items.cargo-grid-card :cargo-grid="data_get($port['equipped_item'], 'cargo_grid')" />
                                @endif

                                @if (data_get($port['equipped_item'], 'missile'))
                                    <x-items.missile-card :missile="data_get($port['equipped_item'], 'missile')" />
                                @endif

                                @if (data_get($port['equipped_item'], 'suit_armor'))
                                    <x-items.suit-armor-card :suit-armor="data_get($port['equipped_item'], 'suit_armor')" />
                                @endif

                                @if (data_get($port['equipped_item'], 'temperature_resistance'))
                                    <x-items.temperature-resistance-card :temperature-resistance="data_get($port['equipped_item'], 'temperature_resistance')" />
                                @endif

                                @if (data_get($port['equipped_item'], 'radiation_resistance'))
                                    <x-items.radiation-resistance-card :radiation-resistance="data_get($port['equipped_item'], 'radiation_resistance')" />
                                @endif

                                @if (data_get($port['equipped_item'], 'ammunition'))
                                    <x-items.ammunition-card :ammunition="data_get($port['equipped_item'], 'ammunition')" />
                                @endif

                                @if (data_get($port['equipped_item'], 'weapon_modifier'))
                                    <x-items.weapon-modifier-card :weapon-modifier="data_get($port['equipped_item'], 'weapon_modifier')" />
                                @endif
                            </div>
                        @else
                            -
                        @endif
                    </dd>
                </div>
            </dl>

            @if (! empty($port['ports']))
                <div class="mt-6 space-y-3">
                    @foreach ($port['ports'] as $childPort)
                        <x-port-display :port="$childPort" :depth="$depth + 1" />
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
