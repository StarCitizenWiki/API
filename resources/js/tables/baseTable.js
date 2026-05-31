import {
	Tabulator,
	Module,
	FilterModule,
	AjaxModule,
	SortModule,
	PageModule,
	FormatModule,
	EditModule,
	FrozenColumnsModule,
	MoveColumnsModule,
	MoveRowsModule,
	ResizeColumnsModule,
	SelectRowModule,
} from "tabulator-tables";

/**
 * Keeps horizontal scroll position across re-renders (remote sort/filter/pagination).
 *
 * Enable/disable per table with:
 *   preserveHorizontalScroll: true/false
 */
class ScrollPositionModule extends Module {
	static moduleName = "scrollPosition";
	static moduleInitOrder = 1;

	lastScrollLeft = 0;

	constructor(table) {
		super(table);

		this.registerTableOption("preserveHorizontalScroll", true);
	}

	initialize() {
		if (!this.table.options.preserveHorizontalScroll) return;

		this.table.on("scrollHorizontal", (left) => {
			if (left > 0) {
				this.lastScrollLeft = left;
			}
		});

		this.table.on("renderComplete", () => {
			const holder = this.table.element?.querySelector?.(
				".tabulator-tableholder",
			);
			if (!holder) {
				return;
			}

			// Defer a frame so Tabulator finishes its own scroll syncing
			requestAnimationFrame(() => {
				holder.scrollLeft = this.lastScrollLeft;
			});
		});
	}
}

Tabulator.registerModule([
	ScrollPositionModule,
	FilterModule,
	AjaxModule,
	SortModule,
	PageModule,
	FormatModule,
	EditModule,
	FrozenColumnsModule,
	MoveColumnsModule,
	MoveRowsModule,
	ResizeColumnsModule,
	SelectRowModule,
]);

const tabulatorTables = new Map();
const tabulatorBeforeInitCallbacks = [];

export function registerTabulatorBeforeInit(callback) {
	if (typeof callback === "function") {
		tabulatorBeforeInitCallbacks.push(callback);
	}
}

function runTabulatorBeforeInitCallbacks(context) {
	tabulatorBeforeInitCallbacks.forEach((callback) => callback(context));
}

export function getTabulatorTable(id) {
	return tabulatorTables.get(id);
}

function readJsonScript(id) {
	const el = document.getElementById(id);
	if (!el) return null;
	return JSON.parse(el.textContent || "null");
}

function get(obj, path, fallback = undefined) {
	if (!path) return fallback;
	return (
		path
			.split(".")
			.reduce((acc, k) => (acc && acc[k] != null ? acc[k] : undefined), obj) ??
		fallback
	);
}

function mapFilterFieldToApiField(field, columnToApiFilterFieldMap = null) {
	if (!field) return field;
	if (columnToApiFilterFieldMap?.[field]) {
		return columnToApiFilterFieldMap[field];
	}
	return field === "created_at_human" ? "created_at" : field;
}

function mapApiFilterFieldToColumnField(
	apiField,
	columnFieldsSet,
	apiToColumnFilterFieldMap,
) {
	if (!apiField) return apiField;

	if (apiToColumnFilterFieldMap?.[apiField]) {
		return apiToColumnFilterFieldMap[apiField];
	}

	if (apiField === "created_at" && columnFieldsSet?.has?.("created_at_human")) {
		return "created_at_human";
	}

	return apiField;
}

function collectColumnFields(columns, externalFilters, set = new Set()) {
	(columns ?? []).forEach((column) => {
		if (Array.isArray(column?.columns) && column.columns.length > 0) {
			collectColumnFields(column.columns, null, set);
		} else if (column?.field) {
			set.add(column.field);
		}
	});
	(externalFilters ?? []).forEach((f) => {
		if (f?.field) set.add(f.field);
	});
	return set;
}

function collectManagedHeaderFilterApiFields(
	columns,
	columnToApiFilterFieldMap = null,
	externalFilters = null,
	set = new Set(),
) {
	(columns ?? []).forEach((column) => {
		if (Array.isArray(column?.columns) && column.columns.length > 0) {
			collectManagedHeaderFilterApiFields(
				column.columns,
				columnToApiFilterFieldMap,
				null,
				set,
			);
			return;
		}

		if (!column?.field) return;

		if (column.headerFilter !== undefined && column.headerFilter !== false) {
			set.add(
				mapFilterFieldToApiField(column.field, columnToApiFilterFieldMap),
			);
		}
	});

	(externalFilters ?? []).forEach((f) => {
		if (f?.field) {
			set.add(mapFilterFieldToApiField(f.field, columnToApiFilterFieldMap));
		}
	});

	return set;
}

function isMultiselectListFilter(column) {
	return (
		column?.headerFilter === "list" &&
		column?.headerFilterParams?.multiselect !== false
	);
}

function collectMultiselectListFilterFields(columns, set = new Set()) {
	(columns ?? []).forEach((column) => {
		if (Array.isArray(column?.columns) && column.columns.length > 0) {
			collectMultiselectListFilterFields(column.columns, set);
		} else if (isMultiselectListFilter(column) && column?.field) {
			set.add(column.field);
		}
	});
	return set;
}

function normalizeListFilterValue(value) {
	return (Array.isArray(value) ? value : [value]).filter(
		(item) => item !== null && item !== undefined && String(item).length > 0,
	);
}

function isEmptyListFilterValue(value) {
	return normalizeListFilterValue(value).length === 0;
}

function serializeFilterValue(value) {
	if (!Array.isArray(value)) {
		return value == null || String(value).length === 0 ? null : String(value);
	}

	const values = normalizeListFilterValue(value);
	return values.length > 0 ? values.join(",") : null;
}

function flattenListOptions(values, depth = 0) {
	if (Array.isArray(values)) {
		return values.flatMap((option) => {
			if (Array.isArray(option?.options)) {
				return [
					{ group: String(option.label ?? "Group") },
					...flattenListOptions(option.options, depth + 1),
				];
			}

			const value = option && typeof option === "object" && "value" in option
				? option.value
				: option;
			const label = option && typeof option === "object" && "label" in option
				? option.label
				: value;

			return [{ value: String(value ?? ""), label: String(label ?? value ?? ""), depth }];
		});
	}

	return Object.entries(values ?? {}).map(([value, label]) => ({
		value: String(value),
		label: String(label),
		depth,
	}));
}

function listOptionsWithAll(values) {
	const options = flattenListOptions(values);

	return options.some((option) => option.value === "")
		? options
		: [{ value: "", label: "All", depth: 0 }, ...options];
}

function sameStringSet(left, right) {
	return left.length === right.length && left.every((value) => right.includes(value));
}

function positionDropdownPanel(panel, anchor) {
	const gutter = 4;
	const rect = anchor.getBoundingClientRect();
	const width = Math.max(rect.width, 220);

	panel.style.top = `${rect.bottom + gutter}px`;
	panel.style.left = `${Math.max(
		gutter,
		Math.min(rect.left, window.innerWidth - width - gutter),
	)}px`;
	panel.style.width = `${width}px`;
}

let activeMultiListDropdownClose = null;

function multiListDropdownHeaderFilter(cell, onRendered, success, cancel, params) {
	const options = listOptionsWithAll(params?.values ?? { "": "All" });
	const root = document.createElement("div");
	const button = document.createElement("button");
	let draftValue = normalizeListFilterValue(cell.getValue()).map(String);
	let appliedValue = [...draftValue];
	let panel = null;
	let panelListeners = null;

	root.className = "w-full";
	button.type = "button";
	button.className = "tabulator-multiselect-filter";
	root.appendChild(button);

	const optionLabel = (value) =>
		options.find((option) => option.value === value)?.label ?? value;

	const updateButton = () => {
		button.textContent = draftValue.length === 0
			? optionLabel("")
			: draftValue.length === 1
				? optionLabel(draftValue[0])
				: `${draftValue.length} selected`;
	};

	const setDraftValue = (value) => {
		draftValue = normalizeListFilterValue(value).map(String);
		updateButton();
	};

	const closePanel = ({ apply = true } = {}) => {
		const shouldApply = apply && !sameStringSet(draftValue, appliedValue);

		panel?.remove();
		panel = null;
		panelListeners?.abort();
		panelListeners = null;

		if (activeMultiListDropdownClose === closePanel) {
			activeMultiListDropdownClose = null;
		}

		if (shouldApply) {
			appliedValue = [...draftValue];
			success([...appliedValue]);
		}
	};

	const renderPanel = () => {
		if (!panel) return;

		panel.replaceChildren();
		options.forEach((option) => {
			if (option.group) {
				const group = document.createElement("div");
				group.className = "px-2 pt-2 pb-1 text-xs font-semibold uppercase tracking-wide text-subtle";
				group.textContent = option.group;
				panel.appendChild(group);
				return;
			}

			const row = document.createElement("label");
			const checkbox = document.createElement("input");
			const text = document.createElement("span");
			const isAllOption = option.value === "";

			row.className = "flex cursor-pointer items-center gap-2 rounded px-2 py-1 text-sm hover:bg-base-200";
			row.style.paddingLeft = `${0.5 + (option.depth ?? 0) * 0.75}rem`;
			checkbox.type = "checkbox";
			checkbox.className = "checkbox checkbox-xs";
			checkbox.checked = isAllOption ? draftValue.length === 0 : draftValue.includes(option.value);
			text.textContent = option.label;

			checkbox.addEventListener("change", () => {
				if (isAllOption) {
					setDraftValue([]);
				} else {
					const selected = new Set(draftValue);
					checkbox.checked ? selected.add(option.value) : selected.delete(option.value);
					setDraftValue(Array.from(selected));
				}

				renderPanel();
			});

			row.append(checkbox, text);
			panel.appendChild(row);
		});
	};

	const openPanel = () => {
		if (panel) {
			closePanel();
			return;
		}

		activeMultiListDropdownClose?.();

		panel = document.createElement("div");
		panel.className = "rounded-box border border-base-300 bg-base-100 p-1 shadow-lg";
		Object.assign(panel.style, {
			position: "fixed",
			zIndex: "9999",
			maxHeight: "20rem",
			overflowY: "auto",
		});
		renderPanel();
		document.body.appendChild(panel);

		const positionPanel = () => positionDropdownPanel(panel, button);
		positionPanel();

		panelListeners = new AbortController();
		const { signal } = panelListeners;
		document.addEventListener("mousedown", (event) => {
			if (!root.contains(event.target) && !panel?.contains(event.target)) {
				closePanel();
			}
		}, { signal });
		document.addEventListener("keydown", (event) => {
			if (event.key === "Escape") closePanel();
		}, { signal });
		window.addEventListener("resize", positionPanel, { signal });
		document.addEventListener("scroll", positionPanel, {
			capture: true,
			passive: true,
			signal,
		});

		activeMultiListDropdownClose = closePanel;
	};

	root.addEventListener("mousedown", (event) => event.stopPropagation());
	button.addEventListener("click", openPanel);
	updateButton();

	return root;
}

function buildInverseSortFieldMap(sortFieldMap) {
	const inverse = {};
	Object.entries(sortFieldMap ?? {}).forEach(([columnField, apiField]) => {
		if (!apiField) return;
		if (inverse[apiField] == null) {
			inverse[apiField] = columnField;
		}
	});
	return inverse;
}

function parsePositiveInt(value) {
	if (value == null) return null;
	const n = Number(value);
	if (!Number.isFinite(n)) return null;
	const i = Math.trunc(n);
	return i > 0 ? i : null;
}

function parseJsonApiStateFromLocation({
	columnFields,
	apiToColumnSortFieldMap,
	apiToColumnFilterFieldMap,
	listFilterFields,
}) {
	const pageUrl = new URL(window.location.href);

	// sort=a,-b
	const sortParam = pageUrl.searchParams.get("sort");
	const initialSort = [];
	if (sortParam) {
		for (const raw of sortParam
			.split(",")
			.map((s) => s.trim())
			.filter(Boolean)) {
			const desc = raw.startsWith("-");
			const apiField = desc ? raw.slice(1) : raw;

			const columnField = apiToColumnSortFieldMap?.[apiField] ?? apiField;

			if (columnFields.has(columnField)) {
				initialSort.push({
					column: columnField,
					dir: desc ? "desc" : "asc",
				});
			}
		}
	}

	// filters: filter[field]=value
	const initialHeaderFilter = [];
	for (const [key, value] of pageUrl.searchParams.entries()) {
		const match = key.match(/^filter\[([^\]]+)\]$/);
		if (!match) continue;

		const apiField = match[1];
		if (value == null || String(value).length === 0) continue;

		const columnField = mapApiFilterFieldToColumnField(
			apiField,
			columnFields,
			apiToColumnFilterFieldMap,
		);
		if (!columnFields.has(columnField)) continue;

		const filterValue = listFilterFields?.has(columnField)
			? normalizeListFilterValue(value.split(","))
			: String(value);

		if (Array.isArray(filterValue) && filterValue.length === 0) continue;

		initialHeaderFilter.push({ field: columnField, value: filterValue });
	}

	// JSON:API pagination
	const paginationSize = parsePositiveInt(
		pageUrl.searchParams.get("page[size]"),
	);
	const paginationInitialPage = parsePositiveInt(
		pageUrl.searchParams.get("page[number]"),
	);

	return {
		initialSort: initialSort.length ? initialSort : null,
		initialHeaderFilter: initialHeaderFilter.length
			? initialHeaderFilter
			: null,
		paginationSize,
		paginationInitialPage,
	};
}

// Convert Tabulator params -> JSON:API query string
function buildJsonApiUrl(baseUrl, params, defaults = {}) {
	const u = new URL(baseUrl, window.location.origin);
	const columnToApiFilterFieldMap = defaults.columnToApiFilterFieldMap ?? null;

	// pagination
	const defaultPage = defaults.page ?? 1;
	const defaultSize = defaults.pageSize ?? null;

	if (params.page != null && Number(params.page) !== defaultPage) {
		u.searchParams.set("page[number]", String(params.page));
	} else {
		u.searchParams.delete("page[number]");
	}

	if (
		params.size != null &&
		(defaultSize === null || Number(params.size) !== Number(defaultSize))
	) {
		u.searchParams.set("page[size]", String(params.size));
	} else {
		u.searchParams.delete("page[size]");
	}

	// sorting: sort=a,-b
	const sorters = params.sorters ?? params.sort ?? [];
	if (sorters.length) {
		const normalizedSorters = Array.isArray(sorters) ? sorters : [];
		const sort = normalizedSorters
			.map((sorter) => (sorter.dir === "desc" ? "-" : "") + sorter.field)
			.join(",");
		u.searchParams.set("sort", sort);
	} else {
		u.searchParams.delete("sort");
	}

	// filters: filter[field]=value (simple mapping)
	const filters = params.filter ?? params.filters ?? [];

	// Build a Set of API-field-names that are managed by Tabulator.
	// Important: this must include fields even when the current filter is cleared,
	// otherwise we'd "preserve" stale filter[...] params from the base URL.
	const managedDefaults = defaults.managedFilterFields
		? Array.from(defaults.managedFilterFields)
		: [];
	const managedFields = new Set([
		...managedDefaults,
		...filters
			.map((f) => mapFilterFieldToApiField(f?.field, columnToApiFilterFieldMap))
			.filter(Boolean),
	]);

	// Preserve existing filter[...] keys that aren't managed by Tabulator
	const preservedFilters = new Map();
	for (const key of [...u.searchParams.keys()]) {
		if (key.startsWith("filter[")) {
			const match = key.match(/^filter\[([^\]]+)\]$/);
			if (match) {
				const apiField = match[1];
				// Only preserve if NOT managed by Tabulator
				if (!managedFields.has(apiField)) {
					preservedFilters.set(key, u.searchParams.get(key));
				}
			}
		}
	}

	// Clear all filter params (we'll rebuild them)
	for (const key of [...u.searchParams.keys()]) {
		if (key.startsWith("filter[")) u.searchParams.delete(key);
	}

	// Restore preserved filters first
	for (const [key, value] of preservedFilters) {
		u.searchParams.set(key, value);
	}

	// Then add/override with Tabulator's filters
	for (const f of filters) {
		const value = serializeFilterValue(f?.value);
		if (!f?.field || value === null) continue;

		const name = mapFilterFieldToApiField(f.field, columnToApiFilterFieldMap);
		u.searchParams.set(`filter[${name}]`, value);
	}

	return u.toString();
}

/**
 * Syncs a computed API URL into:
 * - an optional "API URL" input + open links (existing behavior)
 * - window.history (optional), typically for filter query params
 *
 * @param {string} urlString - The API URL (including query params)
 * @param {string|null} targetId - DOM id of the API URL input (optional)
 * @param {"replace"|"push"|null} historySyncMode - null disables history sync
 * @param {"filters"|"all"} historySyncScope - "filters" copies only filter[...] params, "all" copies full querystring
 */
function syncBrowserUrl(
	urlString,
	targetId,
	historySyncMode = "replace",
	historySyncScope = "all",
) {
	const target = targetId ? document.getElementById(targetId) : null;
	const openLinks = targetId
		? document.querySelectorAll(`[data-api-url-open="${targetId}"]`)
		: [];

	let resolvedUrl = urlString;

	try {
		resolvedUrl = decodeURIComponent(urlString);
	} catch (error) {
		resolvedUrl = urlString;
	}

	if (target) {
		target.value = resolvedUrl;
		openLinks.forEach((link) => {
			link.setAttribute("href", resolvedUrl);
		});
	}

	if (!historySyncMode) return;

	try {
		const apiUrl = new URL(urlString, window.location.origin);
		const pageUrl = new URL(window.location.href);

		const preservedTableColumns = pageUrl.searchParams.get("columns");

		if (historySyncScope === "all") {
			pageUrl.search = apiUrl.search;
			if (preservedTableColumns) {
				pageUrl.searchParams.set("columns", preservedTableColumns);
			}
		} else {
			for (const key of [...pageUrl.searchParams.keys()]) {
				if (key.startsWith("filter[")) pageUrl.searchParams.delete(key);
			}

			for (const [k, v] of apiUrl.searchParams.entries()) {
				if (k.startsWith("filter[")) pageUrl.searchParams.set(k, v);
			}
		}

		if (historySyncMode === "push") {
			window.history.pushState({}, "", pageUrl.toString());
		} else {
			window.history.replaceState({}, "", pageUrl.toString());
		}
	} catch (e) {}
}

const COLUMN_FORMATTERS = {
	yesNo: (cell, params) => {
		const trueLabel = params?.trueLabel ?? "Yes";
		const falseLabel = params?.falseLabel ?? "No";

		if (!cell.getValue()) {
			return "";
		}

        const value = cell.getValue();

        if (Array.isArray(value)) {
            return value.length ? trueLabel : falseLabel;
        }

        if (value && typeof value === "object") {
            return Object.keys(value).length ? trueLabel : falseLabel;
        }

        return value ? trueLabel : falseLabel;
	},
	pct: (cell, params) => {
		if (typeof cell.getValue() !== "number" || isNaN(cell.getValue())) {
			return "";
		}

		const val = cell.getValue() * 100;

		if (params.suffix === false) {
			return val.toFixed(0);
		}

		return `${val.toFixed(0)}%`;
	},
	pctDelta: (cell, params) => {
		if (typeof cell.getValue() !== "number" || isNaN(cell.getValue())) {
			return "";
		}

		const val = cell.getValue() * 100;

		if (val === 0) {
			return "";
		}

		if (params.suffix === false) {
			return val.toFixed(0);
		}

		return `${val > 0 ? "+" : ""}${val.toFixed(0)}%`;
	},
	volumeWithUnit: (cell, params) => {
		const value = cell.getValue();

		if (value === null || value === undefined || value === "") {
			return "";
		}

		const unitField = params?.unitField ?? "dimension.volume_converted_unit";
		const unit = unitField ? get(cell.getData(), unitField, null) : null;
		const numericValue = typeof value === "number" ? value : Number(value);
		const formatted = Number.isFinite(numericValue)
			? new Intl.NumberFormat(params?.locale, {
					minimumFractionDigits: params?.minimumFractionDigits ?? 0,
					maximumFractionDigits: params?.maximumFractionDigits ?? 2,
				}).format(numericValue)
			: String(value);

		if (!unit) {
			return formatted;
		}

		return `${formatted}${params?.separator ?? " "}${unit}`;
	},
	labelList: (cell, params) => {
		const value = cell.getValue();

		if (!Array.isArray(value) || value.length === 0) {
			return "";
		}

		const labelField = params?.labelField ?? "label";
		const fallbackField = params?.fallbackField ?? "name";
		const separator = params?.separator ?? ", ";

		const labels = value
			.map((entry) => {
				if (!entry || typeof entry !== "object") {
					return "";
				}

				const preferred = get(entry, labelField, null);
				const fallback = get(entry, fallbackField, null);
				const resolved = preferred ?? fallback;

				return typeof resolved === "string" ? resolved.trim() : "";
			})
			.filter((label) => label !== "");

		return labels.join(separator);
	},
	viewButton: (cell, params) => {
		const label = params?.label ?? "View";
		const hrefField = params?.hrefField ?? null;
		const fallbackHref = params?.href ?? "#";
		const href = hrefField
			? get(cell.getData(), hrefField, fallbackHref)
			: fallbackHref;
		const isDisabled = params?.disabled ?? false;
		const classes = params?.class ?? "btn btn-sm btn-ghost";

		if (isDisabled) {
			return `<span class="${classes} pointer-events-none opacity-50" aria-disabled="true">${label}</span>`;
		}

		return `<a class="${classes}" href="${href}">${label}</a>`;
	},
};

function populateSelect(select, values) {
	select.innerHTML = "";

	if (Array.isArray(values)) {
		values.forEach((group) => {
			if (group?.options) {
				const optgroup = document.createElement("optgroup");
				optgroup.label = group.label;
				group.options.forEach((opt) => {
					const o = document.createElement("option");
					o.value = opt.value;
					o.textContent = opt.label;
					optgroup.appendChild(o);
				});
				select.appendChild(optgroup);
			} else {
				const o = document.createElement("option");
				o.value = group.value;
				o.textContent = group.label;
				select.appendChild(o);
			}
		});
	} else if (typeof values === "object") {
		Object.entries(values).forEach(([val, label]) => {
			const o = document.createElement("option");
			o.value = val;
			o.textContent = label;
			select.appendChild(o);
		});
	}
}

function normalizeColumns(columns) {
	return (columns ?? []).map((column) => {
		if (Array.isArray(column.columns) && column.columns.length > 0) {
			return { ...column, columns: normalizeColumns(column.columns) };
		}

		const normalized = { ...column };

		if (typeof column.formatter === "string" && COLUMN_FORMATTERS[column.formatter]) {
			normalized.formatter = COLUMN_FORMATTERS[column.formatter];
		}

		if (column.headerFilter === "list") {
			const headerFilterParams = {
				clearable: true,
				multiselect: true,
				...(column.headerFilterParams ?? {}),
			};

			if (headerFilterParams.multiselect !== false) {
				normalized.headerFilter = multiListDropdownHeaderFilter;
				normalized.headerFilterLiveFilter = false;
				normalized.headerFilterEmptyCheck ??= isEmptyListFilterValue;
				normalized.headerFilterFunc ??= "in";
			}

			normalized.headerFilterParams = headerFilterParams;
		}

		return normalized;
	});
}

function buildSelectValues(options) {
	const hasGroups = (options ?? []).some(
		(option) => typeof option?.group === "string" && option.group !== "",
	);

	if (!hasGroups) {
		return [
			{ label: "All", value: "" },
			...(options ?? [])
				.filter((option) => option?.value !== null && option?.value !== "")
				.map((option) => ({
					label:
						typeof option?.count === "number"
							? `${option.label} (${option.count})`
							: option.label,
					value: option.value,
				})),
		];
	}

	const grouped = {};
	(options ?? []).forEach((option) => {
		if (option?.value === null || option?.value === "") {
			return;
		}

		const group = option.group || "Unknown";
		if (!grouped[group]) {
			grouped[group] = [];
		}
		grouped[group].push(option);
	});

	const result = [{ label: "All", value: "" }];

	Object.keys(grouped)
		.sort()
		.forEach((group) => {
			result.push({
				label: group,
				options: grouped[group].map((option) => ({
					label:
						typeof option?.count === "number"
							? `${option.label} (${option.count})`
							: option.label,
					value: option.value,
				})),
			});
		});

	return result;
}

function applyHeaderFilterOptionsToColumns(columns, optionsMap, payload) {
	const filters = payload?.filters ?? {};

	return (columns ?? []).map((column) => {
		if (Array.isArray(column.columns) && column.columns.length > 0) {
			return {
				...column,
				columns: applyHeaderFilterOptionsToColumns(
					column.columns,
					optionsMap,
					payload,
				),
			};
		}

		const filterKey = optionsMap?.[column.field];

		if (!filterKey) {
			return column;
		}

		const facetData = filters[filterKey];
		const hasExistingValues =
			Object.keys(column.headerFilterParams?.values ?? {}).length > 0;

		if (!facetData && hasExistingValues) {
			return column;
		}

		const values = buildSelectValues(facetData ?? []);

		return {
			...column,
			headerFilter: column.headerFilter ?? "list",
			headerFilterParams: {
				...(column.headerFilterParams ?? {}),
				values,
			},
		};
	});
}

function applyHeaderFilterOptionsToColumnComponents(
	columnComponents,
	optionsMap,
	payload,
	mount = null,
) {
	const filters = payload?.filters ?? {};

	(columnComponents ?? []).forEach((column) => {
		const subColumns = column?.getSubColumns?.() ?? [];

		if (subColumns.length > 0) {
			applyHeaderFilterOptionsToColumnComponents(
				subColumns,
				optionsMap,
				payload,
				mount,
			);
			return;
		}

		const field = column?.getField?.();
		const filterKey = optionsMap?.[field];
		const definition = column?.getDefinition?.();

		if (
			!filterKey ||
			!definition ||
			(
				definition.headerFilter !== "list" &&
				definition.headerFilter !== multiListDropdownHeaderFilter
			)
		) {
			return;
		}

		const facetData = filters[filterKey];
		const hasExistingValues =
			Object.keys(definition.headerFilterParams?.values ?? {}).length > 0;

		if (!facetData && hasExistingValues) {
			return;
		}

		definition.headerFilterParams = {
			...(definition.headerFilterParams ?? {}),
			values: buildSelectValues(facetData ?? []),
		};

		column.reloadHeaderFilter?.();
	});

	if (mount) {
		const externalFilterContainer = mount.parentElement;
		const externalSelects =
			externalFilterContainer?.querySelectorAll("[data-external-filter]") ?? [];
		externalSelects.forEach((select) => {
			if (select.dataset.externalFilterStatic !== undefined) return;

			const field = select.dataset.externalFilter;
			const filterKey = optionsMap?.[field];
			if (!filterKey) return;

			const facetData = filters[filterKey];
			const current = select.value;

			populateSelect(select, buildSelectValues(facetData ?? []));
			select.value = current;
		});
	}
}

function buildMirroredQueryUrl(sourceUrl, targetBaseUrl) {
	const source = new URL(sourceUrl, window.location.origin);
	const target = new URL(targetBaseUrl, window.location.origin);

	target.search = source.search;

	return target.toString();
}

/**
 * Build a { [columnField]: sortField } map, including nested/group columns.
 */
function isMobile() {
	return window.innerWidth < 768;
}

function stripFrozen(columns) {
	return (columns ?? []).map((col) => {
		const { frozen, ...rest } = col;
		if (Array.isArray(rest.columns)) {
			return { ...rest, columns: stripFrozen(rest.columns) };
		}
		return rest;
	});
}

function buildSortFieldMap(columns, map = {}) {
	(columns ?? []).forEach((column) => {
		if (column?.field && column?.sortField) {
			map[column.field] = column.sortField;
		}

		if (Array.isArray(column?.columns) && column.columns.length > 0) {
			buildSortFieldMap(column.columns, map);
		}
	});

	return map;
}

function mobileSafeColumns(columns) {
	return isMobile() ? stripFrozen(columns) : columns;
}

export function initTabulatorTables() {
	document.querySelectorAll("[data-tabulator]").forEach((mount) => {
		const id = mount.dataset.tabulatorId;
		const config = readJsonScript(`${id}-config`) || {};
		const initial = readJsonScript(`${id}-initial`); // can be null

		const endpoint = config.endpoint;
		const lastPagePath = config?.meta?.lastPagePath || "meta.last_page";
		const pageSize = config.pageSize ?? 25;
		const headerFilterOptionsMap = config.headerFilterOptionsMap ?? null;
		const columnToApiFilterFieldMap = headerFilterOptionsMap;
		const headerFilterOptionsSeed =
			config.initialHeaderFilterOptions ??
			(Array.isArray(config.initialHeaderFilter)
				? null
				: (config.initialHeaderFilter ?? null));
		const configInitialHeaderFilter = Array.isArray(config.initialFilters)
			? config.initialFilters
			: Array.isArray(config.initialHeaderFilter)
				? config.initialHeaderFilter
				: false;

		const filterOptionsEndpoint = config.filterOptionsEndpoint ?? null;
		const apiUrlTargetId = config.apiUrlTargetId ?? null;
		const apiUrlTarget = apiUrlTargetId
			? document.getElementById(apiUrlTargetId)
			: null;

		// History sync options:
		// - historySyncMode: "replace" (recommended) | "push" | null (disabled)
		// - historySyncScope: "filters" (default) | "all"
		const historySyncMode = config.historySyncMode ?? "replace";
		const historySyncScope = config.historySyncScope ?? "all";
		let sortFieldMap = buildSortFieldMap(config.columns ?? []);
		const apiToColumnFilterFieldMap = columnToApiFilterFieldMap
			? Object.entries(columnToApiFilterFieldMap).reduce(
					(acc, [columnField, apiField]) => {
						if (apiField) {
							acc[apiField] = columnField;
						}
						return acc;
					},
					{},
				)
			: null;

		const defaultColumns =
			headerFilterOptionsSeed && headerFilterOptionsMap
				? applyHeaderFilterOptionsToColumns(
						config.columns ?? [],
						headerFilterOptionsMap,
						{
							filters: headerFilterOptionsSeed,
						},
					)
				: (config.columns ?? []);
		const afterReadyCallbacks = [];
		const beforeInitContext = {
			id,
			mount,
			config,
			defaultColumns,
			columns: defaultColumns,
			sortFieldMap,
			headerFilterOptionsMap,
			headerFilterOptionsSeed,
			applyHeaderFilterOptionsToColumns,
			normalizeColumns,
			mobileSafeColumns,
			onReady(callback) {
				if (typeof callback === "function") {
					afterReadyCallbacks.push(callback);
				}
			},
		};

		runTabulatorBeforeInitCallbacks(beforeInitContext);

		sortFieldMap = beforeInitContext.sortFieldMap;
		const columns = beforeInitContext.columns ?? defaultColumns;
		const apiToColumnSortFieldMap = buildInverseSortFieldMap(sortFieldMap);

		const externalFilters = config.externalFilters ?? null;
		const externalFilterFields = new Set(
			(externalFilters ?? []).map((f) => f.field).filter(Boolean),
		);

		const columnFields = collectColumnFields(columns, externalFilters);
		const listFilterFields = collectMultiselectListFilterFields(columns);

		// Persistent set of API filter fields that Tabulator manages for this table instance.
		// This is what prevents "sticky" filter[...] params when a header filter is cleared.
		const managedApiFilterFields = collectManagedHeaderFilterApiFields(
			columns,
			columnToApiFilterFieldMap,
			externalFilters,
		);

		// Seed table state from the current browser URL so reload keeps sort/filter/page.
		const urlState = parseJsonApiStateFromLocation({
			columnFields,
			apiToColumnSortFieldMap,
			apiToColumnFilterFieldMap,
			listFilterFields,
		});

		const effectiveInitialHeaderFilter =
			urlState.initialHeaderFilter ?? configInitialHeaderFilter;
		const effectiveInitialSort =
			urlState.initialSort ??
			(Array.isArray(config.initialSort) ? config.initialSort : null);

		// Separate external filter initial values from table header filter values
		const externalInitialValues = new Map();
		const tableInitialHeaderFilter = Array.isArray(effectiveInitialHeaderFilter)
			? effectiveInitialHeaderFilter.filter((f) => {
					if (externalFilterFields.has(f.field)) {
						externalInitialValues.set(f.field, f.value);
						return false;
					}
					return true;
				})
			: effectiveInitialHeaderFilter;

		const effectivePaginationSize = urlState.paginationSize ?? pageSize;
		const effectivePaginationInitialPage =
			urlState.paginationInitialPage ?? null;

		let servedInitial = false;
		let latestFilterOptionsRequestId = 0;

		const refreshHeaderFilterOptions = (sourceUrl, ajaxConfig) => {
			if (!filterOptionsEndpoint || !headerFilterOptionsMap) {
				return Promise.resolve();
			}

			const requestId = ++latestFilterOptionsRequestId;
			const filterOptionsUrl = buildMirroredQueryUrl(
				sourceUrl,
				filterOptionsEndpoint,
			);

			return fetch(filterOptionsUrl, { ...ajaxConfig, method: "GET" })
				.then(async (response) => {
					if (!response.ok) {
						throw new Error(`HTTP ${response.status}`);
					}

					return response.json();
				})
				.then((payload) => {
					if (requestId !== latestFilterOptionsRequestId) {
						return;
					}

					applyHeaderFilterOptionsToColumnComponents(
						table.getColumns(),
						headerFilterOptionsMap,
						payload,
						mount,
					);
				})
				.catch(() => {});
		};

		const mobile = isMobile();

		const table = new Tabulator(mount, {
			layout: "fitDataFill",

			preserveHorizontalScroll: true,

			columnDefaults: {
				...(config.columnDefaults ?? {}),
				resizable: true,
			},

			rowHeader: {
				headerSort: false,
				resizable: false,
				minWidth: 30,
				width: 30,
				rowHandle: true,
				formatter: "handle",
				...(mobile ? {} : { frozen: true }),
			},

			columns: normalizeColumns(mobileSafeColumns(columns)),
			movableColumns: true,
			movableRows: true,
			selectableRows: true,

			pagination: true,
			paginationMode: "remote",
			paginationSize: effectivePaginationSize,
			paginationSizeSelector: [25, 50, 100, 200],
			...(effectivePaginationInitialPage
				? { paginationInitialPage: effectivePaginationInitialPage }
				: {}),

			initialHeaderFilter: tableInitialHeaderFilter,
			headerFilterLiveFilterDelay: 600,

			...(effectiveInitialSort ? { initialSort: effectiveInitialSort } : {}),

			headerWordWrap: true,

			sortMode: "remote",
			sortOrderReverse: true,
			filterMode: "remote",

			ajaxURL: endpoint,
			ajaxConfig: {
				credentials: "same-origin",
				headers: {
					Accept: "application/json",
					"X-Requested-With": "XMLHttpRequest",
				},
			},

			ajaxRequestFunc: (url, ajaxConfig, params) => {
				const requestParams = { ...params };

				const requestFilters =
					requestParams.filter ?? requestParams.filters ?? [];
				for (const f of requestFilters) {
					const apiField = mapFilterFieldToApiField(
						f?.field,
						columnToApiFilterFieldMap,
					);
					if (apiField) managedApiFilterFields.add(apiField);
				}

				// Map UI sort fields -> API sort fields for the request only.
				const sorters = requestParams.sorters ?? requestParams.sort ?? [];
				if (Array.isArray(sorters) && sorters.length) {
					requestParams.sorters = sorters.map((sorter) => ({
						...sorter,
						field: sortFieldMap[sorter.field] ?? sorter.field,
					}));
				}

				const finalUrl = buildJsonApiUrl(url, requestParams, {
					pageSize,
					columnToApiFilterFieldMap,
					managedFilterFields: managedApiFilterFields,
				});

				syncBrowserUrl(
					finalUrl,
					apiUrlTargetId,
					historySyncMode,
					historySyncScope,
				);

				if (!servedInitial && initial) {
					servedInitial = true;

					const lastPage = get(initial, lastPagePath, 1);
					return Promise.resolve({
						last_page: lastPage,
						data: initial.data ?? [],
					});
				}

				return fetch(finalUrl, { ...ajaxConfig, method: "GET" })
					.then(async (r) => {
						if (!r.ok) throw new Error(`HTTP ${r.status}`);
						return r.json();
					})
					.then((json) => {
						void refreshHeaderFilterOptions(finalUrl, ajaxConfig);

						const lastPage = get(json, lastPagePath, 1);
						return {
							last_page: lastPage,
							data: json.data ?? [],
						};
					});
			},
		});

		tabulatorTables.set(id, table);
		afterReadyCallbacks.forEach((callback) =>
			callback({ id, table, mount, config }),
		);
		window.dispatchEvent(
			new CustomEvent("tabulator:ready", { detail: { id, table, mount } }),
		);

		// Wire up external filter selects
		if (externalFilters && externalFilters.length > 0) {
			const externalFilterContainer = mount.parentElement;

			if (externalFilterContainer) {
				const externalSelects = externalFilterContainer.querySelectorAll(
					"[data-external-filter]",
				);

				// Populate non-static external selects from seed data on initial load
				if (headerFilterOptionsSeed && headerFilterOptionsMap) {
					const seedFilters =
						headerFilterOptionsSeed?.filters ?? headerFilterOptionsSeed;
					externalSelects.forEach((select) => {
						if (select.dataset.externalFilterStatic !== undefined) return;

						const field = select.dataset.externalFilter;
						const filterKey = headerFilterOptionsMap[field];
						if (!filterKey) return;

						const facetData = seedFilters?.[filterKey];
						if (!facetData) return;

						populateSelect(select, buildSelectValues(facetData));
					});
				}

				externalSelects.forEach((select) => {
					const field = select.dataset.externalFilter;

					// Seed initial value from URL
					if (externalInitialValues.has(field)) {
						select.value = externalInitialValues.get(field);
						table.addFilter(field, "=", externalInitialValues.get(field));
					}

					select.addEventListener("change", () => {
						const value = select.value;

						const current = table.getFilters().find((f) => f.field === field);
						if (current) {
							table.removeFilter(field, current.type, current.value);
						}

						if (value) {
							table.addFilter(field, "=", value);
						}

						table.setData();
					});
				});
			}
		}

		if (apiUrlTarget) {
			apiUrlTarget.addEventListener("click", () => {
				apiUrlTarget.select();
			});
		}

		window.addEventListener("resize", () => {
			table.redraw(true);
		});
	});
}
