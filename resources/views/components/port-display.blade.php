@use('App\Support\Format')
@php use Illuminate\Support\Str; @endphp
@props([
    'port',
    'depth' => 0,
    'editable' => null,
    'powerPools' => [],
    'categoryIndex' => 0,
    'vehicleName' => null,
])

@php
    $indentClass = $depth > 0 ? 'mt-2 pl-2 sm:pl-3 border-l-2 sm:border-l-4 border-base-300/70' : '';
    $portId = $depth . '-' . ($loop->index ?? 0);
    $portIdentifier = 'port-'.$portId;
    $portName = data_get($port, 'name');
    $portLabel = Str::of($portName ?? 'Port')->lower()->replace('hardpoint_', '')->headline();
    $portPosition = data_get($port, 'position');
    $sizeRange = Format::range(data_get($port, 'sizes.min'), data_get($port, 'sizes.max'), '');
    $sizeRangeLabel = $sizeRange === '-' ? '-' : 'S'.$sizeRange;
    $portTypeLabel = collect([data_get($port, 'type')/*, data_get($port, 'subtype')*/])->filter()->implode(' / ');
    $isLocked = is_bool($editable) ? !$editable : (data_get($port, 'editable') === true ? false : true);
    $portSizeMin = data_get($port, 'sizes.min');
    $portSizeMax = data_get($port, 'sizes.max');
    $portType = data_get($port, 'type');
    $equippedCardClasses = 'port-equipped-card';

    // Extract equipped item stats for summary display
    $equippedItem = data_get($port, 'equipped_item', data_get($port, 'equipped_port_item'));
    $equippedItemUuid = data_get($equippedItem, 'uuid');
    $showQuickStats = !empty($equippedItem);
    $equippedItemName = data_get($equippedItem, 'name');
    $hasNamedEquippedItem = ! empty($equippedItemName) && $equippedItemName !== '<= PLACEHOLDER =>';
    $displayPortLabel = $hasNamedEquippedItem ? $equippedItemName : $portLabel;
    $displayPortName = $portName ?? '-';
    $equippedDisplayName = $hasNamedEquippedItem ? $portLabel : ($equippedItemName ?? '-');

    // Power pool deactivation logic
    $isDeactivated = false;
    $deactivationReason = null;

    if ($showQuickStats && !empty($powerPools)) {
        $itemType = data_get($equippedItem, 'type');

        // Normalize item type for power pool matching
        $poolItemType = match (true) {
            $itemType === 'Shield' => 'Shield',
            $itemType === 'WeaponGun' => 'WeaponGun',
            $itemType === 'FlightController' => 'FlightController',
            $itemType === 'TractorBeam' => 'TractorBeam',
            $itemType === 'TowingBeam' => 'TowingBeam',
            $itemType === 'WeaponMining' => 'WeaponMining',
            $itemType === 'SalvageHead' => 'SalvageHead',
            default => $itemType,
        };

        $pool = data_get($powerPools, $poolItemType);
        $poolSize = data_get($pool, 'size');

        // Only apply for Shield pool for now
        if ($poolItemType === 'Shield' && $poolSize !== null && $poolSize >= 0) {
            $shieldIndex = $categoryIndex;

            if ($categoryIndex >= $poolSize) {
                $isDeactivated = true;
                $idx = $shieldIndex+1;
                $deactivationReason = "Pool Limit ({$idx} of {$poolSize} active)";
            }
        }
    }

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
        } elseif (data_get($equippedItem, 'resource_network.generation.coolant')) {
            $typeSpecificStat = data_get($equippedItem, 'resource_network.generation.coolant');
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

<div class="port-entry {{ $indentClass }} {{ $isDeactivated ? 'opacity-60 bg-error/5 border-error/30' : '' }}" data-testid="port-display">
    <details {{ $depth > 0 ? 'data-remove' : '' }}
        id="{{ $portIdentifier }}"
        data-testid="port-display-details"
        class="collapse collapse-arrow border border-base-300 bg-base-100 {{ $depth === 0 ? 'shadow-sm' : '' }}"
    >
        <summary
            data-testid="port-display-summary"
            class="collapse-title min-h-10 py-2 text-sm font-semibold flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
            aria-expanded="false"
            aria-controls="{{ $portIdentifier }}-content"
        >
            <span class="flex flex-wrap items-center gap-2">
                @if ($isDeactivated)
                    <span class="badge badge-ghost badge-sm" data-testid="port-display-deactivated" title="{{ $deactivationReason }}">
                        <x-icon name="power-off" class="size-3"/>
                        <span>Deactivated</span>
                    </span>
                @endif
                @if ($depth > 0)
                    <span class="text-muted">↳</span>
                @endif
                @if($isLocked)
                    <x-icon name="lock" class="size-3"/>
                @endif
                    @if($equippedItemUuid)
                        <a
                            data-testid="port-display-equipped-item-link"
                            href="{{ route('web.items.show', $equippedItemUuid) }}"
                            class="link link-primary text-sm"
                        >{{$displayPortLabel}}</a>
                    @else
                        <span>{{ $displayPortLabel }}</span>
                    @endif
                @if (! empty($portPosition))
                    <span class="text-xs font-normal text-subtle">{{ $portPosition }}</span>
                @endif
                @if ($sizeRangeLabel !== '-')
                    <span class="badge badge-ghost badge-sm">{{ $sizeRangeLabel }}</span>
                @endif
                @if ($portTypeLabel !== '')
                    @if (! $isLocked && $portType)
                        @php
                            $browseFilters = array_filter([
                                'type' => $portType,
                                'name' => $portType === 'FlightController' ? $vehicleName : null,
                            ]);
                            if ($portSizeMin !== null && $portSizeMax !== null) {
                                $browseFilters['size'] = implode(',', range($portSizeMin, $portSizeMax));
                            }
                        @endphp
                        <a href="{{ route('web.items.index', ['filter' => $browseFilters]) }}" class="badge badge-ghost badge-sm max-w-48 truncate no-underline hover:opacity-80" title="Browse {{ $portTypeLabel }}">
                            {{ $portTypeLabel }}
                            <x-icon name="external-link" class="size-3 opacity-60"/>
                        </a>
                    @else
                        <span class="badge badge-ghost badge-sm max-w-48 truncate" title="{{ $portTypeLabel }}">
                            {{ $portTypeLabel }}
                        </span>
                    @endif
                @endif
            </span>

            @if ($showQuickStats)
                <span class="flex flex-wrap items-center gap-2 text-xs font-normal tabular-nums">
                    @if ($hasNamedEquippedItem)
                        <span class="max-w-56 truncate text-subtle" title="{{ $equippedDisplayName }}">
                            {{ $equippedDisplayName }}
                        </span>
                    @endif
                    @if ($itemSize !== null)
                        <span class="badge badge-sm badge-ghost" title="Item Size">S{{ $itemSize }}</span>
                    @endif
                    @if ($powerSegmentUsage > 0)
                        <span class="badge badge-sm badge-ghost" title="Power Segment Usage">
                            <x-icon name="zap" class="size-3"/>
                            <span class="font-medium">{{ Format::compact($powerSegmentUsage, 1) }}</span>
                            <span class="hidden sm:inline">Power Usage</span>
                            <span class="sm:hidden">Pwr</span>
                        </span>
                    @endif
                    @if ($coolantSegmentUsage > 0)
                        <span class="badge badge-sm badge-ghost" title="Coolant Segment Usage">
                            <x-icon name="fan" class="size-3"/>
                            <span class="font-medium">{{ Format::compact($coolantSegmentUsage, 1) }}</span>
                            <span class="hidden sm:inline">Coolant Usage</span>
                            <span class="sm:hidden">Cool</span>
                        </span>
                    @endif
                    @if ($typeSpecificStat !== null)
                        <span class="badge badge-sm badge-primary" title="{{ $typeSpecificLabel }}">
                            @if ($typeSpecificIcon)
                                <x-icon name="{{ $typeSpecificIcon }}" class="size-3"/>
                            @endif
                            <span class="font-medium">{{ Format::compact($typeSpecificStat, 0) }}</span>
                            <span class="hidden sm:inline">{{ $typeSpecificLabel }}</span>
                        </span>
                    @endif
                </span>
            @endif
        </summary>
        <div id="{{ $portIdentifier }}-content" class="collapse-content">

            @php
                $hasPortMeta = $displayPortName !== '-' || $sizeRangeLabel !== '-' || $portTypeLabel !== '' || !empty($portPosition);
            @endphp
            @if ($hasPortMeta)
                <div class="card card-border bg-base-100">
                    <div class="card-body p-2">
                        <x-dl-container :headCols="$portPosition ? 4 : 3">
                            <x-slot:head>
                                <x-dt-dd label="Port Name" :value="$displayPortName">{{ $displayPortName }}</x-dt-dd>
                                <x-dt-dd label="Equippable Size" :value="$sizeRangeLabel !== '-'">{{ $sizeRangeLabel }}</x-dt-dd>
                                <x-dt-dd label="Equippable Type" :value="$portTypeLabel !== ''">{{ $portTypeLabel !== '' ? $portTypeLabel : '-' }}</x-dt-dd>
                                <x-dt-dd label="Position" :value="$portPosition">{{ $portPosition }}</x-dt-dd>
                            </x-slot:head>
                        </x-dl-container>
                    </div>
                </div>
            @endif
            @unless(empty($equippedItem))
                <div class="border border-base-300 rounded-lg overflow-hidden">
                    @php
                        $equippedType = data_get($equippedItem, 'type');
                    @endphp
                    @if ($equippedType === 'WeaponPersonal')
                        <x-items.personal-weapon-card
                            :personal-weapon="data_get($equippedItem, 'personal_weapon')"
                           />
                    @endif

                    @if ($equippedType === 'Armor')
                        <x-items.armor-card :armor="data_get($equippedItem, 'armor')"/>
                    @endif

                    @if ($equippedType === 'WeaponGun')
                        <x-items.vehicle-weapon-card
                            :vehicle-weapon="data_get($equippedItem, 'vehicle_weapon')"
                            :class="$equippedCardClasses"/>
                    @endif

                    @if ($equippedType === 'WeaponAttachment')
                        <x-items.weapon-attachment-card :weapon-attachment="$equippedItem"/>
                    @endif

                    @if (data_get($equippedItem, 'shield'))
                        <x-items.shield-card :shield="data_get($equippedItem, 'shield')"/>
                    @endif

                    @if (data_get($equippedItem, 'quantum_drive'))
                        <x-items.quantum-drive-card
                            :quantum-drive="data_get($equippedItem, 'quantum_drive')"
                           />
                    @endif

                    @if (data_get($equippedItem, 'jump_drive'))
                        <x-items.jump-drive-card
                            :jump-drive="data_get($equippedItem, 'jump_drive')"
                           />
                    @endif

                    @if (data_get($equippedItem, 'power_plant'))
                        <x-items.power-plant-card
                            :power-plant="data_get($equippedItem, 'power_plant')"
                           />
                    @endif

                    @if (data_get($equippedItem, 'resource_network'))
                        <x-items.resource-network-card :resource-network="data_get($equippedItem, 'resource_network')" :item-type="data_get($equippedItem, 'type')"/>
                    @endif

                    @if (data_get($equippedItem, 'counter_measure'))
                        <x-items.counter-measure-card
                            :counter-measure="data_get($equippedItem, 'counter_measure')"
                           />
                    @endif

                    @if (data_get($equippedItem, 'bomb'))
                        <x-items.bomb-card :bomb="data_get($equippedItem, 'bomb')"/>
                    @endif

                    @if (data_get($equippedItem, 'seat'))
                        <x-items.seat-card :seat="data_get($equippedItem, 'seat')"/>
                    @endif

                    @if (data_get($equippedItem, 'thruster'))
                        <x-items.thruster-card :thruster="data_get($equippedItem, 'thruster')"/>
                    @endif

                    @if (data_get($equippedItem, 'fuel_tank'))
                        <x-items.fuel-tank-card :fuel-tank="data_get($equippedItem, 'fuel_tank')"/>
                    @endif

                    @if (data_get($equippedItem, 'fuel_intake'))
                        <x-items.fuel-intake-card
                            :fuel-intake="data_get($equippedItem, 'fuel_intake')"
                           />
                    @endif

                    @if (data_get($equippedItem, 'emp'))
                        <x-items.emp-card :emp="data_get($equippedItem, 'emp')"/>
                    @endif

                    @if (data_get($equippedItem, 'quantum_interdiction_generator'))
                        <x-items.quantum-interdiction-generator-card
                            :quantum-interdiction-generator="data_get($equippedItem, 'quantum_interdiction_generator')"
                           />
                    @endif

                    @if (data_get($equippedItem, 'mining_modifier'))
                        <x-items.mining-modifier-card
                            :mining-modifier="data_get($equippedItem, 'mining_modifier')"
                           />
                    @endif

                    @if (data_get($equippedItem, 'emission') && data_get($equippedItem, 'emission.em_max', 0) > 0)
                        <x-items.emission-card :emission="data_get($equippedItem, 'emission')"/>
                    @endif

                    @if (data_get($equippedItem, 'mining_laser'))
                        <x-items.mining-laser-card
                            :mining-laser="data_get($equippedItem, 'mining_laser')"
                           />
                    @endif

                    @if (data_get($equippedItem, 'self_destruct'))
                        <x-items.self-destruct-card
                            :self-destruct="data_get($equippedItem, 'self_destruct')"
                           />
                    @endif

                    @if (data_get($equippedItem, 'missile_rack'))
                        <x-items.missile-rack-card
                            :missile-rack="data_get($equippedItem, 'missile_rack')"
                           />
                    @endif

                    @if (data_get($equippedItem, 'tractor_beam'))
                        <x-items.tractor-beam-card
                            :tractor-beam="data_get($equippedItem, 'tractor_beam')"
                           />
                    @endif

                    @if ($equippedType === 'FlightController')
                        <x-items.flight-controller-card
                            :flight-controller="data_get($equippedItem, 'flight_controller')"
                           />
                    @endif

                    @if ($equippedType === 'ShieldController')
                        <x-items.shield-controller-card
                            :shield-controller="data_get($equippedItem, 'shield_controller')"
                           />
                    @endif

                    @if ($equippedType === 'Radar')
                        <x-items.radar-card :radar="data_get($equippedItem, 'radar')"/>
                    @endif

                    @if ($equippedType === 'Turret')
                        <x-items.turret-card
                            :turret="data_get($equippedItem, 'turret')"
                           />
                    @endif

                    @if (data_get($equippedItem, 'inventory'))
                        <x-items.cargo-grid-card
                            :cargo-grid="data_get($equippedItem, 'inventory')"
                           />
                    @endif

                    @if (data_get($equippedItem, 'missile'))
                        <x-items.missile-card :missile="data_get($equippedItem, 'missile')"/>
                    @endif

                    @if (data_get($equippedItem, 'suit_armor'))
                        <x-items.suit-armor-card
                            :suit-armor="data_get($equippedItem, 'suit_armor')"
                            :temperature-resistance="data_get($equippedItem, 'temperature_resistance')"
                            :inventory="data_get($equippedItem, 'inventory')"
                           />
                    @endif

                    @if (data_get($equippedItem, 'clothing') && !data_get($equippedItem, 'suit_armor'))
                        <x-items.clothing-card
                            :clothing="data_get($equippedItem, 'clothing')"
                            :temperature-resistance="data_get($equippedItem, 'temperature_resistance')"
                            :inventory="data_get($equippedItem, 'inventory')"
                           />
                    @endif

                    @if (data_get($equippedItem, 'ammunition'))
                        <x-items.ammunition-card
                            :ammunition="data_get($equippedItem, 'ammunition')"
                           />
                    @endif

                    @if (data_get($equippedItem, 'weapon_modifier'))
                        <x-items.weapon-modifier-card
                            :weapon-modifier="data_get($equippedItem, 'weapon_modifier')"
                           />
                    @endif
                </div>
            @endunless


            @if (! empty(data_get($port, 'ports')))
                @foreach (data_get($port, 'ports') as $childPort)
                    <x-port-display :port="$childPort" :depth="$depth + 1" :editable="data_get($childPort, 'editable', false)" :vehicle-name="$vehicleName"/>
                @endforeach
            @endif
        </div>
    </details>
</div>
