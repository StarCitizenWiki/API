@extends('layouts.app')

@section('title', 'Route Planner | Star Citizen Wiki API')
@section('meta_description', 'Plan multi-system quantum travel routes. Calculate distance, time, fuel, and refuel requirements across Stanton, Pyro, and Nyx.')

@push('meta')
    <meta property="og:title" content="Route Planner | Star Citizen Wiki API">
    <meta property="og:description" content="Plan multi-system quantum travel routes. Calculate distance, time, fuel, and refuel requirements across Stanton, Pyro, and Nyx.">
@endpush

@section('content')
    <div class="mx-auto flex max-w-6xl flex-col gap-4 py-2">
        <section class="flex flex-col gap-2">
            <h1 class="text-2xl font-bold sm:text-3xl">Route Planner (WIP)</h1>
        </section>

        <div x-data="routePlanner()" class="grid grid-cols-1 lg:grid-cols-3 gap-4">

            <div class="card card-border bg-base-100 shadow">
                <div class="card-body">
                    <template x-if="!error">
                        <div>
                            <h2 class="card-title">Vehicle Setup</h2>

                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">Vehicle</legend>
                                <div class="relative">
                                    <input
                                        type="text"
                                        x-model="shipQuery"
                                        @input="handleShipInput()"
                                        @focus="if (shipResults.length) showShipDropdown = true"
                                        @blur="closeShipDropdown()"
                                        placeholder="e.g. Gladius, Carrack..."
                                        class="input input-sm w-full"
                                    >
                                    <template x-if="selectedShip">
                                        <button
                                            type="button"
                                            class="btn btn-xs btn-circle btn-ghost absolute right-1 top-1/2 -translate-y-1/2"
                                            @click="clearShip()"
                                        >&times;</button>
                                    </template>

                                    <div
                                        x-show="showShipDropdown"
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="opacity-0 -translate-y-1"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="opacity-100"
                                        x-transition:leave-end="opacity-0"
                                        class="absolute z-50 left-0 right-0 top-full mt-1 bg-base-100 rounded-box border border-base-300 shadow-lg max-h-60 overflow-y-auto"
                                    >
                                        <template x-if="shipLoading">
                                            <div class="p-3 text-xs text-center text-muted">Searching...</div>
                                        </template>
                                        <template x-for="result in shipResults" :key="result.uuid">
                                            <button
                                                type="button"
                                                class="flex items-center justify-between w-full px-3 py-2 hover:bg-base-200 text-left text-sm"
                                                @mousedown.prevent="selectShip(result)"
                                            >
                                                <span x-text="result.name ?? result.display_name"></span>
                                                <span class="text-xs text-muted" x-text="result.manufacturer?.name ?? ''"></span>
                                            </button>
                                        </template>
                                        <template x-if="!shipLoading && shipResults.length === 0 && shipQuery.length >= 2">
                                            <div class="p-3 text-xs text-center text-muted">No results</div>
                                        </template>
                                    </div>
                                </div>
                                <template x-if="selectedShip && !hasQdHardpoint">
                                    <p class="label text-warning">No quantum drive hardpoint</p>
                                </template>
                            </fieldset>

                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">
                                    Quantum Drive
                                    <template x-if="qdMaxSize">
                                        <span x-text="`S${qdMinSize ?? 1}`"></span>
                                    </template>
                                </legend>
                                <select
                                    x-model="selectedQdUuid"
                                    @change="onQdSelect()"
                                    class="select select-sm w-full"
                                    :disabled="allQds.length === 0 || (selectedShip && !hasQdHardpoint)"
                                >
                                    <option value="" x-text="selectedShip && !hasQdHardpoint ? 'No quantum drive hardpoint' : 'Select quantum drive...'"></option>
                                    <template x-for="qd in filteredQds" :key="qd.uuid">
                                        <option :value="qd.uuid" x-text="qdLabel(qd)"></option>
                                    </template>
                                </select>
                            </fieldset>

                            <template x-if="selectedShip || selectedQd">
                                <div>
                                    <div class="divider my-2"></div>
                                    <template x-if="selectedShip && fuelTankCapacity">
                                        <span class="text-sm">
                                            Tank
                                            <strong x-text="fuelTankCapacity.toFixed(2) + ' SCU'"></strong>
                                            <span :class="tankFill < 100 ? 'opacity-70' : 'opacity-0'">
                                                (<span x-text="effectiveTankCapacity.toFixed(2)"></span> SCU)
                                            </span>
                                        </span>
                                    </template>
                                    <template x-if="!selectedShip">
                                        <label class="input input-sm w-full">
                                            Tank
                                            <input
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                placeholder="SCU"
                                                x-model="manualFuelTank"
                                                class="grow tabular-nums"
                                            >
                                            SCU
                                        </label>
                                    </template>
                                    <div class="w-full mt-2">
                                        <span>
                                            Fill level: <span x-text="tankFill + '%'"></span>
                                        </span>
                                        <input
                                            type="range"
                                            min="1"
                                            max="100"
                                            x-model.number="tankFill"
                                            class="range range-xs range-primary flex-1 w-full"
                                        >
                                        <div class="flex justify-between px-2.5 mt-2 text-xs">
                                            <span>0%</span>
                                            <span>50%</span>
                                            <span>100%</span>
                                        </div>
                                    </div>

                                </div>
                            </template>

                            <div class="divider my-0"></div>

                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">Trip Mode</legend>
                                <div class="join w-full">
                                    <button
                                        type="button"
                                        class="join-item btn btn-sm flex-1"
                                        :class="mode === 'waypoint' ? 'btn-primary' : ''"
                                        @click="setMode('waypoint')"
                                    >Waypoints</button>
                                    <button
                                        type="button"
                                        class="join-item btn btn-sm flex-1"
                                        :class="mode === 'cargo' ? 'btn-primary' : ''"
                                        @click="setMode('cargo')"
                                    >Cargo Run</button>
                                </div>
                            </fieldset>
                        </div>
                    </template>

                    <template x-if="error">
                        <div class="alert alert-error text-sm">Failed to load position data.</div>
                    </template>
                </div>
            </div>

            <!-- Card 2: Route -->
            <div class="card card-border bg-base-100 shadow lg:col-span-2">
                <div class="card-body">
                    <template x-if="!error">
                        <div>
                            <h2 class="card-title">Route</h2>
                            <template x-if="mode === 'cargo'">
                                @include('tools.partials.route-cargo-row', [
                                    'class' => 'mb-2',
                                    'primaryLegend' => 'Start from',
                                    'primaryOptional' => true,
                                    'primarySlotExpression' => 'cargoStartPoint',
                                    'primaryPlaceholder' => 'Current location...',
                                    'secondaryLegend' => 'Return to',
                                    'secondaryOptional' => true,
                                    'secondarySlotExpression' => 'cargoReturnPoint',
                                    'secondaryPlaceholder' => 'Final destination...',
                                ])
                            </template>

                            <!-- Waypoint mode -->
                            <template x-if="mode === 'waypoint'">
                            <div class="space-y-1">
                                <template x-for="(wp, idx) in waypoints" :key="wp.id">
                                    <div class="flex items-center gap-2">
                                        <span class="badge badge-ghost badge-sm tabular-nums shrink-0 w-5 justify-center" x-text="idx + 1"></span>

                                        @include('tools.partials.route-location-input', [
                                            'slotExpression' => 'wp',
                                            'placeholder' => 'Search location...',
                                            'active' => true,
                                        ])

                                        <button
                                            type="button"
                                            class="btn btn-xs btn-ghost btn-circle shrink-0 text-error/50 hover:text-error"
                                            :disabled="waypoints.length <= 2"
                                            @click="removeWaypoint(idx)"
                                        >
                                            <i data-lucide="trash-2" class="size-3.5"></i>
                                        </button>
                                    </div>
                                </template>

                                <div class="flex justify-center items-center gap-2 pt-1">
                                    <button type="button" class="btn btn-sm btn-outline btn-primary gap-1" @click="addWaypoint()">
                                        + Add Waypoint
                                    </button>
                                </div>
                            </div>
                            </template>

                            <!-- Cargo Run missions -->
                            <template x-if="mode === 'cargo'">
                            <div class="space-y-2">
                                <template x-for="(mission, mIdx) in cargoMissions" :key="mission.id">
                                    @include('tools.partials.route-cargo-row', [
                                        'badgeExpression' => 'mIdx + 1',
                                        'primaryLegend' => 'Pickup',
                                        'primarySlotExpression' => 'mission.pickup',
                                        'primaryPlaceholder' => 'Pickup from...',
                                        'secondaryLegend' => 'Delivery',
                                        'secondarySlotExpression' => 'mission.delivery',
                                        'secondaryPlaceholder' => 'Deliver to...',
                                        'amountExpression' => 'mission.scu',
                                        'removeExpression' => 'removeCargoMission(mIdx)',
                                    ])
                                </template>

                                <div class="flex justify-center items-center gap-2 pt-1">
                                    <button type="button" class="btn btn-sm btn-outline btn-primary gap-1" @click="addCargoMission()">
                                        + Add Mission
                                    </button>
                                </div>
                            </div>
                            </template>

                            <template x-if="enrichedLegs.length === 0">
                                <p class="mt-4 text-sm text-muted">
                                    <span x-text="mode === 'cargo' ? 'Add pickup and delivery locations to optimize a cargo run.' : 'Select at least two locations to calculate a route.'"></span>
                                </p>
                            </template>

                            <!-- Route output -->
                            <template x-if="enrichedLegs.length > 0">
                                <div class="mt-4">
                                    <div class="border-b border-base-200 pb-1 mb-2">
                                        <h3 class="text-xs font-medium uppercase tracking-wider text-muted">
                                            <span x-text="mode === 'cargo' ? 'Optimized Route' : 'Route'"></span>
                                            <template x-if="mode === 'cargo'">
                                                <span class="text-muted font-normal" x-text="tripSummary.missionCount + ' missions, ' + tripSummary.stopCount + ' stops' + (tripSummary.hasScuData ? ', ' + tripSummary.totalScu.toFixed(1) + ' SCU' : '')"></span>
                                            </template>
                                        </h3>
                                    </div>

                                    <!-- Cargo stop list -->
                                    <template x-if="mode === 'cargo'">
                                        <div class="space-y-0">
                                            <template x-for="(stop, idx) in cargoRoute.cargoPlan" :key="stop.uuid">
                                                <div class="flex items-start gap-2 py-1.5" :class="idx > 0 ? 'border-t border-base-200' : ''">
                                                    <span class="badge badge-ghost badge-sm tabular-nums shrink-0 w-5 justify-center mt-0.5" x-text="idx + 1"></span>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="text-sm font-medium">
                                                            <span x-text="stop.name"></span>
                                                            <template x-if="cargoMultiSystem">
                                                                <span class="text-xs text-muted font-normal" x-text="'(' + (entityMap.get(stop.uuid)?.system ?? '') + ')'" ></span>
                                                            </template>
                                                            <template x-if="stop.hasScu">
                                                                <span class="text-xs text-muted font-normal ml-2">
                                                                    <template x-if="stop.scuOut > 0"><span class="text-warning" x-text="'-' + stop.scuOut.toFixed(1) + ' '"></span></template>
                                                                    <template x-if="stop.scuIn > 0"><span class="text-success" x-text="'+' + stop.scuIn.toFixed(1) + ' '"></span></template>
                                                                    <span x-text="stop.scuTotal.toFixed(1) + ' SCU'"></span>
                                                                </span>
                                                            </template>
                                                        </div>
                                                        <template x-if="stop.pickups.length > 0">
                                                            <div class="flex flex-wrap gap-1 mt-0.5">
                                                                <template x-for="p in stop.pickups" :key="'p' + p.missionIdx">
                                                                    <span class="badge badge-success badge-xs">Pickup #<span x-text="p.missionIdx"></span></span>
                                                                </template>
                                                            </div>
                                                        </template>
                                                        <template x-if="stop.deliveries.length > 0">
                                                            <div class="flex flex-wrap gap-1 mt-0.5">
                                                                <template x-for="d in stop.deliveries" :key="'d' + d.missionIdx">
                                                                    <span class="badge badge-warning badge-xs">Deliver #<span x-text="d.missionIdx"></span></span>
                                                                </template>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    <!-- Legs table -->
                                    <div class="overflow-x-auto" :class="mode === 'cargo' ? 'mt-2' : ''">
                                        <table class="table table-xs">
                                            <thead>
                                                <tr>
                                                    <th>Leg</th>
                                                    <th class="text-right">Distance</th>
                                                    <th class="text-right" x-show="hasQuantumData">Time</th>
                                                    <th class="text-right" x-show="fuelConsumptionPerGm">Fuel</th>
                                                    <th class="text-right" x-show="effectiveTankCapacity">Tank</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <template x-for="(leg, idx) in enrichedLegs" :key="idx">
                                                    <tr
                                                        :class="{
                                                            'bg-warning/5 border-l-2 border-l-warning': leg.type === 'jp',
                                                            'bg-error/5': leg.type === 'no_route',
                                                        }"
                                                    >
                                                        <td>
                                                            <template x-if="leg.type === 'jp'">
                                                                <span class="flex items-center gap-1.5 text-xs">
                                                                    <span class="badge badge-warning badge-xs">JP</span>
                                                                    <span class="text-base-content/50" x-text="capitalize(leg.fromSystem) + ' \u2192 ' + capitalize(leg.toSystem)"></span>
                                                                </span>
                                                            </template>
                                                            <template x-if="leg.type === 'no_route'">
                                                                <span class="text-xs text-error">No route found</span>
                                                            </template>
                                                            <template x-if="leg.type === 'qt'">
                                                                <span class="flex items-center gap-1.5 text-xs">
                                                                    <span class="text-base-content/50" x-text="leg.fromName"></span>
                                                                    <span class="text-muted">&rarr;</span>
                                                                    <span class="font-medium" x-text="leg.toName"></span>
                                                                </span>
                                                            </template>
                                                        </td>
                                                        <td class="text-right tabular-nums text-base-content/50" x-text="formatDistance(leg.distanceGm)"></td>
                                                        <td class="text-right tabular-nums font-semibold" x-text="leg.timeFormatted" x-show="hasQuantumData"></td>
                                                        <td class="text-right tabular-nums font-semibold" x-text="leg.fuelFormatted" x-show="fuelConsumptionPerGm"></td>
                                                        <td
                                                            class="text-right tabular-nums"
                                                            x-show="effectiveTankCapacity"
                                                            :class="{
                                                                'text-error font-semibold': leg.impossible,
                                                                'text-warning font-semibold': leg.needsRefuel && !leg.impossible,
                                                                'text-base-content/50': !leg.impossible && !leg.needsRefuel,
                                                            }"
                                                        >
                                                            <template x-if="leg.impossible">
                                                                <span class="badge badge-error badge-xs mr-1">Exceeds tank</span>
                                                            </template>
                                                            <template x-if="leg.needsRefuel && !leg.impossible">
                                                                <span class="badge badge-warning badge-xs mr-1" x-text="'Refuel at ' + leg.refuelAt"></span>
                                                            </template>
                                                            <span x-text="leg.tankRemaining !== null ? formatFuel(leg.tankRemaining) : '-'"></span>
                                                        </td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                            <tfoot>
                                                <tr class="bg-base-200 font-semibold">
                                                    <td>Total</td>
                                                    <td class="text-right tabular-nums" x-text="formatDistance(tripSummary.distanceGm)"></td>
                                                    <td class="text-right tabular-nums" x-text="tripSummary.timeFormatted" x-show="hasQuantumData"></td>
                                                    <td class="text-right tabular-nums" x-text="tripSummary.fuelFormatted" x-show="fuelConsumptionPerGm"></td>
                                                    <td
                                                        class="text-right tabular-nums font-semibold"
                                                        x-show="effectiveTankCapacity"
                                                        :class="refuelInfo?.impossible ? 'text-error' : refuelInfo?.refuels > 0 ? 'text-warning' : 'text-success'"
                                                        x-text="refuelInfo ? (refuelInfo.impossible ? 'Impossible' : formatFuel(refuelInfo.remainingFuel) + ' remaining') : '-'"
                                                    ></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <template x-if="refuelInfo && refuelInfo.impossible">
                                        <div class="mt-2 alert alert-error text-xs py-2">
                                            <span>Route exceeds fuel tank capacity</span>
                                        </div>
                                    </template>
                                    <template x-if="refuelInfo && !refuelInfo.impossible && refuelInfo.refuels > 0">
                                        <div class="mt-2 alert alert-warning text-xs py-2">
                                            <span><strong x-text="refuelInfo.refuels"></strong> refuel(s) required</span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="error">
                        <div class="alert alert-error text-sm">Failed to load position data.</div>
                    </template>
                </div>
            </div>

        </div>
    </div>
@endsection
