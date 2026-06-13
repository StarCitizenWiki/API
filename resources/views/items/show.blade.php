@php
    use Illuminate\Support\Str;
@endphp

@extends('layouts.app')

@props(['item', 'pageTitle', 'seo'])

@php
    $itemName = data_get($item, 'name', 'Item');
    $type = data_get($item, 'type');
    $classification = data_get($item, 'classification');
    $translations = data_get($item, 'description');
    $breadcrumbs = data_get($seo, 'breadcrumbs', []);
@endphp

@section('title')
    {!! data_get($seo, 'title', $itemName.' - Star Citizen Item') !!}
@endsection

@section('meta_description')
    {!! data_get($seo, 'metaDescription', Str::limit($itemName.' '.($type ?? ''), 160)) !!}
@endsection

@section('meta')
    <x-seo.metadata
        :canonical="data_get($seo, 'canonicalUrl')"
        :keywords="data_get($seo, 'keywords', [])"
        :og-title="data_get($seo, 'ogTitle')"
        :og-description="data_get($seo, 'ogDescription')"
        :twitter-title="data_get($seo, 'twitterTitle')"
        :twitter-description="data_get($seo, 'twitterDescription')"
        :structured-data="data_get($seo, 'structuredData', [])"
    />
@endsection

@section('content')
    @php
        $className = data_get($item, 'class_name');
        $uuid = data_get($item, 'uuid');
        $apiLink = data_get($item, 'link');
        $version = data_get($item, 'version');

        $ports = data_get($item, 'ports', []);
        $baseVariant = data_get($item, 'related_items.base_item');
        $variants = data_get($item, 'related_items.variant_items', []);
        $setItems = data_get($item, 'related_items.set_items');
        $setName = data_get($item, 'related_items.set_name');

        if (is_array($setItems) && $setItems !== []) {
            $currentItemInSet = array_filter($setItems, fn (array $s): bool => ($s['uuid'] ?? null) === $uuid);
            if ($currentItemInSet === []) {
                array_unshift($setItems, [
                    'uuid' => $uuid,
                    'name' => $itemName,
                    'classification' => $classification,
                    'type_label' => data_get($item, 'type_label'),
                    'sub_type_label' => data_get($item, 'sub_type_label'),
                    'size' => data_get($item, 'size'),
                    'web_url' => null,
                ]);
            }
        }

        $uexPrices = data_get($item, 'uex_prices.purchase', []);
        $descriptionData = data_get($item, 'description_data', []);
        $entityTagMap = data_get($item, 'entity_tag_map', []);
        $defaultComposition = data_get($item, 'resource_container.default_composition', []);
        $versionQuery = request()->query('version');
        $vehicles = data_get($item, 'vehicles', []);
        $hasVehiclesCard = is_array($vehicles) && $vehicles !== [];

        $technicalEntries = array_values(array_filter([
            ['label' => 'Classification', 'value' => $classification ?? '-', 'url' => null],
            ['label' => 'Class Name', 'value' => $className ?? '-', 'url' => null],
        ]));

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
            $type === 'SalvageModifier' ||
            data_get($item, 'tractor_beam') ||
            data_get($item, 'self_destruct') ||
            data_get($item, 'seat') ||
            data_get($item, 'thruster') ||
            data_get($item, 'fuel_tank') ||
            data_get($item, 'fuel_intake') ||
            data_get($item, 'mining_modifier') ||
            data_get($item, 'resource_network') ||
            data_get($item, 'cargo_grid')
        );

        $setItemCount = is_array($setItems) ? count($setItems) : 0;
        $variantCount = is_array($variants) ? count($variants) : 0;
        $baseVariantUuid = data_get($baseVariant, 'uuid');
        $isSelfReferential = $baseVariantUuid === $uuid;
        $baseVariantCount = (! empty($baseVariant) && (! $isSelfReferential || $variantCount > 0)) ? 1 : 0;
        $relatedItemsCount = $type === 'Cargo' ? 0 : ($setItemCount + $variantCount + $baseVariantCount);
        $portsCount = is_array($ports) ? count($ports) : 0;
        $uexPricesCount = is_array($uexPrices) ? count($uexPrices) : 0;


        $hasDescriptionDataCard = is_array($descriptionData) && $descriptionData !== [];
        $hasRelatedItemsCard = $relatedItemsCount > 0;
        $hasCompositionCard = is_array($defaultComposition) && $defaultComposition !== [];
        $hasSpecificationSection = $portsCount > 0 || $fpsSpecsAvailable || $vehicleSpecsAvailable;
        $blueprints = data_get($item, 'is_craftable') ? data_get($item, 'blueprint', []) : [];
        $hasBlueprintCards = is_array($blueprints) && $blueprints !== [];
        $hasUexOrBlueprints = $uexPricesCount > 0 || $hasBlueprintCards;
    @endphp

    <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-2">
            <x-items.item-breadcrumbs :item="$item" :breadcrumbs="$breadcrumbs" />
        </div>

        <div class="mx-auto grid w-full gap-4 xl:grid-cols-12">
            <x-items.hero :item="$item" :translations="$translations" class="xl:col-span-7" />
            <x-items.quick-facts-card
                :item="$item"
                :ports-count="$portsCount"
                :related-items-count="$relatedItemsCount"
                :uex-prices-count="$uexPricesCount"
                :composition="$defaultComposition"
                class="xl:col-span-5"
            />
        </div>

        <div class="flex flex-col gap-8">
            <section class="space-y-4">
                <h2 class="text-lg font-semibold tracking-tight">Details & Availability</h2>

                @if ($hasDescriptionDataCard || $hasRelatedItemsCard || $hasUexOrBlueprints)
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
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
                                :classification="$classification"
                            />
                        @endif

                        <x-uex.prices-card :sections="[['prices' => $uexPrices]]" />

                        @if ($hasBlueprintCards)
                            <div class="flex flex-col gap-4">
                                @foreach ($blueprints as $bp)
                                    <x-items.blueprint-card :blueprint="$bp" />
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif


                @if ($hasCompositionCard)
                    <x-items.commodity-composition-card
                        :composition="$defaultComposition"
                        :version-query="$versionQuery"
                        class="w-full"
                    />
                @endif
            </section>

            @if ($hasSpecificationSection)
                <section class="space-y-4">
                    <h2 class="text-lg font-semibold tracking-tight">Specifications & Integration</h2>

                    <div class="flex flex-col gap-4">
                        <div class="grid gap-4 lg:gap-6 grid-cols-1 lg:grid-cols-2">

                        @if ($fpsSpecsAvailable)
                                @if ($type === 'WeaponPersonal')
                                    <x-items.personal-weapon-card :personal-weapon="data_get($item, 'personal_weapon')" />
                                @endif

                                @if ($type === 'Armor')
                                    <x-items.armor-card :armor="data_get($item, 'armor')" />
                                @endif

                                @if ($type === 'WeaponAttachment')
                                    <x-items.weapon-attachment-card :weapon-attachment="$item" />
                                @endif

                                @if (data_get($item, 'suit_armor'))
                                    <x-items.suit-armor-card :suit-armor="data_get($item, 'suit_armor')" :temperature-resistance="data_get($item, 'temperature_resistance')" :inventory="data_get($item, 'inventory')" :gforce-resistance="data_get($item, 'gforce_resistance')" />
                                @endif

                                @if (data_get($item, 'clothing') && !data_get($item, 'suit_armor'))
                                    <x-items.clothing-card :clothing="data_get($item, 'clothing')" :temperature-resistance="data_get($item, 'temperature_resistance')" :inventory="data_get($item, 'inventory')" :gforce-resistance="data_get($item, 'gforce_resistance')" />
                                @endif

                                @if (data_get($item, 'ammunition'))
                                    <x-items.ammunition-card :ammunition="data_get($item, 'ammunition')" />
                                @endif

                                @if (data_get($item, 'weapon_modifier'))
                                    <x-items.weapon-modifier-card :weapon-modifier="data_get($item, 'weapon_modifier')" />
                                @endif

                        @endif

                        @if ($vehicleSpecsAvailable)
                                @if ($type === 'WeaponGun')
                                    <x-items.vehicle-weapon-card :vehicle-weapon="data_get($item, 'vehicle_weapon')" />
                                @endif

                                @if (data_get($item, 'tractor_beam'))
                                    <x-items.tractor-beam-card :tractor-beam="data_get($item, 'tractor_beam')" />
                                @endif

                                @if (data_get($item, 'ammunition') && !data_get($item, 'tractor_beam'))
                                    <x-items.ammunition-card :ammunition="data_get($item, 'ammunition')" />
                                @endif

                                @if ($type === 'Armor')
                                    <x-items.armor-card :armor="data_get($item, 'armor')" />
                                @endif

                                @if ($type === 'Missile')
                                    <x-items.missile-card :missile="data_get($item, 'missile')" />
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
                                    @php
                                        $equippedPortItem = data_get($ports, '0.equipped_item');
                                    @endphp
                                    @if (data_get($equippedPortItem, 'vehicle_weapon'))
                                        <x-items.vehicle-weapon-card :vehicle-weapon="data_get($equippedPortItem, 'vehicle_weapon')" />
                                    @endif
                                    @if (data_get($equippedPortItem, 'missile_rack'))
                                        <x-items.missile-rack-card :missile-rack="data_get($equippedPortItem, 'missile_rack')" />
                                    @endif
                                    @if (data_get($equippedPortItem, 'ammunition'))
                                        <x-items.ammunition-card :ammunition="data_get($equippedPortItem, 'ammunition')" />
                                    @endif
                                    @if (data_get($equippedPortItem, 'resource_network'))
                                        <x-items.resource-network-card :resource-network="data_get($equippedPortItem, 'resource_network')" :item-type="data_get($equippedPortItem, 'type')" />
                                    @endif
                                @endif

                                @if ($type === 'Shield')
                                    <x-items.shield-card :shield="data_get($item, 'shield')" />
                                @endif

                                @if ($type === 'QuantumDrive')
                                    <x-items.quantum-drive-card :quantum-drive="data_get($item, 'quantum_drive')" />
                                @endif


                                @if ($type === 'JumpDrive')
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

                                @if (data_get($item, 'emission') && data_get($item, 'emission.em_max', 0) > 0)
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

                                @if (data_get($item, 'cargo_grid'))
                                    <x-items.cargo-grid-card :cargo-grid="data_get($item, 'cargo_grid')" />
                                @endif

                                @if (data_get($item, 'weapon_modifier'))
                                    <x-items.weapon-modifier-card :weapon-modifier="data_get($item, 'weapon_modifier')" />
                                @endif

                                @if (data_get($item, 'resource_network'))
                                    <x-items.resource-network-card :resource-network="data_get($item, 'resource_network')" :item-type="data_get($item, 'type')" />

                                @endif

                                @if ($hasVehiclesCard)
                                    <x-items.standard-loadout-card :vehicles="$vehicles" />
                                @endif
                        @endif


                            @if ($portsCount > 0)
                                <x-items.ports-card :ports="$ports" class="w-full" />
                            @endif
                        </div>
                    </div>
                </section>
            @endif

            <x-technical-section :entries="$technicalEntries" testId="item-technical-card">
                @if (is_array($entityTagMap) && $entityTagMap !== [])
                    <div class="mt-5 pt-5 border-t border-base-300">
                        <x-dt-dd label="Entity Tag Map" stacked>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($entityTagMap as $tag)
                                    <span class="badge badge-neutral" title="{{ $tag['uuid'] ?? '' }}">{{ $tag['name'] ?? 'Unknown' }}</span>
                                @endforeach
                            </div>
                        </x-dt-dd>
                    </div>
                @endif
            </x-technical-section>
        </div>
    </div>
@endsection
