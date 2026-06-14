@use('App\Support\Format')
@props(['vehicle', 'translations' => null])

@php
    $vehicleName = data_get($vehicle, 'name', 'Vehicle');
    $wikiUrl = 'https://starcitizen.tools/' . str_replace(' ', '_', $vehicleName);
    $manufacturerName = data_get($vehicle, 'manufacturer.name');
    $sizeClass = data_get($vehicle, 'size_class');
    $maxMedicalTier = data_get($vehicle, 'max_medical_tier');
    $career = data_get($vehicle, 'career');
    $role = data_get($vehicle, 'role');

    $msrp = data_get($vehicle, 'msrp');
    $isSpaceship = data_get($vehicle, 'is_spaceship') === true;
    $isGravlev = data_get($vehicle, 'is_gravlev') === true;
    $isVehicle = data_get($vehicle, 'is_vehicle') === true;

    $vehicleTypeIcon = null;
    $vehicleTypeLabel = null;
    $hasImage = (bool) (data_get(data_get($vehicle, 'images', []), '0.thumbnail_url')
        ?? data_get(data_get($vehicle, 'images', []), '0.original_url'));

    if ($isGravlev) {
        $vehicleTypeIcon = 'drone';
        $vehicleTypeLabel = 'Gravlev vehicle';
    } elseif ($isSpaceship) {
        $vehicleTypeIcon = 'rocket';
        $vehicleTypeLabel = 'Ship';
    } elseif ($isVehicle) {
        $vehicleTypeIcon = 'truck';
        $vehicleTypeLabel = 'Ground vehicle';
    }

    $version = request()->query('version');
    $makeVehiclesUrl = static function (array $filters) use ($version): string {
        $url = route('web.vehicles.index', ['filter' => $filters]);

        if (is_string($version) && $version !== '') {
            $url = url()->query($url, ['version' => $version]);
        }

        return $url;
    };
@endphp

<section {{ $attributes->merge(['class' => 'card sm:card-side bg-base-100 shadow', 'data-testid' => 'vehicle-hero']) }}>
    <x-hero.image :images="data_get($vehicle, 'images', [])" :alt="$vehicleName" min-width="" />

    <div class="card-body gap-4 p-5 sm:p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 space-y-1">
                <div class="flex items-center gap-3">
                    @if ($vehicleTypeIcon && ! $hasImage)
                        <span
                            class="inline-flex size-10 shrink-0 items-center justify-center rounded-box bg-accent/10 text-muted sm:size-11"
                            title="{{ $vehicleTypeLabel }}"
                            aria-label="{{ $vehicleTypeLabel }}"
                        >
                            <x-icon :name="$vehicleTypeIcon" class="size-5 sm:size-6" />
                        </span>
                    @endif

                    <h1 class="min-w-0 text-3xl font-semibold tracking-tight sm:text-4xl">
                        {{ $vehicleName }}
                    </h1>
                </div>

                @if ($manufacturerName || $career || $role || $sizeClass !== null || $maxMedicalTier !== null)
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm font-medium text-subtle">
                        @if ($manufacturerName)
                            <a
                                href="{{ $makeVehiclesUrl(['manufacturer' => $manufacturerName]) }}"
                                class="link link-hover font-semibold text-subtle"
                            >
                                {{ $manufacturerName }}
                            </a>
                        @endif
                @if ($manufacturerName && ($career || $role || $sizeClass !== null || $maxMedicalTier !== null))
                    <span aria-hidden="true" class="text-base-content/35">|</span>
                @endif
                @if ($career)
                    <span>{{ $career }}</span>
                @endif
                @if ($career && ($role || $sizeClass !== null || $maxMedicalTier !== null))
                    <span aria-hidden="true" class="text-base-content/35">|</span>
                @endif
                @if ($role)
                    <span>{{ $role }}</span>
                @endif
                @if ($role && ($sizeClass !== null || $maxMedicalTier !== null))
                    <span aria-hidden="true" class="text-base-content/35">|</span>
                @endif
                @if ($sizeClass !== null)
                    <span>S{{ $sizeClass }}</span>
                @endif
                @if ($sizeClass !== null && $maxMedicalTier !== null)
                    <span aria-hidden="true" class="text-base-content/35">|</span>
                @endif
                @if ($maxMedicalTier !== null)
                    <span class="badge badge-primary badge-sm">Medical {{ $maxMedicalTier }}</span>
                @endif
                    </div>
                @endif
            </div>

            @if ($msrp !== null)
                <div class="shrink-0 text-right">
                    <div class="text-xs text-subtle uppercase tracking-wide">MSRP</div>
                    <div class="text-lg font-bold">${{ Format::number((float) $msrp, 0) }}</div>
                </div>
            @endif
        </div>

        <x-translations-content :translations="$translations" :attribution-links="true" data-testid="vehicle-hero-description" class="max-w-3xl" />
        <div class="card-actions justify-end pt-4">
            <span class="text-xs text-muted font-semibold">Find on</span>
            <a href="{{ $wikiUrl }}" class="link link-hover link-primary text-xs" target="_blank" rel="noopener noreferrer">starcitizen.tools</a>
        </div>
    </div>
</section>
