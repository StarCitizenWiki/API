@use('App\Support\Format')
@use('App\Support\Game\HardpointRow')
@props([
    'port',
    'depth' => 0,
    'editable' => null,
    'powerPools' => [],
    'categoryIndex' => 0,
    'vehicleName' => null,
])

@php
    use Illuminate\Support\Str;

    $row = HardpointRow::make($port, $powerPools, $categoryIndex);

    $indentClass = $depth > 0 ? 'mt-2 pl-2 sm:pl-3 border-l-2 sm:border-l-4 border-base-300/70' : '';
    $portId = $depth . '-' . ($loop->index ?? 0);
    $portIdentifier = 'port-'.$portId;
    $portName = $row['name'];
    $portLabel = $row['display_name'];
    $isLocked = is_bool($editable) ? !$editable : !$row['editable'];
    $sizeLabel = $row['size_label'];
    $isDeactivated = $row['deactivated'];
    $deactivationReason = $row['deactivation_reason'];
    $equippedItemUuid = $row['equipped_item_uuid'];
    $hasEquippedItem = $row['has_equipped_item'];
    $equippedItem = data_get($port, 'equipped_item', data_get($port, 'equipped_port_item'));
    $isVehicleDock = $row['is_attached_vehicle'];

    // Display name logic: when item is named, swap port label and item name
    $hasNamedEquippedItem = $hasEquippedItem && ! empty(data_get($equippedItem, 'name')) && data_get($equippedItem, 'name') !== '<= PLACEHOLDER =>';
    $displayPortLabel = $hasNamedEquippedItem ? $portLabel : ($isVehicleDock ? data_get($row['attached_vehicle'], 'name', $portLabel) : $portLabel);
    $displayPortName = $portName;
    $equippedDisplayName = $hasNamedEquippedItem
        ? Str::of($portName ?? 'Port')->lower()->replace('hardpoint_', '')->headline()
        : ($isVehicleDock ? data_get($row['attached_vehicle'], 'class_name', data_get($equippedItem, 'name', '-')) : (data_get($equippedItem, 'name', '-')));
@endphp

@if ($isVehicleDock)
<div class="port-entry {{ $indentClass }}" data-testid="port-display">
    <div
        id="{{ $portIdentifier }}"
        data-testid="port-display-details"
        class="border border-primary/30 bg-primary/5 rounded-lg px-3 py-2 flex flex-wrap items-center gap-2"
    >
        <x-icon name="rocket" class="size-4 text-primary shrink-0"/>
        <a
            data-testid="port-display-attached-vehicle-link"
            href="{{ data_get($row['attached_vehicle'], 'web_url') }}"
            class="link link-primary font-semibold text-sm"
        >{{ data_get($row['attached_vehicle'], 'name', $displayPortLabel) }}</a>
        @if (data_get($row['attached_vehicle'], 'size_class'))
            <span class="badge badge-sm badge-primary" title="Vehicle Size">S{{ data_get($row['attached_vehicle'], 'size_class') }}</span>
        @endif
        <span class="badge badge-sm badge-soft">
            @if (data_get($row['attached_vehicle'], 'is_spaceship'))
                Spaceship
            @elseif (data_get($row['attached_vehicle'], 'is_gravlev'))
                Gravlev
            @elseif (data_get($row['attached_vehicle'], 'is_vehicle'))
                Ground Vehicle
            @endif
        </span>
    </div>
</div>
@else
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
                @if($isLocked)
                    <x-icon name="lock" class="size-3"/>
                @endif
                @if ($row['item_size'] !== null)
                    <span class="badge badge-sm badge-outline" title="Item Size">S{{ $row['item_size'] }}</span>
                @endif
                @if ($isDeactivated)
                    <span class="badge badge-soft badge-sm" data-testid="port-display-deactivated" title="{{ $deactivationReason }}">
                        <x-icon name="power-off" class="size-3"/>
                        <span>Deactivated</span>
                    </span>
                @endif
                @if ($depth > 0)
                    <span class="text-muted">↳</span>
                @endif
                    @if($equippedItemUuid)
                        <a
                            data-testid="port-display-equipped-item-link"
                            href="{{ route('web.items.show', $equippedItemUuid) }}"
                            class="link link-primary text-sm"
                            title="{{ $displayPortName }}"
                        >{{$displayPortLabel}}</a>
                        @if ($row['type_annotation'])
                            <span class="text-xs font-normal text-subtle">({{ $row['type_annotation'] }})</span>
                        @endif
                    @else
                        <span title="{{ $displayPortName }}">{{ $displayPortLabel }}</span>
                        @if ($row['type_annotation'])
                            <span class="text-xs font-normal text-subtle">({{ $row['type_annotation'] }})</span>
                        @endif
                    @endif
                @if (! $isLocked && $row['type'] && ! $hasEquippedItem)
                    @php
                        $browseFilters = array_filter([
                            'type' => $row['type'],
                            'name' => $row['type'] === 'FlightController' ? $vehicleName : null,
                        ]);
                        if ($row['size_min'] !== null && $row['size_max'] !== null) {
                            $browseFilters['size'] = implode(',', range($row['size_min'], $row['size_max']));
                        }
                    @endphp
                    <a href="{{ route('web.items.index', ['filter' => $browseFilters]) }}" class="text-subtle hover:text-primary transition-colors" title="Browse {{ $row['type'] }} items">
                        <x-icon name="external-link" class="size-3"/>
                    </a>
                @endif
                @if (! empty($row['position']))
                    <span class="text-xs font-normal text-subtle">{{ $row['position'] }}</span>
                @endif
            </span>

            @if ($hasEquippedItem)
                <span class="flex flex-wrap items-center justify-end gap-x-3 text-xs font-normal tabular-nums text-subtle">
                    @if ($row['power_usage'] > 0)
                        <span title="Power Segment Usage">
                            <x-icon name="zap" class="size-3 inline"/>
                            <span class="font-medium">{{ Format::compact($row['power_usage'], 1) }}</span>
                        </span>
                    @endif
                    @if ($row['coolant_usage'] > 0)
                        <span title="Coolant Segment Usage">
                            <x-icon name="fan" class="size-3 inline"/>
                            <span class="font-medium">{{ Format::compact($row['coolant_usage'], 1) }}</span>
                        </span>
                    @endif
                    @if ($row['primary_stat'] !== null)
                        <span class="badge badge-sm badge-primary" title="{{ $row['primary_label'] }}">
                            @if ($row['primary_icon'])
                                <x-icon name="{{ $row['primary_icon'] }}" class="size-3"/>
                            @endif
                            <span class="font-medium">{{ Format::compact($row['primary_stat'], 1) }}{{ $row['primary_unit'] }}</span> {{ $row['primary_label'] }}
                        </span>
                    @endif
                    @if (! empty($row['secondary_stats']))
                        @foreach ($row['secondary_stats'] as $sec)
                            <span>{{ $sec }}</span>
                        @endforeach
                    @endif
                    @if ($sizeLabel !== null)
                        <span title="Equippable Size">{{ $sizeLabel }}</span>
                    @endif
                </span>
            @elseif ($sizeLabel !== null)
                <span class="text-xs font-normal tabular-nums text-subtle" title="Equippable Size">{{ $sizeLabel }}</span>
            @endif
        </summary>
        <div id="{{ $portIdentifier }}-content" class="collapse-content space-y-2">
            @if (! empty(data_get($port, 'ports')))
                @foreach (data_get($port, 'ports') as $childPort)
                    @php
                        $childType = data_get($childPort, 'type');
                        $childSubType = data_get($childPort, 'sub_type');
                        $childEquipItem = data_get($childPort, 'equipped_item');
                        $isIgnoredType = in_array($childType, ['Display', 'Screen', 'Seat', 'Door', 'Hatch', 'Ladder', 'Light', 'Button'], true);
                        $isUndefinedSubtype = $childSubType === 'UNDEFINED';
                        $hasChildContent = ! $isIgnoredType && (! $isUndefinedSubtype || ! empty($childEquipItem)) && (! empty($childType) || ! empty($childEquipItem));
                    @endphp
                    @if ($hasChildContent)
                        <x-port-display :port="$childPort" :depth="$depth + 1" :editable="data_get($childPort, 'editable', false)" :vehicle-name="$vehicleName"/>
                    @endif
                @endforeach
            @endif

            @unless(! $hasEquippedItem)
            @php
                $hasNoChildren = empty(data_get($port, 'ports'));
                $autoExpandDetails = $hasNoChildren;
            @endphp
            <details class="collapse collapse-arrow" {{ $autoExpandDetails ? 'open' : '' }}>
                <summary class="collapse-title min-h-0 py-2 text-sm font-semibold text-subtle pl-0">
                    Item Details
                </summary>
                <div class="collapse-content pl-0">
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
                            :class="'port-equipped-card'"/>
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
                </div>
            </details>
            @endunless
        </div>
    </details>
</div>
@endif
