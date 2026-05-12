/**
 * Alpine component for the blueprint detail quality-tuning UI.
 *
 * Replaces the inline initDetailTuning() script (~433 lines).
 * Reads config from the #blueprint-show-data JSON payload.
 */
export function blueprintTuning() {
    return {
        aspects: [],
        qualityByAspect: [],
        selectedByAspect: [],
        summaryProperties: [],
        hasInteractiveAspects: false,
        selectionGroups: [],

        // --- Init ---

        init() {
            const el = document.getElementById("blueprint-show-data");
            if (!el?.textContent) return;

            try {
                const payload = JSON.parse(el.textContent);
                const detail = payload.detail ?? {};

                this.aspects = Array.isArray(detail.aspects) ? detail.aspects : [];
                this.summaryProperties = Array.isArray(detail.summaryProperties) ? detail.summaryProperties : [];
                this.hasInteractiveAspects = !!detail.hasInteractiveAspects;

                this.qualityByAspect = this.aspects.map((a) => num(a?.initial_quality, 500));
                this.selectedByAspect = this.aspects.map((a) => a?.is_selected !== false);

                // Build selection groups
                const groupMap = {};
                this.aspects.forEach((aspect, i) => {
                    const sg = aspect?.selection_group;
                    const key = str(sg?.key);
                    const required = num(sg?.required_count, 1);
                    const optionCount = num(sg?.option_count, 1);
                    if (!key || optionCount <= required) return;
                    if (!groupMap[key]) {
                        groupMap[key] = {
                            key,
                            displayName: str(sg?.display_name),
                            requiredCount: required,
                            optionCount,
                            aspectIndexes: [],
                        };
                    }
                    groupMap[key].aspectIndexes.push(i);
                });
                this.selectionGroups = Object.values(groupMap);
            } catch (e) {
                // no tuning data
            }
        },

        // --- Aspect quality ---

        setQuality(index, value) {
            const aspect = this.aspects[index];
            if (!aspect) return;
            const min = num(aspect.slider_min, 0);
            const max = Math.max(num(aspect.slider_max, 1000), min);
            this.qualityByAspect.splice(index, 1, Math.min(Math.max(num(value, 0), min), max));
        },

        resetQuality(index) {
            const aspect = this.aspects[index];
            if (!aspect) return;
            this.qualityByAspect.splice(index, 1, num(aspect.initial_quality, 500));
        },

        // --- Aspect selection ---

        canToggleOn(aspectIndex) {
            const groupKey = str(this.aspects[aspectIndex]?.selection_group?.key);
            const group = this.selectionGroups.find((g) => g.key === groupKey);
            if (!group) return true;
            const count = group.aspectIndexes.filter((i) => this.selectedByAspect[i]).length;
            return count < group.requiredCount;
        },

        toggleSelected(index) {
            const groupKey = str(this.aspects[index]?.selection_group?.key);
            const group = this.selectionGroups.find((g) => g.key === groupKey);
            if (!group) return;
            if (!this.selectedByAspect[index] && !this.canToggleOn(index)) return;
            this.selectedByAspect.splice(index, 1, !this.selectedByAspect[index]);
        },

        // --- Aspect display ---

        getQualityDisplay(index) {
            return this.selectedByAspect[index] ? String(this.qualityByAspect[index]) : "Off";
        },

        getAspectCardClass(index) {
            return this.selectedByAspect[index]
                ? "card card-border bg-base-200/60 shadow-sm"
                : "card border border-dashed border-base-300 bg-base-100 opacity-70 shadow-sm";
        },

        getToggleClass(index) {
            return this.selectedByAspect[index]
                ? "btn btn-primary btn-xs"
                : "btn btn-outline btn-xs border-base-300 bg-base-100 text-subtle";
        },

        getToggleText(index) {
            return this.selectedByAspect[index] ? "Included" : "Excluded";
        },

        getSliderClass(index) {
            return this.selectedByAspect[index]
                ? "range range-primary range-sm mt-3 w-full"
                : "range range-primary range-sm mt-3 w-full opacity-50";
        },

        isResetDisabled(index) {
            if (!this.selectedByAspect[index]) return true;
            const baseline = num(this.aspects[index]?.initial_quality, 500);
            return this.qualityByAspect[index] === baseline;
        },

        getResetClass(index) {
            return this.isResetDisabled(index)
                ? "invisible btn btn-ghost btn-xs text-muted transition-colors"
                : "btn btn-outline btn-primary btn-xs border-primary/40 bg-base-100 text-primary transition-colors hover:bg-primary/10";
        },

        // --- Modifier interpolation ---

        interpolateModifier(modifier, quality) {
            const qualityMin = num(modifier?.quality_range?.min, 0);
            const qualityMax = num(modifier?.quality_range?.max, 1000);
            const atMin = num(modifier?.modifier_range?.at_min_quality, 1);
            const atMax = num(modifier?.modifier_range?.at_max_quality, atMin);

            if (qualityMax === qualityMin) return atMax;
            const ratio = (quality - qualityMin) / (qualityMax - qualityMin);
            return atMin + (atMax - atMin) * ratio;
        },

        relativeChange(baseline, current) {
            if (baseline === 0) return current === 0 ? 1 : current;
            return current / baseline;
        },

        isNeutral(value) {
            return Math.abs(value - 1) < 0.0005;
        },

        isImprovement(modifier, value) {
            if (modifier?.better_when === "lower") return value < 1;
            if (modifier?.better_when === "higher") return value > 1;
            return false;
        },

        formatSemanticChange(modifier, value) {
            if (this.isNeutral(value)) return "No change";
            const pct = `${Math.abs((value - 1) * 100).toFixed(1)}%`;
            if (modifier?.better_when === "neutral") return `${pct} changed`;
            return `${pct} ${this.isImprovement(modifier, value) ? "better" : "worse"}`;
        },

        toneClasses(modifier, value) {
            if (this.isNeutral(value) || modifier?.better_when === "neutral") {
                return {
                    text: "text-subtle",
                    card: "rounded-box border border-base-300 bg-base-100 px-2.5 py-2",
                };
            }
            return this.isImprovement(modifier, value)
                ? { text: "text-success", card: "rounded-box border border-success/25 bg-success/10 px-2.5 py-2" }
                : { text: "text-error", card: "rounded-box border border-error/25 bg-error/10 px-2.5 py-2" };
        },

        // --- Per-modifier display ---

        getModifierRelValue(aspectIndex, modifierIndex) {
            const aspect = this.aspects[aspectIndex];
            const modifier = aspect?.modifiers?.[modifierIndex];
            if (!modifier || !this.selectedByAspect[aspectIndex]) return null;

            const baseline = num(aspect.initial_quality, 500);
            const current = this.qualityByAspect[aspectIndex];
            return this.relativeChange(
                this.interpolateModifier(modifier, baseline),
                this.interpolateModifier(modifier, current),
            );
        },

        getModifierCardClass(aspectIndex, modifierIndex) {
            if (!this.selectedByAspect[aspectIndex]) {
                return "rounded-box border border-dashed border-base-300 bg-base-100 px-2.5 py-2 opacity-60 transition-colors";
            }
            const rel = this.getModifierRelValue(aspectIndex, modifierIndex);
            const modifier = this.aspects[aspectIndex]?.modifiers?.[modifierIndex];
            if (rel === null) return "rounded-box border border-base-300 bg-base-100 px-2.5 py-2 transition-colors";
            return this.toneClasses(modifier, rel).card + " transition-colors";
        },

        getModifierChangeText(aspectIndex, modifierIndex) {
            if (!this.selectedByAspect[aspectIndex]) return "Excluded";
            const rel = this.getModifierRelValue(aspectIndex, modifierIndex);
            if (rel === null) return "No change";
            return this.formatSemanticChange(this.aspects[aspectIndex]?.modifiers?.[modifierIndex], rel);
        },

        getModifierChangeClass(aspectIndex, modifierIndex) {
            if (!this.selectedByAspect[aspectIndex]) return "text-xs font-semibold tabular-nums text-muted";
            const rel = this.getModifierRelValue(aspectIndex, modifierIndex);
            const modifier = this.aspects[aspectIndex]?.modifiers?.[modifierIndex];
            if (rel === null) return "text-xs font-semibold tabular-nums text-subtle";
            return "text-xs font-semibold tabular-nums " + this.toneClasses(modifier, rel).text;
        },

        // --- Selection groups ---

        getGroupSelectedCount(groupKey) {
            const group = this.selectionGroups.find((g) => g.key === groupKey);
            if (!group) return 0;
            return group.aspectIndexes.filter((i) => this.selectedByAspect[i]).length;
        },

        getGroupCountClass(groupKey) {
            const group = this.selectionGroups.find((g) => g.key === groupKey);
            if (!group) return "badge badge-soft badge-sm";
            const count = this.getGroupSelectedCount(groupKey);
            return count === group.requiredCount ? "badge badge-soft badge-sm" : "badge badge-warning badge-sm";
        },

        getGroupWarning(groupKey) {
            const group = this.selectionGroups.find((g) => g.key === groupKey);
            if (!group) return "";
            const count = this.getGroupSelectedCount(groupKey);
            if (count === group.requiredCount) return "";
            return group.displayName
                ? `Select ${group.requiredCount} of ${group.optionCount} options in ${group.displayName} to model a full recipe.`
                : `Select ${group.requiredCount} of ${group.optionCount} options to model a full recipe.`;
        },

        getGroupCountText(groupKey) {
            const group = this.selectionGroups.find((g) => g.key === groupKey);
            if (!group) return "";
            return `${this.getGroupSelectedCount(groupKey)} of ${group.requiredCount} selected`;
        },

        get selectionGroupsComplete() {
            return this.selectionGroups.every((group) => {
                return this.getGroupSelectedCount(group.key) === group.requiredCount;
            });
        },

        // --- Aggregate summary ---

        getAggregateValues(key) {
            let baseline;
            let current;

            this.aspects.forEach((aspect, i) => {
                if (!this.selectedByAspect[i]) return;
                const bq = num(aspect.initial_quality, 500);
                const cq = this.qualityByAspect[i];

                (aspect.modifiers ?? []).forEach((mod) => {
                    if (str(mod?.property_key) !== key) return;
                    baseline = (baseline ?? 1) * this.interpolateModifier(mod, bq);
                    current = (current ?? 1) * this.interpolateModifier(mod, cq);
                });
            });

            return { baseline, current };
        },

        get aggregateEmptyState() {
            if (!this.hasInteractiveAspects) {
                return {
                    title: "No adjustable output tuning available",
                    copy: "This blueprint does not expose quality-range modifier data for its recipe inputs.",
                };
            }

            if (!this.selectionGroupsComplete) {
                const first = this.selectionGroups.find(
                    (g) => this.getGroupSelectedCount(g.key) !== g.requiredCount,
                );
                return {
                    title: "Selection incomplete",
                    copy: first
                        ? first.displayName
                            ? `Select ${first.requiredCount} of ${first.optionCount} options in ${first.displayName} to preview full output changes.`
                            : `Select ${first.requiredCount} of ${first.optionCount} options to preview full output changes.`
                        : "Complete every grouped input selection to preview full output changes.",
                };
            }

            const hasVisible = this.summaryProperties.some((sp) => {
                const key = str(sp?.property_key);
                if (!key) return false;
                const { baseline, current } = this.getAggregateValues(key);
                if (baseline === undefined || current === undefined) return false;
                return !this.isNeutral(this.relativeChange(baseline, current));
            });

            if (!hasVisible) {
                return {
                    title: "No output changes from baseline",
                    copy: "Move any quality slider away from its baseline to preview tuning changes.",
                };
            }

            return null;
        },

        isAggregateVisible(propertyKey) {
            const { baseline, current } = this.getAggregateValues(propertyKey);
            if (baseline === undefined || current === undefined) return false;
            return !this.isNeutral(this.relativeChange(baseline, current));
        },

        getAggregateCardClass(propertyKey) {
            const { baseline, current } = this.getAggregateValues(propertyKey);
            if (baseline === undefined || current === undefined) {
                return "rounded-box border border-base-300 bg-base-100 px-4 py-3 transition-colors";
            }
            const sp = this.summaryProperties.find((p) => str(p?.property_key) === propertyKey);
            const rel = this.relativeChange(baseline, current);
            if (this.isNeutral(rel)) return "rounded-box border border-base-300 bg-base-100 px-4 py-3 transition-colors";
            return this.toneClasses(sp, rel).card + " transition-colors";
        },

        getAggregateChangeText(propertyKey) {
            const { baseline, current } = this.getAggregateValues(propertyKey);
            if (baseline === undefined || current === undefined) return "No change";
            const sp = this.summaryProperties.find((p) => str(p?.property_key) === propertyKey);
            return this.formatSemanticChange(sp, this.relativeChange(baseline, current));
        },

        getAggregateChangeClass(propertyKey) {
            const { baseline, current } = this.getAggregateValues(propertyKey);
            if (baseline === undefined || current === undefined) return "text-sm font-semibold tabular-nums text-subtle";
            const sp = this.summaryProperties.find((p) => str(p?.property_key) === propertyKey);
            const rel = this.relativeChange(baseline, current);
            return "text-sm font-semibold tabular-nums " + this.toneClasses(sp, rel).text;
        },

        // --- BOM ---

        get bomHasSelected() {
            return this.selectedByAspect.some(Boolean);
        },

        isBomRowVisible(index) {
            return this.selectedByAspect[index] !== false;
        },

        getBomQuality(index) {
            return this.selectedByAspect[index] ? `Q${this.qualityByAspect[index]}` : "Excluded";
        },

        getBomQualityClass(index) {
            return this.selectedByAspect[index] ? "font-medium tabular-nums text-emphasis" : "text-subtle";
        },
    };
}

// --- Utility functions ---

function num(value, fallback = 0) {
    const n = Number(value);
    return Number.isFinite(n) ? n : fallback;
}

function str(value, fallback = "") {
    return typeof value === "string" && value !== "" ? value : fallback;
}
