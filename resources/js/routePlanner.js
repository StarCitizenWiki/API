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
        waypoints: [
            { id: 0, system: '', uuid: '' },
            { id: 1, system: '', uuid: '' },
        ],
        nextWaypointId: 2,

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
            const types = ['Planet', 'Moon', 'Manmade', 'Manmade_VisibleOnInteraction', 'Anomaly'];

            return this.entities.filter(e => types.includes(e.type));
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

            const enriched = allLegs.map(leg => this.enrichLeg(leg));

            const tank = this.effectiveTankCapacity;

            if (tank) {
                let cumulativeFuel = 0;
                let routeBroken = false;

                // First pass: compute cumulative fuel and tank remaining
                for (const leg of enriched) {
                    if (routeBroken) {
                        leg.impossible = leg.fuel !== null;
                        leg.needsRefuel = false;
                        leg.refuelAt = null;
                        leg.tankRemaining = leg.fuel !== null ? 0 : null;
                        continue;
                    }

                    if (leg.fuel !== null) {
                        if (leg.fuel > tank) {
                            leg.impossible = true;
                            leg.needsRefuel = false;
                            leg.refuelAt = null;
                            leg.tankRemaining = 0;
                            routeBroken = true;
                        } else if (cumulativeFuel + leg.fuel > tank) {
                            // Refuel needed before this leg
                            leg.needsRefuel = false;
                            leg.refuelAt = null;
                            leg.impossible = false;
                            cumulativeFuel = leg.fuel;
                            leg.tankRemaining = Math.max(0, tank - cumulativeFuel);
                        } else {
                            leg.needsRefuel = false;
                            leg.refuelAt = null;
                            leg.impossible = false;
                            cumulativeFuel += leg.fuel;
                            leg.tankRemaining = Math.max(0, tank - cumulativeFuel);
                        }
                    } else {
                        leg.tankRemaining = null;
                        leg.needsRefuel = false;
                        leg.refuelAt = null;
                        leg.impossible = false;
                    }
                }

                // Second pass: mark legs where refuel is needed at their destination
                for (let i = 0; i < enriched.length; i++) {
                    const leg = enriched[i];

                    if (leg.fuel === null || leg.impossible) {
                        continue;
                    }

                    // Check if the next leg with fuel needs a refuel
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
            }

            return enriched;
        },

        get tripSummary() {
            const qtLegs = this.legs.filter(l => l.type === 'qt');
            const jpLegs = this.legs.filter(l => l.type === 'jp');

            const totalDistance = qtLegs.reduce((sum, l) => sum + (l.distance ?? 0), 0);
            const totalTime = qtLegs.reduce((sum, l) => sum + (l.time ?? 0), 0);
            const totalFuel = this.legs.reduce((sum, l) => sum + (l.fuel ?? 0), 0);

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

        get refuelInfo() {
            const tank = this.effectiveTankCapacity;

            if (!tank) {
                return null;
            }

            // Check for impossible legs first
            const hasImpossible = this.legs.some(l => l.impossible);

            let cumulative = 0;
            let refuels = 0;
            const refuelPoints = [];

            for (const leg of this.legs) {
                if (leg.fuel === null) {
                    continue;
                }

                if (leg.fuel > tank) {
                    // Impossible
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
                this.hasQdHardpoint = true;
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
            this.waypoints.push({ id: this.nextWaypointId++, system: '', uuid: '' });
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

            const typeOrder = { Planet: 0, Moon: 1, Manmade: 2, Manmade_VisibleOnInteraction: 3, Anomaly: 4 };

            return this.selectableEntities
                .filter(e => e.system === system)
                .sort((a, b) => {
                    const ta = typeOrder[a.type] ?? 99;
                    const tb = typeOrder[b.type] ?? 99;

                    if (ta !== tb) {
                        return ta - tb;
                    }

                    return a.name.localeCompare(b.name);
                });
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
            wp.uuid = '';
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
                Manmade: 'Station',
                Manmade_VisibleOnInteraction: 'Station',
                Anomaly: 'Jump Point',
            };

            return map[type] ?? type;
        },

        // Path Resolution

        getAnchor(entity) {
            if (!entity) {
                return null;
            }

            if (entity.type === 'Planet' || entity.type === 'Anomaly') {
                return entity;
            }

            if (entity.type === 'Star') {
                return null;
            }

            if (!entity.parent_uuid) {
                return entity;
            }

            const parent = this.entityMap.get(entity.parent_uuid);

            if (!parent) {
                return entity;
            }

            if (parent.type === 'Star') {
                return entity;
            }

            return this.getAnchor(parent);
        },

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
            const anchorA = this.getAnchor(entityA);
            const anchorB = this.getAnchor(entityB);

            if (entityA.uuid !== anchorA.uuid) {
                legs.push({
                    type: 'qt',
                    from: entityA,
                    to: anchorA,
                    fromName: entityA.name,
                    toName: anchorA.name,
                });
            }

            if (anchorA.system === anchorB.system) {
                if (anchorA.uuid !== anchorB.uuid) {
                    legs.push({
                        type: 'qt',
                        from: anchorA,
                        to: anchorB,
                        fromName: anchorA.name,
                        toName: anchorB.name,
                    });
                }
            } else {
                const systemPath = this.findSystemPath(anchorA.system, anchorB.system);

                if (!systemPath) {
                    legs.push({
                        type: 'no_route',
                        from: anchorA,
                        to: anchorB,
                        fromName: anchorA.name,
                        toName: anchorB.name,
                    });

                    return legs;
                }

                let current = anchorA;

                for (const hop of systemPath) {
                    const entryJP = this.entityMap.get(hop.entryUuid);
                    const exitJP = this.entityMap.get(hop.exitUuid);

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

                if (current.uuid !== anchorB.uuid) {
                    legs.push({
                        type: 'qt',
                        from: current,
                        to: anchorB,
                        fromName: current.name,
                        toName: anchorB.name,
                    });
                }
            }

            if (entityB.uuid !== anchorB.uuid) {
                legs.push({
                    type: 'qt',
                    from: anchorB,
                    to: entityB,
                    fromName: anchorB.name,
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
