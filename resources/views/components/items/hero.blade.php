@props(['item'])

@php
    use Illuminate\Support\Arr;
    use Illuminate\Support\Str;

    $itemName = data_get($item, 'name', 'Item');
    $manufacturerName = data_get($item, 'manufacturer.name');
    $itemType = data_get($item, 'type');
    $classification = data_get($item, 'classification');
    $itemClass = data_get($item, 'class');
    $itemSize = data_get($item, 'size');
    $grade = data_get($item, 'grade');
    $isCraftable = data_get($item, 'is_craftable') === true;
    $isBaseVariant = data_get($item, 'is_base_variant');
    $description = data_get($item, 'description');
    $currentItemUuid = data_get($item, 'uuid');
    $baseVariant = data_get($item, 'related_items.base_item', []);
    $baseVariantUuid = data_get($baseVariant, 'uuid');
    $version = request()->query('version');
    $relatedVariants = data_get($item, 'related_items.variant_items', []);

    if (is_array($description)) {
        $descriptionTranslations = $description;

        $description = collect([
            data_get($descriptionTranslations, 'en'),
            data_get($descriptionTranslations, 'en_EN'),
        ])->first(static fn (mixed $value): bool => is_string($value) && trim($value) !== '');

        if ($description === null) {
            $description = collect($descriptionTranslations)
                ->filter(static fn (mixed $value, mixed $key): bool => is_string($key) && str_starts_with(strtolower($key), 'en') && is_string($value) && trim($value) !== '')
                ->first();
        }

        if ($description === null) {
            $description = Arr::first($descriptionTranslations, static fn (mixed $value): bool => is_string($value) && trim($value) !== '');
        }
    }

    $description = is_string($description)
        ? trim(html_entity_decode($description))
        : null;

    if ($description === '') {
        $description = null;
    }

    if ($description !== null) {
        $description = Str::limit($description, 420);
    }

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
                'label' => $itemType,
                'url' => $typeUrl,
            ]
            : null,
        $classification
            ? [
                'label' => $classification,
                'url' => null,
            ]
            : null,
    ]));

    $badges = array_values(array_filter([
        $gradeLetter ? ['label' => 'Grade '.$gradeLetter, 'url' => null, 'test_id' => null] : null,
        $itemClass ? ['label' => $itemClass, 'url' => null, 'test_id' => null] : null,
        $isCraftable ? ['label' => 'Craftable', 'url' => $blueprintUrl, 'test_id' => 'item-hero-pill-craftable'] : null,
        $variantStateLabel ? ['label' => $variantStateLabel, 'url' => null, 'test_id' => 'item-hero-pill-variant-state'] : null,
    ]));
@endphp

<section {{ $attributes->merge(['class' => 'w-full rounded-box border border-base-300 bg-base-100 shadow', 'data-testid' => 'item-hero']) }}>
    <div class="card-body gap-4 p-5 sm:p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 space-y-2">
                <div class="flex items-center gap-3">
                    <span
                        class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl bg-base-200/70 text-base-content/55 sm:size-11"
                        aria-label="Item type"
                    >
                        <x-icon :name="$iconName" class="size-5 sm:size-6" />
                    </span>

                    <h1 class="min-w-0 text-3xl font-semibold tracking-tight sm:text-4xl">
                        {{ $itemName }}
                    </h1>
                </div>

                @if ($headlineLinks !== [])
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm font-medium text-base-content/60">
                        @foreach ($headlineLinks as $entry)
                            @if (! $loop->first)
                                <span aria-hidden="true" class="text-base-content/35">|</span>
                            @endif

                            @if ($entry['url'])
                                <a href="{{ $entry['url'] }}" class="link link-hover font-semibold text-base-content/70">
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
                <div class="rounded-xl border border-base-300 bg-base-200/45 px-3 py-2.5 sm:shrink-0">
                    <div class="text-[10px] font-semibold uppercase tracking-[0.22em] text-base-content/50">
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
                            class="badge badge-ghost transition hover:border-base-content/25 hover:bg-base-200"
                            @if ($badge['test_id']) data-testid="{{ $badge['test_id'] }}" @endif
                        >
                            {{ $badge['label'] }}
                        </a>
                    @else
                        <span class="badge badge-ghost" @if ($badge['test_id']) data-testid="{{ $badge['test_id'] }}" @endif>{{ $badge['label'] }}</span>
                    @endif
                @endforeach
            </div>
        @endif

        @if ($description)
            <div class="max-w-3xl text-sm leading-6 whitespace-pre-line text-base-content/70 sm:text-base" data-testid="item-hero-description">
                {!! nl2br(e($description)) !!}
            </div>
        @endif
    </div>
</section>
