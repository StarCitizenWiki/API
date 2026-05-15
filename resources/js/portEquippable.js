import { createIcons, icons } from "lucide";

/**
 * Alpine component for browsing equippable items on a hardpoint port.
 *
 * @param {{ type: string, subType: string|null, sizeMin: number|null, sizeMax: number|null }} filters
 */
export function portEquippable(filters) {
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

    const compact = (value, decimals = 1) => {
        if (value === null || value === undefined) return null;
        const num = Number(value);
        if (isNaN(num)) return null;
        if (Math.abs(num) >= 1_000_000_000) return (num / 1_000_000_000).toFixed(decimals) + "B";
        if (Math.abs(num) >= 1_000_000) return (num / 1_000_000).toFixed(decimals) + "M";
        if (Math.abs(num) >= 1_000) return (num / 1_000).toFixed(decimals) + "K";
        return num % 1 === 0 ? String(num) : num.toFixed(decimals);
    };

    const extractStat = (item) => {
        const map = STAT_MAP[item.type];
        if (!map) return null;

        const raw = getNestedValue(item, map.path);
        if (raw === null || raw === undefined) return null;

        const formatted = compact(raw);
        if (!formatted) return null;

        return formatted + map.unit + " " + map.label;
    };

    const getNestedValue = (obj, path) => {
        return path.split(".").reduce((acc, key) => acc?.[key], obj);
    };

    return {
        open: false,
        loading: false,
        loaded: false,
        items: [],
        popupStyle: "",
        _anchor: null,
        _reposition: null,

        init() {
            this._reposition = () => {
                if (this.open && this._anchor) {
                    this.positionAt(this._anchor);
                }
            };
            window.addEventListener("scroll", this._reposition, true);
            window.addEventListener("resize", this._reposition);
        },

        destroy() {
            window.removeEventListener("scroll", this._reposition, true);
            window.removeEventListener("resize", this._reposition);
        },

        async loadItems(event) {
            if (this.open && this.loaded) {
                this.open = false;
                return;
            }

            this._anchor = event.currentTarget;
            this.open = true;
            this.positionAt(this._anchor);

            if (this.loaded) {
                return;
            }

            this.loading = true;

            const params = new URLSearchParams();
            params.set("filter[type]", filters.type);

            if (filters.subType) {
                params.set("filter[sub_type]", filters.subType);
            }

            if (filters.sizeMin != null && filters.sizeMax != null) {
                const sizes = [];
                for (let s = filters.sizeMin; s <= filters.sizeMax; s++) {
                    sizes.push(s);
                }
                params.set("filter[size]", sizes.join(","));
            }

            if (filters.requiredTags) {
                const tags = Array.isArray(filters.requiredTags)
                    ? filters.requiredTags
                    : [filters.requiredTags];
                tags.forEach(tag => params.append("filter[tags]", tag));
            } else if (filters.portTags) {
                const portTags = Array.isArray(filters.portTags)
                    ? filters.portTags
                    : [filters.portTags];
                portTags.forEach(tag => params.append("filter[port_tags]", tag));
            }

            try {
                const response = await fetch(`/api/items?${params}&page_size=50`, {
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                });

                const json = await response.json();
                this.items = (json.data ?? []).map((item) => {
                    const isWeapon = WEAPON_TYPES.includes(item.type);
                    const weaponType =
                        isWeapon && item.vehicle_weapon
                            ? item.vehicle_weapon.type
                            : null;

                    let annotation = null;
                    if (weaponType) {
                        annotation = weaponType;
                    } else if (item.grade || item.class) {
                        const parts = [item.class, item.grade].filter(Boolean);
                        annotation = parts.join(" / ");
                    }

                    return {
                        name: item.name ?? item.class_name ?? "-",
                        size: item.size,
                        web_url: item.web_url,
                        grade_label: item.grade_label ?? null,
                        annotation,
                        stat: extractStat(item),
                    };
                });
            } catch {
                this.items = [];
            } finally {
                this.loading = false;
                this.loaded = true;
                this.$nextTick(() => createIcons({ icons }));
            }
        },

        close() {
            this.open = false;
        },

        positionAt(anchor) {
            if (!anchor) return;

            const rect = anchor.getBoundingClientRect();
            const width = 480;
            const popupHeight = 350;
            const spaceBelow = window.innerHeight - rect.bottom;

            let top;
            if (spaceBelow < popupHeight) {
                top = Math.max(8, rect.top - popupHeight - 4);
            } else {
                top = rect.bottom + 4;
            }

            const left = Math.max(8, Math.min(rect.right - width, window.innerWidth - width - 8));

            this.popupStyle = `position:fixed;z-index:9999;top:${top}px;left:${left}px;width:${width}px;`;
        },
    };
}
