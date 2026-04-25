import { createIcons, icons } from 'lucide';

const DEBOUNCE_MS = 250;
const MIN_QUERY_LENGTH = 2;
const MAX_RESULTS = 15;

const ACTIVE_ITEM_CLASS = 'bg-base-200';

export function initLiveSearch() {
    const inputs = document.querySelectorAll('[data-live-search]');

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
            dropdown.style.top = `${rect.bottom + window.scrollY + 4}px`;
            dropdown.style.left = `${rect.left + window.scrollX}px`;
            dropdown.style.width = `${rect.width}px`;
        };

        input.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            const query = input.value.trim();

            if (query.length < MIN_QUERY_LENGTH) {
                hideDropdown(dropdown, repositionHandler);
                repositionHandler = null;
                return;
            }

            debounceTimer = setTimeout(() => {
                fetchResults(apiEndpoint, query, abortController, (controller) => {
                    abortController = controller;
                }).then((data) => {
                    results = data;
                    activeIndex = -1;
                    renderResults(dropdown, results);
                    showDropdown(dropdown, positionDropdown);
                    repositionHandler = positionDropdown;
                    window.addEventListener('scroll', repositionHandler, true);
                    window.addEventListener('resize', repositionHandler);
                });
            }, DEBOUNCE_MS);
        });

        input.addEventListener('keydown', (e) => {
            const items = dropdown.querySelectorAll('[data-live-search-item]');

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = Math.min(activeIndex + 1, items.length - 1);
                updateActiveItem(items, activeIndex);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = Math.max(activeIndex - 1, -1);
                updateActiveItem(items, activeIndex);
                if (activeIndex === -1) {
                    input.focus();
                }
            } else if (e.key === 'Enter') {
                if (activeIndex >= 0 && items[activeIndex]) {
                    e.preventDefault();
                    const url = items[activeIndex].getAttribute('href');
                    if (url) {
                        window.location.href = url;
                    }
                }
            } else if (e.key === 'Escape') {
                hideDropdown(dropdown, repositionHandler);
                repositionHandler = null;
                input.blur();
            }
        });

        input.addEventListener('focus', () => {
            if (results.length > 0 && input.value.trim().length >= MIN_QUERY_LENGTH) {
                showDropdown(dropdown, positionDropdown);
                repositionHandler = positionDropdown;
                window.addEventListener('scroll', repositionHandler, true);
                window.addEventListener('resize', repositionHandler);
            }
        });

        document.addEventListener('click', (e) => {
            if (!dropdown.contains(e.target) && e.target !== input) {
                hideDropdown(dropdown, repositionHandler);
                repositionHandler = null;
            }
        });
    });
}

function createDropdown() {
    const dropdown = document.createElement('div');
    dropdown.style.display = 'none';
    dropdown.style.position = 'absolute';
    dropdown.style.zIndex = '9999';
    dropdown.setAttribute('role', 'listbox');
    dropdown.className = 'rounded-box border border-base-300 bg-base-100 shadow-xl max-h-72 overflow-y-auto';

    document.body.appendChild(dropdown);

    return dropdown;
}

function renderResults(dropdown, results) {
    dropdown.innerHTML = '';

    if (results.length === 0) {
        const empty = document.createElement('div');
        empty.className = 'px-3 py-2 text-sm text-base-content/50';
        empty.textContent = 'No results found';
        dropdown.appendChild(empty);
        return;
    }

    const list = document.createElement('ul');
    list.className = 'menu menu-sm p-1 gap-0.5 w-full';

    results.forEach((item) => {
        const li = document.createElement('li');
        const a = document.createElement('a');
        a.href = item.web_url;
        a.className = 'flex items-center gap-2 rounded-lg px-3 py-2 text-sm w-full';
        a.setAttribute('data-live-search-item', '');
        a.setAttribute('role', 'option');

        if (item.type_label) {
            const badge = document.createElement('span');
            badge.className = 'text-xs text-base-content/40 shrink-0 inline-block w-22';
            badge.textContent = item.type_label;
            a.appendChild(badge);
        }

        const icon = document.createElement('i');
        icon.setAttribute('data-lucide', 'arrow-right');
        icon.className = 'size-3.5 text-base-content/40';

        const name = document.createElement('span');
        name.className = 'truncate';
        name.textContent = item.name ?? item.title;

        const parts = [];
        if (item.classification) parts.push(item.classification);
        if (item.item_type_label) parts.push(item.item_type_label);
        if (item.extra_label) parts.push(item.extra_label);
        if (item.class_name && parts.length === 0) parts.push(item.class_name);

        if (parts.length > 0) {
            const suffix = document.createElement('span');
            suffix.className = 'text-xs text-base-content/40 pl-1';
            suffix.textContent = `(${parts.join(' · ')})`;
            name.appendChild(suffix);
        }

        a.appendChild(icon);
        a.appendChild(name);
        li.appendChild(a);
        list.appendChild(li);
    });

    dropdown.appendChild(list);
    createIcons({ icons });
}

function showDropdown(dropdown, positionFn) {
    positionFn();
    dropdown.style.display = 'block';
}

function hideDropdown(dropdown, repositionHandler) {
    dropdown.style.display = 'none';
    if (repositionHandler) {
        window.removeEventListener('scroll', repositionHandler, true);
        window.removeEventListener('resize', repositionHandler);
    }
}

function updateActiveItem(items, activeIndex) {
    items.forEach((item, i) => {
        if (i === activeIndex) {
            item.classList.add(ACTIVE_ITEM_CLASS);
            item.scrollIntoView({ block: 'nearest' });
        } else {
            item.classList.remove(ACTIVE_ITEM_CLASS);
        }
    });
}

async function fetchResults(apiEndpoint, query, previousController, onNewController) {
    if (previousController) {
        previousController.abort();
    }

    const controller = new AbortController();
    onNewController(controller);

    const url = `${apiEndpoint}?filter[query]=${encodeURIComponent(query)}&limit=${MAX_RESULTS}`;

    try {
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            signal: controller.signal,
        });

        const json = await response.json();

        const isGrouped = Array.isArray(json.data) && json.data.some((item) => Array.isArray(item.results));

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
        if (e.name === 'AbortError') {
            return [];
        }
        throw e;
    }
}
