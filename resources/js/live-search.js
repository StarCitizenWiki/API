import { createIcons, icons } from "lucide";

const DEBOUNCE_MS = 250;
const MIN_QUERY_LENGTH = 2;
const MAX_RESULTS = 15;

export function initLiveSearch() {
	const inputs = document.querySelectorAll("[data-live-search]");

	inputs.forEach((input) => {
		const apiEndpoint = input.dataset.apiEndpoint;
		if (!apiEndpoint) {
			return;
		}

		const dropdown = createDropdown();
		let abortController = null;
		let debounceTimer = null;
		let activeIndex = -1;
		let results = [];
		let repositionHandler = null;

		const positionDropdown = () => {
			const rect = input.getBoundingClientRect();
			const parentRect = input.parentElement.getBoundingClientRect();
			dropdown.style.top = `${rect.bottom + window.scrollY + 4}px`;
			dropdown.style.left = `${parentRect.left + window.scrollX}px`;
			dropdown.style.width = `${parentRect.width}px`;
		};

		const showDropdown = () => {
			positionDropdown();
			dropdown.style.display = "block";
			repositionHandler = positionDropdown;
			window.addEventListener("scroll", repositionHandler, true);
			window.addEventListener("resize", repositionHandler);
		};

		const hideDropdown = () => {
			dropdown.style.display = "none";
			if (repositionHandler) {
				window.removeEventListener("scroll", repositionHandler, true);
				window.removeEventListener("resize", repositionHandler);
				repositionHandler = null;
			}
		};

		input.addEventListener("input", () => {
			clearTimeout(debounceTimer);
			const query = input.value.trim();

			if (query.length < MIN_QUERY_LENGTH) {
				hideDropdown();
				return;
			}

			debounceTimer = setTimeout(async () => {
				if (abortController) {
					abortController.abort();
				}
				abortController = new AbortController();

				const data = await fetchResults(apiEndpoint, query, abortController);
				if (!data) {
					return;
				}

				results = data;
				activeIndex = -1;
				renderResults(dropdown, results);
				showDropdown();
			}, DEBOUNCE_MS);
		});

		input.addEventListener("keydown", (e) => {
			const items = dropdown.querySelectorAll("[data-live-search-item]");

			if (e.key === "ArrowDown") {
				e.preventDefault();
				activeIndex = Math.min(activeIndex + 1, items.length - 1);
				updateActiveItem(items, activeIndex);
			} else if (e.key === "ArrowUp") {
				e.preventDefault();
				activeIndex = Math.max(activeIndex - 1, -1);
				updateActiveItem(items, activeIndex);
				if (activeIndex === -1) {
					input.focus();
				}
			} else if (e.key === "Enter") {
				if (activeIndex >= 0 && items[activeIndex]) {
					e.preventDefault();
					const url = items[activeIndex].getAttribute("href");
					if (url) {
						window.location.href = url;
					}
				}
			} else if (e.key === "Escape") {
				hideDropdown();
				input.blur();
			}
		});

		input.addEventListener("focus", () => {
			if (results.length > 0 && input.value.trim().length >= MIN_QUERY_LENGTH) {
				showDropdown();
			}
		});

		document.addEventListener("click", (e) => {
			if (!dropdown.contains(e.target) && e.target !== input) {
				hideDropdown();
			}
		});
	});
}

function createDropdown() {
	const dropdown = document.createElement("div");
	dropdown.style.display = "none";
	dropdown.style.position = "absolute";
	dropdown.style.zIndex = "9999";
	dropdown.setAttribute("role", "listbox");
	dropdown.className =
		"rounded-box border border-base-300 bg-base-100 shadow-xl max-h-96 overflow-y-auto overflow-x-hidden";

	document.body.appendChild(dropdown);

	return dropdown;
}

function renderResults(dropdown, results) {
	dropdown.innerHTML = "";

	if (results.length === 0) {
		const empty = document.createElement("div");
		empty.className = "flex flex-col items-center gap-2 px-4 py-6 text-center";

		const icon = document.createElement("i");
		icon.setAttribute("data-lucide", "search-x");
		icon.className = "size-6 text-muted";

		const text = document.createElement("span");
		text.className = "text-sm text-muted";
		text.textContent = "No results found";

		empty.appendChild(icon);
		empty.appendChild(text);
		dropdown.appendChild(empty);
		createIcons({ icons });
		return;
	}

	const list = document.createElement("ul");
	list.className = "menu menu-sm p-1 gap-0.5 w-full";

	results.forEach((item) => {
		const li = document.createElement("li");
		const a = document.createElement("a");
		a.href = item.web_url;
		a.className =
			"grid grid-cols-[auto_1fr] items-center gap-x-2 rounded-lg px-3 py-2 text-sm w-full";
		a.setAttribute("data-live-search-item", "");
		a.setAttribute("role", "option");

		if (item.type_label) {
			const badge = document.createElement("span");
			badge.className = "text-xs text-muted truncate max-w-20";
			badge.textContent = item.type_label;
			a.appendChild(badge);
		} else {
			const spacer = document.createElement("span");
			a.appendChild(spacer);
		}

		const icon = document.createElement("i");
		icon.setAttribute("data-lucide", "arrow-right");
		icon.className = "size-3.5 shrink-0 text-muted";

		const name = document.createElement("span");
		name.className = "truncate";
		name.textContent = item.name ?? item.title;

		const parts = [
			item.classification,
			item.item_type_label,
			item.extra_label,
		].filter(Boolean);
		if (parts.length === 0 && item.class_name) {
			parts.push(item.class_name);
		}

		if (parts.length > 0) {
			const suffix = document.createElement("span");
			suffix.className = "text-xs text-muted shrink-0 pl-1";
			suffix.textContent = `(${parts.join(" · ")})`;
			name.appendChild(suffix);
		}

		const nameCol = document.createElement("div");
		nameCol.className = "flex items-center gap-2 min-w-0";
		nameCol.appendChild(icon);
		nameCol.appendChild(name);
		a.appendChild(nameCol);
		li.appendChild(a);
		list.appendChild(li);
	});

	dropdown.appendChild(list);
	createIcons({ icons });
}

function updateActiveItem(items, activeIndex) {
	items.forEach((item, i) => {
		item.classList.toggle("bg-base-200", i === activeIndex);
		if (i === activeIndex) {
			item.scrollIntoView({ block: "nearest" });
		}
	});
}

async function fetchResults(apiEndpoint, query, controller) {
	const url = `${apiEndpoint}?filter[query]=${encodeURIComponent(query)}&limit=${MAX_RESULTS}`;

	try {
		const response = await fetch(url, {
			headers: {
				"X-Requested-With": "XMLHttpRequest",
				Accept: "application/json",
			},
			signal: controller.signal,
		});

		const json = await response.json();
		const isGrouped =
			Array.isArray(json.data) &&
			json.data.some((item) => Array.isArray(item.results));

		if (isGrouped) {
			return json.data.flatMap((group) =>
				group.results.map((result) => ({
					name: result.name ?? result.title,
					classification: result.classification_label,
					class_name: result.class_name,
					web_url: result.web_url,
					type_label: group.label,
					item_type_label: result.item_type_label ?? null,
					extra_label: result.extra_label ?? null,
				})),
			);
		}

		return (json.data ?? []).map((item) => ({
			name: item.name ?? item.title,
			classification: item.classification_label,
			class_name: item.class_name,
			web_url: item.web_url,
		}));
	} catch (e) {
		if (e.name === "AbortError") {
			return null;
		}
		throw e;
	}
}
