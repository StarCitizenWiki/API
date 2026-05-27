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
	if (!context.headerFilterOptionsSeed || !context.headerFilterOptionsMap) {
		return columns;
	}

	return context.applyHeaderFilterOptionsToColumns(
		columns,
		context.headerFilterOptionsMap,
		{ filters: context.headerFilterOptionsSeed },
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

	const updateCounts = () => {
		if (count) {
			count.textContent = activeFields.length;
		}
	};

	const applyFields = (fields) => {
		const nextFields = [...new Set(fields)].filter((field) =>
			validFields.has(field),
		);
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
		});

		activeFields = nextFields;
		void table.setColumns(normalizeColumns(mobileSafeColumns(nextColumns)));

		syncColumnsParam(activeFields, defaultFields);
		updateCounts();

		return true;
	};

	const render = () => {
		if (!list) {
			return;
		}

		const query = (search?.value ?? "").trim().toLowerCase();
		const activeSet = new Set(activeFields);
		const grouped = new Map();

		fieldCatalog.forEach((field) => {
			if (query && !field._searchText.includes(query)) {
				return;
			}

			const group = field.group ?? "Other";
			if (!grouped.has(group)) {
				grouped.set(group, []);
			}

			grouped.get(group).push(field);
		});

		const fragment = document.createDocumentFragment();

		if (grouped.size === 0) {
			const empty = document.createElement("p");
			empty.className = "px-3 py-6 text-center text-sm text-subtle";
			empty.textContent = query
				? "No documented item fields match that search."
				: "No documented item fields are available.";
			fragment.appendChild(empty);
		} else {
			for (const [group, fields] of grouped) {
				const heading = document.createElement("div");
				heading.className = "sticky top-0 z-10 bg-base-200/95 px-2 py-1.5 text-xs font-semibold uppercase tracking-wide text-subtle backdrop-blur";
				heading.textContent = `${group} (${fields.length})`;
				fragment.appendChild(heading);

				for (const field of fields) {
					const label = document.createElement("label");
					label.className = "grid cursor-pointer grid-cols-12 items-center gap-2 rounded px-2 py-1 text-xs hover:bg-base-200";
					label.title = field.field;

					const checkbox = document.createElement("input");
					checkbox.type = "checkbox";
					checkbox.className = "checkbox checkbox-xs col-span-1";
					checkbox.checked = activeSet.has(field.field);
					checkbox.value = field.field;
					checkbox.addEventListener("change", () => {
						let ok;
						if (checkbox.checked) {
							ok = applyFields([...activeFields, field.field]);
						} else if (activeFields.length > 1) {
							ok = applyFields(activeFields.filter((f) => f !== field.field));
						} else {
							checkbox.checked = true;
							return;
						}

						if (!ok) {
							checkbox.checked = !checkbox.checked;
						}
					});

					const title = document.createElement("span");
					title.className = "col-span-4 truncate font-medium";
					title.textContent = fieldDisplayTitle(field);

					const detail = document.createElement("span");
					detail.className = "col-span-6 truncate text-xs text-subtle";
					detail.textContent = field.description || field.field;

					const flags = document.createElement("span");
					flags.className = "col-span-1 whitespace-nowrap text-xs font-semibold text-subtle";
					flags.title = "S = sortable, F = filterable";
					flags.textContent = `${field.sortable ? "S" : ""}${field.filterable ? "F" : ""}`;

					label.appendChild(checkbox);
					label.appendChild(title);
					label.appendChild(detail);
					label.appendChild(flags);
					fragment.appendChild(label);
				}
			}
		}

		list.replaceChildren(fragment);
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
			setStatus("Columns updated.");
		}
	};

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

	search?.addEventListener("input", render);
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
				normalizeColumns: context.normalizeColumns,
				mobileSafeColumns: context.mobileSafeColumns,
			});
		});
	});
}
