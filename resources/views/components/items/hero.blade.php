@props(['item', 'translations' => null])

@php
    use App\Support\Items\ItemHeroViewData;

    $rawVersion = request()->query('version');
    $version = is_string($rawVersion) && $rawVersion !== '' ? $rawVersion : null;

    $hero = ItemHeroViewData::make()->build(is_array($item) ? $item : [], $version);
@endphp

<section {{ $attributes->merge(['class' => 'card sm:card-side bg-base-100 shadow', 'data-testid' => 'item-hero']) }}>
    <x-hero.image :images="data_get($item, 'images', [])" :alt="$hero['name']" :portrait="$hero['isPortrait']" />

    <div class="card-body gap-4 p-5 sm:p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 space-y-2">
                <div class="flex items-center gap-3">
                    @if (! $hero['hasImage'])
                        <span
                            class="inline-flex size-10 shrink-0 items-center justify-center rounded-box bg-accent/10 text-muted sm:size-11"
                            aria-label="Item type"
                        >
                            <x-icon :name="$hero['iconName']" class="size-5 sm:size-6" />
                        </span>
                    @endif

                    <h1 class="min-w-0 text-3xl font-semibold tracking-tight sm:text-4xl">
                        {{ $hero['name'] }}
                    </h1>
                </div>

                @if ($hero['headlineLinks'] !== [])
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm font-medium text-subtle">
                        @foreach ($hero['headlineLinks'] as $entry)
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

            @if ($hero['itemSize'] !== null)
                <div class="shrink-0 text-right">
                    <div class="text-xs text-subtle uppercase tracking-wide">
                        Size
                    </div>
                    <div class="text-lg font-bold">
                        {{ $hero['itemSize'] }}
                    </div>
                </div>
            @endif
        </div>

        @if ($hero['badges'] !== [])
            <div class="flex flex-wrap items-center gap-2">
                @foreach ($hero['badges'] as $badge)
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
            <a href="{{ $hero['wikiUrl'] }}" class="link link-hover link-primary text-xs" target="_blank" rel="noopener noreferrer">starcitizen.tools</a>
        </div>
    </div>
</section>
