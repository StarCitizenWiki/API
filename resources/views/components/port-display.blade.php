@use('App\Support\Format')
@use('App\Support\Game\HardpointRow')
@props([
    'port',
    'depth' => 0,
    'editable' => null,
    'powerPools' => [],
    'categoryIndex' => 0,
    'vehicleName' => null,
    'vehiclePortTags' => [],
])

@php
    $row = HardpointRow::make($port, $powerPools, $categoryIndex);

    $portId = $depth . '-' . ($loop->index ?? 0);
    $portIdentifier = 'port-'.$portId;
    $equippedItem = data_get($port, 'equipped_item', data_get($port, 'equipped_port_item'));
    $attachedVehicle = $row['attached_vehicle'];
    $isAttachedVehicle = $row['is_attached_vehicle'];
    $isDeactivated = $row['deactivated'];
    $isLocked = is_bool($editable) ? ! $editable : ! $row['editable'];

    $title = $isAttachedVehicle ? data_get($attachedVehicle, 'name', $row['display_name']) : $row['name'];
    $label = $row['display_name'];
    $url = null;
    $linkTestId = null;
    $leadingIcon = $isLocked ? 'lock' : null;
    $leadingIconClass = $isLocked ? 'text-muted' : '';
    $size = $row['item_size'];
    $sizeTitle = 'Item Size';
    $fallbackSizeLabel = ! $row['has_equipped_item'] ? $row['size_label'] : null;
    $annotation = $row['type_annotation'];
    $subtext = $row['is_empty_port'] ? $row['compatible_type_label'] : null;
    $detailStats = [];
    $summaryText = null;
    $summaryPrimary = null;
    $summarySecondaries = $row['secondary_stats'];
    $childPorts = collect(data_get($port, 'ports') ?? []);

    if ($row['power_usage'] > 0) {
        $detailStats[] = ['icon' => 'zap', 'title' => 'Power segment usage', 'value' => Format::compact($row['power_usage'], 1)];
    }

    if ($row['coolant_usage'] > 0) {
        $detailStats[] = ['icon' => 'fan', 'title' => 'Cooling segment usage', 'value' => Format::compact($row['coolant_usage'], 1)];
    }

    if (is_int($row['primary_stat']) || is_float($row['primary_stat'])) {
        $summaryPrimary = [
            'title' => $row['primary_label'],
            'value' => Format::compact($row['primary_stat'], 1).$row['primary_unit'],
            'label' => $row['primary_label'],
        ];
    }

    if ($isAttachedVehicle) {
        $url = data_get($attachedVehicle, 'web_url');
        $linkTestId = 'port-display-attached-vehicle-link';
        $leadingIcon = 'rocket';
        $leadingIconClass = 'text-primary';
        $size = data_get($attachedVehicle, 'size_class');
        $sizeTitle = 'Vehicle Size';
        $fallbackSizeLabel = null;
        $annotation = null;
        $subtext = data_get($attachedVehicle, 'class_name');
        $detailStats = [];
        $summaryText = match (true) {
            (bool) data_get($attachedVehicle, 'is_spaceship') => 'Spaceship',
            (bool) data_get($attachedVehicle, 'is_gravlev') => 'Gravlev',
            (bool) data_get($attachedVehicle, 'is_vehicle') => 'Ground Vehicle',
            default => null,
        };
        $summaryPrimary = null;
        $summarySecondaries = [];
        $childPorts = collect();
    } elseif ($row['equipped_item_uuid']) {
        $url = data_get($equippedItem, 'web_url') ?? route('web.items.show', $row['equipped_item_uuid']);
        $linkTestId = 'port-display-equipped-item-link';
    }

    $visibleChildPorts = $childPorts
        ->filter(function (array $childPort): bool {
            $childType = data_get($childPort, 'type');
            $childSubType = data_get($childPort, 'sub_type');
            $childEquipItem = data_get($childPort, 'equipped_item', data_get($childPort, 'equipped_port_item'));
            $isIgnoredType = in_array($childType, ['Display', 'Screen', 'Seat', 'Door', 'Hatch', 'Ladder', 'Light', 'Button', 'Misc', 'WeaponAttachment', 'UNDEFINED'], true);
            $hasNamedEquipItem = ! empty($childEquipItem) && data_get($childEquipItem, 'name') !== '<= PLACEHOLDER =>';
            $isIgnoredSubtype = ($childSubType === 'UNDEFINED' && ! $hasNamedEquipItem) || ($childType === 'Misc' && $childSubType === 'Utility');

            return ! $isIgnoredType && ! $isIgnoredSubtype && (! empty($childType) || ! empty($childEquipItem));
        })
        ->values();

    $compatibleTypes = collect(data_get($port, 'compatible_types', []))->map(fn (array $ct) => $ct['type'])->filter();
    $browseType = $compatibleTypes->contains($row['type']) ? $row['type'] : $compatibleTypes->first();
    $browseType = $browseType ?: $row['type'];
    $browseSubType = $row['sub_type'] && $row['sub_type'] !== 'UNDEFINED' ? $row['sub_type'] : null;
    $canBrowse = ! $isAttachedVehicle && (! $isLocked || $isDeactivated) && $browseType !== null && $browseType !== '';
    $browseConfig = [];
    $browseFilters = [];

    if ($canBrowse) {
        $browseConfig = [
            'type' => $browseType,
            'subType' => $browseSubType,
            'sizeMin' => $row['size_min'],
            'sizeMax' => $row['size_max'],
            'requiredTags' => $row['required_tags'],
            'portTags' => $row['port_tags'],
            'vehiclePortTags' => $vehiclePortTags,
        ];

        $browseFilters = array_filter([
            'type' => $browseType,
            'sub_type' => $browseSubType,
            'name' => $browseType === 'FlightController' ? $vehicleName : null,
        ]);

        if ($row['size_min'] !== null && $row['size_max'] !== null) {
            $browseFilters['size'] = implode(',', range($row['size_min'], $row['size_max']));
        }

        if ($row['required_tags'] !== null) {
            $tags = is_array($row['required_tags']) ? $row['required_tags'] : [$row['required_tags']];
            $browseFilters['tags'] = count($tags) === 1 ? $tags[0] : $tags;
        } elseif ($row['port_tags'] !== null) {
            $browseFilters['port_tags'] = count($row['port_tags']) === 1 ? $row['port_tags'][0] : $row['port_tags'];
        } elseif (! empty($vehiclePortTags)) {
            $browseFilters['vehicle'] = count($vehiclePortTags) === 1 ? $vehiclePortTags[0] : implode(',', $vehiclePortTags);
        }
    }

    $hasSummary = $summaryText || $summaryPrimary || ! empty($summarySecondaries);
@endphp

<div class="port-entry space-y-2" data-testid="port-display">
    <div
        id="{{ $portIdentifier }}"
        data-testid="port-display-details"
        @if ($canBrowse)
            x-data="portEquippable(@js($browseConfig))"
            @click.stop="toggle($el)"
        @endif
        @class([
            'grid grid-cols-[auto_minmax(0,1fr)_auto] items-stretch overflow-hidden rounded-lg border-2 bg-base-200 border-base-200',
            'cursor-pointer hover:bg-base-300/50 transition-colors' => $canBrowse,
            'border-dashed border-base-300' => $isDeactivated,
        ])
    >
        <aside class="grid grid-flow-col auto-cols-max items-stretch divide-x divide-base-200 bg-base-100">
            @if ($isDeactivated)
                <div class="flex items-center px-2 py-2 text-warning" title="{{ $row['deactivation_reason'] }}">
                    <x-icon name="power-off" class="size-3" data-testid="port-display-deactivated" />
                </div>
            @endif

            <div class="flex items-center gap-1 px-2 py-2 font-medium">
                @if($leadingIcon)
                    <x-icon name="{{ $leadingIcon }}" class="size-3 shrink-0 {{ $leadingIconClass }}"/>
                @endif

                @if ($size !== null)
                    <span class="text-md" title="{{ $sizeTitle }}">S{{ $size }}</span>
                @endif

                @if ($fallbackSizeLabel !== null)
                    <span class="text-md" title="Equippable Size">{{ $fallbackSizeLabel }}</span>
                @endif
            </div>
        </aside>

        <div class="flex min-w-0 flex-col px-2 py-2">
            <span class="truncate" title="{{ $title }}">
                @if($url)
                    <a
                        data-testid="{{ $linkTestId }}"
                        href="{{ $url }}"
                        class="link-primary"
                        @click.stop
                    >{{ $label }}</a>
                @else
                    {{ $label }}
                @endif

                @if ($annotation)
                    <span class="text-xs font-normal text-subtle">({{ $annotation }})</span>
                @endif
            </span>

            @if ($subtext)
                <span class="truncate text-xs text-subtle">{{ $subtext }}</span>
            @elseif ($detailStats !== [])
                <div class="flex gap-2">
                    @foreach ($detailStats as $stat)
                        <span title="{{ $stat['title'] }}">
                            <x-icon name="{{ $stat['icon'] }}" class="inline" size="sm"/>
                            <span class="text-sm">{{ $stat['value'] }}</span>
                        </span>
                    @endforeach
                </div>
            @endif
        </div>

        @if ($hasSummary)
            <aside data-testid="port-display-summary" class="px-2 py-2 text-right">
                @if ($summaryText)
                    <div class="text-xs">{{ $summaryText }}</div>
                @endif

                @if ($summaryPrimary)
                    <div title="{{ $summaryPrimary['title'] }}">
                        <span class="font-medium">{{ $summaryPrimary['value'] }}</span> {{ $summaryPrimary['label'] }}
                    </div>
                @endif

                @if (! empty($summarySecondaries))
                    <div class="flex gap-2 text-xs">
                        @foreach ($summarySecondaries as $secondary)
                            <span>{{ $secondary }}</span>
                        @endforeach
                    </div>
                @endif
            </aside>
        @endif

        @if ($canBrowse)
            <x-port-browse-popup :type="$browseType" :browse-url="url()->query(route('web.items.index', ['filter' => $browseFilters]), array_filter(['version' => request()->query('version')]))"/>
        @endif
    </div>

    @if ($visibleChildPorts->isNotEmpty())
        <div id="{{ $portIdentifier }}-content" @class([
            'space-y-1 -mt-1',
            'pl-6' => $depth === 0,
            'pl-8' => $depth === 1,
            'pl-12' => $depth >= 2,
        ])>
            @foreach ($visibleChildPorts as $childPort)
                <x-port-display :port="$childPort" :depth="$depth + 1" :editable="data_get($childPort, 'editable', false)" :vehicle-name="$vehicleName" :vehicle-port-tags="$vehiclePortTags"/>
            @endforeach
        </div>
    @endif
</div>
