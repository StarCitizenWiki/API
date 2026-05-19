import { createIcons, ExternalLink } from "lucide";

/**
 * Maps item type to {path, label, unit} for the primary stat column.
 * Mirrors HardpointRow::STAT_MAP on the backend.
 */
const STAT_MAP = {
    WeaponGun: { path: "vehicle_weapon.damage.burst", label: "DPS", unit: "" },
    Shield: { path: "shield.max_health", label: "HP", unit: "" },
    PowerPlant: { path: "power_plant.power_segment_generation", label: "Power Gen", unit: "" },
    Cooler: { path: "resource_network.generation.coolant", label: "Cool Gen", unit: "" },
    QuantumDrive: { path: "quantum_drive.standard_jump.drive_speed", label: "Speed", unit: "" },
    Armor: { path: "armor.health", label: "HP", unit: "" },
    CountermeasureLauncher: { path: "ammunition.capacity", label: "Ammo", unit: "" },
    WeaponDefensive: { path: "ammunition.capacity", label: "Ammo", unit: "" },
    EMP: { path: "emp.emp_radius", label: "Radius", unit: "m" },
    QuantumInterdictionGenerator: { path: "quantum_interdiction_generator.interdiction_range", label: "Range", unit: "m" },
    Radar: { path: "radar.aim_assist.distance_max_assignment", label: "Aim", unit: "m" },
    CargoGrid: { path: "inventory.scu", label: "SCU", unit: "" },
};

const WEAPON_TYPES = ["WeaponGun", "WeaponMining", "WeaponPersonal"];
const OPENED_EVENT = "port-equippable:opened";

const componentId = () => globalThis.crypto?.randomUUID?.() ?? `port-equippable-${Date.now()}-${Math.random()}`;

const compact = (value, decimals = 1) => {
    if (value === null || value === undefined) return null;

    const num = Number(value);

    if (Number.isNaN(num)) return null;
    if (Math.abs(num) >= 1_000_000_000) return `${(num / 1_000_000_000).toFixed(decimals)}B`;
    if (Math.abs(num) >= 1_000_000) return `${(num / 1_000_000).toFixed(decimals)}M`;
    if (Math.abs(num) >= 1_000) return `${(num / 1_000).toFixed(decimals)}K`;

    return num % 1 === 0 ? String(num) : num.toFixed(decimals);
};

const getNestedValue = (obj, path) => path.split(".").reduce((acc, key) => acc?.[key], obj);

const extractStat = (item) => {
    const map = STAT_MAP[item.type];

    if (!map) return null;

    const formatted = compact(getNestedValue(item, map.path));

    return formatted ? `${formatted}${map.unit} ${map.label}` : null;
};

const normalizeArray = (value) => {
    if (!value) return [];

    return Array.isArray(value) ? value : [value];
};

const buildParams = (filters) => {
    const params = new URLSearchParams();
    params.set("filter[type]", filters.type);

    if (filters.subType) {
        params.set("filter[sub_type]", filters.subType);
    }

    if (filters.sizeMin != null && filters.sizeMax != null) {
        const sizes = [];

        for (let size = filters.sizeMin; size <= filters.sizeMax; size++) {
            sizes.push(size);
        }

        params.set("filter[size]", sizes.join(","));
    }

    const requiredTags = normalizeArray(filters.requiredTags);
    const portTags = normalizeArray(filters.portTags);
    const vehiclePortTags = normalizeArray(filters.vehiclePortTags);

    if (requiredTags.length > 0) {
        requiredTags.forEach((tag) => params.append("filter[tags]", tag));
    } else if (portTags.length > 0) {
        portTags.forEach((tag) => params.append("filter[port_tags]", tag));
    } else if (vehiclePortTags.length > 0) {
        params.set("filter[vehicle]", vehiclePortTags.join(","));
    }

    params.set("page_size", "50");

    return params;
};

const formatItem = (item) => {
    const weaponType = WEAPON_TYPES.includes(item.type) ? item.vehicle_weapon?.type : null;
    const itemClassification = [item.class, item.grade].filter(Boolean).join(" / ");

    return {
        name: item.name ?? item.class_name ?? "-",
        size: item.size ?? null,
        web_url: item.web_url,
        grade_label: item.grade_label ?? null,
        annotation: weaponType ?? (itemClassification || null),
        stat: extractStat(item),
    };
};

/**
 * Alpine component for browsing equippable items on a hardpoint port.
 *
 * @param {{ type: string, subType: string|null, sizeMin: number|null, sizeMax: number|null, requiredTags?: string|string[]|null, portTags?: string|string[]|null, vehiclePortTags?: string|string[]|null }} filters
 */
export function portEquippable(filters) {
    return {
        id: componentId(),
        open: false,
        loading: false,
        loaded: false,
        items: [],
        anchorEl: null,
        closeWhenAnotherOpens: null,

        init() {
            this.closeWhenAnotherOpens = (event) => {
                if (event.detail?.id !== this.id) {
                    this.close();
                }
            };

            window.addEventListener(OPENED_EVENT, this.closeWhenAnotherOpens);
        },

        destroy() {
            window.removeEventListener(OPENED_EVENT, this.closeWhenAnotherOpens);
        },

        toggle(anchor) {
            if (this.open) {
                this.close();
                return;
            }

            this.openAt(anchor);
        },

        async openAt(anchor) {
            window.dispatchEvent(new CustomEvent(OPENED_EVENT, { detail: { id: this.id } }));

            this.anchorEl = anchor?.closest?.('[data-testid="port-display-details"]') ?? anchor;
            this.open = true;

            if (this.loaded || this.loading) {
                return;
            }

            await this.loadItems();
        },

        async loadItems() {
            this.loading = true;

            try {
                const response = await fetch(`/api/items?${buildParams(filters)}`, {
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                });

                const json = await response.json();
                this.items = (json.data ?? []).map(formatItem);
            } catch {
                this.items = [];
            } finally {
                this.loading = false;
                this.loaded = true;
                this.$nextTick(() => createIcons({ icons: { ExternalLink } }));
            }
        },

        close() {
            this.open = false;
        },
    };
}
