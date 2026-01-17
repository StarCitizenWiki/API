@extends('layouts.app')

@php
    $pageTitleDecoded = html_entity_decode(html_entity_decode($pageTitle));
@endphp

@section('title')
    {!! $pageTitleDecoded !!} - Star Citizen Item
@endsection
@section('meta_description', "{$pageTitle} item details.")

@section('content')
    @php
        $itemName = data_get($item, 'name', 'Item');
        $className = data_get($item, 'class_name');
        $classification = data_get($item, 'classification');

        $manufacturerName = data_get($item, 'manufacturer.name');
        $manufacturerCode = data_get($item, 'manufacturer.code');

        $type = data_get($item, 'type');
        $subType = data_get($item, 'sub_type');
        $size = data_get($item, 'size');
        $mass = data_get($item, 'mass');

        $dimension = data_get($item, 'dimension', []);
        $length = data_get($dimension, 'length');
        $width = data_get($dimension, 'width');
        $height = data_get($dimension, 'height');
        $volume = data_get($dimension, 'volume_converted', data_get($dimension, 'volume'));
        $volumeUnit = data_get($dimension, 'volume_converted_unit');

        $entityTagMap = data_get($item, 'entity_tag_map', []);

        $description = data_get($item, 'description');
        $descriptionText = is_array($description)
            ? (data_get($description, 'en_EN') ?? collect($description)->first())
            : $description;
        $descriptionData = data_get($item, 'description_data', []);

        $ports = data_get($item, 'ports', []);
        $variants = data_get($item, 'variants', []);
        $baseVariant = data_get($item, 'related_items.base_item');
        $relatedVariants = data_get($item, 'related_items.variant_items', []);
        if (! is_array($variants) || $variants === []) {
            $variants = is_array($relatedVariants) ? $relatedVariants : [];
        }

        $uuid = data_get($item, 'uuid');
        $apiLink = data_get($item, 'link');
        $version = data_get($item, 'version');
        $rawItemJson = json_encode($item, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $filter = ['type' => $type];
        $filterKey = $type;

        if (str_starts_with($classification, 'FPS.Clothing')) {
            $filter = ['category' => 'clothes'];
            $filterKey = 'Clothes';
        } elseif (str_starts_with($classification, 'FPS.Armor')) {
            $filter = ['category' => 'armor'];
            $filterKey = 'Armor';
        }

        $specKeys = [
            'inventory' => 'Inventory',
            'heat' => 'Heat',
            'power' => 'Power',
            'distortion' => 'Distortion',
            'durability' => 'Durability',
            'resource_container' => 'Resource Container',
            'resource_network' => 'Resource Network',
            'emission' => 'Emission',
            'temperature' => 'Temperature',
            'seat' => 'Seat',
            'ammunition' => 'Ammunition',
            'temperature_resistance' => 'Temperature Resistance',
            'radiation_resistance' => 'Radiation Resistance',
            'armor' => 'Armor',
            'cooler' => 'Cooler',
            'flight_controller' => 'Flight Controller',
            'fuel_tank' => 'Fuel Tank',
            'hacking_chip' => 'Hacking Chip',
            'mining_laser' => 'Mining Laser',
            'mining_module' => 'Mining Module',
            'quantum_drive' => 'Quantum Drive',
            'quantum_interdiction_generator' => 'Quantum Interdiction Generator',
            'self_destruct' => 'Self Destruct',
            'shield' => 'Shield',
            'thruster' => 'Thruster',
            'clothing' => 'Clothing',
            'character_armor' => 'Character Armor',
            'suit_armor' => 'Suit Armor',
            'bomb' => 'Bomb',
            'missile' => 'Missile',
            'emp' => 'EMP',
            'personal_weapon' => 'Personal Weapon',
            'melee_weapon' => 'Melee Weapon',
            'grenade' => 'Grenade',
            'knife' => 'Knife',
            'barrel_attach' => 'Barrel Attachment',
            'weapon_modifier' => 'Weapon Modifier',
            'iron_sight' => 'Iron Sight',
            'salvage_modifier' => 'Salvage Modifier',
        ];
    @endphp

    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-2">
            <div class="breadcrumbs text-sm text-base-content/70">
                <ul>
                    <li><a href="{{ route('web.items.index') }}">All Items</a></li>
                    <li><a href="{{ route('web.items.index', ['filter' => $filter]) }}">{{ $filterKey }}</a></li>
                    @if (isset($filter['category']))
                        <li><a href="{{ route('web.items.index', ['filter' => ['type' => $type]]) }}">{{ $type }}</a></li>
                    @endif
                    <li>{{ $itemName }}</li>
                </ul>
            </div>
            <h1 class="text-2xl font-semibold tracking-tight">{{ $itemName }} <span class="text-secondary">({{ $type }})</span> </h1>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="card border border-base-200 bg-base-100 shadow-sm">
                <div class="card-body gap-4">
                    <h2 class="card-title text-base">Title</h2>
                    <dl class="grid gap-4 sm:grid-cols">
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Name</dt>
                            <dd class="text-sm font-medium">{{ $itemName }}</dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Class Name</dt>
                            <dd class="text-sm font-medium">{{ $className ?? '-' }}</dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Classification</dt>
                            <dd class="text-sm font-medium">{{ $classification ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="card border border-base-200 bg-base-100 shadow-sm">
                <div class="card-body gap-4">
                    <h2 class="card-title text-base">General</h2>
                    <dl class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Manufacturer</dt>
                            <dd class="text-sm font-medium">
                                @if ($manufacturerName)
                                    {{ $manufacturerName }}
                                    @if ($manufacturerCode)
                                        <span class="badge badge-outline ml-2">{{ $manufacturerCode }}</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Type + SubType</dt>
                            <dd class="text-sm font-medium">
                                {{ $type ?? '-' }}@if ($subType) / {{ $subType }}@endif
                            </dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Size</dt>
                            <dd class="text-sm font-medium">{{ $size ?? '-' }}</dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Mass</dt>
                            <dd class="text-sm font-medium">{{ $mass ?? '-' }}kg</dd>
                        </div>
                        <div class="space-y-1 sm:col-span-2">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Dimensions</dt>
                            <dd class="text-sm font-medium">
                                @if ($length || $width || $height)
                                    {{ $length ?? '-' }} x {{ $width ?? '-' }} x {{ $height ?? '-' }} m
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Volume</dt>
                            <dd class="text-sm font-medium">
                                @if ($volume !== null)
                                    {{ $volume }}@if ($volumeUnit) {{ $volumeUnit }}@endif
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                        <div class="space-y-1 sm:col-span-2">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Entity Tag Map</dt>
                            <dd class="text-sm font-medium">
                                @if (is_array($entityTagMap) && $entityTagMap !== [])
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($entityTagMap as $tag)
                                            <span class="badge badge-neutral" title="{{ $tag['uuid'] ?? '' }}">
                                                {{ $tag['name'] ?? 'Unknown' }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="card border border-base-200 bg-base-100 shadow-sm">
                <div class="card-body gap-4">
                    <h2 class="card-title text-base">Description</h2>
                    <div class="space-y-2">
                        <div class="text-sm text-base-content/80">
                            <span class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Description</span>
                            <div class="mt-1 whitespace-pre-line">{{ $descriptionText ?? '-' }}</div>
                        </div>
                        <div class="text-sm text-base-content/80">
                            <span class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Description Data</span>
                            @if (is_array($descriptionData) && $descriptionData !== [])
                                <div class="overflow-x-auto">
                                    <table class="table table-sm">
                                        <thead>
                                        <tr>
                                            <th>Key</th>
                                            <th>Value</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($descriptionData as $datum)
                                            <tr>
                                                <td class="whitespace-nowrap">{{ $datum['name'] ?? '-' }}</td>
                                                <td>{{ $datum['value'] ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="mt-1">-</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border border-base-200 bg-base-100 shadow-sm">
                <div class="card-body gap-4">
                    <h2 class="card-title text-base">Ports</h2>
                    @if (is_array($ports) && $ports !== [])
                        <div class="space-y-3">
                            @foreach ($ports as $port)
                                <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                                    <input type="checkbox" />
                                    <div class="collapse-title text-sm font-semibold">
                                        {{ $port['display_name'] ?? $port['name'] ?? 'Port' }}
                                        @if (! empty($port['position']))
                                            <span class="ml-2 text-xs font-normal text-base-content/60">{{ $port['position'] }}</span>
                                        @endif
                                    </div>
                                    <div class="collapse-content">
                                        <dl class="grid gap-3 sm:grid-cols-2">
                                            <div class="space-y-1">
                                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Name</dt>
                                                <dd class="text-sm">{{ $port['name'] ?? '-' }}</dd>
                                            </div>
                                            <div class="space-y-1">
                                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Size</dt>
                                                <dd class="text-sm">
                                                    @if (isset($port['size']))
                                                        {{ $port['size'] }}
                                                    @elseif (isset($port['sizes']))
                                                        {{ $port['sizes']['min'] ?? '-' }} - {{ $port['sizes']['max'] ?? '-' }}
                                                    @else
                                                        -
                                                    @endif
                                                </dd>
                                            </div>
                                            <div class="space-y-1 sm:col-span-2">
                                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Types</dt>
                                                <dd class="text-sm">
                                                    @if (! empty($port['types']))
                                                        <div class="flex flex-wrap gap-2">
                                                            @foreach ($port['types'] as $typeEntry)
                                                                <span class="badge badge-outline">{{ $typeEntry }}</span>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        -
                                                    @endif
                                                </dd>
                                            </div>
                                            <div class="space-y-1 sm:col-span-2">
                                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Tags</dt>
                                                <dd class="text-sm">
                                                    @if (! empty($port['tags']))
                                                        <div class="flex flex-wrap gap-2">
                                                            @foreach ($port['tags'] as $tagEntry)
                                                                <span class="badge badge-neutral">{{ $tagEntry }}</span>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        -
                                                    @endif
                                                </dd>
                                            </div>
                                            <div class="space-y-1 sm:col-span-2">
                                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Required Tags</dt>
                                                <dd class="text-sm">
                                                    @if (! empty($port['required_tags']))
                                                        <div class="flex flex-wrap gap-2">
                                                            @foreach ($port['required_tags'] as $tagEntry)
                                                                <span class="badge badge-outline">{{ $tagEntry }}</span>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        -
                                                    @endif
                                                </dd>
                                            </div>
                                            <div class="space-y-1 sm:col-span-2">
                                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Equipped Item</dt>
                                                <dd class="text-sm">
                                                    @if (! empty($port['equipped_item']))
                                                        <div class="flex items-center gap-2">
                                                            <span>{{ $port['equipped_item']['name'] ?? '-' }}</span>
                                                            @if (! empty($port['equipped_item']['uuid']))
                                                                <a href="{{ route('web.items.show', $port['equipped_item']['uuid']) }}" class="link link-primary">View</a>
                                                            @endif
                                                        </div>
                                                    @else
                                                        -
                                                    @endif
                                                </dd>
                                            </div>
                                            <div class="space-y-1">
                                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Flags</dt>
                                                <dd class="text-sm">
                                                    @if (! empty($port['flags']))
                                                        {{ implode(', ', $port['flags']) }}
                                                    @else
                                                        -
                                                    @endif
                                                </dd>
                                            </div>
                                            <div class="space-y-1">
                                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Locked</dt>
                                                <dd class="text-sm">{{ $port['uneditable'] ? 'Yes' : 'No' }}</dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-sm text-base-content/70">No ports available.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body gap-4">
                <h2 class="card-title text-base">Specifications</h2>
                @php
                    $specEntries = [];
                    foreach ($specKeys as $key => $label) {
                        $value = data_get($item, $key);
                        if ($value !== null && $value !== [] && $value !== '') {
                            $specEntries[$key] = [
                                'label' => $label,
                                'value' => $value,
                                'json' => json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                            ];
                        }
                    }
                @endphp
                @if ($specEntries !== [])
                    <div class="grid gap-3 md:grid-cols-2">
                        @foreach ($specEntries as $entry)
                            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                                <input type="checkbox" />
                                <div class="collapse-title text-sm font-semibold">{{ $entry['label'] }}</div>
                                <div class="collapse-content">
                                    <pre class="text-xs whitespace-pre-wrap">{{ $entry['json'] }}</pre>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-sm text-base-content/70">No specifications available.</div>
                @endif
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="card border border-base-200 bg-base-100 shadow-sm">
                <div class="card-body gap-4">
                    <h2 class="card-title text-base">Variants</h2>
                    @if (is_array($variants) && $variants !== [])
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Variant</th>
                                    <th>Link</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if (! empty($baseVariant))
                                    <tr>
                                        <td class="whitespace-nowrap">{{ $baseVariant['name'] ?? '-' }}</td>
                                        <td>Base Item</td>
                                        <td>
                                            @if (! empty($baseVariant['uuid']))
                                                <a href="{{ route('web.items.show', $baseVariant['uuid']) }}" class="link link-primary">View</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                                @foreach ($variants as $variant)
                                    <tr>
                                        <td class="whitespace-nowrap">{{ $variant['name'] ?? '-' }}</td>
                                        <td>{{ $variant['variant_name'] ?? $variant['sub_type'] ?? $variant['type'] ?? '-' }}</td>
                                        <td>
                                            @if (! empty($variant['uuid']))
                                                <a href="{{ route('web.items.show', $variant['uuid']) }}" class="link link-primary">View</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-sm text-base-content/70">No variants available.</div>
                    @endif
                </div>
            </div>

            <div class="card border border-base-200 bg-base-100 shadow-sm">
                <div class="card-body gap-4">
                    <h2 class="card-title text-base">Technical</h2>
                    <dl class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">UUID</dt>
                            <dd class="text-sm font-medium">{{ $uuid ?? '-' }}</dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Version Code</dt>
                            <dd class="text-sm font-medium">{{ $version ?? '-' }}</dd>
                        </div>
                        <div class="space-y-1 sm:col-span-2">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">API Link</dt>
                            <dd class="text-sm font-medium">
                                @if ($apiLink)
                                    <a href="{{ $apiLink }}" class="link link-primary">{{ $apiLink }}</a>
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body gap-4">
                <h2 class="card-title text-base">All Data</h2>
                <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                    <input type="checkbox" />
                    <div class="collapse-title text-sm font-semibold">Raw Item Payload</div>
                    <div class="collapse-content">
                        <pre class="text-xs whitespace-pre-wrap">{{ $rawItemJson }}</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
