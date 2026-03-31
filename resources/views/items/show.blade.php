@extends('layouts.app')

@props(['item', 'pageTitle'])

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
        $type = data_get($item, 'type');

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

        $uuid = data_get($item, 'uuid');
        $apiLink = data_get($item, 'link');
        $version = data_get($item, 'version');
        $rawItemJson = json_encode($item, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $ports = data_get($item, 'ports', []);
        $variants = data_get($item, 'variants', []);
        $baseVariant = data_get($item, 'related_items.base_item');
        $relatedVariants = data_get($item, 'related_items.variant_items', []);
        $setItems = data_get($item, 'related_items.set_items');
        $setName = data_get($item, 'related_items.set_name');

        if (! is_array($variants) || $variants === []) {
            $variants = is_array($relatedVariants) ? $relatedVariants : [];
        }

        $uexPrices = data_get($item, 'uex_prices', []);
        $descriptionData = data_get($item, 'description_data', []);
        $entityTagMap = data_get($item, 'entity_tag_map', []);

        $fpsSpecsAvailable = (
            $type === 'WeaponPersonal' ||
            str_starts_with($classification, 'FPS.Armor') ||
            str_starts_with($classification, 'FPS.Clothing') ||
            in_array($type, ['Food', 'Bottle', 'Drink'], true) ||
            $type === 'WeaponAttachment' ||
            data_get($item, 'weapon_modifier') ||
            data_get($item, 'temperature_resistance') ||
            data_get($item, 'radiation_resistance') ||
            (data_get($item, 'inventory') && data_get($item, 'inventory.unit') === 'µSCU')
        ) && !str_starts_with($classification, 'Ship.');

        $vehicleSpecsAvailable = (
            $type === 'WeaponGun' ||
            $type === 'Armor' ||
            $type === 'Shield' ||
            $type === 'ShieldController' ||
            $type === 'Cooler' ||
            $type === 'QuantumDrive' ||
            $type === 'PowerPlant' ||
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
            data_get($item, 'fuel_intake') ||
            data_get($item, 'mining_modifier') ||
            data_get($item, 'cargo_grid')
        );

        $setItemCount = is_array($setItems) ? count($setItems) : 0;
        $variantCount = is_array($variants) ? count($variants) : 0;
        $baseVariantUuid = data_get($baseVariant, 'uuid');
        $isSelfReferential = $baseVariantUuid === $uuid;
        $baseVariantCount = (!empty($baseVariant) && (!$isSelfReferential || $variantCount > 0)) ? 1 : 0;
        $relatedItemsCount = $setItemCount + $variantCount + $baseVariantCount;
        $portsCount = is_array($ports) ? count($ports) : 0;
        $uexPricesCount = is_array($uexPrices) ? count($uexPrices) : 0;
    @endphp

    <div class="flex flex-col gap-3">
        <div class="flex flex-col gap-2 sm:gap-3">
            <x-items.item-breadcrumbs :item="$item" />
            <x-items.summary-card
                :item="$item"
                :item-name="$itemName"
                :item-type="$type"
                :item-classification="$classification"
                :manufacturer-name="$manufacturerName"
                :grade-letter="$gradeLetter"
                :item-class="$class"
                :item-size="$size"
                :ports-count="$portsCount"
                :related-items-count="$relatedItemsCount"
                :uex-prices-count="$uexPricesCount"
                :version="$version"
            />
        </div>

        <x-resource-search
            title="Search items"
            description="Find items by name across the universe database."
            :route="route('web.items.index')"
            placeholder="Search item names"
            variant="minimal"
        />

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-2 gap-3">
            <x-items.overview-card :item="$item" class="col-span-full" />

            @if($relatedItemsCount > 0)
                <x-items.related-items-card
                    :set-items="$setItems"
                    :set-name="$setName"
                    :variants="$variants"
                    :base-variant="$baseVariant"
                    :current-item-uuid="$uuid"
                />
            @endif

            @if($uexPricesCount > 0)
                <x-items.uex-prices-card :prices="$uexPrices" />
            @endif

            <x-items.description-card
                :translations="$translations"
                :description-data="$descriptionData"
                class="col-span-full"
            />

            @if($portsCount > 0)
                <x-items.ports-card :ports="$ports" class="col-span-full" />
            @endif

            @if($fpsSpecsAvailable)
                <x-items.fps-data-card :item="$item" :type="$type" class="col-span-full" />
            @endif

            @if($vehicleSpecsAvailable)
                <x-items.vehicle-data-card :item="$item" :type="$type" class="col-span-full" />
            @endif

            <x-items.technical-card
                :uuid="$uuid"
                :classification="$classification"
                :class-name="$className"
                :version="$version"
                :api-link="$apiLink"
                :entity-tag-map="$entityTagMap"
                class="md:col-span-2"
            />

            <x-items.raw-payload-card :raw-data="$rawItemJson" class="col-span-full" />
        </div>
    </div>
@endsection
