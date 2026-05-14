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

    // Browse-equippable filters — prefer compatible_types over port type
    $compatibleTypes = collect(data_get($port, 'compatible_types', []))->map(fn (array $ct) => $ct['type'])->filter();
    $browseType = $compatibleTypes->contains($row['type']) ? $row['type'] : $compatibleTypes->first();
    $browseType = $browseType ?: $row['type'];
    $browseSubType = $row['sub_type'] && $row['sub_type'] !== 'UNDEFINED' ? $row['sub_type'] : null;
    $canBrowse = ! $isLocked && $browseType !== null && $browseType !== '';
    $browseFilters = [];
    if ($canBrowse) {
        $browseFilters = array_filter([
            'type' => $browseType,
            'sub_type' => $browseSubType,
            'name' => $browseType === 'FlightController' ? $vehicleName : null,
        ]);
        if ($row['size_min'] !== null && $row['size_max'] !== null) {
            $browseFilters['size'] = implode(',', range($row['size_min'], $row['size_max']));
        }
    }
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
    <div
        id="{{ $portIdentifier }}"
        data-testid="port-display-details"
        class="border border-base-300  rounded-lg {{ $depth === 0 ? 'shadow-sm bg-base-200' : 'bg-base-100' }}"
    >
        <div
            data-testid="port-display-summary"
            class="px-3 py-2 text-sm font-semibold flex flex-col gap-1.5"
        >
            <span class="flex flex-wrap items-center gap-2 min-w-0">
                @if ($depth > 0)
                    <span class="text-muted shrink-0">↳</span>
                @endif
                @if($isLocked)
                    <x-icon name="lock" class="size-3 shrink-0"/>
                @endif
                @if ($row['pilot_slaveable'] ?? false)
                    <span class="badge badge-sm badge-soft badge-warning shrink-0" title="Pilot Slaveable">
                        <x-icon name="joystick" class="size-3"/>
                    </span>
                @endif
                @if ($row['item_size'] !== null)
                    <span class="badge badge-sm badge-outline shrink-0" title="Item Size">S{{ $row['item_size'] }}</span>
                @endif
                @if ($isDeactivated)
                    <span class="badge badge-soft badge-sm shrink-0" data-testid="port-display-deactivated" title="{{ $deactivationReason }}">
                        <x-icon name="power-off" class="size-3"/>
                        <span>Deactivated</span>
                    </span>
                @endif
                <span class="truncate" title="{{ $displayPortName }}">
                    @if($equippedItemUuid)
                        <a
                            data-testid="port-display-equipped-item-link"
                            href="{{ route('web.items.show', $equippedItemUuid) }}"
                            class="link link-primary text-sm"
                        >{{$displayPortLabel}}</a>
                    @else
                        {{ $displayPortLabel }}
                    @endif
                </span>
                @if ($row['type_annotation'])
                    <span class="text-xs font-normal text-subtle">({{ $row['type_annotation'] }})</span>
                @endif
                @if (! empty($row['position']))
                    <span class="text-xs font-normal text-subtle">{{ $row['position'] }}</span>
                @endif
                @if ($row['primary_stat'] !== null)
                    <span class="ml-auto badge badge-sm badge-primary" title="{{ $row['primary_label'] }}">
                        @if ($row['primary_icon'])
                            <x-icon name="{{ $row['primary_icon'] }}" class="size-3"/>
                        @endif
                        <span class="font-medium">{{ Format::compact($row['primary_stat'], 1) }}{{ $row['primary_unit'] }}</span> {{ $row['primary_label'] }}
                    </span>
                @endif
                @if ($canBrowse)
                    <span class="{{ $row['primary_stat'] === null ? 'ml-auto' : '' }}">
                        <x-port-browse-popup :type="$browseType" :sub-type="$browseSubType" :size-min="$row['size_min']" :size-max="$row['size_max']" :browse-url="route('web.items.index', ['filter' => $browseFilters])"/>
                    </span>
                @endif
            </span>

            <span class="flex flex-wrap items-center justify-end gap-x-3 pl-5 text-xs font-normal tabular-nums text-subtle">
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
                @if (! empty($row['secondary_stats']))
                    @foreach ($row['secondary_stats'] as $sec)
                        <span>{{ $sec }}</span>
                    @endforeach
                @endif
                @if (! $hasEquippedItem && $sizeLabel !== null)
                    <span title="Equippable Size">{{ $sizeLabel }}</span>
                @endif
            </span>
        </div>
        @if (! empty(data_get($port, 'ports')))
        <div id="{{ $portIdentifier }}-content" class="px-3 pb-2 space-y-2">
            @foreach (data_get($port, 'ports') as $childPort)
                @php
                    $childType = data_get($childPort, 'type');
                    $childSubType = data_get($childPort, 'sub_type');
                    $childEquipItem = data_get($childPort, 'equipped_item');
                    $isIgnoredType = in_array($childType, ['Display', 'Screen', 'Seat', 'Door', 'Hatch', 'Ladder', 'Light', 'Button', 'Misc', 'WeaponAttachment'], true);
                    $isIgnoredSubtype = $childSubType === 'UNDEFINED' || ($childType === 'Misc' && $childSubType === 'Utility');
                    $hasChildContent = ! $isIgnoredType && ! $isIgnoredSubtype && (! empty($childType) || ! empty($childEquipItem));
                @endphp
                @if ($hasChildContent)
                    <x-port-display :port="$childPort" :depth="$depth + 1" :editable="data_get($childPort, 'editable', false)" :vehicle-name="$vehicleName"/>
                @endif
            @endforeach
        </div>
        @endif
    </div>
</div>
@endif
