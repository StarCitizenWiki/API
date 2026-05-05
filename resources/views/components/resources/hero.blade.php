@props(['resource'])

@php
    $name = data_get($resource, 'name', 'Resource');
    $description = data_get($resource, 'description');
    $tier = data_get($resource, 'tier');

    $heroImage = data_get(data_get($resource, 'images', []), '0.thumbnail_url')
        ?? data_get(data_get($resource, 'images', []), '0.original_url');
    $fullImageUrl = data_get(data_get($resource, 'images', []), '0.original_url');
    $imageSource = data_get(data_get($resource, 'images', []), '0.source');
@endphp

<section {{ $attributes->merge(['class' => 'card bg-base-100 shadow', 'data-testid' => 'resource-hero']) }}>
    @if ($heroImage)
        <figure class="relative">
            <a href="{{ $fullImageUrl ?? $heroImage }}" target="_blank" rel="noopener noreferrer">
                <img src="{{ $heroImage }}" alt="{{ $name }}" class="size-full object-cover max-h-96" loading="lazy" />
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
                            aria-label="Resource type"
                        >
                            <x-icon name="gem" class="size-5 sm:size-6" />
                        </span>
                    @endif

                    <h1 class="min-w-0 text-3xl font-semibold tracking-tight sm:text-4xl">
                        {{ $name }}
                    </h1>
                </div>
            </div>

            @if ($tier)
                <div class="rounded-xl border border-accent/30 bg-base-200 px-3 py-2.5 sm:shrink-0">
                    <div class="text-xs font-semibold uppercase tracking-widest text-muted">
                        Rarity
                    </div>
                    <div class="mt-1.5">
                        <x-resources.rarity-badge :tier="$tier" class="" />
                    </div>
                </div>
            @endif
        </div>

        @if ($description)
            <div class="max-w-3xl text-sm leading-6 whitespace-pre-line text-subtle sm:text-base" data-testid="resource-hero-description">
                {!! nl2br(e($description)) !!}
            </div>
        @endif
    </div>
</section>
