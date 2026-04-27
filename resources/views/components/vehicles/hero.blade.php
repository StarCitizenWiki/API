@props(['vehicle'])

@php
    use Illuminate\Support\Arr;

    $vehicleName = data_get($vehicle, 'name', 'Vehicle');
    $manufacturerName = data_get($vehicle, 'manufacturer.name');
    $sizeClass = data_get($vehicle, 'size_class');
    $maxMedicalTier = data_get($vehicle, 'max_medical_tier');
    $career = data_get($vehicle, 'career');
    $role = data_get($vehicle, 'role');
    $description = data_get($vehicle, 'description.en');

    if (is_array($description)) {
        $description = Arr::first($description, static fn (mixed $value): bool => is_string($value) && $value !== '');
    }

    if ($description === null || $description === '') {
        $description = data_get($vehicle, 'description');

        if (is_array($description)) {
            $description = Arr::first($description, static fn (mixed $value): bool => is_string($value) && $value !== '');
        }
    }

    $msrp = data_get($vehicle, 'msrp');
    $isSpaceship = data_get($vehicle, 'is_spaceship') === true;
    $isGravlev = data_get($vehicle, 'is_gravlev') === true;
    $isVehicle = data_get($vehicle, 'is_vehicle') === true;

    $vehicleTypeIcon = null;
    $vehicleTypeLabel = null;
    $heroImage = data_get(data_get($vehicle, 'images', []), '0.thumbnail_url')
        ?? data_get(data_get($vehicle, 'images', []), '0.original_url');
    $fullImageUrl = data_get(data_get($vehicle, 'images', []), '0.original_url');
    $imageSource = data_get(data_get($vehicle, 'images', []), '0.source');
    $imageWidth = data_get(data_get($vehicle, 'images', []), '0.thumbnail_width')
        ?? data_get(data_get($vehicle, 'images', []), '0.original_width');
    $imageHeight = data_get(data_get($vehicle, 'images', []), '0.thumbnail_height')
        ?? data_get(data_get($vehicle, 'images', []), '0.original_height');
    $isPortrait = $imageWidth !== null && $imageHeight !== null && $imageHeight > $imageWidth;

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

    $isPortrait = true;
@endphp

<section {{ $attributes->merge(['class' => 'w-full rounded-box border border-base-300 bg-base-100 shadow flex ' . ($isPortrait ? 'flex-col sm:flex-row' : 'flex-col'), 'data-testid' => 'vehicle-hero']) }}>
    @if ($heroImage)
        <div class="relative overflow-hidden {{ $isPortrait ? 'h-48 w-full sm:h-auto sm:w-64 sm:shrink-0 rounded-t-box sm:rounded-l-box sm:rounded-tr-none' : 'h-48 rounded-t-box sm:h-56' }}">
            <a href="{{ $fullImageUrl ?? $heroImage }}" target="_blank" rel="noopener noreferrer">
                <img src="{{ $heroImage }}" alt="{{ $vehicleName }}" class="h-full w-full object-cover" loading="lazy" />
            </a>
            <div class="pointer-events-none absolute inset-0 bg-linear-to-t from-base-100/60 to-transparent"></div>
            @if ($imageSource)
                <span class="pointer-events-none absolute right-3 bottom-2 rounded bg-black/30 px-2 py-0.5 text-xs text-white/70 backdrop-blur-sm">
                    Image from {{ $imageSource }}
                </span>
            @endif
        </div>
    @endif

    <div class="card-body gap-4 p-5 sm:p-6 {{ $isPortrait ? 'flex-1 min-w-0' : '' }}">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 space-y-1">
                <div class="flex items-center gap-3">
                    @if ($vehicleTypeIcon && ! $heroImage)
                        <span
                            class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl bg-base-200/70 text-base-content/55 sm:size-11"
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
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm font-medium text-base-content/60">
                        @if ($manufacturerName)
                            <a
                                href="{{ route('web.vehicles.index', ['filter' => ['manufacturer' => $manufacturerName]]) }}"
                                class="link link-hover font-semibold text-base-content/70"
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
                <div class="rounded-xl border border-base-300 bg-base-200/45 px-3 py-2.5 sm:shrink-0">
                    <div class="text-[10px] font-semibold uppercase tracking-[0.22em] text-base-content/50">
                        MSRP
                    </div>
                    <div class="mt-1.5 text-base font-semibold leading-none text-base-content">
                        ${{ number_format((float) $msrp, 0) }}
                    </div>
                </div>
            @endif
        </div>

        @if ($description)
            <div class="max-w-3xl text-sm leading-6 whitespace-pre-line text-base-content/70 sm:text-base">
                {!! nl2br(e((string) $description)) !!}
            </div>
        @endif
    </div>
</section>
