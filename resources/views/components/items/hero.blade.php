@props(['item', 'translations' => null])

@php
    $itemName = data_get($item, 'name', 'Item');
    $wikiUrl = 'https://starcitizen.tools/' . str_replace(' ', '_', $itemName);
    $manufacturerName = data_get($item, 'manufacturer.name');
    $itemType = data_get($item, 'type');
    $itemTypeLabel = data_get($item, 'type_label') ?? $itemType;
    $subTypeLabel = data_get($item, 'sub_type_label') ?? data_get($item, 'sub_type') ?? '';
    $classification = data_get($item, 'classification');
    $classificationLabel = data_get($item, 'classification_label') ?? $classification;
    $itemClass = data_get($item, 'class');
    $itemSize = data_get($item, 'size');
    $grade = data_get($item, 'grade');
    $isCraftable = data_get($item, 'is_craftable') === true;
    $isFps = str_starts_with($classification, 'FPS.');
    $isBaseVariant = data_get($item, 'is_base_variant');
    $currentItemUuid = data_get($item, 'uuid');
    $baseVariant = data_get($item, 'related_items.base_item', []);
    $baseVariantUuid = data_get($baseVariant, 'uuid');
    $version = request()->query('version');
    $relatedVariants = data_get($item, 'related_items.variant_items', []);

    $gradeLetter = match ($grade) {
        1 => 'A',
        2 => 'B',
        3 => 'C',
        4 => 'D',
        default => $grade,
    };

    $classificationValue = is_string($classification) ? $classification : null;
    $typeUrl = data_get($item, 'type_web_url');

    if ($typeUrl === null && $itemType) {
        $typeUrl = route('web.items.index', ['filter' => ['type' => $itemType]]);
    }

    $primaryBlueprint = collect(data_get($item, 'blueprint', []))
        ->first(static fn (mixed $blueprint): bool => is_array($blueprint) && is_string(data_get($blueprint, 'uuid')) && data_get($blueprint, 'uuid') !== '');

    $blueprintUuid = data_get($primaryBlueprint, 'uuid');
    $blueprintUrl = $blueprintUuid
        ? route('web.blueprints.show', ['blueprint' => $blueprintUuid])
        : null;

    if ($blueprintUrl !== null && is_string($version) && $version !== '') {
        $blueprintUrl = url()->query($blueprintUrl, ['version' => $version]);
    }

    $hasVariantFamily = ($isBaseVariant === false && is_string($baseVariantUuid) && $baseVariantUuid !== '' && $baseVariantUuid !== $currentItemUuid)
        || (is_array($relatedVariants) && $relatedVariants !== []);

    $variantStateLabel = match (true) {
        $isBaseVariant === false => 'Variant',
        $hasVariantFamily => 'Base Variant',
        default => null,
    };

    $heroImage = data_get(data_get($item, 'images', []), '0.thumbnail_url')
        ?? data_get(data_get($item, 'images', []), '0.original_url');
    $fullImageUrl = data_get(data_get($item, 'images', []), '0.original_url');
    $imageSource = data_get(data_get($item, 'images', []), '0.source');

    $iconName = match (true) {
        $itemType === 'PowerPlant' => 'power',
        $itemType === 'Shield' || str_contains($classificationValue ?? '', 'Shield') => 'shield',
        $itemType === 'QuantumDrive' => 'atom',
        $itemType === 'JumpDrive' => 'egg-fried',
        $itemType === 'Cooler' => 'fan',
        $itemType === 'Radar' => 'radar',
        $itemType === 'Missile' || $itemType === 'MissileLauncher' => 'rocket',
        $itemType === 'Armor' || str_contains($classificationValue ?? '', 'Armor') => 'shield-check',
        str_contains($classificationValue ?? '', 'Weapon') || str_contains($classificationValue ?? '', 'FPS') => 'crosshair',
        str_starts_with($classificationValue ?? '', 'Ship.') => 'rocket',
        default => 'box',
    };

    $headlineLinks = array_values(array_filter([
        $manufacturerName
            ? [
                'label' => $manufacturerName,
                'url' => route('web.items.index', ['filter' => ['manufacturer' => $manufacturerName]]),
            ]
            : null,
        $itemType
            ? [
                'label' => $itemTypeLabel,
                'url' => $typeUrl,
            ]
            : null,
        $subTypeLabel
            ? [
                'label' => $subTypeLabel,
                'url' => null,
            ]
            : null,
    ]));

    $badges = array_values(array_filter([
        $gradeLetter ? ['label' => 'Grade '.$gradeLetter, 'url' => null, 'test_id' => null, 'badge_class' => 'badge-accent badge-outline'] : null,
        $isCraftable ? ['label' => 'Craftable', 'url' => $blueprintUrl, 'test_id' => 'item-hero-pill-craftable', 'badge_class' => 'badge-primary badge-outline'] : null,
        ($variantStateLabel && $isFps) ? ['label' => $variantStateLabel, 'url' => null, 'test_id' => 'item-hero-pill-variant-state', 'badge_class' => 'badge-accent badge-outline'] : null,
    ]));

    // TODO: Override for now
    $isPortrait = true;
@endphp

<section {{ $attributes->merge(['class' => 'card sm:card-side bg-base-100 shadow', 'data-testid' => 'item-hero']) }}>
    @if ($heroImage)
        <figure class="relative">
            <a href="{{ $fullImageUrl ?? $heroImage }}" target="_blank" rel="noopener noreferrer">
                <img src="{{ $heroImage }}" alt="{{ $itemName }}" class="size-full object-cover max-h-96" loading="lazy" />
            </a>
            @if ($imageSource)
                <span class="pointer-events-none absolute right-3 bottom-2 rounded bg-black/30 px-2 py-0.5 text-xs text-white/70 w-auto h-auto">
                    Image from {{ $imageSource }}
                </span>
            @endif
        </figure>
    @endif

    <div class="card-body gap-4 p-5 sm:p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 space-y-2">
                <div class="flex items-center gap-3">
                    @if (! $heroImage)
                        <span
                            class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl bg-accent/10 text-muted sm:size-11"
                            aria-label="Item type"
                        >
                            <x-icon :name="$iconName" class="size-5 sm:size-6" />
                        </span>
                    @endif

                    <h1 class="min-w-0 text-3xl font-semibold tracking-tight sm:text-4xl">
                        {{ $itemName }}
                    </h1>
                </div>

                @if ($headlineLinks !== [])
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm font-medium text-subtle">
                        @foreach ($headlineLinks as $entry)
                            @if (! $loop->first)
                                <span aria-hidden="true" class="text-base-content/35">|</span>
                            @endif

                            @if ($entry['url'])
                                <a href="{{ $entry['url'] }}" class="link link-hover font-semibold text-subtle">
                                    {{ $entry['label'] }}
                                </a>
                            @else
                                <span>{{ $entry['label'] }}</span>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            @if ($itemSize !== null)
                <div class="rounded-xl border border-accent/30 bg-base-200 px-3 py-2.5 sm:shrink-0">
                    <div class="text-xs font-semibold uppercase tracking-widest text-muted">
                        Size
                    </div>
                    <div class="mt-1.5 text-base font-semibold leading-none text-base-content">
                        {{ $itemSize }}
                    </div>
                </div>
            @endif
        </div>

        @if ($badges !== [])
            <div class="flex flex-wrap items-center gap-2">
                @foreach ($badges as $badge)
                    @if ($badge['url'])
                        <a
                            href="{{ $badge['url'] }}"
                            class="badge {{ $badge['badge_class'] ?? 'badge-soft' }} transition hover:border-base-content/25 hover:bg-base-200"
                            @if ($badge['test_id']) data-testid="{{ $badge['test_id'] }}" @endif
                        >
                            {{ $badge['label'] }}
                        </a>
                    @else
                        <span class="badge {{ $badge['badge_class'] ?? 'badge-soft' }}" @if ($badge['test_id']) data-testid="{{ $badge['test_id'] }}" @endif>{{ $badge['label'] }}</span>
                    @endif
                @endforeach
            </div>
        @endif

        <x-translations-content :translations="$translations" :attribution-links="true" data-testid="item-hero-description" class="max-w-3xl" />
        <div class="card-actions justify-end pt-4">
            <span class="text-xs text-muted font-semibold">Find on</span>
            <a href="{{ $wikiUrl }}" class="link link-hover link-primary text-xs" target="_blank" rel="noopener noreferrer">starcitizen.tools</a>
        </div>
    </div>
</section>
