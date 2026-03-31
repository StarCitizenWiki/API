@php
    use Illuminate\Support\Arr;
    use Illuminate\Support\Str;
@endphp

@extends('layouts.app')

@props(['item', 'pageTitle'])

@php
    $pageTitleDecoded = html_entity_decode($pageTitle);

    $itemName = data_get($item, 'name', 'Item');
    $type = data_get($item, 'type');
    $manufacturerName = data_get($item, 'manufacturer.name');
    $classification = data_get($item, 'classification');
    $translations = data_get($item, 'description');
    $descriptionPreview = is_array($translations)
        ? Arr::first($translations, static fn (mixed $value): bool => is_string($value) && trim($value) !== '')
        : $translations;

    $descriptionPreview = is_string($descriptionPreview)
        ? trim(html_entity_decode($descriptionPreview))
        : null;

    if ($descriptionPreview === '') {
        $descriptionPreview = null;
    }
@endphp

@section('title')
    {!! $pageTitleDecoded !!} - Star Citizen Item
@endsection

@section('meta_description')
    {!! Str::limit($descriptionPreview ?? $itemName.' '.($type ?? ''), 160) !!}
@endsection

@section('meta')
    <meta name="keywords" content="{{ $itemName }},{{ $type ?? '' }},{{ $manufacturerName ?? '' }},{{ $classification ?? '' }},Star Citizen,SC">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $itemName }} - {{ $type ?? '' }} {{ $manufacturerName ?? '' }}">
    <meta property="og:description" content="{!! Str::limit($descriptionPreview ?? $itemName.' '.($type ?? ''), 160) !!}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $itemName }} - {{ $type ?? '' }}">
    <meta name="twitter:description" content="{!! Str::limit($descriptionPreview ?? $itemName.' '.($type ?? ''), 160) !!}">
@endsection

@section('content')
    @php
        $className = data_get($item, 'class_name');
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
        ) && ! str_starts_with($classification, 'Ship.');

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
        $baseVariantCount = (! empty($baseVariant) && (! $isSelfReferential || $variantCount > 0)) ? 1 : 0;
        $relatedItemsCount = $setItemCount + $variantCount + $baseVariantCount;
        $portsCount = is_array($ports) ? count($ports) : 0;
        $uexPricesCount = is_array($uexPrices) ? count($uexPrices) : 0;

        $hasDescriptionCard = (is_array($translations) && $translations !== []) || (is_string($translations) && trim($translations) !== '');
        $hasDescriptionDataCard = is_array($descriptionData) && $descriptionData !== [];
        $hasRelatedItemsCard = $relatedItemsCount > 0;
        $hasSpecificationSection = $portsCount > 0 || $fpsSpecsAvailable || $vehicleSpecsAvailable;
    @endphp

    <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-2">
            <x-items.item-breadcrumbs :item="$item" />
        </div>

        <x-resource-search
            title="Search items"
            description="Find items by name across the universe database."
            :route="route('web.items.index')"
            placeholder="Search item names"
            variant="minimal"
        />

        <div class="mx-auto grid w-full gap-4 xl:grid-cols-12 xl:items-stretch">
            <x-items.hero :item="$item" class="xl:col-span-7" />
            <x-items.quick-facts-card
                :item="$item"
                :ports-count="$portsCount"
                :related-items-count="$relatedItemsCount"
                :uex-prices-count="$uexPricesCount"
                class="xl:col-span-5"
            />
        </div>

        <div class="flex flex-col gap-8">
            <section class="space-y-4">
                <h2 class="text-lg font-semibold tracking-tight">Details & Availability</h2>

                @if ($hasDescriptionDataCard || $hasRelatedItemsCard)
                    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                        @if ($hasDescriptionDataCard)
                            <x-items.description-data-card
                                :description-data="$descriptionData"
                            />
                        @endif

                        @if ($hasRelatedItemsCard)
                            <x-items.related-items-card
                                :set-items="$setItems"
                                :set-name="$setName"
                                :variants="$variants"
                                :base-variant="$baseVariant"
                                :current-item-uuid="$uuid"
                            />
                        @endif
                    </div>
                @endif

                @if ($hasDescriptionCard)
                    <x-items.description-card
                        :translations="$translations"
                        class="w-full"
                    />
                @endif

                @if ($uexPricesCount > 0)
                    <x-items.uex-prices-card :prices="$uexPrices" class="w-full" />
                @endif
            </section>

            @if ($hasSpecificationSection)
                <section class="space-y-4">
                    <h2 class="text-lg font-semibold tracking-tight">Specifications & Integration</h2>

                    <div class="flex flex-col gap-4">
                        @if ($portsCount > 0)
                            <x-items.ports-card :ports="$ports" class="w-full" />
                        @endif

                        @if ($fpsSpecsAvailable)
                            <x-items.fps-data-card :item="$item" :type="$type" class="w-full" />
                        @endif

                        @if ($vehicleSpecsAvailable)
                            <x-items.vehicle-data-card :item="$item" :type="$type" class="w-full" />
                        @endif
                    </div>
                </section>
            @endif

            <section class="space-y-4">
                <h2 class="text-lg font-semibold tracking-tight">Technical</h2>

                <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                    <x-items.technical-card
                        :uuid="$uuid"
                        :classification="$classification"
                        :class-name="$className"
                        :version="$version"
                        :api-link="$apiLink"
                        :entity-tag-map="$entityTagMap"
                    />

                    <x-items.raw-payload-card :raw-data="$rawItemJson" />
                </div>
            </section>
        </div>
    </div>
@endsection
