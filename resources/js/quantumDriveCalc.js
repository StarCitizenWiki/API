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
        distanceInput: 10,

        get distance() {
            return num(this.distanceInput);
        },

        get travelTimeSeconds() {
            if (this.distance <= 0 || travelTime10gm <= 0) return null;
            return (this.distance / 10) * travelTime10gm;
        },

        get travelTimeFormatted() {
            const s = this.travelTimeSeconds;
            if (s === null) return "-";
            const h = Math.floor(s / 3600);
            const m = Math.floor((s % 3600) / 60);
            const sec = Math.round(s % 60);
            if (h > 0) {
                return `${h}:${pad(m)}:${pad(sec)}`;
            }
            return `${m}:${pad(sec)}`;
        },

        get fuelRequired() {
            if (this.distance <= 0 || fuelConsumption <= 0) return null;
            return this.distance * fuelConsumption;
        },

        get fuelFormatted() {
            const f = this.fuelRequired;
            if (f === null) return '-';
            if (f < 0.01) return `${(f * 1000).toFixed(1)} mSCU`;
            return `${f.toFixed(2)} SCU`;
        },

        get tankPercent() {
            if (this.fuelRequired === null || fuelCapacity <= 0) return null;
            return Math.round((this.fuelRequired / fuelCapacity) * 100);
        },

        get tankFormatted() {
            const pct = this.tankPercent;
            if (pct === null) return null;
            return `${pct}%`;
        },

        get exceedsTank() {
            return this.tankPercent !== null && this.tankPercent > 100;
        },

        setPreset(gm) {
            this.distanceInput = String(gm);
        },
    };
}

function num(value, fallback = 0) {
    const n = Number(value);
    return Number.isFinite(n) ? n : fallback;
}

function pad(n) {
    return String(n).padStart(2, "0");
}
