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
                            <span class="label-text text-xs text-base-content/50">Vehicle</span>
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                x-model="shipQuery"
                                @input="handleShipInput()"
                                @focus="if (shipResults.length) showShipDropdown = true"
                                @blur="closeShipDropdown()"
                                placeholder="e.g. Gladius, Carrack..."
                                class="input input-sm input-bordered w-full"
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
                            <p class="text-xs text-warning mt-1">No quantum drive hardpoint</p>
                        </template>
                    </div>

                    <div class="form-control">
                        <label class="label py-1">
                            <span class="label-text text-xs text-base-content/50">
                                Quantum Drive
                                <template x-if="qdMaxSize">
                                    <span class="text-muted" x-text="`S${qdMinSize ?? 1}`"></span>
                                </template>
                            </span>
                        </label>
                        <select
                            x-model="selectedQdUuid"
                            @change="onQdSelect()"
                            class="select select-sm select-bordered w-full"
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
                            <span class="text-xs text-base-content/70 shrink-0">
                                Tank
                                <strong class="font-semibold text-base-content" x-text="fuelTankCapacity.toFixed(2) + ' SCU'"></strong>
                                <span :class="tankFill < 100 ? 'text-muted' : 'opacity-0'">
                                    (<span x-text="effectiveTankCapacity.toFixed(2)"></span> SCU)
                                </span>
                            </span>
                        </template>
                        <template x-if="!selectedShip">
                            <div class="flex items-center gap-2">
                                <label class="text-xs text-base-content/70 shrink-0">Tank</label>
                                <input
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    placeholder="SCU"
                                    x-model="manualFuelTank"
                                    class="input input-xs input-bordered w-24 tabular-nums"
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
                            <span class="text-xs tabular-nums text-base-content/70" x-text="tankFill + '%'"></span>
                        </div>
                    </div>
                </template>

                <div class="border-b border-base-200 pb-1 mb-1 mt-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-medium uppercase tracking-wider text-muted">Trip</h3>
                        <div class="join join-xs">
                            <button
                                type="button"
                                class="join-item btn btn-xs"
                                :class="mode === 'stellar' ? 'btn-primary' : ''"
                                @click="setMode('stellar')"
                            >Stellar</button>
                            <button
                                type="button"
                                class="join-item btn btn-xs"
                                :class="mode === 'interstellar' ? 'btn-primary' : ''"
                                @click="setMode('interstellar')"
                            >Interstellar</button>
                            <button
                                type="button"
                                class="join-item btn btn-xs"
                                :class="mode === 'cargo' ? 'btn-primary' : ''"
                                @click="setMode('cargo')"
                            >Cargo Run</button>
                        </div>
                    </div>
                </div>

                <template x-if="mode === 'stellar' || mode === 'cargo'">
                    <div class="form-control mb-2">
                        <select
                            x-model="stellarSystem"
                            @change="onStellarSystemChange()"
                            class="select select-sm select-bordered w-full"
                            :disabled="loading"
                        >
                            <option value="" disabled>Select star system...</option>
                            <template x-for="sys in systems" :key="sys">
                                <option :value="sys" x-text="capitalize(sys)"></option>
                            </template>
                        </select>
                    </div>
                </template>

                <!-- Stellar / Interstellar waypoints -->
                <template x-if="mode !== 'cargo'">
                <div class="space-y-1">
                    <template x-for="(wp, idx) in waypoints" :key="wp.id">
                        <div class="flex items-center gap-2">
                            <span class="badge badge-ghost badge-sm tabular-nums shrink-0 w-5 justify-center" x-text="idx + 1"></span>

                            <template x-if="mode === 'interstellar'">
                                <select
                                    x-model="wp.system"
                                    @change="onSystemChange(wp)"
                                    class="select select-sm select-bordered w-28 shrink-0"
                                    :disabled="loading"
                                >
                                    <option value="" disabled>System</option>
                                    <template x-for="sys in systems" :key="sys">
                                        <option :value="sys" x-text="capitalize(sys)"></option>
                                    </template>
                                </select>
                            </template>

                            <div class="relative flex-1 min-w-0">
                                <input
                                    type="text"
                                    x-model="wp.query"
                                    @input="handleLocationInput(wp)"
                                    @focus="onLocationFocus(wp)"
                                    @blur="closeLocationDropdown(wp)"
                                    :placeholder="!wpSystem(wp) ? (mode === 'stellar' ? 'Select system first' : 'Pick system first') : 'Search location...'"
                                    class="input input-sm input-bordered w-full pr-7"
                                    :disabled="loading || !wpSystem(wp)"
                                >
                                <template x-if="wp.uuid">
                                    <button
                                        type="button"
                                        class="btn btn-xs btn-circle btn-ghost absolute right-1 top-1/2 -translate-y-1/2"
                                        @mousedown.prevent="clearLocation(wp)"
                                    >&times;</button>
                                </template>

                                <div
                                    x-show="wp.showDropdown"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100"
                                    x-transition:leave-end="opacity-0"
                                    class="absolute z-50 left-0 right-0 top-full mt-1 bg-base-100 rounded-box border border-base-300 shadow-lg max-h-60 overflow-y-auto"
                                >
                                    <template x-for="group in (() => { const m = new Map(); for (const r of wp.results) { if (!m.has(r.type)) m.set(r.type, { label: r.groupLabel, items: [] }); m.get(r.type).items.push(r); } return [...m.values()]; })()" :key="group.label">
                                        <div>
                                            <div class="px-3 py-1 text-xs font-medium uppercase tracking-wider text-muted bg-base-200 sticky top-0" x-text="group.label"></div>
                                            <template x-for="e in group.items" :key="e.uuid">
                                                <button
                                                    type="button"
                                                    class="flex items-center w-full px-3 py-1.5 hover:bg-base-200 text-left text-sm truncate"
                                                    :class="{ 'bg-primary/10': wp.uuid === e.uuid }"
                                                    @mousedown.prevent="selectLocation(wp, e)"
                                                >
                                                    <span x-text="e.name"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <button
                                type="button"
                                class="btn btn-xs btn-ghost btn-circle shrink-0"
                                :disabled="waypoints.length <= 2"
                                @click="removeWaypoint(idx)"
                            >&times;</button>
                        </div>
                    </template>

                    <div class="flex justify-center items-center gap-2 pt-1">
                        <button
                            type="button"
                            class="btn btn-sm btn-outline btn-primary gap-1"
                            @click="addWaypoint()"
                        >
                            + Add Stop
                        </button>
                        <template x-if="waypoints.filter(wp => wp.uuid).length >= 3">
                            <button
                                type="button"
                                class="btn btn-sm btn-outline btn-secondary gap-1"
                                :disabled="optimizing"
                                @click="optimizeRoute()"
                            >
                                <template x-if="optimizing">
                                    <span class="loading loading-xs loading-spinner"></span>
                                </template>
                                <span x-text="optimizing ? 'Optimizing...' : 'Shortest Path'"></span>
                            </button>
                        </template>
                    </div>
                </div>
                </template>

                <!-- Cargo Run missions -->
                <template x-if="mode === 'cargo'">
                <div class="space-y-2">
                    <template x-for="(mission, mIdx) in cargoMissions" :key="mission.id">
                        <div class="flex items-center gap-2">
                            <span class="badge badge-ghost badge-sm tabular-nums shrink-0 w-5 justify-center" x-text="mIdx + 1"></span>

                            <!-- Pickup -->
                            <div class="relative flex-1 min-w-0">
                                <input
                                    type="text"
                                    x-model="mission.pickupQuery"
                                    @input="cargoLocationInput(mission, 'pickup')"
                                    @focus="cargoOnFocus(mission, 'pickup')"
                                    @blur="cargoOnBlur(mission, 'pickup')"
                                    placeholder="Pickup from..."
                                    class="input input-sm input-bordered w-full pr-7"
                                    :disabled="loading || !stellarSystem"
                                >
                                <template x-if="mission.pickupUuid">
                                    <button type="button" class="btn btn-xs btn-circle btn-ghost absolute right-1 top-1/2 -translate-y-1/2" @mousedown.prevent="cargoClearLocation(mission, 'pickup')">&times;</button>
                                </template>
                                <div
                                    x-show="cargoDropdowns[mission.id + '-pickup']"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100"
                                    x-transition:leave-end="opacity-0"
                                    class="absolute z-50 left-0 right-0 top-full mt-1 bg-base-100 rounded-box border border-base-300 shadow-lg max-h-60 overflow-y-auto"
                                >
                                    <template x-for="group in (() => { const m = new Map(); for (const r of mission.pickupResults) { if (!m.has(r.type)) m.set(r.type, { label: r.groupLabel, items: [] }); m.get(r.type).items.push(r); } return [...m.values()]; })()" :key="group.label">
                                        <div>
                                            <div class="px-3 py-1 text-xs font-medium uppercase tracking-wider text-muted bg-base-200 sticky top-0" x-text="group.label"></div>
                                            <template x-for="e in group.items" :key="e.uuid">
                                                <button type="button" class="flex items-center w-full px-3 py-1.5 hover:bg-base-200 text-left text-sm truncate" @mousedown.prevent="cargoSelectLocation(mission, 'pickup', e)">
                                                    <span x-text="e.name"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <span class="text-muted text-xs shrink-0">&rarr;</span>

                            <!-- Delivery -->
                            <div class="relative flex-1 min-w-0">
                                <input
                                    type="text"
                                    x-model="mission.deliveryQuery"
                                    @input="cargoLocationInput(mission, 'delivery')"
                                    @focus="cargoOnFocus(mission, 'delivery')"
                                    @blur="cargoOnBlur(mission, 'delivery')"
                                    placeholder="Deliver to..."
                                    class="input input-sm input-bordered w-full pr-7"
                                    :disabled="loading || !stellarSystem"
                                >
                                <template x-if="mission.deliveryUuid">
                                    <button type="button" class="btn btn-xs btn-circle btn-ghost absolute right-1 top-1/2 -translate-y-1/2" @mousedown.prevent="cargoClearLocation(mission, 'delivery')">&times;</button>
                                </template>
                                <div
                                    x-show="cargoDropdowns[mission.id + '-delivery']"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100"
                                    x-transition:leave-end="opacity-0"
                                    class="absolute z-50 left-0 right-0 top-full mt-1 bg-base-100 rounded-box border border-base-300 shadow-lg max-h-60 overflow-y-auto"
                                >
                                    <template x-for="group in (() => { const m = new Map(); for (const r of mission.deliveryResults) { if (!m.has(r.type)) m.set(r.type, { label: r.groupLabel, items: [] }); m.get(r.type).items.push(r); } return [...m.values()]; })()" :key="group.label">
                                        <div>
                                            <div class="px-3 py-1 text-xs font-medium uppercase tracking-wider text-muted bg-base-200 sticky top-0" x-text="group.label"></div>
                                            <template x-for="e in group.items" :key="e.uuid">
                                                <button type="button" class="flex items-center w-full px-3 py-1.5 hover:bg-base-200 text-left text-sm truncate" @mousedown.prevent="cargoSelectLocation(mission, 'delivery', e)">
                                                    <span x-text="e.name"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- SCU -->
                            <div class="shrink-0">
                                <input
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    placeholder="SCU"
                                    x-model="mission.scu"
                                    class="input input-sm input-bordered w-16 tabular-nums"
                                >
                            </div>

                            <button
                                type="button"
                                class="btn btn-xs btn-ghost btn-circle shrink-0"
                                :disabled="cargoMissions.length <= 1"
                                @click="removeCargoMission(mIdx)"
                            >&times;</button>
                        </div>
                    </template>

                    <div class="flex justify-center items-center gap-2 pt-1">
                        <button type="button" class="btn btn-sm btn-outline btn-primary gap-1" @click="addCargoMission()">
                            + Add Mission
                        </button>
                        <details class="inline">
                            <summary class="btn btn-sm btn-outline btn-ghost cursor-pointer">Paste</summary>
                            <div class="mt-1 p-2 bg-base-200 rounded-box">
                                <textarea
                                    x-model="cargoPasteText"
                                    class="textarea textarea-sm textarea-bordered w-full font-mono"
                                    rows="6"
                                    placeholder="Paste missions, one per line:&#10;MIC-L2 -> Everus Harbor&#10;Port Tressler -> MIC-L1&#10;Seraphim -> Port Tressler"></textarea>
                                <button type="button" class="btn btn-sm btn-primary mt-1" @click="parseCargoPaste()">Import</button>
                            </div>
                        </details>
                    </div>
                </div>
                </template>

                <!-- Route output: regular modes -->
                <template x-if="mode !== 'cargo' && legs.length > 0">
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

                <!-- Route output: cargo mode -->
                <template x-if="mode === 'cargo' && cargoEnrichedLegs.length > 0">
                    <div class="mt-4">
                        <div class="border-b border-base-200 pb-1 mb-2">
                            <h3 class="text-xs font-medium uppercase tracking-wider text-muted">
                                Optimized Route
                                <span class="text-muted font-normal" x-text="cargoTripSummary.missionCount + ' missions, ' + cargoTripSummary.stopCount + ' stops' + (cargoTripSummary.hasScuData ? ', ' + cargoTripSummary.totalScu.toFixed(1) + ' SCU' : '')"></span>
                            </h3>
                        </div>

                        <!-- Stop list with cargo annotations -->
                        <div class="space-y-0">
                            <template x-for="(stop, idx) in cargoRoute.cargoPlan" :key="stop.uuid">
                                <div class="flex items-start gap-2 py-1.5" :class="idx > 0 ? 'border-t border-base-200' : ''">
                                    <span class="badge badge-ghost badge-sm tabular-nums shrink-0 w-5 justify-center mt-0.5" x-text="idx + 1"></span>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium">
                                            <span x-text="stop.name"></span>
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

                        <!-- Legs table -->
                        <div class="overflow-x-auto mt-2">
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
                                    <template x-for="(leg, idx) in cargoEnrichedLegs" :key="idx">
                                        <tr
                                            :class="{
                                                'bg-warning/5 border-l-2 border-l-warning': leg.type === 'jp',
                                                'bg-error/5': leg.type === 'no_route',
                                            }"
                                        >
                                            <td>
                                                <template x-if="leg.type === 'qt'">
                                                    <span class="flex items-center gap-1.5 text-xs">
                                                        <span class="text-base-content/50" x-text="leg.fromName"></span>
                                                        <span class="text-muted">&rarr;</span>
                                                        <span class="font-medium" x-text="leg.toName"></span>
                                                    </span>
                                                </template>
                                                <template x-if="leg.type === 'jp'">
                                                    <span class="flex items-center gap-1.5 text-xs">
                                                        <span class="badge badge-warning badge-xs">JP</span>
                                                        <span class="text-base-content/50" x-text="capitalize(leg.fromSystem) + ' \u2192 ' + capitalize(leg.toSystem)"></span>
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
                                        <td class="text-right tabular-nums" x-text="formatDistance(cargoTripSummary.distanceGm)"></td>
                                        <td class="text-right tabular-nums" x-text="cargoTripSummary.timeFormatted" x-show="hasQuantumData"></td>
                                        <td class="text-right tabular-nums" x-text="cargoTripSummary.fuelFormatted" x-show="fuelConsumptionPerGm"></td>
                                        <td
                                            class="text-right tabular-nums font-semibold"
                                            x-show="effectiveTankCapacity"
                                            :class="cargoRefuelInfo?.impossible ? 'text-error' : cargoRefuelInfo?.refuels > 0 ? 'text-warning' : 'text-success'"
                                            x-text="cargoRefuelInfo ? (cargoRefuelInfo.impossible ? 'Impossible' : formatFuel(cargoRefuelInfo.remainingFuel) + ' remaining') : '-'"
                                        ></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        <template x-if="error">
            <div class="alert alert-error text-sm">Failed to load position data.</div>
        </template>
    </div>
</div>
