@props(['vehicle'])

@php
    $sizeClass = data_get($vehicle, 'size_class');
    $career = data_get($vehicle, 'career');
    $role = data_get($vehicle, 'role');
    $classification = data_get($vehicle, 'classification');

    $isVehicle = data_get($vehicle, 'is_vehicle');
    $isGravlev = data_get($vehicle, 'is_gravlev');
    $isSpaceship = data_get($vehicle, 'is_spaceship');

    $crew = data_get($vehicle, 'crew', []);
    $cargoCapacity = data_get($vehicle, 'cargo_capacity');
    $stowage = data_get($vehicle, 'vehicle_inventory');

    $speed = data_get($vehicle, 'speed', []);
    $scmSpeed = data_get($speed, 'scm');
    $maxSpeed = data_get($speed, 'max');

    $health = data_get($vehicle, 'health');
    $shield = data_get($vehicle, 'shield', []);
    $shieldHp = data_get($shield, 'hp');

    $massTotal = data_get($vehicle, 'mass_total', data_get($vehicle, 'mass'));
    $dimension = data_get($vehicle, 'dimension', []);
    $length = data_get($dimension, 'length');
    $width = data_get($dimension, 'width');
    $height = data_get($dimension, 'height');

    $crossSection = data_get($vehicle, 'cross_section', []);
    $crossSectionLength = data_get($crossSection, 'length');
    $crossSectionWidth = data_get($crossSection, 'width');
    $crossSectionHeight = data_get($crossSection, 'height');

    $shipMatrixMsrp = data_get($vehicle, 'msrp');
    $shipMatrixProductionStatus = data_get($vehicle, 'production_status');
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow']) }}>
    <div class="card-body gap-2">
        <h2 class="card-title">
            {{ data_get($vehicle, 'name') }} - S{{ $sizeClass }}
            <a href="{{ route('web.vehicles.index', ['filter' => ['manufacturer' => data_get($vehicle, 'manufacturer.name')]]) }}" class="badge badge-soft badge-sm link">{{ data_get($vehicle, 'manufacturer.name') }}</a>
        </h2>
        <dl class="grid gap-2 grid-cols-2 sm:grid-cols-2 lg:grid-cols-3">

            <div class="space-y-1">
                <dt class="text-xs text-base-content/60">Career</dt>
                <dd class="text-sm">{{ $career ?? '-' }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs text-base-content/60">Role</dt>
                <dd class="text-sm">{{ $role ?? '-' }}</dd>
            </div>

            <div class="space-y-1">
                <dt class="text-xs text-base-content/60">Crew</dt>
                <dd class="text-sm">
                    {{ data_get($crew, 'min', '-') }}
                </dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs text-base-content/60">MSRP</dt>
                <dd class="text-sm">{{ $shipMatrixMsrp ? '$' . number_format($shipMatrixMsrp) : '-' }}</dd>
            </div>
{{--            <div class="space-y-1">--}}
{{--                <dt class="text-xs text-base-content/60">Type</dt>--}}
{{--                <dd class="text-sm">--}}
{{--                    @if($isGravlev)--}}
{{--                        Gravlev--}}
{{--                    @elseif($isSpaceship)--}}
{{--                        Spaceship--}}
{{--                    @elseif($isVehicle)--}}
{{--                        Ground--}}
{{--                    @else--}}
{{--                        ---}}
{{--                    @endif--}}
{{--                </dd>--}}
{{--            </div>--}}

            <div class="space-y-1">
                <dt class="text-xs text-base-content/60">Cargo</dt>
                <dd class="text-sm">{{ fmt_value_with_unit($cargoCapacity, 'SCU', 0) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs text-base-content/60">Stowage</dt>
                <dd class="text-sm">{{ fmt_value_with_unit($stowage, 'µSCU', 0) }}</dd>
            </div>

            <div class="space-y-1">
                <dt class="text-xs text-base-content/60">Health</dt>
                <dd class="text-sm">{{ fmt_value_with_unit($health, 'HP', 0) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs text-base-content/60">Shield HP</dt>
                <dd class="text-sm">{{ fmt_value_with_unit($shieldHp, 'HP', 0) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs text-base-content/60">Mass</dt>
                <dd class="text-sm">{{ fmt_value_with_unit($massTotal, 'kg', 0) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs text-base-content/60">SCM Speed</dt>
                <dd class="text-sm">{{ fmt_value_with_unit($scmSpeed, 'm/s', 0) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs text-base-content/60">Max Speed</dt>
                <dd class="text-sm">{{ fmt_value_with_unit($maxSpeed, 'm/s', 0) }}</dd>
            </div>

            <div class="space-y-1">
                <dt class="text-xs text-base-content/60">Dimensions</dt>
                <dd class="text-sm">
                    @if ($length || $width || $height)
                        {{ $length ?? '-' }} × {{ $width ?? '-' }} × {{ $height ?? '-' }}m
                    @else
                        -
                    @endif
                </dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs text-base-content/60">IR Emission</dt>
                <dd class="text-sm">
                    {{ fmt_or_dash(data_get($vehicle, 'signature.ir_shields'))  }}
                </dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs text-base-content/60">EM Emission</dt>
                <dd class="text-sm">
                    {{ fmt_or_dash(data_get($vehicle, 'signature.em_shields'))  }}
                </dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs text-base-content/60">Cross Section</dt>
                <dd class="text-sm">
                    {{ fmt_or_dash(data_get($vehicle, 'cross_section_max'))  }}
                </dd>
            </div>

        </dl>
    </div>
</div>
