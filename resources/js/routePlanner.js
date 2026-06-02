import { createIcons, Trash2 } from 'lucide';

const TYPE_ORDER = { Planet: 0, Moon: 1, LandingZone: 2, Manmade: 3, Manmade_VisibleOnInteraction: 3, Outpost: 4, PointOfInterest: 5, Anomaly: 6 };

export function routePlanner() {
    return {
        entities: [],
        connections: [],
        entityMap: new Map(),
        systemGraph: {},
        loading: true,
        error: false,

        // Ship selector
        shipQuery: '',
        shipResults: [],
        shipLoading: false,
        showShipDropdown: false,
        shipDebounceTimer: null,
        selectedShip: null,
        hasQdHardpoint: true,

        // Quantum drive selector
        allQds: [],
        selectedQd: null,
        selectedQdUuid: '',
        qdMinSize: null,
        qdMaxSize: null,

        // Quantum parameters
        quantumSpeed: null,
        stageOneAccel: null,
        stageTwoAccel: null,
        travelTime10gm: null,
        fuelConsumptionPerGm: null,
        fuelTankCapacity: null,

        // Manual fuel tank input
        manualFuelTank: '',
        tankFill: 100,

        // Trip
        mode: 'waypoint',
        waypoints: [
            locationSlot(0),
            locationSlot(1),
        ],
        nextWaypointId: 2,
        locationDebounceTimers: {},

        // Cargo run mode
        cargoMissions: [],
        nextMissionId: 0,
        cargoStartPoint: locationSlot('startpoint'),
        cargoReturnPoint: locationSlot('returnpoint'),

        // Route optimization
        optimizing: false,

        get effectiveTankCapacity() {
            if (this.fuelTankCapacity) {
                return this.fuelTankCapacity * (this.tankFill / 100);
            }

            const v = parseFloat(this.manualFuelTank);

            return Number.isFinite(v) && v > 0 ? v * (this.tankFill / 100) : null;
        },

        get hasQuantumData() {
            return this.quantumSpeed > 0 || this.travelTime10gm > 0;
        },

        get selectableEntities() {
            const types = Object.keys(TYPE_ORDER);

            return this.entities.filter(e => types.includes(e.type) && !e.hidden && e.qt_valid);
        },

        get hasEnoughWaypoints() {
            return this.waypoints.filter(wp => wp.uuid).length >= 2;
        },

        get rawLegs() {
            if (this.mode === 'cargo') {
                return this.cargoRoute.legs;
            }

            if (!this.hasEnoughWaypoints) {
                return [];
            }

            const validWaypoints = this.waypoints.filter(wp => wp.uuid);
            const allLegs = [];

            for (let i = 0; i < validWaypoints.length - 1; i++) {
                const fromEntity = this.entityMap.get(validWaypoints[i].uuid);
                const toEntity = this.entityMap.get(validWaypoints[i + 1].uuid);

                if (!fromEntity || !toEntity) {
                    continue;
                }

                allLegs.push(...this.resolveRoute(fromEntity, toEntity));
            }

            return allLegs;
        },

        get enrichedLegs() {
            const raw = this.rawLegs;

            if (raw.length === 0) {
                return [];
            }

            return this.applyFuelTracking(raw);
        },

        summarizeLegs(legs) {
            const qtLegs = legs.filter(l => l.type === 'qt');
            const totalDistance = qtLegs.reduce((sum, l) => sum + (l.distance ?? 0), 0);
            const totalTime = qtLegs.reduce((sum, l) => sum + (l.time ?? 0), 0);
            const totalFuel = legs.reduce((sum, l) => sum + (l.fuel ?? 0), 0);

            return {
                distanceGm: totalDistance / 1e9,
                timeFormatted: this.formatTime(totalTime),
                fuelFormatted: this.formatFuel(totalFuel),
            };
        },

        computeRefuelInfo(legs, tank) {
            if (!tank) {
                return null;
            }

            const hasImpossible = legs.some(l => l.impossible);

            let cumulative = 0;
            let refuels = 0;
            for (const leg of legs) {
                if (leg.fuel === null) {
                    continue;
                }

                if (leg.fuel > tank) {
                    break;
                }

                if (cumulative + leg.fuel > tank) {
                    refuels++;
                    cumulative = leg.fuel;
                } else {
                    cumulative += leg.fuel;
                }
            }

            return {
                impossible: hasImpossible,
                refuels,
                remainingFuel: Math.max(0, tank - cumulative),
            };
        },

        get tripSummary() {
            const base = this.summarizeLegs(this.enrichedLegs);

            if (this.mode === 'cargo') {
                const totalScu = this.cargoCompleteMissions.reduce((sum, m) => {
                    const v = parseFloat(m.scu);
                    return sum + (Number.isFinite(v) && v > 0 ? v : 0);
                }, 0);

                return {
                    ...base,
                    stopCount: this.cargoRoute.cargoPlan?.length ?? 0,
                    missionCount: this.cargoCompleteMissions.length,
                    totalScu,
                    hasScuData: totalScu > 0,
                };
            }

            return base;
        },

        get refuelInfo() {
            return this.computeRefuelInfo(this.enrichedLegs, this.effectiveTankCapacity);
        },


        async init() {
            try {
                const [posRes, qdRes] = await Promise.all([
                    fetch('/api/locations/positions'),
                    fetch('/api/vehicle-items?filter[type]=QuantumDrive&page[size]=100'),
                ]);

                if (!posRes.ok) {
                    throw new Error(`Positions HTTP ${posRes.status}`);
                }

                const posData = await posRes.json();

                this.entities = posData.data ?? [];
                this.connections = posData.connections ?? [];
                this.buildEntityMap();
                this.buildSystemGraph();

                if (qdRes.ok) {
                    const qdData = await qdRes.json();

                    this.allQds = (qdData.data ?? []).sort((a, b) => {
                        const sa = a.size ?? 0;
                        const sb = b.size ?? 0;
                        return sa - sb || (a.name ?? '').localeCompare(b.name ?? '');
                    });
                }
            } catch (e) {
                console.error('Failed to load positions:', e);
                this.error = true;
            } finally {
                this.loading = false;
            }

            this.$watch('waypoints', () => {
                if (this._skipOptimize) {
                    this._skipOptimize = false;
                    return;
                }
                if (this.mode === 'waypoint' && this.waypoints.filter(wp => wp.uuid).length >= 3) {
                    this.optimizeRoute();
                }
            }, { deep: true });

            this.$watch('mode', () => {
                this.$nextTick(() => createIcons({ icons: { Trash2 } }));
            });
        },

        buildEntityMap() {
            this.entityMap = new Map();

            for (const e of this.entities) {
                this.entityMap.set(e.uuid, e);
            }
        },

        buildSystemGraph() {
            this.systemGraph = {};

            for (const conn of this.connections) {
                if (!this.systemGraph[conn.entry_system]) {
                    this.systemGraph[conn.entry_system] = [];
                }

                if (!this.systemGraph[conn.exit_system]) {
                    this.systemGraph[conn.exit_system] = [];
                }

                this.systemGraph[conn.entry_system].push({
                    entryUuid: conn.entry_uuid,
                    exitUuid: conn.exit_uuid,
                    targetSystem: conn.exit_system,
                    fuelCost: conn.fuel_cost,
                    sizeClass: conn.size_class,
                });

                this.systemGraph[conn.exit_system].push({
                    entryUuid: conn.exit_uuid,
                    exitUuid: conn.entry_uuid,
                    targetSystem: conn.entry_system,
                    fuelCost: conn.fuel_cost,
                    sizeClass: conn.size_class,
                });
            }
        },


        handleShipInput() {
            clearTimeout(this.shipDebounceTimer);

            if (this.shipQuery.length < 2) {
                this.shipResults = [];
                this.showShipDropdown = false;
                return;
            }

            this.shipDebounceTimer = setTimeout(() => this.doShipSearch(), 300);
        },

        async doShipSearch() {
            this.shipLoading = true;

            try {
                const url = `/api/vehicles?filter[query]=${encodeURIComponent(this.shipQuery)}&filter[is_spaceship]=1&page[size]=20`;
                const res = await fetch(url);

                if (!res.ok) {
                    throw new Error(`HTTP ${res.status}`);
                }

                const data = await res.json();

                this.shipResults = data.data ?? [];
                this.showShipDropdown = this.shipResults.length > 0;
            } catch (e) {
                console.error('Ship search failed:', e);
                this.shipResults = [];
            } finally {
                this.shipLoading = false;
            }
        },

        selectShip(result) {
            this.selectedShip = result;
            this.shipQuery = result.name ?? result.display_name ?? '';
            this.showShipDropdown = false;
            this.shipResults = [];

            const quantum = result.quantum ?? {};
            this.fuelTankCapacity = num(quantum.quantum_fuel_capacity) || null;

            this.qdMaxSize = null;
            this.qdMinSize = null;
            this.hasQdHardpoint = true;

            const ports = result.ports ?? [];
            const qdPort = ports.find(p => p.type === 'QuantumDrive');

            if (qdPort) {
                this.qdMaxSize = qdPort.sizes?.max ?? null;
                this.qdMinSize = qdPort.sizes?.min ?? null;

                const installedClassName = qdPort.equipped_item?.class_name;

                if (installedClassName) {
                    const match = this.allQds.find(q => q.class_name === installedClassName);

                    if (match) {
                        this.selectedQdUuid = match.uuid;
                        this.applyQdSelection(match);
                    }
                }
            } else {
                this.hasQdHardpoint = false;
            }
        },

        clearShip() {
            this.selectedShip = null;
            this.shipQuery = '';
            this.shipResults = [];
            this.showShipDropdown = false;
            this.qdMaxSize = null;
            this.qdMinSize = null;
            this.fuelTankCapacity = null;
            this.hasQdHardpoint = true;
        },

        closeShipDropdown() {
            setTimeout(() => { this.showShipDropdown = false; }, 200);
        },


        get filteredQds() {
            if (!this.qdMaxSize && !this.qdMinSize) {
                return this.allQds;
            }

            return this.allQds.filter(q => {
                const size = q.size ?? 0;
                const belowMinimum = this.qdMinSize && size < this.qdMinSize;
                const aboveMaximum = this.qdMaxSize && size > this.qdMaxSize;

                return !belowMinimum && !aboveMaximum;
            });
        },

        onQdSelect() {
            if (!this.selectedQdUuid) {
                this.clearQd();
                return;
            }

            const item = this.allQds.find(q => q.uuid === this.selectedQdUuid);

            if (item) {
                this.applyQdSelection(item);
            }
        },

        applyQdSelection(item) {
            this.selectedQd = item;
            this.selectedQdUuid = item.uuid ?? '';

            const qd = item.quantum_drive ?? {};
            const sj = qd.standard_jump ?? {};
            this.travelTime10gm = num(qd.travel_time_10gm?.seconds) || null;
            this.fuelConsumptionPerGm = num(qd.fuel_consumption_scu_per_gm) || null;
            this.quantumSpeed = num(sj.drive_speed) || null;
            this.stageOneAccel = num(sj.stage_one_accel_rate) || null;
            this.stageTwoAccel = num(sj.stage_two_accel_rate) || null;

            if (!this.selectedShip) {
                this.fuelTankCapacity = null;
            }
        },

        clearQd() {
            this.selectedQd = null;
            this.selectedQdUuid = '';
            this.quantumSpeed = null;
            this.travelTime10gm = null;
            this.fuelConsumptionPerGm = null;
            this.stageOneAccel = null;
            this.stageTwoAccel = null;
        },

        qdLabel(qd) {
            const parts = [`S${qd.size ?? '?'}`];
            parts.push(qd.name);

            const qdSpec = qd.quantum_drive ?? {};
            const speed = num(qdSpec.standard_jump?.drive_speed, 0);
            const fuel = num(qdSpec.fuel_consumption_scu_per_gm, 0);

            if (speed > 0) {
                parts.push(`${(speed / 1e6).toFixed(0)} Mm/s`);
            }

            if (fuel > 0) {
                parts.push(`${fuel.toFixed(3)} SCU/Gm`);
            }

            return parts.join(' · ');
        },


        addWaypoint() {
            this.waypoints.push(locationSlot(this.nextWaypointId++));
            this.$nextTick(() => createIcons({ icons: { Trash2 } }));
        },

        removeWaypoint(index) {
            if (this.waypoints.length > 2) {
                this.waypoints.splice(index, 1);
            }
        },

        setMode(mode) {
            this.mode = mode;

            if (mode === 'cargo' && this.cargoMissions.length === 0) {
                this.addCargoMission();
            }
        },

        searchEntities(query) {
            const q = query.toLowerCase().trim();

            return this.selectableEntities
                .filter(e => e.name.toLowerCase().includes(q))
                .map(e => ({
                    ...e,
                    meta: [this.capitalize(e.system), this.entityTypeName(e.type)].filter(Boolean).join(' - '),
                }))
                .sort((a, b) => {
                    const aExact = a.name.toLowerCase() === q ? 0 : 1;
                    const bExact = b.name.toLowerCase() === q ? 0 : 1;

                    if (aExact !== bExact) {
                        return aExact - bExact;
                    }

                    const ta = TYPE_ORDER[a.type] ?? 99;
                    const tb = TYPE_ORDER[b.type] ?? 99;

                    if (ta !== tb) {
                        return ta - tb;
                    }

                    return a.name.localeCompare(b.name);
                })
                .slice(0, 30);
        },

        // Shared location search handlers

        _slotKey(slot) {
            return slot.id ?? slot.uuid;
        },

        locationInput(slot) {
            const key = this._slotKey(slot);
            clearTimeout(this.locationDebounceTimers[key]);

            if (slot.query.length < 1) {
                slot.results = [];
                slot.showDropdown = false;
                return;
            }

            this.locationDebounceTimers[key] = setTimeout(() => {
                slot.results = this.searchEntities(slot.query);
                slot.showDropdown = slot.results.length > 0;
            }, 150);
        },

        locationSelect(slot, entity) {
            slot.uuid = entity.uuid;
            slot.query = entity.name;
            slot.showDropdown = false;
            slot.results = [];

            if (this.mode === 'waypoint') {
                const idx = this.waypoints.indexOf(slot);
                if (idx === this.waypoints.length - 1) {
                    this.addWaypoint();
                }
            }

            if (this.mode === 'cargo') {
                const idx = this.cargoMissions.findIndex(mission => mission.delivery === slot);
                if (idx !== -1 && idx === this.cargoMissions.length - 1) {
                    this.addCargoMission();
                }
            }
        },

        locationClear(slot) {
            slot.uuid = '';
            slot.query = '';
            slot.results = [];
            slot.showDropdown = false;
        },

        locationFocus(slot) {
            if (slot.uuid) {
                return;
            }

            if (slot.results.length) {
                slot.showDropdown = true;
            } else if (slot.query.length >= 1) {
                slot.results = this.searchEntities(slot.query);
                slot.showDropdown = slot.results.length > 0;
            }
        },

        locationBlur(slot) {
            setTimeout(() => {
                slot.showDropdown = false;

                if (!slot.uuid) {
                    slot.query = '';
                    slot.results = [];
                }
            }, 200);
        },

        capitalize(str) {
            return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
        },

        entityTypeName(type) {
            const map = {
                Planet: 'Planet',
                Moon: 'Moon',
                LandingZone: 'Landing Zone',
                Manmade: 'Station',
                Manmade_VisibleOnInteraction: 'Station',
                Outpost: 'Outpost',
                PointOfInterest: 'POI',
                Anomaly: 'Jump Point',
            };

            return map[type] ?? type;
        },

        // Cargo Run Mode

        addCargoMission() {
            const id = this.nextMissionId++;
            this.cargoMissions.push({
                id,
                pickup: locationSlot(`${id}-pickup`),
                delivery: locationSlot(`${id}-delivery`),
                scu: '',
            });
            this.$nextTick(() => createIcons({ icons: { Trash2 } }));
        },

        removeCargoMission(index) {
            if (this.cargoMissions.length > 1) {
                this.cargoMissions.splice(index, 1);
            }
        },

        get cargoCompleteMissions() {
            return this.cargoMissions.filter(m => m.pickup.uuid && m.delivery.uuid);
        },

        get cargoMultiSystem() {
            const stops = this.cargoRoute?.cargoPlan;
            if (!stops || stops.length === 0) return false;
            const systems = new Set();
            for (const s of stops) {
                const e = this.entityMap.get(s.uuid);
                if (e) systems.add(e.system);
            }
            return systems.size > 1;
        },

        // Cargo route: task building, optimization, stop collapsing, leg building, SCU tracking

        _buildCargoTasks(complete) {
            const tasks = [];

            for (let i = 0; i < complete.length; i++) {
                tasks.push({ missionIdx: i, type: 'pickup', uuid: complete[i].pickup.uuid });
                tasks.push({ missionIdx: i, type: 'delivery', uuid: complete[i].delivery.uuid });
            }

            return tasks;
        },

        _optimizeCargoOrder(tasks) {
            const remaining = new Set(tasks.map((_, i) => i));
            const pickupsDone = new Set();
            const orderedTasks = [];

            let currentUuid;
            let startPointProvided = false;

            if (this.cargoStartPoint.uuid) {
                currentUuid = this.cargoStartPoint.uuid;
                startPointProvided = true;
            } else {
                const pickupCounts = {};

                for (const t of tasks) {
                    if (t.type === 'pickup') {
                        pickupCounts[t.uuid] = (pickupCounts[t.uuid] ?? 0) + 1;
                    }
                }

                let startIdx = 0;
                let bestPickupCount = 0;

                for (let i = 0; i < tasks.length; i++) {
                    if (tasks[i].type === 'pickup') {
                        const count = pickupCounts[tasks[i].uuid] ?? 0;

                        if (count > bestPickupCount) {
                            bestPickupCount = count;
                            startIdx = i;
                        }
                    }
                }

                currentUuid = tasks[startIdx].uuid;
                orderedTasks.push(tasks[startIdx]);
                remaining.delete(startIdx);

                if (tasks[startIdx].type === 'pickup') {
                    pickupsDone.add(tasks[startIdx].missionIdx);
                }
            }

            while (remaining.size > 0) {
                const currentEntity = this.entityMap.get(currentUuid);
                let bestIdx = null;
                let bestDist = Infinity;

                for (const idx of remaining) {
                    const task = tasks[idx];

                    if (task.type === 'delivery' && !pickupsDone.has(task.missionIdx)) {
                        continue;
                    }

                    let dist = Infinity;
                    const targetEntity = this.entityMap.get(task.uuid);

                    if (currentEntity && targetEntity) {
                        const cacheKey = [currentUuid, task.uuid].sort().join('|');
                        if (!(cacheKey in this._cargoRouteCostCache)) {
                            this._cargoRouteCostCache[cacheKey] = this.computeRouteCost([
                                { uuid: currentUuid }, { uuid: task.uuid },
                            ]);
                        }
                        dist = this._cargoRouteCostCache[cacheKey];
                    }

                    const adjusted = task.type === 'pickup' ? dist * 0.85 : dist;

                    if (adjusted < bestDist) {
                        bestDist = adjusted;
                        bestIdx = idx;
                    }
                }

                if (bestIdx === null) {
                    throw new Error('cargoRoute: no valid candidate found, but remaining tasks exist');
                }

                const chosen = tasks[bestIdx];
                orderedTasks.push(chosen);
                remaining.delete(bestIdx);

                if (chosen.type === 'pickup') {
                    pickupsDone.add(chosen.missionIdx);
                }
                currentUuid = chosen.uuid;
            }

            return { orderedTasks, startPointProvided };
        },

        _collapseCargoStops(orderedTasks, complete, startPointProvided) {
            const stops = [];

            if (startPointProvided) {
                const startEntity = this.entityMap.get(this.cargoStartPoint.uuid);
                stops.push({
                    uuid: this.cargoStartPoint.uuid,
                    name: startEntity?.name ?? '?',
                    pickups: [],
                    deliveries: [],
                });
            }

            for (const task of orderedTasks) {
                const last = stops[stops.length - 1];
                const entry = {
                    missionIdx: task.missionIdx + 1,
                };

                if (last && last.uuid === task.uuid) {
                    (task.type === 'pickup' ? last.pickups : last.deliveries).push(entry);
                } else {
                    const entity = this.entityMap.get(task.uuid);
                    stops.push({
                        uuid: task.uuid,
                        name: entity?.name ?? '?',
                        pickups: task.type === 'pickup' ? [entry] : [],
                        deliveries: task.type === 'delivery' ? [entry] : [],
                    });
                }
            }

            if (this.cargoReturnPoint.uuid) {
                const lastStop = stops[stops.length - 1];
                if (!lastStop || lastStop.uuid !== this.cargoReturnPoint.uuid) {
                    const returnEntity = this.entityMap.get(this.cargoReturnPoint.uuid);
                    stops.push({
                        uuid: this.cargoReturnPoint.uuid,
                        name: returnEntity?.name ?? '?',
                        pickups: [],
                        deliveries: [],
                    });
                }
            }

            return stops;
        },

        _buildCargoLegs(stops) {
            const allLegs = [];

            for (let i = 0; i < stops.length - 1; i++) {
                const from = this.entityMap.get(stops[i].uuid);
                const to = this.entityMap.get(stops[i + 1].uuid);

                if (from && to) {
                    allLegs.push(...this.resolveRoute(from, to));
                }
            }

            return allLegs;
        },

        _applyScuTracking(stops, complete) {
            let runningScu = 0;
            const hasAnyScu = complete.some(m => parseFloat(m.scu) > 0);

            for (const stop of stops) {
                const scuIn = stop.pickups.reduce((sum, p) => {
                    const v = parseFloat(complete[p.missionIdx - 1]?.scu);
                    return sum + (Number.isFinite(v) && v > 0 ? v : 0);
                }, 0);
                const scuOut = stop.deliveries.reduce((sum, d) => {
                    const v = parseFloat(complete[d.missionIdx - 1]?.scu);
                    return sum + (Number.isFinite(v) && v > 0 ? v : 0);
                }, 0);
                runningScu += scuIn - scuOut;
                stop.scuIn = scuIn;
                stop.scuOut = scuOut;
                stop.scuTotal = runningScu;
                stop.hasScu = hasAnyScu;
            }
        },

        get cargoRoute() {
            this._cargoRouteCostCache = {};
            const complete = this.cargoCompleteMissions;

            if (complete.length === 0) {
                return { legs: [], cargoPlan: [] };
            }

            const tasks = this._buildCargoTasks(complete);
            const { orderedTasks, startPointProvided } = this._optimizeCargoOrder(tasks);
            const stops = this._collapseCargoStops(orderedTasks, complete, startPointProvided);
            const legs = this._buildCargoLegs(stops);
            this._applyScuTracking(stops, complete);

            return { legs, cargoPlan: stops };
        },


        // Route Optimization

        optimizeRoute() {
            if (this.optimizing) {
                return;
            }

            const validWps = this.waypoints.filter(wp => wp.uuid);
            const emptyWps = this.waypoints.filter(wp => !wp.uuid);

            if (validWps.length < 3) {
                return;
            }

            this.optimizing = true;

            try {
                const first = validWps[0];
                const rest = validWps.slice(1);

                let bestOrder = null;
                let bestCost = Infinity;

                if (rest.length <= 6) {
                    const arr = rest.slice();

                    const permute = (a, l) => {
                        if (l === a.length - 1) {
                            const cost = this.computeRouteCost([first, ...a]);

                            if (cost < bestCost) {
                                bestCost = cost;
                                bestOrder = a.map(x => x);
                            }
                            return;
                        }

                        for (let i = l; i < a.length; i++) {
                            [a[l], a[i]] = [a[i], a[l]];
                            permute(a, l + 1);
                            [a[l], a[i]] = [a[i], a[l]];
                        }
                    };

                    permute(arr, 0);
                } else {
                    bestOrder = this.nearestNeighborSort(first, rest);
                }

                if (bestOrder) {
                    this._skipOptimize = true;
                    this.waypoints = [first, ...bestOrder, ...emptyWps];
                }
            } finally {
                this.optimizing = false;
            }
        },

        computeRouteCost(waypoints) {
            let total = 0;

            for (let i = 0; i < waypoints.length - 1; i++) {
                const from = this.entityMap.get(waypoints[i].uuid);
                const to = this.entityMap.get(waypoints[i + 1].uuid);

                if (!from || !to) {
                    continue;
                }

                if (from.system === to.system) {
                    total += this.calculateDistance(from, to);
                } else {
                    for (const leg of this.resolveRoute(from, to)) {
                        if (leg.type === 'qt') {
                            total += this.calculateDistance(leg.from, leg.to);
                        }
                        // JP legs have no distance
                    }
                }
            }

            return total;
        },

        nearestNeighborSort(start, points) {
            const remaining = points.slice();
            const ordered = [];
            let current = start;

            while (remaining.length > 0) {
                let bestIdx = 0;
                let bestDist = Infinity;

                for (let i = 0; i < remaining.length; i++) {
                    const cost = this.pairwiseDistance(current, remaining[i]);

                    if (cost < bestDist) {
                        bestDist = cost;
                        bestIdx = i;
                    }
                }

                ordered.push(remaining[bestIdx]);
                current = remaining[bestIdx];
                remaining.splice(bestIdx, 1);
            }

            return ordered;
        },

        pairwiseDistance(wpA, wpB) {
            const a = this.entityMap.get(wpA.uuid);
            const b = this.entityMap.get(wpB.uuid);

            if (!a || !b) {
                return Infinity;
            }

            return this.computeRouteCost([wpA, wpB]);
        },

        // Path Resolution

        findSystemPath(fromSystem, toSystem) {
            if (fromSystem === toSystem) {
                return [];
            }

            const queue = [{ system: fromSystem, path: [] }];
            const visited = new Set([fromSystem]);

            while (queue.length > 0) {
                const { system, path } = queue.shift();
                const neighbors = this.systemGraph[system] ?? [];

                for (const conn of neighbors) {
                    if (conn.targetSystem === toSystem) {
                        return [...path, conn];
                    }

                    if (!visited.has(conn.targetSystem)) {
                        visited.add(conn.targetSystem);
                        queue.push({ system: conn.targetSystem, path: [...path, conn] });
                    }
                }
            }

            return null;
        },

        resolveRoute(entityA, entityB) {
            if (!entityA || !entityB) {
                return [];
            }

            if (entityA.uuid === entityB.uuid) {
                return [];
            }

            if (entityA.system === entityB.system) {
                return [{
                    type: 'qt',
                    from: entityA,
                    to: entityB,
                    fromName: entityA.name,
                    toName: entityB.name,
                }];
            }

            const systemPath = this.findSystemPath(entityA.system, entityB.system);

            if (!systemPath) {
                return [{
                    type: 'no_route',
                    from: entityA,
                    to: entityB,
                    fromName: entityA.name,
                    toName: entityB.name,
                }];
            }

            const legs = [];
            let current = entityA;

            for (const hop of systemPath) {
                const entryJP = this.entityMap.get(hop.entryUuid);
                const exitJP = this.entityMap.get(hop.exitUuid);

                if (!entryJP || !exitJP) {
                    legs.push({ type: 'no_route', from: current, to: entityB, fromName: current.name, toName: entityB.name });

                    return legs;
                }

                if (current.uuid !== entryJP.uuid) {
                    legs.push({ type: 'qt', from: current, to: entryJP, fromName: current.name, toName: entryJP.name });
                }

                legs.push({
                    type: 'jp',
                    from: entryJP,
                    to: exitJP,
                    fromSystem: entryJP.system,
                    toSystem: exitJP.system,
                    fuelCost: hop.fuelCost,
                    fromName: entryJP.name,
                    toName: exitJP.name,
                });

                current = exitJP;
            }

            if (current.uuid !== entityB.uuid) {
                legs.push({ type: 'qt', from: current, to: entityB, fromName: current.name, toName: entityB.name });
            }

            return legs;
        },

        enrichLeg(leg) {
            if (leg.type !== 'qt') {
                const isNoRoute = leg.type === 'no_route';
                return {
                    ...leg,
                    distance: null,
                    distanceGm: null,
                    time: null,
                    timeFormatted: '-',
                    fuel: isNoRoute ? null : (leg.fuelCost ?? null),
                    fuelFormatted: isNoRoute ? 'No route' : this.formatFuel(leg.fuelCost ?? null),
                };
            }

            const dist = this.calculateDistance(leg.from, leg.to);
            const distGm = dist / 1e9;
            const time = this.calculateTime(dist);
            const fuel = this.calculateFuel(distGm);

            return {
                ...leg,
                distance: dist,
                distanceGm: distGm,
                time,
                timeFormatted: this.formatTime(time),
                fuel,
                fuelFormatted: this.formatFuel(fuel),
            };
        },

        applyFuelTracking(rawLegs) {
            const enriched = rawLegs.map(leg => this.enrichLeg(leg));
            const tank = this.effectiveTankCapacity;

            if (!tank) {
                return enriched;
            }

            let cumulativeFuel = 0;
            let routeBroken = false;

            for (const leg of enriched) {
                if (routeBroken) {
                    leg.impossible = leg.fuel !== null;
                    leg.needsRefuel = false;
                    leg.refuelAt = null;
                    leg.tankRemaining = leg.fuel !== null ? 0 : null;
                    continue;
                }

                leg.impossible = false;
                leg.needsRefuel = false;
                leg.refuelAt = null;

                if (leg.fuel === null) {
                    leg.tankRemaining = null;
                } else if (leg.fuel > tank) {
                    leg.impossible = true;
                    leg.tankRemaining = 0;
                    routeBroken = true;
                } else if (cumulativeFuel + leg.fuel > tank) {
                    cumulativeFuel = leg.fuel;
                    leg.tankRemaining = Math.max(0, tank - cumulativeFuel);
                } else {
                    cumulativeFuel += leg.fuel;
                    leg.tankRemaining = Math.max(0, tank - cumulativeFuel);
                }
            }

            for (let i = 0; i < enriched.length; i++) {
                const leg = enriched[i];

                if (leg.fuel === null || leg.impossible) {
                    continue;
                }

                for (let j = i + 1; j < enriched.length; j++) {
                    const nextLeg = enriched[j];

                    if (nextLeg.fuel === null || nextLeg.impossible) {
                        continue;
                    }

                    if (leg.tankRemaining < nextLeg.fuel && leg.tankRemaining !== 0) {
                        leg.needsRefuel = true;
                        leg.refuelAt = leg.toName;
                    }
                    break;
                }
            }

            return enriched;
        },


        calculateDistance(a, b) {
            const dx = a.x - b.x;
            const dy = a.y - b.y;
            const dz = a.z - b.z;
            return Math.sqrt(dx * dx + dy * dy + dz * dz);
        },

        calculateTime(distanceMeters) {
            const vmax = this.quantumSpeed;
            const a1 = this.stageOneAccel;
            const a2 = this.stageTwoAccel;

            if (vmax > 0 && a1 > 0 && a2 > 0) {
                return this.estimateTravelTime(vmax, a1, a2, distanceMeters / 1e9);
            }

            if (vmax > 0) {
                return distanceMeters / vmax;
            }

            if (this.travelTime10gm > 0) {
                return (distanceMeters / 1e9 / 10) * this.travelTime10gm;
            }

            return null;
        },

        /**
         * Copied from ScDataDumper
         *
         * Estimate QT travel time using the linear-ramp acceleration model.
         * Acceleration starts at a1, increases linearly to a2 until vmax is reached.
         * Long trips: ramp up -> cruise -> ramp down.
         * Short trips: never reach vmax, solve ramp-only distance.
         */
        estimateTravelTime(vmax, a1, a2, distanceGm) {
            if (distanceGm <= 0) {
                return 0;
            }

            if (vmax <= 0 || a1 <= 0 || a2 <= 0) {
                return null;
            }

            const distanceM = distanceGm * 1e9;
            const sumA = a1 + a2;
            const tRamp = (2.0 * vmax) / sumA;
            const dRamp = (2.0 * vmax * vmax / (sumA * sumA)) * ((a2 - a1) / 3.0 + a1);
            const dTwoRamps = 2.0 * dRamp;

            if (distanceM >= dTwoRamps) {
                return (2.0 * tRamp) + ((distanceM - dTwoRamps) / vmax);
            }

            const tHalf = this.solveRampTimeForDistance(distanceM / 2.0, a1, a2, tRamp);

            return tHalf !== null ? 2.0 * tHalf : null;
        },

        /**
         * Distance during ramp phase at time t:
         * a(t) = a1 + (a2-a1)*(t/tRamp)
         * s(t) = 0.5*a1*tˆ2 + (a2-a1)*tˆ3/(6*tRamp)
         */
        rampDistance(t, a1, a2, tRamp) {
            const delta = a2 - a1;
            return (0.5 * a1 * t * t) + (delta * t * t * t) / (6.0 * tRamp);
        },

        /**
         * Binary search for t in [0, tRamp] such that rampDistance(t) ~= targetDistance.
         */
        solveRampTimeForDistance(targetDistance, a1, a2, tRamp) {
            if (targetDistance < 0 || tRamp <= 0) {
                return null;
            }

            if (targetDistance > this.rampDistance(tRamp, a1, a2, tRamp)) {
                return null;
            }

            let low = 0.0;
            let high = tRamp;

            for (let i = 0; i < 60; i++) {
                const mid = (low + high) / 2.0;

                if (this.rampDistance(mid, a1, a2, tRamp) < targetDistance) {
                    low = mid;
                } else {
                    high = mid;
                }
            }

            return (low + high) / 2.0;
        },

        calculateFuel(distanceGm) {
            return this.fuelConsumptionPerGm > 0 ? distanceGm * this.fuelConsumptionPerGm : null;
        },


        formatTime(seconds) {
            if (seconds === null || seconds === undefined) {
                return '-';
            }

            const h = Math.floor(seconds / 3600);
            const m = Math.floor((seconds % 3600) / 60);
            const sec = Math.round(seconds % 60);

            return h > 0 ? `${h}:${pad(m)}:${pad(sec)}` : `${m}:${pad(sec)}`;
        },

        formatFuel(fuel) {
            if (fuel === null || fuel === undefined) {
                return '-';
            }

            if (fuel < 0.001) {
                return `${(fuel * 1000000).toFixed(0)} µSCU`;
            }

            if (fuel < 0.01) {
                return `${(fuel * 1000).toFixed(1)} mSCU`;
            }

            return `${fuel.toFixed(2)} SCU`;
        },

        formatDistance(gm) {
            if (gm === null || gm === undefined) {
                return '-';
            }

            if (gm < 0.01) {
                return `${(gm * 1000).toFixed(1)} Mm`;
            }

            if (gm < 1) {
                return `${(gm * 1000).toFixed(0)} Mm`;
            }

            return `${gm.toFixed(2)} Gm`;
        },
    };
}

function locationSlot(id) {
    return { id, uuid: '', query: '', results: [], showDropdown: false };
}

function num(value, fallback = 0) {
    const n = Number(value);
    return Number.isFinite(n) ? n : fallback;
}

function pad(n) {
    return String(n).padStart(2, '0');
}
