/**
 * Quantum drive travel calculator.
 *
 * Given a distance in GM, computes estimated travel time and fuel
 * requirement using the linear scaling established by the game's
 * TravelTime10GMSeconds and FuelConsumptionSCUPerGM fields.
 */
export function quantumDriveCalc(config) {
    const travelTime10gm = num(config?.travelTime10gm);
    const fuelConsumption = num(config?.fuelConsumption);
    const fuelCapacity = num(config?.fuelCapacity);

    return {
        entities: [],
        connections: [],
        loading: true,
        error: false,
        startUuid: null,
        endUuid: null,
        distanceInput: 10,

        tankFill: 100,
        showTank: false,

        get hasFuelTank() {
            return fuelCapacity > 0;
        },

        get effectiveCapacity() {
            return fuelCapacity * (this.tankFill / 100);
        },

        async init() {
            try {
                const res = await fetch("/api/locations/positions?filter[type]=Planet");
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                const data = await res.json();
                this.entities = data.data ?? [];
                this.connections = data.connections ?? [];
            } catch (e) {
                console.error("Failed to load positions:", e);
                this.error = true;
            } finally {
                this.loading = false;
            }
        },

        // --- Distance selection ---

        get systems() {
            return [...new Set(this.entities.map((e) => e.system))];
        },

        get startEntity() {
            return this.entities.find((e) => e.uuid === this.startUuid) ?? null;
        },

        get endEntity() {
            return this.entities.find((e) => e.uuid === this.endUuid) ?? null;
        },

        get endOptions() {
            if (!this.startEntity) return this.entities;
            return this.entities.filter((e) => e.system === this.startEntity.system);
        },

        get sameEntity() {
            return this.startUuid && this.endUuid && this.startUuid === this.endUuid;
        },

        get distanceMeters() {
            if (!this.startEntity || !this.endEntity) return null;
            const dx = this.startEntity.x - this.endEntity.x;
            const dy = this.startEntity.y - this.endEntity.y;
            const dz = this.startEntity.z - this.endEntity.z;
            return Math.sqrt(dx * dx + dy * dy + dz * dz);
        },

        get distanceFromSelection() {
            const m = this.distanceMeters;
            return m === null ? null : m / 1e9;
        },

        // --- Planet row calcs ---

        get selectionTime() {
            const gm = this.distanceFromSelection;
            if (gm === null || gm <= 0 || travelTime10gm <= 0) return null;
            return (gm / 10) * travelTime10gm;
        },

        get selectionTimeFormatted() {
            return formatTime(this.selectionTime);
        },

        get selectionFuel() {
            const gm = this.distanceFromSelection;
            if (gm === null || gm <= 0 || fuelConsumption <= 0) return null;
            return gm * fuelConsumption;
        },

        get selectionFuelFormatted() {
            return formatFuel(this.selectionFuel);
        },

        get selectionTankPercent() {
            if (this.selectionFuel === null || fuelCapacity <= 0) return null;
            return Math.round((this.selectionFuel / this.effectiveCapacity) * 100);
        },

        get selectionTankFormatted() {
            const pct = this.selectionTankPercent;
            return pct === null ? null : `${pct}%`;
        },

        get selectionExceedsTank() {
            return this.selectionTankPercent !== null && this.selectionTankPercent > 100;
        },

        // --- Custom row calcs (distanceInput) ---

        get distance() {
            return num(this.distanceInput);
        },

        get travelTimeSeconds() {
            if (this.distance <= 0 || travelTime10gm <= 0) return null;
            return (this.distance / 10) * travelTime10gm;
        },

        get travelTimeFormatted() {
            return formatTime(this.travelTimeSeconds);
        },

        get fuelRequired() {
            if (this.distance <= 0 || fuelConsumption <= 0) return null;
            return this.distance * fuelConsumption;
        },

        get fuelFormatted() {
            return formatFuel(this.fuelRequired);
        },

        get tankPercent() {
            if (this.fuelRequired === null || fuelCapacity <= 0) return null;
            return Math.round((this.fuelRequired / this.effectiveCapacity) * 100);
        },

        get tankFormatted() {
            const pct = this.tankPercent;
            return pct === null ? null : `${pct}%`;
        },

        get exceedsTank() {
            return this.tankPercent !== null && this.tankPercent > 100;
        },
    };
}

function formatTime(seconds) {
    if (seconds === null) return "-";
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const sec = Math.round(seconds % 60);
    if (h > 0) return `${h}:${pad(m)}:${pad(sec)}`;
    return `${m}:${pad(sec)}`;
}

function formatFuel(fuel) {
    if (fuel === null) return "-";
    if (fuel < 0.01) return `${(fuel * 1000).toFixed(1)} mSCU`;
    return `${fuel.toFixed(2)} SCU`;
}

function num(value, fallback = 0) {
    const n = Number(value);
    return Number.isFinite(n) ? n : fallback;
}

function pad(n) {
    return String(n).padStart(2, "0");
}
