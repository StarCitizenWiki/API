@extends('layouts.app')

@php
    $pageTitleDecoded = html_entity_decode($pageTitle);

    $itemName = data_get($item, 'name', 'Item');
    $type = data_get($item, 'type');
    $manufacturerName = data_get($item, 'manufacturer.name');
    $classification = data_get($item, 'classification');
    $translations = data_get($item, 'description');
@endphp

@section('title')
    {!! $pageTitleDecoded !!} - Star Citizen Item
@endsection

@section('meta_description')
    {!! \Illuminate\Support\Str::limit(data_get($translations, 'en_EN') ?? $itemName . ' ' . ($type ?? ''), 160) !!}
@endsection

@section('meta')
    <meta name="keywords" content="{{ $itemName }},{{ $type ?? '' }},{{ $manufacturerName ?? '' }},{{ $classification ?? '' }},Star Citizen,SC">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $itemName }} - {{ $type ?? '' }} {{ $manufacturerName ?? '' }}">
    <meta property="og:description" content="{!! \Illuminate\Support\Str::limit(data_get($translations, 'en_EN') ?? $itemName . ' ' . ($type ?? ''), 160) !!}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $itemName }} - {{ $type ?? '' }}">
    <meta name="twitter:description" content="{!! \Illuminate\Support\Str::limit(data_get($translations, 'en_EN') ?? $itemName . ' ' . ($type ?? ''), 160) !!}">
@endsection



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

        $translations = data_get($item, 'description');

        $descriptionData = data_get($item, 'description_data', []);

        $ports = data_get($item, 'ports', []);
        $variants = data_get($item, 'variants', []);
        $baseVariant = data_get($item, 'related_items.base_item');
        $relatedVariants = data_get($item, 'related_items.variant_items', []);
        $setItems = data_get($item, 'related_items.set_items');
        $setName = data_get($item, 'related_items.set_name');

        $grade = data_get($item, 'grade');
        $class = data_get($item, 'class');
        $size = data_get($item, 'size');

        $gradeLetter = match ($grade) {
            1 => 'A',
            2 => 'B',
            3 => 'C',
            4 => 'D',
            default => $grade,
        };

        if (! is_array($variants) || $variants === []) {
            $variants = is_array($relatedVariants) ? $relatedVariants : [];
        }

        $uuid = data_get($item, 'uuid');
        $apiLink = data_get($item, 'link');
        $version = data_get($item, 'version');
        $rawItemJson = json_encode($item, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $specKeys = [
            'ammunition' => 'Ammunition',
            'armor' => 'Armor',
            'barrel_attach' => 'Barrel Attachment',
            'bomb' => 'Bomb',
            'character_armor' => 'Character Armor',
            'clothing' => 'Clothing',
            'cooler' => 'Cooler',
            'distortion' => 'Distortion',
            'durability' => 'Durability',
            'emission' => 'Emission',
            'emp' => 'EMP',
            'flight_controller' => 'Flight Controller',
            'food' => 'Food',
            'fuel_tank' => 'Fuel Tank',
            'grenade' => 'Grenade',
            'hacking_chip' => 'Hacking Chip',
            'heat' => 'Heat',
            'inventory' => 'Inventory',
            'iron_sight' => 'Iron Sight',
            'knife' => 'Knife',
            'medical' => 'Medical',
            'melee_weapon' => 'Melee Weapon',
            'mining_laser' => 'Mining Laser',
            'mining_module' => 'Mining Module',
            'missile' => 'Missile',
            'personal_weapon' => 'Personal Weapon',
            'power' => 'Power',
            'quantum_drive' => 'Quantum Drive',
            'quantum_interdiction_generator' => 'Quantum Interdiction Generator',
            'radiation_resistance' => 'Radiation Resistance',
            'resource_container' => 'Resource Container',
            'resource_network' => 'Resource Network',
            'salvage_modifier' => 'Salvage Modifier',
            'seat' => 'Seat',
            'self_destruct' => 'Self Destruct',
            'shield' => 'Shield',
            'suit_armor' => 'Suit Armor',
            'temperature' => 'Temperature',
            'temperature_resistance' => 'Temperature Resistance',
            'thruster' => 'Thruster',
            'weapon_modifier' => 'Weapon Modifier',
        ];
    @endphp

    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-3">
            <x-items.item-breadcrumbs :item="$item" />
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-semibold tracking-tight">{{ $itemName }} <span class="text-secondary">({{ $type }})</span></h1>
                @if ($gradeLetter || $class || $size !== null)
                    <div class="flex items-center gap-2 text-sm text-base-content/70">
                        @if ($gradeLetter)
                            <span class="badge badge-ghost">Grade {{ $gradeLetter }}</span>
                        @endif
                        @if ($class)
                            <span class="badge badge-ghost">{{ $class }}</span>
                        @endif
                        @if ($size !== null)
                            <span class="badge badge-ghost">Size {{ $size }}</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <x-resource-search
            title="Search items"
            description="Find items by name across the universe database."
            :route="route('web.items.index')"
            placeholder="Search item names"
        />

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="card border border-base-200 bg-base-100 shadow-sm">
                <div class="card-body gap-4">
                    <h2 class="card-title text-base">Item</h2>
                    <dl class="grid gap-4 sm:grid-cols">
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Name</dt>
                            <dd class="text-sm font-medium">{{ $itemName }}</dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Class Name</dt>
                            <dd class="text-sm font-medium">{{ $className ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            @if ((is_array($setItems) && !empty($setItems)) || (is_array($variants) && !empty($variants)))
                <div class="card border border-base-200 bg-base-100 shadow-sm">
                    <div class="card-body gap-4">
                        <h2 class="card-title text-base">Related Items</h2>

                        @if (is_array($setItems) && $setItems !== [])
                            <div class="space-y-2">
                                <h3 class="text-sm font-semibold">Set Items: {{ $setName ?? 'Unknown Set' }}</h3>
                                <div class="overflow-x-auto">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Slot</th>
                                                <th>Link</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($setItems as $setItem)
                                                <tr>
                                                    <td class="whitespace-nowrap">{{ $setItem['name'] ?? '-' }}</td>
                                                    <td>{{ array_last(explode('.', $setItem['classification'] ?? '')) ?? '-' }}</td>
                                                    <td>
                                                        @if (! empty($setItem['uuid']))
                                                            <a href="{{ route('web.items.show', $setItem['uuid']) }}" class="link link-primary">View</a>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class="text-sm text-base-content/70">No Set Items available.</div>
                        @endif

                        @if (is_array($variants) && $variants !== [])
                            <div class="space-y-2 mt-4">
                                <h3 class="text-sm font-semibold">Variants</h3>
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
                            </div>
                        @else
                            <div class="text-sm text-base-content/70">No variants available.</div>
                        @endif
                    </div>
                </div>
            @endif

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
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Classification</dt>
                            <dd class="text-sm font-medium">{{ $classification ?? '-' }}</dd>
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
                            @if (!empty($translations))
                                <div class="space-y-3">
                                    @foreach ($translations as $locale => $translation)
                                        @php
                                            $label = is_string($locale) ? \App\Models\System\Language::LABEL_MAP[$locale] : 'Translation '.$loop->iteration;
                                            $translationText = is_string($translation)
                                                ? $translation
                                                : json_encode($translation, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                                        @endphp
                                        <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                                            <input type="checkbox" name="collapse-{{ $locale }}" @if($locale === 'en_EN') checked="checked" @endif />
                                            <div class="collapse-title text-sm font-semibold">{{ $label }}</div>
                                            <div class="collapse-content">
                                                @if ($translationText)
                                                    <div class="text-sm leading-relaxed text-base-content/80">
                                                        {!! nl2br(e($translationText)) !!}
                                                    </div>
                                                    @if(in_array(\App\Models\System\Language::LABEL_MAP[$locale], ['German', 'Chinese'], true))
                                                        <div class="text-xs text-base-content/70 mt-4">
                                                            {{ \App\Models\System\Language::LABEL_MAP[$locale] ?? $locale }} translation from
                                                            <a class="link"
                                                               href="{{ config("translations.sources_git.$locale") }}"
                                                               target="_blank" rel="noopener noreferrer nofollow"
                                                               referrerpolicy="no-referrer">{{ config("translations.sources_git.$locale") }}</a>
                                                        </div>
                                                    @endif
                                                @else
                                                    <div class="text-sm text-base-content/70">No content available.</div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-sm text-base-content/70">No translations available.</div>
                            @endif
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


        @if (
            $type === 'WeaponPersonal' ||
            str_starts_with($classification, 'FPS.Armor') ||
            str_starts_with($classification, 'FPS.Clothing') ||
            in_array($type, ['Food', 'Bottle', 'Drink'], true) ||
            $type === 'WeaponAttachment' ||
            data_get($item, 'weapon_modifier') ||
            data_get($item, 'temperature_resistance') ||
            data_get($item, 'radiation_resistance')
        )
            <div class="mt-4 flex items-center gap-2 border-b border-base-200 pb-2">
                <x-icon name="contact-round" class="size-5 text-primary" />
                <h3 class="text-lg font-semibold">FPS Items Specifications</h3>
            </div>
        @endif

        @if (
            $type === 'WeaponGun' ||
            $type === 'Armor' ||
            $type === 'Shield' ||
            $type === 'ShieldController' ||
            $type === 'Cooler' ||
            $type === 'QuantumDrive' ||
            $type === 'JumpDrive' ||
            $type === 'FlightController' ||
            $type === 'Radar' ||
            $type === 'Turret' ||
            $type === 'WeaponDefensive' ||
            $type === 'MissileLauncher' ||
            $type === 'Bomb' ||
            $type === 'Missile' ||
            $type === 'EMP' ||
            $type === 'QuantumInterdictionGenerator' ||
            $type === 'WeaponMining' ||
            $type === 'MiningModifier' ||
            data_get($item, 'tractor_beam') ||
            data_get($item, 'self_destruct') ||
            data_get($item, 'seat') ||
            data_get($item, 'thruster') ||
            data_get($item, 'fuel_tank') ||
            data_get($item, 'fuel_intake')
        )
            <div class="mt-4 flex items-center gap-2 border-b border-base-200 pb-2">
                <x-icon name="cpu" class="size-5 text-primary" />
                <h3 class="text-lg font-semibold">Vehicle Items Specifications</h3>
            </div>
        @endif


        @if ($type === 'WeaponPersonal')
            <x-items.personal-weapon-card :personal-weapon="data_get($item, 'personal_weapon')" />
        @endif

        @if ($type === 'Armor')
            <x-items.armor-card :armor="data_get($item, 'armor')" />
        @endif

        @if ($type === 'WeaponGun')
            <x-items.vehicle-weapon-card :vehicle-weapon="data_get($item, 'vehicle_weapon')" />
        @endif

        @if ($type === 'WeaponAttachment')
            <x-items.weapon-attachment-card :weapon-attachment="$item" />
        @endif


        @if (data_get($item, 'shield'))
            <x-items.shield-card :shield="data_get($item, 'shield')" />
        @endif

        @if (data_get($item, 'power_plant'))
            <x-items.power-plant-card :power-plant="data_get($item, 'power_plant')" />
        @endif

        @if (data_get($item, 'quantum_drive'))
            <x-items.quantum-drive-card :quantum-drive="data_get($item, 'quantum_drive')" />
        @endif

        @if (data_get($item, 'cooler'))
            <x-items.cooler-card :cooler="data_get($item, 'cooler')" />
        @endif

        @if (data_get($item, 'jump_drive'))
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

        @if (data_get($item, 'emission'))
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

        @if (data_get($item, 'tractor_beam'))
            <x-items.tractor-beam-card :tractor-beam="data_get($item, 'tractor_beam')" />
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

        @if (data_get($item, 'cargo_grid'))
            <x-items.cargo-grid-card :cargo-grid="data_get($item, 'cargo_grid')" />
        @endif

        @if ($type === 'Missile')
            <x-items.missile-card :missile="data_get($item, 'missile')" />
        @endif

        @if (data_get($item, 'suit_armor'))
            <x-items.suit-armor-card :suit-armor="data_get($item, 'suit_armor')" />
        @endif

        @if (data_get($item, 'temperature_resistance'))
            <x-items.temperature-resistance-card :temperature-resistance="data_get($item, 'temperature_resistance')" />
        @endif

        @if (data_get($item, 'radiation_resistance'))
            <x-items.radiation-resistance-card :radiation-resistance="data_get($item, 'radiation_resistance')" />
        @endif



        @if (
            data_get($item, 'ammunition') ||
            data_get($item, 'weapon_modifier')
        )
            <div class="mt-4 flex items-center gap-2 border-b border-base-200 pb-2">
                <x-icon name="circle-small" class="size-5 text-primary" />
                <h3 class="text-lg font-semibold">General Items Specifications</h3>
            </div>
        @endif


        @if (data_get($item, 'ammunition'))
            <x-items.ammunition-card :ammunition="data_get($item, 'ammunition')" />
        @endif


        @if (data_get($item, 'weapon_modifier'))
            <x-items.weapon-modifier-card :weapon-modifier="data_get($item, 'weapon_modifier')" />
        @endif

        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body gap-4">
                <h2 class="card-title text-base">Technical & Specifications</h2>

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
                    <div class="space-y-2">
                        <h3 class="text-sm font-semibold">Specifications</h3>
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
                    </div>
                @endif

                <div class="space-y-2 mt-4">
                    <h3 class="text-sm font-semibold">Technical</h3>
                    <dl class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">UUID</dt>
                            <dd class="text-sm font-medium">{{ $uuid ?? '-' }}</dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Version Code</dt>
                            <dd class="text-sm font-medium">{{ $version ?? '-' }}</dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">API Link</dt>
                            <dd class="text-sm font-medium">
                                @if ($apiLink)
                                    <a href="{{ $apiLink }}" class="link link-primary">{{ $apiLink }}</a>
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
