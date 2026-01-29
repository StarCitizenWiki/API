@php use Illuminate\Support\Str; @endphp
@props([
    'port',
    'depth' => 0,
    'editable' => false,
])

@php
    $indentClass = $depth > 0 ? 'mt-2 pl-2 sm:pl-3 border-l-2 sm:border-l-4 border-base-300/70' : '';
    $portId = $depth . '-' . ($loop->index ?? 0);
    $portIdentifier = 'port-'.$portId;
    $portName = data_get($port, 'name');
    $portLabel = Str::of($portName ?? 'Port')->lower()->replace('hardpoint_', '')->headline();
    $portPosition = data_get($port, 'position');
    $sizeRange = fmt_range(data_get($port, 'sizes.min'), data_get($port, 'sizes.max'), '');
    $sizeRangeLabel = $sizeRange === '-' ? '-' : 'S'.$sizeRange;
    $portTypeLabel = collect([data_get($port, 'type')/*, data_get($port, 'subtype')*/])->filter()->implode(' / ');
    $isLocked = data_get($port, 'editable') === false || ! $editable;
    $equippedCardClasses = 'card-compact';

    // Extract equipped item stats for summary display
    $equippedItem = data_get($port, 'equipped_item', data_get($port, 'equipped_port_item'));
    $showQuickStats = !empty($equippedItem);
    $equippedItemName = data_get($equippedItem, 'name');
    $hasNamedEquippedItem = ! empty($equippedItemName) && $equippedItemName !== 'Placeholder';
    $displayPortLabel = $hasNamedEquippedItem ? $equippedItemName : $portLabel;
    $displayPortName = $portName ?? $equippedItemName ?? '-';
    $equippedDisplayName = $hasNamedEquippedItem ? $portLabel : ($equippedItemName ?? '-');

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
        class="collapse collapse-arrow border border-base-300 bg-base-100 shadow-sm"
    >
        <summary
            class="collapse-title min-h-10 py-2 text-sm font-semibold flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
            aria-expanded="false"
            aria-controls="{{ $portIdentifier }}-content"
        >
            <span class="flex flex-wrap items-center gap-2">
                @if ($depth > 0)
                    <span class="text-base-content/50">↳</span>
                @endif
                @if($isLocked)
                    <x-icon name="lock" class="size-3"/>
                @endif
                <span>{{ $displayPortLabel }}</span>
                @if (! empty($portPosition))
                    <span class="text-xs font-normal text-base-content/60">{{ $portPosition }}</span>
                @endif
                @if ($sizeRangeLabel !== '-')
                    <span class="badge badge-ghost badge-sm">{{ $sizeRangeLabel }}</span>
                @endif
                @if ($portTypeLabel !== '')
                    <span class="badge badge-ghost badge-sm max-w-[12rem] truncate" title="{{ $portTypeLabel }}">
                        {{ $portTypeLabel }}
                    </span>
                @endif
            </span>

            @if ($showQuickStats)
                <span class="flex flex-wrap items-center gap-2 text-xs font-normal tabular-nums">
                    @if (! empty($equippedItemName))
                        <span class="max-w-[14rem] truncate text-base-content/70" title="{{ $equippedDisplayName }}">
                            {{ $equippedDisplayName }}
                        </span>
                    @endif
                    @if ($itemSize !== null)
                        <span class="badge badge-sm badge-soft" title="Item Size">S{{ $itemSize }}</span>
                    @endif
                    @if ($powerSegmentUsage !== null)
                        <span class="badge badge-sm badge-soft" title="Power Segment Usage">
                            <x-icon name="zap" class="size-3"/>
                            <span class="font-medium">{{ fmt_compact($powerSegmentUsage, 1) }}</span>
                            <span class="hidden sm:inline">Power Usage</span>
                            <span class="sm:hidden">Pwr</span>
                        </span>
                    @endif
                    @if ($coolantSegmentUsage !== null)
                        <span class="badge badge-sm badge-soft" title="Coolant Segment Usage">
                            <x-icon name="fan" class="size-3"/>
                            <span class="font-medium">{{ fmt_compact($coolantSegmentUsage, 1) }}</span>
                            <span class="hidden sm:inline">Coolant Usage</span>
                            <span class="sm:hidden">Cool</span>
                        </span>
                    @endif
                    @if ($typeSpecificStat !== null)
                        <span class="badge badge-sm badge-primary" title="{{ $typeSpecificLabel }}">
                            @if ($typeSpecificIcon)
                                <x-icon name="{{ $typeSpecificIcon }}" class="size-3"/>
                            @endif
                            <span class="font-medium">{{ fmt_compact($typeSpecificStat, 0) }}</span>
                            <span class="hidden sm:inline">{{ $typeSpecificLabel }}</span>
                        </span>
                    @endif
                </span>
            @endif
        </summary>
        <div id="{{ $portIdentifier }}-content" class="collapse-content">
            <div class="grid gap-4">
                <dl class="grid gap-2 sm:gap-3 grid-cols-1 sm:grid-cols-2 md:grid-cols-3 tabular-nums">
                    <div class="flex flex-col gap-1">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                            Port Name
                        </dt>
                        <dd class="text-sm font-medium">{{ $displayPortName }}</dd>
                    </div>

                    <div class="flex flex-col gap-1">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                            Equippable Item Size
                        </dt>
                        <dd class="text-sm font-medium">{{ $sizeRangeLabel }}</dd>
                    </div>

                    <div class="flex flex-col gap-1">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                            Equippable Type + Sub Type
                        </dt>
                        <dd class="text-sm font-medium">{{ $portTypeLabel !== '' ? $portTypeLabel : '-' }}</dd>
                    </div>

                    @if (! empty($portPosition))
                        <div class="flex flex-col gap-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                                Position
                            </dt>
                            <dd class="text-sm font-medium">{{ $portPosition }}</dd>
                        </div>
                    @endif
                </dl>

                @unless(empty($equippedItem))
                    <div class="grid gap-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                                Equipped Item
                            </span>
                            <span class="text-sm font-medium">{{ $equippedItemName }}</span>
                            @if (! empty(data_get($equippedItem, 'uuid')))
                                <a href="{{ route('web.items.show', data_get($equippedItem, 'uuid')) }}"
                                   class="link link-primary text-sm">View</a>
                            @endif
                        </div>
                        <div class="grid gap-3 lg:grid-cols-2">
                            @php
                                $equippedType = data_get($equippedItem, 'type');
                            @endphp
                            @if ($equippedType === 'WeaponPersonal')
                                <x-items.personal-weapon-card
                                    :personal-weapon="data_get($equippedItem, 'personal_weapon')"
                                    :class="$equippedCardClasses"/>
                            @endif

                            @if ($equippedType === 'Armor')
                                <x-items.armor-card :armor="data_get($equippedItem, 'armor')" :class="$equippedCardClasses"/>
                            @endif

                            @if ($equippedType === 'WeaponGun')
                                <x-items.vehicle-weapon-card
                                    :vehicle-weapon="data_get($equippedItem, 'vehicle_weapon')"
                                    :class="$equippedCardClasses"/>
                            @endif

                            @if ($equippedType === 'WeaponAttachment')
                                <x-items.weapon-attachment-card :weapon-attachment="$equippedItem" :class="$equippedCardClasses"/>
                            @endif

                            @if (data_get($equippedItem, 'shield'))
                                <x-items.shield-card :shield="data_get($equippedItem, 'shield')" :class="$equippedCardClasses"/>
                            @endif

                            @if (data_get($equippedItem, 'quantum_drive'))
                                <x-items.quantum-drive-card
                                    :quantum-drive="data_get($equippedItem, 'quantum_drive')"
                                    :class="$equippedCardClasses"/>
                            @endif

                            @if (data_get($equippedItem, 'jump_drive'))
                                <x-items.jump-drive-card
                                    :jump-drive="data_get($equippedItem, 'jump_drive')"
                                    :class="$equippedCardClasses"/>
                            @endif

                            @if (data_get($equippedItem, 'power_plant'))
                                <x-items.power-plant-card
                                    :power-plant="data_get($equippedItem, 'power_plant')"
                                    :class="$equippedCardClasses"/>
                            @endif

                            @if (data_get($equippedItem, 'cooler'))
                                <x-items.cooler-card :cooler="data_get($equippedItem, 'cooler')" :class="$equippedCardClasses"/>
                            @endif

                            @if (data_get($equippedItem, 'counter_measure'))
                                <x-items.counter-measure-card
                                    :counter-measure="data_get($equippedItem, 'counter_measure')"
                                    :class="$equippedCardClasses"/>
                            @endif

                            @if (data_get($equippedItem, 'bomb'))
                                <x-items.bomb-card :bomb="data_get($equippedItem, 'bomb')" :class="$equippedCardClasses"/>
                            @endif

                            @if (data_get($equippedItem, 'seat'))
                                <x-items.seat-card :seat="data_get($equippedItem, 'seat')" :class="$equippedCardClasses"/>
                            @endif

                            @if (data_get($equippedItem, 'thruster'))
                                <x-items.thruster-card :thruster="data_get($equippedItem, 'thruster')" :class="$equippedCardClasses"/>
                            @endif

                            @if (data_get($equippedItem, 'fuel_tank'))
                                <x-items.fuel-tank-card :fuel-tank="data_get($equippedItem, 'fuel_tank')" :class="$equippedCardClasses"/>
                            @endif

                            @if (data_get($equippedItem, 'fuel_intake'))
                                <x-items.fuel-intake-card
                                    :fuel-intake="data_get($equippedItem, 'fuel_intake')"
                                    :class="$equippedCardClasses"/>
                            @endif

                            @if (data_get($equippedItem, 'emp'))
                                <x-items.emp-card :emp="data_get($equippedItem, 'emp')" :class="$equippedCardClasses"/>
                            @endif

                            @if (data_get($equippedItem, 'quantum_interdiction_generator'))
                                <x-items.quantum-interdiction-generator-card
                                    :quantum-interdiction-generator="data_get($equippedItem, 'quantum_interdiction_generator')"
                                    :class="$equippedCardClasses"/>
                            @endif

                            @if (data_get($equippedItem, 'mining_modifier'))
                                <x-items.mining-modifier-card
                                    :mining-modifier="data_get($equippedItem, 'mining_modifier')"
                                    :class="$equippedCardClasses"/>
                            @endif

                            @if (data_get($equippedItem, 'emission'))
                                <x-items.emission-card :emission="data_get($equippedItem, 'emission')" :class="$equippedCardClasses"/>
                            @endif

                            @if (data_get($equippedItem, 'mining_laser'))
                                <x-items.mining-laser-card
                                    :mining-laser="data_get($equippedItem, 'mining_laser')"
                                    :class="$equippedCardClasses"/>
                            @endif

                            @if (data_get($equippedItem, 'self_destruct'))
                                <x-items.self-destruct-card
                                    :self-destruct="data_get($equippedItem, 'self_destruct')"
                                    :class="$equippedCardClasses"/>
                            @endif

                            @if (data_get($equippedItem, 'missile_rack'))
                                <x-items.missile-rack-card
                                    :missile-rack="data_get($equippedItem, 'missile_rack')"
                                    :class="$equippedCardClasses"/>
                            @endif

                            @if (data_get($equippedItem, 'tractor_beam'))
                                <x-items.tractor-beam-card
                                    :tractor-beam="data_get($equippedItem, 'tractor_beam')"
                                    :class="$equippedCardClasses"/>
                            @endif

                            @if ($equippedType === 'FlightController')
                                <x-items.flight-controller-card
                                    :flight-controller="data_get($equippedItem, 'flight_controller')"
                                    :class="$equippedCardClasses"/>
                            @endif

                            @if ($equippedType === 'ShieldController')
                                <x-items.shield-controller-card
                                    :shield-controller="data_get($equippedItem, 'shield_controller')"
                                    :class="$equippedCardClasses"/>
                            @endif

                            @if ($equippedType === 'Radar')
                                <x-items.radar-card :radar="data_get($equippedItem, 'radar')" :class="$equippedCardClasses"/>
                            @endif

                            @if ($equippedType === 'Turret')
                                <x-items.turret-card
                                    :turret="data_get($equippedItem, 'turret')"
                                    :class="$equippedCardClasses.' pr-0'"/>
                            @endif

                            @if (data_get($equippedItem, 'inventory'))
                                <x-items.cargo-grid-card
                                    :cargo-grid="data_get($equippedItem, 'inventory')"
                                    :class="$equippedCardClasses"/>
                            @endif

                            @if (data_get($equippedItem, 'missile'))
                                <x-items.missile-card :missile="data_get($equippedItem, 'missile')" :class="$equippedCardClasses"/>
                            @endif

                            @if (data_get($equippedItem, 'suit_armor'))
                                <x-items.suit-armor-card
                                    :suit-armor="data_get($equippedItem, 'suit_armor')"
                                    :class="$equippedCardClasses"/>
                            @endif

                            @if (data_get($equippedItem, 'temperature_resistance'))
                                <x-items.temperature-resistance-card
                                    :temperature-resistance="data_get($equippedItem, 'temperature_resistance')"
                                    :class="$equippedCardClasses"/>
                            @endif

                            @if (data_get($equippedItem, 'radiation_resistance'))
                                <x-items.radiation-resistance-card
                                    :radiation-resistance="data_get($equippedItem, 'radiation_resistance')"
                                    :class="$equippedCardClasses"/>
                            @endif

                            @if (data_get($equippedItem, 'ammunition'))
                                <x-items.ammunition-card
                                    :ammunition="data_get($equippedItem, 'ammunition')"
                                    :class="$equippedCardClasses"/>
                            @endif

                            @if (data_get($equippedItem, 'weapon_modifier'))
                                <x-items.weapon-modifier-card
                                    :weapon-modifier="data_get($equippedItem, 'weapon_modifier')"
                                    :class="$equippedCardClasses"/>
                            @endif
                        </div>
                    </div>
                @endunless
            </div>

            @if (! empty(data_get($port, 'ports')))
                @foreach (data_get($port, 'ports') as $childPort)
                    <x-port-display :port="$childPort" :depth="$depth + 1" :editable="data_get($port, 'editable_children')"/>
                @endforeach
            @endif
        </div>
    </details>
</div>
