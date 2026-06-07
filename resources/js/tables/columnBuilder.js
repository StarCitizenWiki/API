function collectLeafColumns(columns, map = new Map()) {
	(columns ?? []).forEach((column) => {
		if (Array.isArray(column?.columns) && column.columns.length > 0) {
			collectLeafColumns(column.columns, map);
			return;
		}

		if (column?.field) {
			map.set(column.field, column);
		}
	});

	return map;
}

function parseColumnsParam() {
	const value = new URL(window.location.href).searchParams.get("columns");

	if (!value) {
		return null;
	}

	const fields = value
		.split(",")
		.map((field) => field.trim())
		.filter(Boolean);

	return fields.length > 0 ? fields : null;
}

function defaultSelectedFields(columns) {
	return [...collectLeafColumns(columns).keys()];
}

function syncColumnsParam(fields, defaults) {
	try {
		const pageUrl = new URL(window.location.href);
		const isDefault =
			fields.length === defaults.length &&
			fields.every((field, index) => field === defaults[index]);

		if (isDefault) {
			pageUrl.searchParams.delete("columns");
		} else {
			pageUrl.searchParams.set("columns", fields.join(","));
		}

		window.history.replaceState({}, "", pageUrl.toString());
	} catch (e) {}
}

function catalogByField(fieldCatalog) {
	return new Map((fieldCatalog ?? []).map((field) => [field.field, field]));
}

function fieldDisplayTitle(field) {
	return field?.shortTitle ?? field?.title ?? field?.field;
}

function selectedFieldsForTable(columns, fieldCatalog) {
	const defaultFields = defaultSelectedFields(columns);
	const validFields = new Set([
		...defaultFields,
		...(fieldCatalog ?? [])
			.filter((field) => field?.field && field.columnable !== false)
			.map((field) => field.field),
	]);

	const fromUrl = parseColumnsParam()?.filter((field) => validFields.has(field));

	return fromUrl?.length ? fromUrl : defaultFields;
}

function inferredColumnDefinition(field) {
	const type = field?.type ?? "string";
	const column = {
		title: fieldDisplayTitle(field),
		field: field?.field,
		headerSort: Boolean(field?.sortable),
		minWidth: type === "string" ? 180 : 130,
	};

	if (field?.sortable && field?.sortField) {
		column.sortField = field.sortField;
	}

	if (field?.filterable) {
		column.headerFilter = field.filterType === "list" ? "list" : "input";
	}

	if (["integer", "number"].includes(type)) {
		column.sorter = "number";
		column.hozAlign = "right";
	}

	if (type === "boolean") {
		column.formatter = "yesNo";
		column.hozAlign = "center";
		column.width = 120;
	}

	if (field?.formatter) {
		column.formatter = field.formatter;

        if (field?.formatter_params) {
			column.formatterParams = field.formatter_params;
		}
	} else if (field?.suffix) {
		column.formatter = (cell) => {
			const value = cell.getValue();

            if (value === null || value === undefined || value === "") {
				return "";
			}

            return `${value}${field.suffix}`;
		};
	}

	return column;
}

function columnsFromSelection(selectedFields, defaultColumns, fieldCatalog) {
	const defaultColumnMap = collectLeafColumns(defaultColumns);
	const catalog = catalogByField(fieldCatalog);

	return selectedFields
		.map((field) => {
			const existing = defaultColumnMap.get(field);

            if (existing) {
				return existing;
			}

			const catalogField = catalog.get(field);

            if (!catalogField || catalogField.columnable === false) {
				return null;
			}

			return inferredColumnDefinition(catalogField);
		})
		.filter(Boolean);
}

function buildCatalogSortFieldMap(fieldCatalog, map = {}) {
	(fieldCatalog ?? []).forEach((field) => {
		if (field?.field && field?.sortable && field?.sortField) {
			map[field.field] = field.sortField;
		}
	});

	return map;
}

function withHeaderFilterOptions(columns, context) {
	const payload = context.getHeaderFilterOptionsPayload?.() ?? (
		context.headerFilterOptionsSeed ? { filters: context.headerFilterOptionsSeed } : null
	);

	if (!payload || !context.headerFilterOptionsMap) {
		return columns;
	}

	return context.applyHeaderFilterOptionsToColumns(
		columns,
		context.headerFilterOptionsMap,
		payload,
	);
}

function setupColumnBuilder({
	id,
	table,
	config,
	defaultColumns,
	selectedFields,
	applyHeaderFilterOptionsToColumns,
	headerFilterOptionsMap,
	headerFilterOptionsSeed,
	getHeaderFilterOptionsPayload,
	normalizeColumns,
	mobileSafeColumns,
}) {
	const builder = document.querySelector(
		`[data-tabulator-column-builder="${id}"]`,
	);

	if (!builder) {
		return;
	}

	const rawCatalog = (config.fieldCatalog ?? []).filter(
		(field) => field?.field && field.columnable !== false,
	);

	if (!rawCatalog.length) {
		return;
	}

	const fieldCatalog = rawCatalog.map((field) => ({
		...field,
		_searchText: `${field.field} ${field.title ?? ""} ${field.shortTitle ?? ""} ${field.description ?? ""} ${field.group ?? ""}`.toLowerCase(),
	}));

	const defaultFields = defaultSelectedFields(defaultColumns);
	const validFields = new Set(fieldCatalog.map((field) => field.field));
	defaultFields.forEach((field) => validFields.add(field));

	let activeFields = selectedFields.filter((field) => validFields.has(field));
	if (!activeFields.length) {
		activeFields = defaultFields;
	}

	let lastFocus = null;

	const dialog = builder.querySelector("[data-column-builder-dialog]");
	const openButton = builder.querySelector("[data-column-builder-open]");
	const search = builder.querySelector("[data-column-builder-search]");
	const list = builder.querySelector("[data-column-builder-list]");
	const count = builder.querySelector("[data-column-builder-count]");
	const status = builder.querySelector("[data-column-builder-status]");
	const coreButtons = builder.querySelectorAll("[data-column-builder-core]");
	const defaultsButtons = builder.querySelectorAll(
		"[data-column-builder-defaults]",
	);
	const cancelButtons = builder.querySelectorAll("[data-column-builder-cancel]");
	const coreFields = (config.columnBuilderCoreFields ?? ["name"]).filter((field) =>
		validFields.has(field),
	);

	if (!list) {
		return;
	}

	const updateCounts = () => {
		if (count) {
			count.textContent = activeFields.length;
		}
	};

	const applyFields = (fields) => {
		const nextFields = [...new Set(fields)].filter((field) =>
			validFields.has(field),
		);

		if (!nextFields.includes("name") && validFields.has("name")) {
			nextFields.push("name");
		}

		if (!nextFields.length) {
			return false;
		}

		let nextColumns = columnsFromSelection(
			nextFields,
			defaultColumns,
			config.fieldCatalog ?? [],
		);

		if (!nextColumns.length) {
			return false;
		}

		nextColumns = withHeaderFilterOptions(nextColumns, {
			applyHeaderFilterOptionsToColumns,
			headerFilterOptionsMap,
			headerFilterOptionsSeed,
			getHeaderFilterOptionsPayload,
		});

		activeFields = nextFields;
		void table.setColumns(normalizeColumns(mobileSafeColumns(nextColumns)));

		syncColumnsParam(activeFields, defaultFields);
		updateCounts();

		return true;
	};

	const debounce = (callback, delay = 150) => {
		let timeout;

		return () => {
			window.clearTimeout(timeout);
			timeout = window.setTimeout(callback, delay);
		};
	};

	const createElement = (tag, className, props = {}) =>
		Object.assign(document.createElement(tag), { className, ...props });

	const groupElements = new Map();
	const fieldRows = [];
	let emptyMessage;

	const groupCatalog = () => {
		const groups = new Map();

		for (const field of fieldCatalog) {
			const group = field.group ?? "Other";
			const fields = groups.get(group) ?? [];
			fields.push(field);
			groups.set(group, fields);
		}

		return groups;
	};

	const createFlag = (text, title, className) =>
		createElement("span", className, { textContent: text, title });

	const createFieldRow = (field, group) => {
		const isLocked = field.field === "name";
		const label = createElement(
			"label",
			isLocked
				? "flex items-center gap-1.5 rounded px-2 py-1 text-xs"
				: "flex cursor-pointer items-center gap-1.5 rounded px-2 py-1 text-xs hover:bg-base-200",
			{ title: field.description ? `${field.field}\n${field.description}` : field.field },
		);
		label.dataset.columnBuilderFieldRow = field.field;

		const checkbox = createElement("input", "checkbox checkbox-xs flex-shrink-0", {
			disabled: isLocked,
			type: "checkbox",
			value: field.field,
		});
		checkbox.dataset.columnBuilderField = field.field;

		const titleWrap = createElement("span", "min-w-0 flex-1");
		titleWrap.append(
			createElement("span", "block truncate font-medium", {
				textContent: fieldDisplayTitle(field),
			}),
			createElement("span", "block truncate text-xs text-subtle", {
				textContent: field.description ?? "",
			}),
		);

		const type = createElement(
			"span",
			"flex-shrink-0 rounded bg-base-300 px-1 py-px text-xs font-mono text-subtle",
			{ textContent: field.type ?? "?" },
		);
		const flags = createElement("span", "flex flex-shrink-0 gap-0.5 ml-auto");

		if (field.sortable) {
			flags.appendChild(
				createFlag("S", "sortable", "rounded bg-info/15 px-1 py-px text-xs font-medium text-info"),
			);
		}

		if (field.filterable) {
			flags.appendChild(
				createFlag("F", "filterable", "rounded bg-success/15 px-1 py-px text-xs font-medium text-success"),
			);
		}

		label.append(checkbox, titleWrap, type, flags);
		fieldRows.push({ checkbox, field, group, label });

		return label;
	};

	const createGroupCard = (group, fields) => {
		const collapsed = group !== "Core";
		const schemas = [...new Set(fields.map((field) => field.schema).filter(Boolean))];
		const card = createElement(
			"div",
			"rounded-lg border border-base-300 bg-base-100 overflow-hidden first:mt-0 mt-2",
		);
		const heading = createElement(
			"div",
			"sticky top-0 z-10 flex items-center gap-2 bg-base-200 px-3 py-1.5 border-b border-base-300 cursor-pointer select-none",
		);
		const groupCheck = createElement("input", "checkbox checkbox-xs", {
			title: `Toggle all ${group} fields`,
			type: "checkbox",
		});
		const groupLabel = createElement(
			"span",
			"text-xs font-semibold uppercase tracking-wide text-subtle",
		);
		const arrow = createElement("span", "text-subtle transition-transform duration-150 select-none", {
			textContent: "▾",
		});
		const grid = createElement("div", "grid grid-cols-2 lg:grid-cols-3 gap-px p-1");

		heading.dataset.columnBuilderGroupHeading = group;
		groupCheck.dataset.columnBuilderGroupToggle = group;
		grid.dataset.columnBuilderGroupGrid = group;

		if (collapsed) {
			arrow.style.transform = "rotate(-90deg)";
			grid.classList.add("hidden");
		}

		heading.append(groupCheck, groupLabel);

		if (schemas.length) {
			heading.appendChild(
				createElement("span", "ml-auto rounded bg-base-300 px-1 py-px text-xs font-mono text-subtle", {
					textContent: schemas.length === 1 ? schemas[0] : `${schemas[0]} +${schemas.length - 1}`,
					title: schemas.join(", "),
				}),
			);
		}

		heading.appendChild(arrow);
		grid.append(...fields.map((field) => createFieldRow(field, group)));
		card.append(heading, grid);

		groupElements.set(group, {
			arrow,
			card,
			fields,
			grid,
			groupCheck,
			groupLabel,
			visibleFields: [...fields],
		});

		return card;
	};

	const buildList = () => {
		if (groupElements.size) {
			return;
		}

		emptyMessage = createElement("p", "px-3 py-6 text-center text-sm text-subtle", {
			hidden: true,
		});
		list.replaceChildren(
			...[...groupCatalog()].map(([group, fields]) => createGroupCard(group, fields)),
			emptyMessage,
		);
	};

	const searchQuery = () => (search?.value ?? "").trim().toLowerCase();

	const filterList = () => {
		const query = searchQuery();
		let visibleCount = 0;

		for (const group of groupElements.values()) {
			group.visibleFields = [];
		}

		for (const row of fieldRows) {
			const isVisible = !query || row.field._searchText.includes(query);
			row.label.hidden = !isVisible;

			if (isVisible) {
				visibleCount++;
				groupElements.get(row.group).visibleFields.push(row.field);
			}
		}

		for (const group of groupElements.values()) {
			group.card.hidden = group.visibleFields.length === 0;
		}

		emptyMessage.textContent = query
			? "No documented item fields match that search."
			: "No documented item fields are available.";
		emptyMessage.hidden = visibleCount > 0;
	};

	const syncSelectionState = () => {
		const activeSet = new Set(activeFields);
		const query = searchQuery();

		for (const { checkbox, field } of fieldRows) {
			checkbox.checked = field.field === "name" || activeSet.has(field.field);
		}

		for (const [group, state] of groupElements) {
			const fields = query ? state.visibleFields : state.fields;
			const activeCount = fields.filter((field) => activeSet.has(field.field)).length;

			state.groupCheck.checked = fields.length > 0 && activeCount === fields.length;
			state.groupCheck.indeterminate = activeCount > 0 && activeCount < fields.length;
			state.groupLabel.textContent = `${group} ${activeCount}/${fields.length}`;
		}
	};

	const render = () => {
		buildList();
		filterList();
		syncSelectionState();
		updateCounts();
	};

	const closeDialog = () => {
		if (!dialog?.open) {
			return;
		}

		dialog.close();
		openButton?.setAttribute("aria-expanded", "false");
		lastFocus?.focus?.();
	};

	const setStatus = (message) => {
		if (!status) {
			return;
		}

		status.textContent = message;
		if (message) {
			window.setTimeout(() => {
				if (status.textContent === message) {
					status.textContent = "";
				}
			}, 3000);
		}
	};

	const setPreset = (fields) => {
		if (applyFields(fields)) {
			syncSelectionState();
			setStatus("Columns updated.");
		}
	};

	list.addEventListener("change", (event) => {
		const target = event.target;

		if (!(target instanceof HTMLInputElement)) {
			return;
		}

		const group = target.dataset.columnBuilderGroupToggle;
		if (group) {
			const groupFields = groupElements.get(group).visibleFields.map((field) => field.field);
			const nextFields = target.checked
				? [...activeFields, ...groupFields]
				: activeFields.filter((field) => !groupFields.includes(field));

			if (!applyFields(nextFields)) {
				target.checked = !target.checked;
			}

			syncSelectionState();
			return;
		}

		const field = target.dataset.columnBuilderField;
		if (!field) {
			return;
		}

		if (!target.checked && activeFields.length <= 1) {
			target.checked = true;
			return;
		}

		const nextFields = target.checked
			? [...activeFields, field]
			: activeFields.filter((activeField) => activeField !== field);

		if (!applyFields(nextFields)) {
			target.checked = !target.checked;
		}

		syncSelectionState();
	});

	list.addEventListener("click", (event) => {
		const target = event.target;

		if (!(target instanceof Element) || target.closest("input")) {
			return;
		}

		const heading = target.closest("[data-column-builder-group-heading]");
		if (!heading) {
			return;
		}

		const group = heading.dataset.columnBuilderGroupHeading;
		const groupElement = groupElements.get(group);

		groupElement.grid.classList.toggle("hidden");
		const isHidden = groupElement.grid.classList.contains("hidden");
		groupElement.arrow.style.transform = isHidden ? "rotate(-90deg)" : "";
	});

	const debouncedRender = debounce(render, 150);

	openButton?.setAttribute("aria-expanded", "false");
	openButton?.addEventListener("click", () => {
		if (!dialog) {
			return;
		}

		lastFocus = document.activeElement;
		if (search) {
			search.value = "";
		}

		render();

		if (!dialog.open) {
			dialog.showModal();
		}

		openButton.setAttribute("aria-expanded", "true");
		window.requestAnimationFrame(() => search?.focus());
	});

	search?.addEventListener("input", debouncedRender);
	coreButtons.forEach((button) => {
		button.addEventListener("click", () => setPreset(coreFields));
	});
	defaultsButtons.forEach((button) => {
		button.addEventListener("click", () => setPreset(defaultFields));
	});
	cancelButtons.forEach((button) => {
		button.addEventListener("click", closeDialog);
	});
	dialog?.addEventListener("close", () => {
		openButton?.setAttribute("aria-expanded", "false");
		updateCounts();
	});

	updateCounts();
}

export function registerColumnBuilder({ registerTabulatorBeforeInit }) {
	registerTabulatorBeforeInit((context) => {
		if (!context.config.columnBuilder || !context.config.fieldCatalog?.length) {
			return;
		}

		const fieldCatalog = context.config.fieldCatalog ?? [];
		context.sortFieldMap = {
			...buildCatalogSortFieldMap(fieldCatalog),
			...context.sortFieldMap,
		};

		const selectedFields = selectedFieldsForTable(
			context.defaultColumns,
			fieldCatalog,
		);
		const selectedColumns = columnsFromSelection(
			selectedFields,
			context.defaultColumns,
			fieldCatalog,
		);

		context.columns = withHeaderFilterOptions(selectedColumns, context);

		context.onReady(({ table }) => {
			setupColumnBuilder({
				id: context.id,
				table,
				config: context.config,
				defaultColumns: context.defaultColumns,
				selectedFields,
				applyHeaderFilterOptionsToColumns: context.applyHeaderFilterOptionsToColumns,
				headerFilterOptionsMap: context.headerFilterOptionsMap,
				headerFilterOptionsSeed: context.headerFilterOptionsSeed,
				getHeaderFilterOptionsPayload: context.getHeaderFilterOptionsPayload,
				normalizeColumns: context.normalizeColumns,
				mobileSafeColumns: context.mobileSafeColumns,
			});
		});
	});
}
