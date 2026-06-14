@props(['location'])

@php
    $locationName = data_get($location, 'name', 'Starmap Location');
    $wikiUrl = 'https://starcitizen.tools/' . str_replace(' ', '_', $locationName);
    $typeName = data_get($location, 'type.name', data_get($location, 'type_name'));
    $description = data_get($location, 'description');
    $respawnLocationType = data_get($location, 'respawn_location_type');
    $designation = data_get($location, 'designation');

    $displayTitle = $designation !== null
        ? $designation . ': ' . $locationName
        : $locationName;

    if (is_string($description)) {
        $description = trim(html_entity_decode($description));
    }

    if (! is_string($description) || $description === '') {
        $description = null;
    }

    $hasImage = (bool) (data_get(data_get($location, 'images', []), '0.thumbnail_url')
        ?? data_get(data_get($location, 'images', []), '0.original_url'));
@endphp

<section {{ $attributes->merge(['class' => 'card sm:card-side bg-base-100 shadow', 'data-testid' => 'starmap-location-hero']) }}>
    <x-hero.image :images="data_get($location, 'images', [])" :alt="$locationName" />

    <div class="card-body gap-4 p-5 sm:p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 space-y-2">
                <div class="flex items-center gap-3">
                    @if (! $hasImage)
                        <span
                            class="inline-flex size-10 shrink-0 items-center justify-center rounded-box bg-accent/10 text-muted sm:size-11"
                            aria-label="Starmap location"
                        >
                            <x-icon name="radar" class="size-5 sm:size-6" />
                        </span>
                    @endif

                    <h1 class="min-w-0 text-3xl font-semibold tracking-tight sm:text-4xl">
                        {{ $displayTitle }}
                    </h1>
                </div>
            </div>

            @if (is_string($typeName) && $typeName !== '')
                <div class="shrink-0 text-right">
                    <div class="text-xs text-subtle uppercase tracking-wide">
                        Type
                    </div>
                    <div class="text-lg font-bold">
                        {{ $typeName }}
                    </div>
                </div>
            @endif
        </div>

        @if ($description)
            <div class="max-h-56 max-w-3xl overflow-y-auto pr-2 text-sm leading-6 whitespace-pre-line text-subtle sm:text-base" data-testid="starmap-location-hero-description">
                {!! nl2br(e($description)) !!}
            </div>
        @endif
        <div class="card-actions justify-end pt-4">
            <span class="text-xs text-muted font-semibold">Find on</span>
            <a href="{{ $wikiUrl }}" class="link link-hover link-primary text-xs" target="_blank" rel="noopener noreferrer">starcitizen.tools</a>
        </div>
    </div>
</section>
