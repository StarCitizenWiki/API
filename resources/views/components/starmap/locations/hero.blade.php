@props(['location'])

@php
    $locationName = data_get($location, 'name', 'Starmap Location');
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
@endphp

<section {{ $attributes->merge(['class' => 'w-full rounded-box border border-base-300 bg-base-100 shadow', 'data-testid' => 'starmap-location-hero']) }}>
    <div class="card-body gap-4 p-5 sm:p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 space-y-2">
                <div class="flex items-center gap-3">
                    <span
                        class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl bg-base-200/70 text-base-content/55 sm:size-11"
                        aria-label="Starmap location"
                    >
                        <x-icon name="radar" class="size-5 sm:size-6" />
                    </span>

                    <h1 class="min-w-0 text-3xl font-semibold tracking-tight sm:text-4xl">
                        {{ $displayTitle }}
                    </h1>
                </div>
            </div>

            @if (is_string($typeName) && $typeName !== '')
                <div class="rounded-xl border border-base-300 bg-base-200/45 px-3 py-2.5 sm:shrink-0">
                    <div class="text-[10px] font-semibold uppercase tracking-[0.22em] text-base-content/50">
                        Type
                    </div>
                    <div class="mt-1.5 text-base font-semibold leading-none text-base-content">
                        {{ $typeName }}
                    </div>
                </div>
            @endif
        </div>

        @if ($description)
            <div class="max-h-56 max-w-3xl overflow-y-auto pr-2 text-sm leading-6 whitespace-pre-line text-base-content/70 sm:text-base" data-testid="starmap-location-hero-description">
                {!! nl2br(e($description)) !!}
            </div>
        @endif
    </div>
</section>
