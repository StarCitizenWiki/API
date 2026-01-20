@php use Illuminate\Support\Str; @endphp
@extends('layouts.app')

@php
    $pageTitleDecoded = html_entity_decode($pageTitle);

    $vehicleName = data_get($vehicle, 'name', 'Vehicle');
    $manufacturerName = data_get($vehicle, 'manufacturer.name');
    $sizeClass = data_get($vehicle, 'size_class');
    $career = data_get($vehicle, 'career');
    $role = data_get($vehicle, 'role');
    $className = data_get($vehicle, 'class_name');
    $shipMatrixDescription = data_get($vehicle, 'description.en_EN');
@endphp

@section('title')
    {!! $pageTitleDecoded !!} - {{ $manufacturerName }} - Star Citizen Vehicle
@endsection
@section('meta_description')
    {!! Str::limit($shipMatrixDescription ?? $vehicleName, 160) !!}
@endsection

@section('meta')
    <meta name="keywords" content="{{ $vehicleName }},{{ $manufacturerName ?? '' }},{{ $sizeClass ? "Size {$sizeClass}" : '' }},{{ $career ?? '' }},{{ $role ?? '' }},Star Citizen,SC">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $vehicleName }} - {{ $manufacturerName ?? '' }} {{ $className ?? '' }}">
    <meta property="og:description" content="{!! Str::limit($shipMatrixDescription ?? $vehicleName, 160) !!}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $vehicleName }} - {{ $manufacturerName ?? '' }}">
    <meta name="twitter:description" content="{!! Str::limit($shipMatrixDescription ?? $vehicleName, 160) !!}">
@endsection


@section('content')
    @php
        $vehicleName = data_get($vehicle, 'name', 'Vehicle');
        $gameName = data_get($vehicle, 'game_name');
        $className = data_get($vehicle, 'class_name');

        $manufacturerName = data_get($vehicle, 'manufacturer.name');
        $manufacturerCode = data_get($vehicle, 'manufacturer.code');

        $sizeClass = data_get($vehicle, 'size_class');
        $career = data_get($vehicle, 'career');
        $role = data_get($vehicle, 'role');

        $isVehicle = data_get($vehicle, 'is_vehicle');
        $isGravlev = data_get($vehicle, 'is_gravlev');
        $isSpaceship = data_get($vehicle, 'is_spaceship');

        $dimension = data_get($vehicle, 'dimension', []);
        $length = data_get($dimension, 'length');
        $width = data_get($dimension, 'width');
        $height = data_get($dimension, 'height');

        $crossSection = data_get($vehicle, 'cross_section', []);

        $massTotal = data_get($vehicle, 'mass_total', data_get($vehicle, 'mass'));
        $massHull = data_get($vehicle, 'mass_hull');
        $massLoadout = data_get($vehicle, 'mass_loadout');

        $cargoCapacity = data_get($vehicle, 'cargo_capacity');
        $cargoLimits = data_get($vehicle, 'cargo_limits', []);
        $cargoGrids = data_get($vehicle, 'cargo_grids', []);
        $vehicleInventory = data_get($vehicle, 'vehicle_inventory');
        $inventoryContainers = data_get($vehicle, 'inventory_containers', []);

        $crew = data_get($vehicle, 'crew', []);

        $health = data_get($vehicle, 'health');
        $shield = data_get($vehicle, 'shield', []);
        $armor = data_get($vehicle, 'armor', []);

        $speed = data_get($vehicle, 'speed', []);
        $agility = data_get($vehicle, 'agility', []);
        $afterburner = data_get($vehicle, 'afterburner', []);
        $fuel = data_get($vehicle, 'fuel', []);
        $quantum = data_get($vehicle, 'quantum', []);

        $signature = data_get($vehicle, 'signature', []);
        $cooling = data_get($vehicle, 'cooling', []);
        $power = data_get($vehicle, 'power', []);
        $insurance = data_get($vehicle, 'insurance', []);
        $damageLimits = data_get($vehicle, 'damage_limits', []);
        $penetrationMultiplier = data_get($vehicle, 'penetration_multiplier', []);

        $ports = data_get($vehicle, 'ports', []);
        $hardpoints = data_get($vehicle, 'hardpoints', []);

        $parts = data_get($vehicle, 'parts', []);
        $flatParts = [];
        if (is_array($parts) && $parts !== []) {
            $flattenParts = function (array $parts, int $depth = 0) use (&$flattenParts): array {
                $rows = [];

                foreach ($parts as $part) {
                    $rows[] = [
                        'part' => $part,
                        'depth' => $depth,
                    ];

                    $children = $part['children'] ?? [];
                    if (is_array($children) && $children !== []) {
                        $rows = array_merge($rows, $flattenParts($children, $depth + 1));
                    }
                }

                return $rows;
            };

            $flatParts = $flattenParts($parts);
        }
        $mannedTurrets = data_get($vehicle, 'turrets.manned', []);
        $remoteTurrets = data_get($vehicle, 'turrets.remote', []);

        $components = data_get($vehicle, 'components', []);

        $shipMatrixName = data_get($vehicle, 'shipmatrix_name');

        $shipMatrixType = data_get($vehicle, 'type');
        $shipMatrixSize = data_get($vehicle, 'size');
        $shipMatrixProductionStatus = data_get($vehicle, 'production_status');
        $shipMatrixProductionNote = data_get($vehicle, 'production_note');
        $shipMatrixMsrp = data_get($vehicle, 'msrp');
        $shipMatrixPledgeUrl = data_get($vehicle, 'pledge_url');
        $shipMatrixLoaner = data_get($vehicle, 'loaner');
        $shipMatrixFoci = data_get($vehicle, 'foci', []);
        $shipMatrixSkus = data_get($vehicle, 'skus', []);

        $uuid = data_get($vehicle, 'uuid');
        $apiLink = data_get($vehicle, 'link');
        $webUrl = data_get($vehicle, 'web_url');
        $version = data_get($vehicle, 'version');
        $rawVehicleJson = json_encode($vehicle, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $systemsPayloads = [
            'Shield' => $shield,
            'Armor' => $armor,
            'Afterburner' => $afterburner,
            'Fuel' => $fuel,
            'Quantum' => $quantum,
            'Signature' => $signature,
            'Cooling' => $cooling,
            'Power' => $power,
            'Insurance' => $insurance,
            'Damage Limits' => $damageLimits,
            'Penetration Multiplier' => $penetrationMultiplier,
        ];
    @endphp

    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-2">
            <div class="breadcrumbs text-sm text-base-content/70">
                <ul>
                    <li><a href="{{ route('web.vehicles.index') }}">All Vehicles</a></li>
                    <li>
                        <a href="{{ route('web.vehicles.index', ['filter' => ['manufacturer' => $manufacturerCode]]) }}">{{ $manufacturerName }}</a>
                    </li>
                    <li>{{ $vehicleName }}</li>
                </ul>
            </div>
            <h1 class="text-2xl font-semibold tracking-tight">
                {{ $vehicleName }}
                @if ($className)
                    <span class="text-secondary">({{ $className }})</span>
                @endif
            </h1>
        </div>

        <x-resource-search
            title="Search vehicles"
            description="Find vehicles by name across the universe database."
            :route="route('web.vehicles.index')"
            placeholder="Search vehicle names"
        />

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="card border border-base-200 bg-base-100 shadow-sm">
                <div class="card-body gap-4">
                    <h2 class="card-title text-base">Overview</h2>
                    <dl class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Name</dt>
                            <dd class="text-sm font-medium">{{ $vehicleName }}</dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Game Name
                            </dt>
                            <dd class="text-sm font-medium">{{ $gameName ?? '-' }}</dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Class Name
                            </dt>
                            <dd class="text-sm font-medium">{{ $className ?? '-' }}</dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                                Manufacturer
                            </dt>
                            <dd class="text-sm font-medium">
                                @if ($manufacturerName)
                                    {{ $manufacturerName }}
                                    @if ($manufacturerCode)
                                        <span class="badge badge-outline ml-2">{{ $manufacturerCode }}</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Size</dt>
                            <dd class="text-sm font-medium">{{ $sizeClass ?? '-' }}</dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Career</dt>
                            <dd class="text-sm font-medium">{{ $career ?? '-' }}</dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Role</dt>
                            <dd class="text-sm font-medium">{{ $role ?? '-' }}</dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Type</dt>
                            <dd class="text-sm font-medium">
                                <div class="flex flex-wrap gap-2">
                                    @if($isGravlev)
                                        Gravlev
                                    @endif
                                    @if($isSpaceship)
                                        Spaceship
                                    @endif
                                    @if($isVehicle)
                                        Ground Vehicle
                                    @endif
                                </div>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="card border border-base-200 bg-base-100 shadow-sm">
                <div class="card-body gap-4">
                    <h2 class="card-title text-base">Description</h2>
                    @if ($shipMatrixDescription)
                        <div class="text-sm text-base-content/80">
                            {!! nl2br(e($shipMatrixDescription)) !!}
                        </div>
                    @else
                        <div class="text-sm text-base-content/70">No description available.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="card border border-base-200 bg-base-100 shadow-sm">
                <div class="card-body gap-4">
                    <h2 class="card-title text-base">Dimensions & Mass</h2>
                    <dl class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-1 sm:col-span-2">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Dimensions
                            </dt>
                            <dd class="text-sm font-medium">
                                @if ($length || $width || $height)
                                    {{ $length ?? '-' }} x {{ $width ?? '-' }} x {{ $height ?? '-' }} m
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Cross
                                Section
                            </dt>
                            <dd class="text-sm font-medium">
                                @if (data_get($crossSection, 'length') || data_get($crossSection, 'width') || data_get($crossSection, 'height'))
                                    {{ data_get($crossSection, 'length', '-') }}
                                    x {{ data_get($crossSection, 'width', '-') }}
                                    x {{ data_get($crossSection, 'height', '-') }}
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Mass Total
                            </dt>
                            <dd class="text-sm font-medium">
                                @if ($massTotal !== null)
                                    {{ $massTotal }} kg
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Mass Hull
                            </dt>
                            <dd class="text-sm font-medium">
                                @if ($massHull !== null)
                                    {{ $massHull }} kg
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Mass
                                Loadout
                            </dt>
                            <dd class="text-sm font-medium">
                                @if ($massLoadout !== null)
                                    {{ $massLoadout }} kg
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="card border border-base-200 bg-base-100 shadow-sm">
                <div class="card-body gap-4">
                    <h2 class="card-title text-base">Insurance</h2>
                    @if ($insurance !== [])
                        <dl class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Claim
                                    Time
                                </dt>
                                <dd class="text-sm font-medium">{{ number_format(data_get($insurance, 'claim_time'), 0, '.', ',') }}
                                    min
                                </dd>
                            </div>
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Expedite
                                    Time
                                </dt>
                                <dd class="text-sm font-medium">{{ number_format(data_get($insurance, 'expedite_time'), 0, '.', ',') }}
                                    min
                                </dd>
                            </div>
                            <div class="space-y-1 sm:col-span-2">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Expedite
                                    Cost
                                </dt>
                                <dd class="text-sm font-medium">{{ number_format(data_get($insurance, 'expedite_cost'), 0, '.', ',') }}
                                    aUEC
                                </dd>
                            </div>
                        </dl>
                    @else
                        <div class="text-sm text-base-content/70">No insurance data available.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="card border border-base-200 bg-base-100 shadow-sm">
                <div class="card-body gap-4">
                    <h2 class="card-title text-base">Signature</h2>
                    <p class="grow-0">Signature calculations assume full power usage of all components, even if they exceed the
                        available power segments.</p>
                    @if ($signature !== [])
                        <dl class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">IR
                                    Quantum
                                </dt>
                                <dd class="text-sm font-medium">{{ number_format(data_get($signature, 'ir_quantum'), 0, '.', ',') }}</dd>
                            </div>
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">IR
                                    Shields
                                </dt>
                                <dd class="text-sm font-medium">{{ number_format(data_get($signature, 'ir_shields'), 0, '.', ',') }}</dd>
                            </div>
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">EM
                                    Quantum
                                </dt>
                                <dd class="text-sm font-medium">{{ number_format(data_get($signature, 'em_quantum'), 0, '.', ',') }}</dd>
                            </div>
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">EM
                                    Shields
                                </dt>
                                <dd class="text-sm font-medium">{{ number_format(data_get($signature, 'em_shields'), 0, '.', ',') }}</dd>
                            </div>
                            <div class="space-y-1 sm:col-span-2">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">EM Per
                                    Segment
                                </dt>
                                <dd class="text-sm font-medium">{{ number_format(data_get($signature, 'em_per_segment'), 0, '.', ',') }}</dd>
                            </div>
                            @if (! empty(data_get($signature, 'em_groups_quantum')))
                                <div class="space-y-1 sm:col-span-2">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">EM
                                        Groups Quantum
                                    </dt>
                                    <dd class="text-sm">
                                        <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                                            <input type="checkbox"/>
                                            <div class="collapse-title text-xs font-medium">View Groups</div>
                                            <div class="collapse-content p-0">
                                                <table class="table table-auto w-full">
                                                    <thead>
                                                    <tr>
                                                        <th>System</th>
                                                        <th>Signature</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach (data_get($signature, 'em_groups_quantum') as $system => $group)
                                                        <tr>
                                                            <td>{{Str::headline($system)}}</td>
                                                            <td>{{ number_format($group, 0, '.', ',') }}</td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </dd>
                                </div>
                            @endif
                            @if (! empty(data_get($signature, 'em_groups_shields')))
                                <div class="space-y-1 sm:col-span-2">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">EM
                                        Groups Shields
                                    </dt>
                                    <dd class="text-sm">
                                        <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                                            <input type="checkbox"/>
                                            <div class="collapse-title text-xs font-medium">View Groups</div>
                                            <div class="collapse-content p-0">
                                                <table class="table table-auto w-full">
                                                    <thead>
                                                    <tr>
                                                        <th>System</th>
                                                        <th>Signature</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach (data_get($signature, 'em_groups_shields') as $system => $group)
                                                        <tr>
                                                            <td>{{Str::headline($system)}}</td>
                                                            <td>{{ number_format($group, 0, '.', ',') }}</td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    @else
                        <div class="text-sm text-base-content/70">No signature data available.</div>
                    @endif
                </div>
            </div>

            <div class="card border border-base-200 bg-base-100 shadow-sm">
                <div class="card-body gap-4">
                    <h2 class="card-title text-base">Cooling & Power</h2>
                    <div class="space-y-6">
                        <div>
                            <h3 class="mb-3 text-sm font-semibold">Cooling</h3>
                            @if ($cooling !== [])
                                <dl class="grid gap-4 sm:grid-cols-2">
                                    <div class="space-y-1 col-span-2">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                                            Generation Segments
                                        </dt>
                                        <dd class="text-sm font-medium">{{ number_format(data_get($cooling, 'generation_segments'), 0, '.', ',') }}</dd>
                                    </div>
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                                            Usage (Shields)
                                        </dt>
                                        <dd class="text-sm font-medium">{{ number_format(data_get($cooling, 'usage_shields_pct') * 100, 1, '.', ',') }}
                                            %
                                        </dd>
                                    </div>
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                                            Usage (Quantum)
                                        </dt>
                                        <dd class="text-sm font-medium">{{ number_format(data_get($cooling, 'usage_quantum_pct') * 100, 1, '.', ',') }}
                                            %
                                        </dd>
                                    </div>
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                                            Used Segments (Shields)
                                        </dt>
                                        <dd class="text-sm font-medium">{{ number_format(data_get($cooling, 'used_segments_shields'), 0, '.', ',') }}</dd>
                                    </div>
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                                            Used Segments (Quantum)
                                        </dt>
                                        <dd class="text-sm font-medium">{{ number_format(data_get($cooling, 'used_segments_quantum'), 0, '.', ',') }}</dd>
                                    </div>
                                    @if (! empty(data_get($cooling, 'used_segments_shields_grouped')))
                                        <div class="space-y-1 sm:col-span-2">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                                                Used Segments Shields
                                            </dt>
                                            <dd class="text-sm">
                                                <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                                                    <input type="checkbox"/>
                                                    <div class="collapse-title text-xs font-medium">View Groups</div>
                                                    <div class="collapse-content p-0">
                                                        <table class="table table-auto w-full">
                                                            <thead>
                                                            <tr>
                                                                <th>System</th>
                                                                <th>Used Segments</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            @foreach (data_get($cooling, 'used_segments_shields_grouped') as $system => $group)
                                                                <tr>
                                                                    <td>{{Str::headline($system)}}</td>
                                                                    <td>{{ number_format($group, 0, '.', ',') }}</td>
                                                                </tr>
                                                            @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </dd>
                                        </div>
                                    @endif
                                    @if (! empty(data_get($cooling, 'used_segments_quantum_grouped')))
                                        <div class="space-y-1 sm:col-span-2">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                                                Used Segments Quantum
                                            </dt>
                                            <dd class="text-sm">
                                                <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                                                    <input type="checkbox"/>
                                                    <div class="collapse-title text-xs font-medium">View Groups</div>
                                                    <div class="collapse-content p-0">
                                                        <table class="table table-auto w-full">
                                                            <thead>
                                                            <tr>
                                                                <th>System</th>
                                                                <th>Used Segments</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            @foreach (data_get($cooling, 'used_segments_quantum_grouped') as $system => $group)
                                                                <tr>
                                                                    <td>{{Str::headline($system)}}</td>
                                                                    <td>{{ number_format($group, 0, '.', ',') }}</td>
                                                                </tr>
                                                            @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </dd>
                                        </div>
                                    @endif
                                </dl>
                            @else
                                <div class="text-sm text-base-content/70">No cooling data available.</div>
                            @endif
                        </div>

                        <div class="divider"></div>

                        <div>
                            <h3 class="mb-3 text-sm font-semibold">Power</h3>
                            @if ($power !== [])
                                <dl class="grid gap-4 sm:grid-cols-2">
                                    <div class="space-y-1 col-span-2">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                                            Generation Segments
                                        </dt>
                                        <dd class="text-sm font-medium">{{ number_format(data_get($power, 'generation_segments'), 0, '.', ',') }}</dd>
                                    </div>
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                                            Used Segments (Shields)
                                        </dt>
                                        <dd class="text-sm font-medium">{{ number_format(data_get($power, 'used_segments_shields'), 0, '.', ',') }}</dd>
                                    </div>
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                                            Used Segments (Quantum)
                                        </dt>
                                        <dd class="text-sm font-medium">{{ number_format(data_get($power, 'used_segments_quantum'), 0, '.', ',') }}</dd>
                                    </div>
                                    @if (! empty(data_get($power, 'used_segments_grouped')))
                                        <div class="space-y-1 sm:col-span-2">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                                                Used Segments
                                            </dt>
                                            <dd class="text-sm">
                                                <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                                                    <input type="checkbox"/>
                                                    <div class="collapse-title text-xs font-medium">View Groups</div>
                                                    <div class="collapse-content p-0">
                                                        <table class="table table-auto w-full">
                                                            <thead>
                                                            <tr>
                                                                <th>System</th>
                                                                <th>Used Segments</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            @foreach (data_get($power, 'used_segments_grouped') as $system => $group)
                                                                <tr>
                                                                    <td>{{$system}}</td>
                                                                    <td>{{ number_format($group, 0, '.', ',') }}</td>
                                                                </tr>
                                                            @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </dd>
                                        </div>
                                    @endif
                                </dl>
                            @else
                                <div class="text-sm text-base-content/70">No power data available.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="card border border-base-200 bg-base-100 shadow-sm">
                <div class="card-body gap-4">
                    <h2 class="card-title text-base">Systems</h2>
                    @php
                        $systemEntries = [];
                        foreach ($systemsPayloads as $label => $payload) {
                            if (is_array($payload) && $payload !== []) {
                                $systemEntries[$label] = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                            }
                        }
                    @endphp
                    @if ($systemEntries !== [])
                        <div class="grid gap-3 md:grid-cols-2">
                            @foreach ($systemEntries as $label => $payload)
                                <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                                    <input type="checkbox"/>
                                    <div class="collapse-title text-sm font-semibold">{{ $label }}</div>
                                    <div class="collapse-content">
                                        <pre class="text-xs whitespace-pre-wrap">{{ $payload }}</pre>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-sm text-base-content/70">No systems data available.</div>
                    @endif
                </div>
            </div>

            <div class="card border border-base-200 bg-base-100 shadow-sm">
                <div class="card-body gap-4">
                    <h2 class="card-title text-base">Ports & Hardpoints</h2>
                    @php
                        $portEntries = is_array($ports) && $ports !== [] ? $ports : (is_array($hardpoints) ? $hardpoints : []);
                    @endphp
                    @if ($portEntries !== [])
                        <div class="space-y-3">
                            @foreach ($portEntries as $port)
                                <x-port-display :port="$port" />
                            @endforeach
                        </div>
                    @else
                        <div class="text-sm text-base-content/70">No ports available.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="card border border-base-200 bg-base-100 shadow-sm">
                <div class="card-body gap-4">
                    <h2 class="card-title text-base">Cargo Grids</h2>
                    @if (is_array($cargoGrids) && $cargoGrids !== [])
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <thead>
                                <tr>
                                    <th>Class</th>
                                    <th>SCU</th>
                                    <th>Capacity</th>
                                    <th>Flags</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($cargoGrids as $grid)
                                    <tr>
                                        <td class="whitespace-nowrap">{{ $grid['class_name'] ?? '-' }}</td>
                                        <td>{{ $grid['scu'] ?? '-' }}</td>
                                        <td>
                                            {{ $grid['capacity'] ?? '-' }}
                                            @if (! empty($grid['capacity_name']))
                                                {{ $grid['capacity_name'] }}
                                            @endif
                                        </td>
                                        <td>
                                            <div class="flex flex-wrap gap-1">
                                                @if (array_key_exists('is_open', $grid))
                                                    <span
                                                        class="badge badge-outline text-sm text-nowrap">Open: {{ $grid['is_open'] ? 'Yes' : 'No' }}</span>
                                                @endif
                                                @if (array_key_exists('is_external', $grid))
                                                    <span
                                                        class="badge badge-outline text-sm text-nowrap">External: {{ $grid['is_external'] ? 'Yes' : 'No' }}</span>
                                                @endif
                                                @if (array_key_exists('is_closed', $grid))
                                                    <span
                                                        class="badge badge-outline text-sm text-nowrap">Closed: {{ $grid['is_closed'] ? 'Yes' : 'No' }}</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-sm text-base-content/70">No cargo grids available.</div>
                    @endif
                </div>
            </div>

            <div class="card border border-base-200 bg-base-100 shadow-sm">
                <div class="card-body gap-4">
                    <h2 class="card-title text-base">Parts</h2>
                    @if ($flatParts !== [])
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <thead>
                                <tr>
                                    <th>Part</th>
                                    <th>Damage Max</th>
                                    <th>Children</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($flatParts as $partEntry)
                                    @php
                                        $part = $partEntry['part'];
                                        $depth = $partEntry['depth'];
                                    @endphp
                                    <tr>
                                        <td class="whitespace-nowrap">
                                            <span class="block" style="padding-left: {{ $depth * 1.25 }}rem;">
                                                {{ $part['display_name'] ?? $part['name'] ?? '-' }}
                                            </span>
                                        </td>
                                        <td>{{ $part['damage_max'] ?? '-' }}</td>
                                        <td>{{ ! empty($part['children']) ? count($part['children']) : '-' }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-sm text-base-content/70">No parts available.</div>
                    @endif
                </div>
            </div>
        </div>


        <div class="grid gap-6 lg:grid-cols-2">
            <div class="card border border-base-200 bg-base-100 shadow-sm">
                <div class="card-body gap-4">
                    <h2 class="card-title text-base">Turrets</h2>
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm font-semibold">Manned</h3>
                            @if (is_array($mannedTurrets) && $mannedTurrets !== [])
                                <div class="overflow-x-auto">
                                    <table class="table table-sm">
                                        <thead>
                                        <tr>
                                            <th>Size</th>
                                            <th>Fixed</th>
                                            <th>Weapon Sizes</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($mannedTurrets as $turret)
                                            <tr>
                                                <td>{{ $turret['size'] ?? '-' }}</td>
                                                <td>{{ array_key_exists('fixed', $turret) ? ($turret['fixed'] ? 'Yes' : 'No') : '-' }}</td>
                                                <td>{{ ! empty($turret['weapon_sizes']) ? implode(', ', $turret['weapon_sizes']) : '-' }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-sm text-base-content/70">No manned turrets.</div>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold">Remote</h3>
                            @if (is_array($remoteTurrets) && $remoteTurrets !== [])
                                <div class="overflow-x-auto">
                                    <table class="table table-sm">
                                        <thead>
                                        <tr>
                                            <th>Size</th>
                                            <th>Fixed</th>
                                            <th>Weapon Sizes</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($remoteTurrets as $turret)
                                            <tr>
                                                <td>{{ $turret['size'] ?? '-' }}</td>
                                                <td>{{ array_key_exists('fixed', $turret) ? ($turret['fixed'] ? 'Yes' : 'No') : '-' }}</td>
                                                <td>{{ ! empty($turret['weapon_sizes']) ? implode(', ', $turret['weapon_sizes']) : '-' }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-sm text-base-content/70">No remote turrets.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if ($shipMatrixName || $shipMatrixDescription || $shipMatrixType || $shipMatrixSize || $shipMatrixProductionStatus)
                <div class="card border border-base-200 bg-base-100 shadow-sm">
                    <div class="card-body gap-4">
                        <h2 class="card-title text-base">Ship Matrix</h2>
                        <dl class="grid gap-4 sm:grid-cols-2">

                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">MSRP</dt>
                                <dd class="text-sm font-medium">{{ $shipMatrixMsrp ?? '-' }}$</dd>
                            </div>
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Pledge URL
                                </dt>
                                <dd class="text-sm font-medium">
                                    @if ($shipMatrixPledgeUrl)
                                        <a href="{{ $shipMatrixPledgeUrl }}" class="link link-primary">View</a>
                                    @else
                                        -
                                    @endif
                                </dd>
                            </div>
                            <div class="space-y-1 sm:col-span-2">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Loaner</dt>
                                <dd class="text-sm font-medium">
                                    @if (is_array($shipMatrixLoaner) && $shipMatrixLoaner !== [])
                                        <div class="flex flex-wrap gap-2">
                                            <table class="table table-sm">
                                                <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>View</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach ($shipMatrixLoaner as $loaner)
                                                    <tr>
                                                        <td class="whitespace-nowrap">{{ $loaner['name'] ?? '-' }}</td>


                                                        <td><a href="{{ $loaner['link'] }}"
                                                               class="link link-primary">View</a></td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        -
                                    @endif
                                </dd>
                            </div>
                            <div class="space-y-1 sm:col-span-2">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">SKUs</dt>
                                <dd class="text-sm font-medium">
                                    @if (is_array($shipMatrixSkus) && $shipMatrixSkus !== [])
                                        <pre
                                            class="text-xs whitespace-pre-wrap">{{ json_encode($shipMatrixSkus, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                    @else
                                        -
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            @endif
        </div>



        <div class="grid gap-6 lg:grid-cols-2">
            <div class="card border border-base-200 bg-base-100 shadow-sm">
                <div class="card-body gap-4">
                    <h2 class="card-title text-base">Technical</h2>
                    <dl class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">UUID</dt>
                            <dd class="text-sm font-medium">{{ $uuid ?? '-' }}</dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Version
                                Code
                            </dt>
                            <dd class="text-sm font-medium">{{ $version ?? '-' }}</dd>
                        </div>
                        <div class="space-y-1 sm:col-span-2">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Web URL</dt>
                            <dd class="text-sm font-medium">
                                @if ($webUrl)
                                    <a href="{{ $webUrl }}" class="link link-primary">Open</a>
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                        <div class="space-y-1 sm:col-span-2">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">API Link</dt>
                            <dd class="text-sm font-medium">
                                @if ($apiLink)
                                    <a href="{{ $apiLink }}" class="link link-primary">{{ $apiLink }}</a>
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="card border border-base-200 bg-base-100 shadow-sm">
                <div class="card-body gap-4">
                    <h2 class="card-title text-base">All Data</h2>
                    <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                        <input type="checkbox"/>
                        <div class="collapse-title text-sm font-semibold">Raw Vehicle Payload</div>
                        <div class="collapse-content">
                            <pre class="text-xs whitespace-pre-wrap">{{ $rawVehicleJson }}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
