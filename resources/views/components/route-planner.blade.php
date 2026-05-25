<div x-data="routePlanner()" class="card card-border bg-base-100 shadow">
    <div class="card-body gap-4 p-4 sm:p-5">
        @if($slot ?? false)
            {{ $slot }}
        @endif

        <template x-if="!error">
            <div>
                <div class="border-b border-base-200 pb-1 mb-1">
                    <h3 class="text-xs font-medium uppercase tracking-wider text-muted">Configuration</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="form-control relative">
                        <label class="label py-1">
                            <span class="label-text text-xs text-subtle">Vehicle</span>
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                x-model="shipQuery"
                                @input="handleShipInput()"
                                @focus="if (shipResults.length) showShipDropdown = true"
                                @blur="closeShipDropdown()"
                                placeholder="e.g. Gladius, Carrack..."
                                class="input input-sm input-bordered w-full border-1 pl-3 pr-8"
                            >
                            <template x-if="selectedShip">
                                <button
                                    type="button"
                                    class="btn btn-xs btn-circle btn-ghost absolute right-1 top-1"
                                    @click="clearShip()"
                                >✕</button>
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
                            <p class="text-xs text-warning mt-1">No quantum drive hardpoint</p>
                        </template>
                    </div>

                    <div class="form-control">
                        <label class="label py-1">
                            <span class="label-text text-xs text-subtle">
                                Quantum Drive
                                <template x-if="qdMaxSize">
                                    <span class="text-muted" x-text="`S${qdMinSize ?? 1}`"></span>
                                </template>
                            </span>
                        </label>
                        <select
                            x-model="selectedQdUuid"
                            @change="onQdSelect()"
                            class="select select-sm select-bordered border-1 w-full"
                            :disabled="allQds.length === 0 || (selectedShip && !hasQdHardpoint)"
                        >
                            <option value="" x-text="selectedShip && !hasQdHardpoint ? 'No quantum drive hardpoint' : 'Select quantum drive...'"></option>
                            <template x-for="qd in filteredQds" :key="qd.uuid">
                                <option :value="qd.uuid" x-text="qdLabel(qd)"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div class="border-b border-base-200 pb-1 mb-1 mt-4">
                    <h3 class="text-xs font-medium uppercase tracking-wider text-muted">Fuel Tank</h3>
                </div>

                <template x-if="!selectedShip && !selectedQd">
                    <p class="text-xs text-muted">Select a ship or quantum drive to configure fuel.</p>
                </template>

                <template x-if="selectedShip || selectedQd">
                    <div class="flex items-center gap-3">
                        <template x-if="selectedShip && fuelTankCapacity">
                            <span class="text-xs text-subtle shrink-0">
                                Tank
                                <strong class="font-semibold text-base-content" x-text="fuelTankCapacity.toFixed(2) + ' SCU'"></strong>
                                <span :class="tankFill < 100 ? 'text-muted' : 'opacity-0'">
                                    (<span x-text="effectiveTankCapacity.toFixed(2)"></span> SCU)
                                </span>
                            </span>
                        </template>
                        <template x-if="!selectedShip">
                            <div class="flex items-center gap-2">
                                <label class="text-xs text-subtle shrink-0">Tank</label>
                                <input
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    placeholder="SCU"
                                    x-model="manualFuelTank"
                                    class="input input-xs input-bordered w-24 border-1 tabular-nums"
                                >
                                <span class="text-xs text-muted">SCU</span>
                            </div>
                        </template>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-muted">Fill</span>
                            <input
                                type="range"
                                min="1"
                                max="100"
                                x-model.number="tankFill"
                                class="range range-xs range-primary w-20"
                            >
                            <span class="text-xs tabular-nums text-subtle" x-text="tankFill + '%'"></span>
                        </div>
                    </div>
                </template>

                <div class="border-b border-base-200 pb-1 mb-1 mt-4">
                    <h3 class="text-xs font-medium uppercase tracking-wider text-muted">Trip</h3>
                </div>

                <div class="space-y-1">
                    <template x-for="(wp, idx) in waypoints" :key="wp.id">
                        <div class="flex items-center gap-2">
                            <span class="badge badge-ghost badge-sm tabular-nums shrink-0 w-5 justify-center" x-text="idx + 1"></span>

                            <div class="join flex-1 min-w-0">
                                <select
                                    x-model="wp.system"
                                    @change="onSystemChange(wp)"
                                    class="select select-sm select-bordered border-1 join-item w-28"
                                    :disabled="loading"
                                >
                                    <option value="" disabled>System</option>
                                    <template x-for="sys in systems" :key="sys">
                                        <option :value="sys" x-text="capitalize(sys)"></option>
                                    </template>
                                </select>

                                <select
                                    x-model="wp.uuid"
                                    class="select select-sm select-bordered border-1 join-item flex-1 min-w-0"
                                    :disabled="loading || !wp.system"
                                >
                                    <option value="" disabled x-text="wp.system ? 'Select location' : 'Pick system first'"></option>
                                    <template x-for="group in entitiesGroupedByType(wp.system)" :key="group.type">
                                        <optgroup :label="group.label">
                                            <template x-for="e in group.items" :key="e.uuid">
                                                <option :value="e.uuid" x-text="e.name"></option>
                                            </template>
                                        </optgroup>
                                    </template>
                                </select>
                            </div>

                            <button
                                type="button"
                                class="btn btn-xs btn-ghost btn-circle text-error/50 shrink-0"
                                :disabled="waypoints.length <= 2"
                                @click="removeWaypoint(idx)"
                            >✕</button>
                        </div>
                    </template>

                    <div class="flex justify-center pt-1">
                        <button
                            type="button"
                            class="btn btn-sm btn-outline btn-primary gap-1"
                            @click="addWaypoint()"
                        >
                            + Add Stop
                        </button>
                    </div>
                </div>

                <template x-if="legs.length > 0">
                    <div class="mt-4">
                        <div class="border-b border-base-200 pb-1 mb-2">
                            <h3 class="text-xs font-medium uppercase tracking-wider text-muted">Route</h3>
                        </div>

                        <div class="overflow-x-auto">
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
                                    <template x-for="(leg, idx) in legs" :key="idx">
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
                                                        <span class="text-subtle" x-text="capitalize(leg.fromSystem) + ' → ' + capitalize(leg.toSystem)"></span>
                                                    </span>
                                                </template>
                                                <template x-if="leg.type === 'no_route'">
                                                    <span class="text-xs text-error">No route found</span>
                                                </template>
                                                <template x-if="leg.type === 'qt'">
                                                    <span class="flex items-center gap-1.5 text-xs">
                                                        <span class="text-subtle" x-text="leg.fromName"></span>
                                                        <span class="text-muted">→</span>
                                                        <span class="font-medium" x-text="leg.toName"></span>
                                                    </span>
                                                </template>
                                            </td>
                                            <td class="text-right tabular-nums text-subtle" x-text="formatDistance(leg.distanceGm)"></td>
                                            <td class="text-right tabular-nums font-semibold" x-text="leg.timeFormatted" x-show="hasQuantumData"></td>
                                            <td class="text-right tabular-nums font-semibold" x-text="leg.fuelFormatted" x-show="fuelConsumptionPerGm"></td>
                                            <td
                                                class="text-right tabular-nums"
                                                x-show="effectiveTankCapacity"
                                                :class="{
                                                    'text-error font-semibold': leg.impossible,
                                                    'text-warning font-semibold': leg.needsRefuel && !leg.impossible,
                                                    'text-subtle': !leg.impossible && !leg.needsRefuel,
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
