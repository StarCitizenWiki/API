@php use Illuminate\Support\Str; @endphp
@props([
    'port',
    'depth' => 0,
    'editable' => false,
])

@php
    $indentClass = $depth > 0 ? 'mt-3 pl-2 border-l-8 border-base-300' : '';
    $portId = $depth . '-' . ($loop->index ?? 0);
    $portIdentifier = 'port-'.$portId;

    // Extract equipped item stats for summary display
    $equippedItem = data_get($port, 'equipped_item', data_get($port, 'equipped_port_item'));
    $showQuickStats = !empty($equippedItem);

    if ($showQuickStats) {
        // Universal stats
        $itemSize = data_get($equippedItem, 'size');
        $powerSegmentUsage = data_get($equippedItem, 'resource_network.usage.power.maximum');
        $coolantSegmentUsage = data_get($equippedItem, 'resource_network.usage.coolant.maximum');

        // Type-specific stat determination
        $typeSpecificStat = null;
        $typeSpecificLabel = null;
        $typeSpecificIcon = null;

        if (data_get($equippedItem, 'shield.MaxShieldHealth')) {
            $typeSpecificStat = data_get($equippedItem, 'shield.MaxShieldHealth');
            $typeSpecificLabel = 'Max Shield HP';
            $typeSpecificIcon = 'shield';
        } elseif (data_get($equippedItem, 'power_plant.power_segment_generation')) {
            $typeSpecificStat = data_get($equippedItem, 'power_plant.power_segment_generation');
            $typeSpecificLabel = 'Generation';
            $powerSegmentUsage = null;
            $typeSpecificIcon = 'power';
        } elseif (data_get($equippedItem, 'cooler.coolant_segment_generation')) {
            $typeSpecificStat = data_get($equippedItem, 'cooler.coolant_segment_generation');
            $typeSpecificLabel = 'Generation';
            $coolantSegmentUsage = null;
            $typeSpecificIcon = 'fan';
        } elseif (data_get($equippedItem, 'vehicle_weapon.damage.alpha_total')) {
            $typeSpecificStat = data_get($equippedItem, 'vehicle_weapon.damage.alpha_total');
            $typeSpecificLabel = 'Alpha Damage';
            $typeSpecificIcon = 'sword';
        }
    }
@endphp

<div class="port-entry {{ $indentClass }}">
    <details
        id="{{ $portIdentifier }}"
        class="collapse collapse-arrow border border-base-300 bg-base-100 shadow"
    >
        <summary
            class="collapse-title min-h-11 py-3 text-sm font-semibold flex items-center justsify-between gap-2 flex-wrap"
            aria-expanded="false"
            aria-controls="{{ $portIdentifier }}-content"
        >
            <span class="flex items-center gap-2">
                @if ($depth > 0)
                    <span class="text-base-content/50 mr-2">↳</span>
                @endif
                @if(data_get($port, 'editable') === false || !$editable)
                    <x-icon name="lock" class="size-3"/>
                @endif
                {{ Str::of(data_get($port, 'name'))->lower()->replace('hardpoint_', '')->headline() ?? 'Port' }}
                @if (! empty(data_get($port, 'position')))
                    <span class="ml-2 text-xs font-normal text-base-content/60">{{ data_get($port, 'position') }}</span>
                @endif
            </span>

            @if ($showQuickStats)
                <span class="flex items-center gap-2 text-xs font-normal flex-wrap">
                    @if ($itemSize !== null)
                        <span class="badge badge-sm badge-soft" title="Item Size">S{{ $itemSize }}</span>
                    @endif
                    @if ($powerSegmentUsage !== null)
                        <span class="badge badge-sm badge-soft" title="Power Segment Usage">
                            <x-icon name="zap" class="size-3"/>
                            {{ fmt_compact($powerSegmentUsage, 1) }}
                            Power Usage
                        </span>
                    @endif
                    @if ($coolantSegmentUsage !== null)
                        <span class="badge badge-sm badge-soft" title="Coolant Segment Usage">
                            <x-icon name="fan" class="size-3"/>
                            {{ fmt_compact($coolantSegmentUsage, 1) }}
                            Coolant Usage
                        </span>
                    @endif
                    @if ($typeSpecificStat !== null)
                        <span class="badge badge-sm badge-primary" title="{{ $typeSpecificLabel }}">
                            @if(@$typeSpecificIcon)
                                <x-icon name="{{ $typeSpecificIcon }}" class="size-3"/>
                            @endif
                            {{ fmt_compact($typeSpecificStat, 0) }} {{ $typeSpecificLabel }}
                        </span>
                    @endif
                </span>
            @endif
        </summary>
        <div id="{{ $portIdentifier }}-content" class="collapse-content">
            <dl class="grid gap-3 sm:grid-cols-2 md:grid-cols-3">
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                        Port Name
                    </dt>
                    <dd class="text-sm">{{ data_get($port, 'name') ?? '-' }}</dd>
                </div>

                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                        Equippable Item Size
                    </dt>
                    <dd class="text-sm">
                        S{{ fmt_range(data_get($port, 'sizes.min'), data_get($port, 'sizes.max'), '') }}
                    </dd>
                </div>

                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                        Equippable Type + Sub Type
                    </dt>
                    <dd class="text-sm">{{ data_get($port, 'type') ?? '-' }}
                        / {{ data_get($port, 'subtype') ?? '-' }}</dd>
                </div>

                @unless(empty(data_get($port, 'equipped_item')))
                <div class="space-y-1 col-span-full">
                    <dt class="font-semibold text-sm uppercase tracking-wide">
                        Equipped Item
                    </dt>
                    <dd class="text-sm">
                        <div class="flex items-center gap-2">
                            <span>{{ data_get($port, 'equipped_item.name') ?? '-' }}</span>
                            @if (! empty(data_get($port, 'equipped_item.uuid')))
                                <a href="{{ route('web.items.show', data_get($port, 'equipped_item.uuid')) }}"
                                   class="link link-primary">View</a>
                            @endif
                        </div>
                        <div class="mt-4 space-y-4">
                            @php
                                $equippedType = data_get(data_get($port, 'equipped_item'), 'type');
                            @endphp
                            @if ($equippedType === 'WeaponPersonal')
                                <x-items.personal-weapon-card
                                    :personal-weapon="data_get($port['equipped_item'], 'personal_weapon')"/>
                            @endif

                            @if ($equippedType === 'Armor')
                                <x-items.armor-card :armor="data_get($port['equipped_item'], 'armor')"/>
                            @endif

                            @if ($equippedType === 'WeaponGun')
                                <x-items.vehicle-weapon-card
                                    :vehicle-weapon="data_get($port['equipped_item'], 'vehicle_weapon')"/>
                            @endif

                            @if ($equippedType === 'WeaponAttachment')
                                <x-items.weapon-attachment-card :weapon-attachment="$port['equipped_item']"/>
                            @endif

                            @if (data_get($port['equipped_item'], 'shield'))
                                <x-items.shield-card :shield="data_get($port['equipped_item'], 'shield')"/>
                            @endif

                            @if (data_get($port['equipped_item'], 'quantum_drive'))
                                <x-items.quantum-drive-card
                                    :quantum-drive="data_get($port['equipped_item'], 'quantum_drive')"/>
                            @endif

                            @if (data_get($port['equipped_item'], 'jump_drive'))
                                <x-items.jump-drive-card
                                    :jump-drive="data_get($port['equipped_item'], 'jump_drive')"/>
                            @endif

                            @if (data_get($port['equipped_item'], 'power_plant'))
                                <x-items.power-plant-card
                                    :power-plant="data_get($port['equipped_item'], 'power_plant')"/>
                            @endif

                            @if (data_get($port['equipped_item'], 'cooler'))
                                <x-items.cooler-card :cooler="data_get($port['equipped_item'], 'cooler')"/>
                            @endif

                            @if (data_get($port['equipped_item'], 'counter_measure'))
                                <x-items.counter-measure-card
                                    :counter-measure="data_get($port['equipped_item'], 'counter_measure')"/>
                            @endif

                            @if (data_get($port['equipped_item'], 'bomb'))
                                <x-items.bomb-card :bomb="data_get($port['equipped_item'], 'bomb')"/>
                            @endif

                            @if (data_get($port['equipped_item'], 'seat'))
                                <x-items.seat-card :seat="data_get($port['equipped_item'], 'seat')"/>
                            @endif

                            @if (data_get($port['equipped_item'], 'thruster'))
                                <x-items.thruster-card :thruster="data_get($port['equipped_item'], 'thruster')"/>
                            @endif

                            @if (data_get($port['equipped_item'], 'fuel_tank'))
                                <x-items.fuel-tank-card :fuel-tank="data_get($port['equipped_item'], 'fuel_tank')"/>
                            @endif

                            @if (data_get($port['equipped_item'], 'fuel_intake'))
                                <x-items.fuel-intake-card
                                    :fuel-intake="data_get($port['equipped_item'], 'fuel_intake')"/>
                            @endif

                            @if (data_get($port['equipped_item'], 'emp'))
                                <x-items.emp-card :emp="data_get($port['equipped_item'], 'emp')"/>
                            @endif

                            @if (data_get($port['equipped_item'], 'quantum_interdiction_generator'))
                                <x-items.quantum-interdiction-generator-card
                                    :quantum-interdiction-generator="data_get($port['equipped_item'], 'quantum_interdiction_generator')"/>
                            @endif

                            @if (data_get($port['equipped_item'], 'mining_modifier'))
                                <x-items.mining-modifier-card
                                    :mining-modifier="data_get($port['equipped_item'], 'mining_modifier')"/>
                            @endif

                            @if (data_get($port['equipped_item'], 'emission'))
                                <x-items.emission-card :emission="data_get($port['equipped_item'], 'emission')"/>
                            @endif

                            @if (data_get($port['equipped_item'], 'mining_laser'))
                                <x-items.mining-laser-card
                                    :mining-laser="data_get($port['equipped_item'], 'mining_laser')"/>
                            @endif

                            @if (data_get($port['equipped_item'], 'self_destruct'))
                                <x-items.self-destruct-card
                                    :self-destruct="data_get($port['equipped_item'], 'self_destruct')"/>
                            @endif

                            @if (data_get($port['equipped_item'], 'missile_rack'))
                                <x-items.missile-rack-card
                                    :missile-rack="data_get($port['equipped_item'], 'missile_rack')"/>
                            @endif

                            @if (data_get($port['equipped_item'], 'tractor_beam'))
                                <x-items.tractor-beam-card
                                    :tractor-beam="data_get($port['equipped_item'], 'tractor_beam')"/>
                            @endif

                            @if ($equippedType === 'FlightController')
                                <x-items.flight-controller-card
                                    :flight-controller="data_get($port['equipped_item'], 'flight_controller')"/>
                            @endif

                            @if ($equippedType === 'ShieldController')
                                <x-items.shield-controller-card
                                    :shield-controller="data_get($port['equipped_item'], 'shield_controller')"/>
                            @endif

                            @if ($equippedType === 'Radar')
                                <x-items.radar-card :radar="data_get($port['equipped_item'], 'radar')"/>
                            @endif

                            @if ($equippedType === 'Turret')
                                <x-items.turret-card :turret="data_get($port['equipped_item'], 'turret')" class="pr-0" />
                            @endif

                            @if (data_get($port['equipped_item'], 'inventory'))
                                <x-items.cargo-grid-card
                                    :cargo-grid="data_get($port['equipped_item'], 'inventory')"/>
                            @endif

                            @if (data_get($port['equipped_item'], 'missile'))
                                <x-items.missile-card :missile="data_get($port['equipped_item'], 'missile')"/>
                            @endif

                            @if (data_get($port['equipped_item'], 'suit_armor'))
                                <x-items.suit-armor-card
                                    :suit-armor="data_get($port['equipped_item'], 'suit_armor')"/>
                            @endif

                            @if (data_get($port['equipped_item'], 'temperature_resistance'))
                                <x-items.temperature-resistance-card
                                    :temperature-resistance="data_get($port['equipped_item'], 'temperature_resistance')"/>
                            @endif

                            @if (data_get($port['equipped_item'], 'radiation_resistance'))
                                <x-items.radiation-resistance-card
                                    :radiation-resistance="data_get($port['equipped_item'], 'radiation_resistance')"/>
                            @endif

                            @if (data_get($port['equipped_item'], 'ammunition'))
                                <x-items.ammunition-card
                                    :ammunition="data_get($port['equipped_item'], 'ammunition')"/>
                            @endif

                            @if (data_get($port['equipped_item'], 'weapon_modifier'))
                                <x-items.weapon-modifier-card
                                    :weapon-modifier="data_get($port['equipped_item'], 'weapon_modifier')"/>
                            @endif
                        </div>
                    </dd>
                </div>
                @endunless
            </dl>

            @if (! empty(data_get($port, 'ports')))
                @foreach (data_get($port, 'ports') as $childPort)
                    <x-port-display :port="$childPort" :depth="$depth + 1" :editable="data_get($port, 'editable_children')"/>
                @endforeach
            @endif
        </div>
    </details>
</div>
