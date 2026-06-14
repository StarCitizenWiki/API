@props(['resource'])

@php
    $name = data_get($resource, 'display_name', data_get($resource, 'name', 'Resource'));
    $description = data_get($resource, 'description');
    $tier = data_get($resource, 'tier');

    $hasImage = (bool) (data_get(data_get($resource, 'images', []), '0.thumbnail_url')
        ?? data_get(data_get($resource, 'images', []), '0.original_url'));
@endphp

<section {{ $attributes->merge(['class' => 'card sm:card-side bg-base-100 shadow', 'data-testid' => 'resource-hero']) }}>
    <x-hero.image :images="data_get($resource, 'images', [])" :alt="$name" />

    <div class="card-body gap-4 p-5 sm:p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 space-y-2">
                <div class="flex items-center gap-3">
                    @if (! $hasImage)
                        <span
                            class="inline-flex size-10 shrink-0 items-center justify-center rounded-box bg-accent/10 text-muted sm:size-11"
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
                <div class="shrink-0 text-right">
                    <div class="text-xs text-subtle uppercase tracking-wide">
                        Rarity
                    </div>
                    <div class="mt-1">
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
