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
        shipQdPortSize: null,
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
        mode: 'stellar',
        stellarSystem: '',
        waypoints: [
            { id: 0, system: '', uuid: '', query: '', results: [], showDropdown: false },
            { id: 1, system: '', uuid: '', query: '', results: [], showDropdown: false },
        ],
        nextWaypointId: 2,
        locationDebounceTimers: {},

        // Cargo run mode
        cargoMissions: [],
        nextMissionId: 0,
        cargoDebounceTimers: {},
        cargoDropdowns: {},

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

        get systems() {
            const order = ['stanton', 'pyro', 'nyx'];
            const all = [...new Set(this.entities.map(e => e.system))];

            return all.sort((a, b) => {
                const ia = order.indexOf(a.toLowerCase());
                const ib = order.indexOf(b.toLowerCase());

                if (ia === -1 && ib === -1) {
                    return a.localeCompare(b);
                }

                if (ia === -1) {
                    return 1;
                }

                if (ib === -1) {
                    return -1;
                }

                return ia - ib;
            });
        },

        get selectableEntities() {
            const types = Object.keys(TYPE_ORDER);

            return this.entities.filter(e => types.includes(e.type) && !e.hidden && e.qt_valid);
        },

        get hasEnoughWaypoints() {
            return this.waypoints.filter(wp => wp.uuid).length >= 2;
        },

        get legs() {
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

                const routeLegs = this.resolveRoute(fromEntity, toEntity);

                allLegs.push(...routeLegs);
            }

            return this.applyFuelTracking(allLegs);
        },

        summarizeLegs(legs) {
            const qtLegs = legs.filter(l => l.type === 'qt');
            const jpLegs = legs.filter(l => l.type === 'jp');

            const totalDistance = qtLegs.reduce((sum, l) => sum + (l.distance ?? 0), 0);
            const totalTime = qtLegs.reduce((sum, l) => sum + (l.time ?? 0), 0);
            const totalFuel = legs.reduce((sum, l) => sum + (l.fuel ?? 0), 0);

            return {
                distance: totalDistance,
                distanceGm: totalDistance / 1e9,
                time: totalTime,
                timeFormatted: this.formatTime(totalTime),
                fuel: totalFuel,
                fuelFormatted: this.formatFuel(totalFuel),
                legCount: qtLegs.length,
                jpCount: jpLegs.length,
            };
        },

        computeRefuelInfo(legs, tank) {
            if (!tank) {
                return null;
            }

            const hasImpossible = legs.some(l => l.impossible);

            let cumulative = 0;
            let refuels = 0;
            const refuelPoints = [];

            for (const leg of legs) {
                if (leg.fuel === null) {
                    continue;
                }

                if (leg.fuel > tank) {
                    break;
                }

                if (cumulative + leg.fuel > tank) {
                    refuels++;
                    refuelPoints.push({ at: leg.fromName, before: leg.toName });
                    cumulative = leg.fuel;
                } else {
                    cumulative += leg.fuel;
                }
            }

            return {
                impossible: hasImpossible,
                refuels,
                refuelPoints,
                remainingFuel: Math.max(0, tank - cumulative),
                remainingPercent: Math.round(Math.max(0, ((tank - cumulative) / tank)) * 100),
            };
        },

        get tripSummary() {
            return this.summarizeLegs(this.legs);
        },

        get refuelInfo() {
            return this.computeRefuelInfo(this.legs, this.effectiveTankCapacity);
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

        async selectShip(result) {
            this.selectedShip = result;
            this.shipQuery = result.name ?? result.display_name ?? '';
            this.showShipDropdown = false;
            this.shipResults = [];

            const quantum = result.quantum ?? {};
            this.fuelTankCapacity = num(quantum.quantum_fuel_capacity) || null;

            this.qdMaxSize = null;
            this.qdMinSize = null;
            this.shipQdPortSize = null;
            this.hasQdHardpoint = true;

            const ports = result.ports ?? [];
            const qdPort = ports.find(p => p.type === 'QuantumDrive');

            if (qdPort) {
                this.qdMaxSize = qdPort.sizes?.max ?? null;
                this.qdMinSize = qdPort.sizes?.min ?? null;
                this.shipQdPortSize = this.qdMaxSize;

                // Auto-select installed QD from preloaded list
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
            this.shipQdPortSize = null;
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
            const wp = { id: this.nextWaypointId++, system: '', uuid: '', query: '', results: [], showDropdown: false };
            this.waypoints.push(wp);
        },

        removeWaypoint(index) {
            if (this.waypoints.length > 2) {
                this.waypoints.splice(index, 1);
            }
        },

        entitiesForSystem(system) {
            if (!system) {
                return [];
            }

            return this.selectableEntities
                .filter(e => e.system === system)
                .sort((a, b) => {
                    const ta = TYPE_ORDER[a.type] ?? 99;
                    const tb = TYPE_ORDER[b.type] ?? 99;

                    if (ta !== tb) {
                        return ta - tb;
                    }

                    return a.name.localeCompare(b.name);
                });
        },

        wpSystem(wp) {
            return this.mode === 'stellar' ? this.stellarSystem : wp.system;
        },

        clearLocationFields(wp) {
            wp.uuid = '';
            wp.query = '';
            wp.results = [];
            wp.showDropdown = false;
        },

        setMode(mode) {
            this.mode = mode;

            for (const wp of this.waypoints) {
                this.clearLocationFields(wp);
            }

            this.stellarSystem = '';

            if (mode === 'cargo' && this.cargoMissions.length === 0) {
                this.addCargoMission();
            }
        },

        onStellarSystemChange() {
            for (const wp of this.waypoints) {
                this.clearLocationFields(wp);
            }

            for (const m of this.cargoMissions) {
                m.pickupUuid = '';
                m.pickupQuery = '';
                m.deliveryUuid = '';
                m.deliveryQuery = '';
            }

            this.cargoDropdowns = {};
        },

        entitiesGroupedByType(system) {
            const entities = this.entitiesForSystem(system);
            const groups = new Map();

            for (const e of entities) {
                const label = this.entityTypeName(e.type);

                if (!groups.has(e.type)) {
                    groups.set(e.type, { type: e.type, label, items: [] });
                }

                groups.get(e.type).items.push(e);
            }

            return [...groups.values()];
        },

        onSystemChange(wp) {
            this.clearLocationFields(wp);
        },

        searchEntities(system, query) {
            const q = query.toLowerCase().trim();
            const groups = this.entitiesGroupedByType(system);
            const results = [];

            for (const group of groups) {
                const matches = group.items.filter(e => e.name.toLowerCase().includes(q));

                for (const e of matches) {
                    results.push({ ...e, groupLabel: group.label });
                }
            }

            results.sort((a, b) => {
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
            });

            return results.slice(0, 30);
        },

        // Searchable location dropdown

        handleLocationInput(wp) {
            clearTimeout(this.locationDebounceTimers[wp.id]);

            if (!this.wpSystem(wp) || wp.query.length < 1) {
                wp.results = [];
                wp.showDropdown = false;
                return;
            }

            this.locationDebounceTimers[wp.id] = setTimeout(() => {
                this.searchLocations(wp);
            }, 150);
        },

        searchLocations(wp) {
            wp.results = this.searchEntities(this.wpSystem(wp), wp.query);
            wp.showDropdown = wp.results.length > 0;
        },

        selectLocation(wp, entity) {
            wp.uuid = entity.uuid;
            wp.query = entity.name;
            wp.showDropdown = false;
            wp.results = [];
        },

        clearLocation(wp) {
            this.clearLocationFields(wp);
        },

        onLocationFocus(wp) {
            if (wp.uuid) {
                return;
            }

            if (wp.results.length) {
                wp.showDropdown = true;
            } else if (wp.query.length >= 1) {
                this.searchLocations(wp);
            }
        },

        closeLocationDropdown(wp) {
            setTimeout(() => {
                wp.showDropdown = false;

                if (!wp.uuid) {
                    wp.query = '';
                    wp.results = [];
                }
            }, 200);
        },

        capitalize(str) {
            if (!str) {
                return '';
            }

            return str.charAt(0).toUpperCase() + str.slice(1);
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

        cargoPasteText: '',

        addCargoMission() {
            this.cargoMissions.push({
                id: this.nextMissionId++,
                pickupUuid: '',
                pickupQuery: '',
                pickupResults: [],
                deliveryUuid: '',
                deliveryQuery: '',
                deliveryResults: [],
                scu: '',
            });
        },

        removeCargoMission(index) {

            if (this.cargoMissions.length > 1) {
                this.cargoMissions.splice(index, 1);
            }
        },

        cargoLocationInput(mission, field) {
            const key = `${mission.id}-${field}`;
            clearTimeout(this.cargoDebounceTimers[key]);

            const query = this._cargoGet(mission, field, 'Query');

            if (!this.stellarSystem || query.length < 1) {
                this._cargoSet(mission, field, 'Results', []);
                this.cargoDropdowns[key] = false;
                return;
            }

            this.cargoDebounceTimers[key] = setTimeout(() => {
                this.cargoSearch(mission, field);
            }, 150);
        },

        _cargoKey(mission, field) {
            return `${mission.id}-${field}`;
        },

        _cargoGet(mission, field, suffix) {
            return mission[`${field}${suffix}`];
        },

        _cargoSet(mission, field, suffix, value) {
            mission[`${field}${suffix}`] = value;
        },

        cargoSearch(mission, field) {
            const query = this._cargoGet(mission, field, 'Query');
            const sliced = this.searchEntities(this.stellarSystem, query);
            this._cargoSet(mission, field, 'Results', sliced);
            this.cargoDropdowns[this._cargoKey(mission, field)] = sliced.length > 0;
        },

        cargoSelectLocation(mission, field, entity) {
            this._cargoSet(mission, field, 'Uuid', entity.uuid);
            this._cargoSet(mission, field, 'Query', entity.name);
            this._cargoSet(mission, field, 'Results', []);
            this.cargoDropdowns[this._cargoKey(mission, field)] = false;
        },

        cargoClearLocation(mission, field) {
            this._cargoSet(mission, field, 'Uuid', '');
            this._cargoSet(mission, field, 'Query', '');
            this._cargoSet(mission, field, 'Results', []);
            this.cargoDropdowns[this._cargoKey(mission, field)] = false;
        },

        cargoOnFocus(mission, field) {
            if (this._cargoGet(mission, field, 'Uuid')) {
                return;
            }

            if (this._cargoGet(mission, field, 'Results').length) {
                this.cargoDropdowns[this._cargoKey(mission, field)] = true;
            } else if (this._cargoGet(mission, field, 'Query').length >= 1) {
                this.cargoSearch(mission, field);
            }
        },

        cargoOnBlur(mission, field) {
            const key = this._cargoKey(mission, field);

            setTimeout(() => {
                this.cargoDropdowns[key] = false;

                if (!this._cargoGet(mission, field, 'Uuid')) {
                    this._cargoSet(mission, field, 'Query', '');
                    this._cargoSet(mission, field, 'Results', []);
                }
            }, 200);
        },

        get cargoCompleteMissions() {
            return this.cargoMissions.filter(m => m.pickupUuid && m.deliveryUuid);
        },

        // Build the optimized route from cargo missions
        get cargoRoute() {
            const complete = this.cargoCompleteMissions;

            if (complete.length === 0) {
                return { stops: [], legs: [], cargoPlan: [] };
            }

            const tasks = [];

            for (let i = 0; i < complete.length; i++) {
                tasks.push({ missionIdx: i, type: 'pickup', uuid: complete[i].pickupUuid });
                tasks.push({ missionIdx: i, type: 'delivery', uuid: complete[i].deliveryUuid });
            }

            // Nearest-neighbor respecting pickup-before-delivery constraints
            const remaining = new Set(tasks.map((_, i) => i));
            const pickupsDone = new Set();
            const orderedTasks = [];

            // Start at the location with the most pickup tasks
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

            let currentUuid = tasks[startIdx].uuid;
            orderedTasks.push(tasks[startIdx]);
            remaining.delete(startIdx);

            if (tasks[startIdx].type === 'pickup') {
                pickupsDone.add(tasks[startIdx].missionIdx);
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

                    const targetEntity = this.entityMap.get(task.uuid);
                    const dist = currentEntity && targetEntity
                        ? this.calculateDistance(currentEntity, targetEntity)
                        : Infinity;

                    // Prefer pickups so cargo is loaded before hauling
                    const adjusted = task.type === 'pickup' ? dist * 0.85 : dist;

                    if (adjusted < bestDist) {
                        bestDist = adjusted;
                        bestIdx = idx;
                    }
                }

                if (bestIdx === null) {
                    for (const idx of remaining) {
                        const task = tasks[idx];
                        const targetEntity = this.entityMap.get(task.uuid);
                        const dist = currentEntity && targetEntity
                            ? this.calculateDistance(currentEntity, targetEntity)
                            : Infinity;

                        if (dist < bestDist) {
                            bestDist = dist;
                            bestIdx = idx;
                        }
                    }
                }

                if (bestIdx === null) {
                    break;
                }

                const chosen = tasks[bestIdx];
                orderedTasks.push(chosen);
                remaining.delete(bestIdx);

                if (chosen.type === 'pickup') {
                    pickupsDone.add(chosen.missionIdx);
                }
                currentUuid = chosen.uuid;
            }

            // Collapse consecutive tasks at the same location into stops
            const stops = [];

            for (const task of orderedTasks) {
                const last = stops[stops.length - 1];

                if (last && last.uuid === task.uuid) {
                    const entry = { missionIdx: task.missionIdx + 1, from: complete[task.missionIdx].pickupQuery, to: complete[task.missionIdx].deliveryQuery };

                    if (task.type === 'pickup') {
                        last.pickups.push(entry);
                    } else {
                        last.deliveries.push(entry);
                    }
                } else {
                    const entity = this.entityMap.get(task.uuid);
                    const entry = { missionIdx: task.missionIdx + 1, from: complete[task.missionIdx].pickupQuery, to: complete[task.missionIdx].deliveryQuery };

                    stops.push({
                        uuid: task.uuid,
                        name: entity?.name ?? '?',
                        pickups: task.type === 'pickup' ? [entry] : [],
                        deliveries: task.type === 'delivery' ? [entry] : [],
                    });
                }
            }

            // Build legs between consecutive stops
            const allLegs = [];

            for (let i = 0; i < stops.length - 1; i++) {
                const fromEntity = this.entityMap.get(stops[i].uuid);
                const toEntity = this.entityMap.get(stops[i + 1].uuid);

                if (!fromEntity || !toEntity) {
                    continue;
                }

                allLegs.push(...this.resolveRoute(fromEntity, toEntity));
            }

            // SCU tracking per stop
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

            return { stops: stops.map(s => s.uuid), legs: allLegs, cargoPlan: stops };
        },

        get cargoLegs() {

            if (this.mode !== 'cargo') {
                return [];
            }

            return this.cargoRoute.legs;
        },

        get cargoEnrichedLegs() {
            const rawLegs = this.cargoLegs;

            if (rawLegs.length === 0) {
                return [];
            }

            return this.applyFuelTracking(rawLegs);
        },

        get cargoTripSummary() {
            const base = this.summarizeLegs(this.cargoEnrichedLegs);

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
        },

        get cargoRefuelInfo() {
            return this.computeRefuelInfo(this.cargoEnrichedLegs, this.effectiveTankCapacity);
        },

        // Route Optimization
        optimizeRoute() {
            if (this.optimizing) {
                return;
            }

            const validWps = this.waypoints.filter(wp => wp.uuid);

            if (validWps.length < 3) {
                return;
            }

            this.optimizing = true;

            setTimeout(() => {
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
                        this.waypoints = [first, ...bestOrder];
                    }
                } finally {
                    this.optimizing = false;
                }
            }, 0);
        },

        computeRouteCost(waypoints) {
            let total = 0;

            for (let i = 0; i < waypoints.length - 1; i++) {
                const fromEntity = this.entityMap.get(waypoints[i].uuid);
                const toEntity = this.entityMap.get(waypoints[i + 1].uuid);

                if (!fromEntity || !toEntity) {
                    continue;
                }

                const routeLegs = this.resolveRoute(fromEntity, toEntity);

                for (const leg of routeLegs) {
                    const enriched = this.enrichLeg(leg);
                    total += enriched.distance ?? 0;
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

            const legs = [];

            if (entityA.system === entityB.system) {
                if (entityA.uuid !== entityB.uuid) {
                    legs.push({
                        type: 'qt',
                        from: entityA,
                        to: entityB,
                        fromName: entityA.name,
                        toName: entityB.name,
                    });
                }

                return legs;
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

            let current = entityA;

            for (const hop of systemPath) {
                const entryJP = this.entityMap.get(hop.entryUuid);
                const exitJP = this.entityMap.get(hop.exitUuid);

                if (!entryJP || !exitJP) {
                    legs.push({ type: 'no_route', from: current, to: entityB, fromName: current.name, toName: entityB.name });

                    return legs;
                }

                if (current.uuid !== entryJP.uuid) {
                    legs.push({
                        type: 'qt',
                        from: current,
                        to: entryJP,
                        fromName: current.name,
                        toName: entryJP.name,
                    });
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
                legs.push({
                    type: 'qt',
                    from: current,
                    to: entityB,
                    fromName: current.name,
                    toName: entityB.name,
                });
            }

            return legs;
        },

        enrichLeg(leg) {
            if (leg.type === 'jp') {
                const fuel = leg.fuelCost != null ? leg.fuelCost / 1e6 : null;

                return {
                    ...leg,
                    distance: null,
                    distanceGm: null,
                    time: null,
                    timeFormatted: '-',
                    fuel,
                    fuelFormatted: this.formatFuel(fuel),
                    tankPercent: null,
                };
            }

            if (leg.type === 'no_route') {
                return {
                    ...leg,
                    distance: null,
                    distanceGm: null,
                    time: null,
                    timeFormatted: '-',
                    fuel: null,
                    fuelFormatted: 'No route',
                    tankPercent: null,
                };
            }

            const dist = this.calculateDistance(leg.from, leg.to);
            const distGm = dist / 1e9;
            const time = this.calculateTime(dist);
            const fuel = this.calculateFuel(distGm);
            const tankPercent = this.effectiveTankCapacity && fuel !== null
                ? Math.round((fuel / this.effectiveTankCapacity) * 100)
                : null;

            return {
                ...leg,
                distance: dist,
                distanceGm: distGm,
                time,
                timeFormatted: this.formatTime(time),
                fuel,
                fuelFormatted: this.formatFuel(fuel),
                tankPercent,
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
                const cruiseDist = distanceM - dTwoRamps;
                return (2.0 * tRamp) + (cruiseDist / vmax);
            }

            const halfDist = distanceM / 2.0;
            const tHalf = this.solveRampTimeForDistance(halfDist, a1, a2, tRamp);

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

            const maxDist = this.rampDistance(tRamp, a1, a2, tRamp);

            if (targetDistance > maxDist) {
                return null;
            }

            let low = 0.0;
            let high = tRamp;

            for (let i = 0; i < 60; i++) {
                const mid = (low + high) / 2.0;
                const d = this.rampDistance(mid, a1, a2, tRamp);

                if (d < targetDistance) {
                    low = mid;
                } else {
                    high = mid;
                }
            }

            return (low + high) / 2.0;
        },

        calculateFuel(distanceGm) {
            if (this.fuelConsumptionPerGm > 0) {
                return distanceGm * this.fuelConsumptionPerGm;
            }

            return null;
        },


        formatTime(seconds) {
            if (seconds === null || seconds === undefined) {
                return '-';
            }

            const h = Math.floor(seconds / 3600);
            const m = Math.floor((seconds % 3600) / 60);
            const sec = Math.round(seconds % 60);

            if (h > 0) {
                return `${h}:${pad(m)}:${pad(sec)}`;
            }

            return `${m}:${pad(sec)}`;
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

function num(value, fallback = 0) {
    const n = Number(value);
    return Number.isFinite(n) ? n : fallback;
}

function pad(n) {
    return String(n).padStart(2, '0');
}
