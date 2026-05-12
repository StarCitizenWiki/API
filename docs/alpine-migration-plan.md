# Alpine.js Migration Plan

## Overview

Migrate `resources/js/` from vanilla DOM manipulation to Alpine.js for the two areas where it provides clear value (theme toggle, live search), while leaving the Tabulator logic untouched.

**Scope:** ~306 lines affected out of ~1,135 total. The ~900-line Tabulator module stays as-is.

---

## Prerequisites

| Item | Details |
|---|---|
| Alpine.js version | `alpinejs` + `@alpinejs/persist` plugin |
| Bundle impact | ~15 KB gzipped (Alpine core + persist plugin) |
| Build tool | Vite — already configured, no changes needed |
| No SPA | App is server-rendered Blade. Alpine is purely progressive enhancement |

---

## Phase 0 — Install & Wire Up Alpine

### Step 0.1: Install dependencies

```bash
npm install alpinejs @alpinejs/persist
```

### Step 0.2: Register Alpine in `resources/js/bootstrap.js`

```js
import Alpine from 'alpinejs';
import persist from '@alpinejs/persist';

Alpine.plugin(persist);
window.Alpine = Alpine;
```

### Step 0.3: Start Alpine in `resources/js/app.js`

Add `Alpine.start()` at the end of the DOMContentLoaded handler (or after all `init` calls).

### Step 0.4: Verify

Load any page — Alpine is running but nothing uses it yet. No visual change.

---

## Phase 1 — Theme Toggle (est. 30 min)

**Goal:** Replace the vanilla JS theme toggle in `app.js` + the inline `<script>` in `layouts/app.blade.php` with Alpine + `$persist`.

### Step 1.1: Add an Alpine store or root component on `<html>`

In `layouts/app.blade.php`, replace the inline `<script>` block and the `<html>` tag:

```html
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    x-data="themeToggle()"
    x-init="init()"
    x-bind:data-theme="isDark ? darkTheme : lightTheme"
>
```

### Step 1.2: Create `resources/js/themeToggle.js`

```js
export function themeToggle() {
    return {
        isDark: false,

        init() {
            const stored = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            this.isDark = stored
                ? stored === (window.AppThemes?.dark ?? 'night')
                : prefersDark;
        },

        get lightTheme() { return window.AppThemes?.light ?? 'nord'; },
        get darkTheme()   { return window.AppThemes?.dark  ?? 'night'; },
    };
}
```

### Step 1.3: Update the toggle checkboxes in `layouts/app.blade.php`

Replace the two `<input type="checkbox" class="theme-toggle" data-theme-toggle>` elements:

```html
<!-- Desktop toggle -->
<input
    type="checkbox"
    class="theme-toggle"
    aria-label="Toggle dark mode"
    x-model="isDark"
>

<!-- Mobile toggle (same binding, automatically synced) -->
<input
    type="checkbox"
    class="theme-toggle"
    aria-label="Toggle dark mode"
    x-model="isDark"
>
```

### Step 1.4: Persist to localStorage

In the component, watch for changes:

```js
// Inside themeToggle()
x-effect="localStorage.setItem('theme', isDark ? darkTheme : lightTheme)"
```

(Or use `$persist('isDark').as('theme-dark')` if preferred.)

### Step 1.5: Clean up `resources/js/app.js`

Remove the entire theme toggle block (~30 lines: `querySelectorAll("[data-theme-toggle]")`, the `change` event listeners, and the initial theme application). Keep only `initTabulatorTables()`, `initLiveSearch()`, and `createIcons({icons})`.

### Step 1.6: Remove the inline `<script>` in `<head>`

The FOUC-prevention script that runs before Alpine loads should be replaced with a minimal inline fallback:

```html
<script>
    // FOUC prevention — runs before Alpine
    (function() {
        const t = localStorage.getItem('theme')
            || (matchMedia('(prefers-color-scheme:dark)').matches
                ? window.AppThemes?.dark : window.AppThemes?.light);
        if (t) document.documentElement.dataset.theme = t;
    })();
</script>
```

This stays (it's a one-liner that prevents flash). Alpine takes over after boot.

### Step 1.7: Delete dead code

- Remove `data-theme-toggle` attribute from templates (no longer used by JS)
- Remove all theme-related `querySelector`/`addEventListener` from `app.js`

### Files changed

| File | Change |
|---|---|
| `resources/js/bootstrap.js` | Import Alpine + persist |
| `resources/js/app.js` | Remove theme block, add `Alpine.start()` |
| `resources/js/themeToggle.js` | **New** — Alpine component |
| `resources/views/layouts/app.blade.php` | Alpine attrs on `<html>`, `x-model` on toggles, trim inline script |

---

## Phase 2 — Live Search (est. 2–3 hours)

**Goal:** Move the dropdown rendering (`renderResults`, `createDropdown`) into Blade templates with Alpine directives, while keeping the imperative fetch/position/keyboard logic as a helper module.

### Step 2.1: Create Alpine component `resources/js/liveSearch.js`

Refactor the existing `live-search.js` into an Alpine component factory:

```js
// resources/js/liveSearch.js
import { createIcons, icons } from 'lucide';

export function liveSearch(apiEndpoint) {
    return {
        query: '',
        results: [],
        open: false,
        activeIndex: -1,
        _abortController: null,
        _debounceTimer: null,

        get dropdownStyle() {
            // computed from $refs.input position
            if (!this.open) return 'display:none';
            const rect = this.$refs.input.getBoundingClientRect();
            const parent = this.$refs.input.parentElement.getBoundingClientRect();
            return `position:absolute;z-index:9999;top:${rect.bottom + 4}px;left:${parent.left}px;width:${parent.width}px`;
        },

        search() {
            clearTimeout(this._debounceTimer);
            if (this.query.trim().length < 2) {
                this.results = [];
                this.open = false;
                return;
            }
            this._debounceTimer = setTimeout(async () => {
                this._abortController?.abort();
                this._abortController = new AbortController();
                const data = await this.fetchResults(this.query);
                if (data) {
                    this.results = data;
                    this.activeIndex = -1;
                    this.open = true;
                    this.$nextTick(() => createIcons({ icons }));
                }
            }, 250);
        },

        navigate(e) {
            // Arrow keys, Enter, Escape — same logic, but uses this.results/this.activeIndex
            ...
        },

        async fetchResults(query) { /* extracted from existing fetchResults */ },

        selectItem(index) {
            const url = this.results[index]?.web_url;
            if (url) window.location.href = url;
        },

        closeOnOutside(e) {
            if (!this.$refs.dropdown.contains(e.target) && e.target !== this.$refs.input) {
                this.open = false;
            }
        },
    };
}
```

### Step 2.2: Convert `resource-search.blade.php` markup

Replace the bare `<input data-live-search ...>` with an Alpine wrapper:

```blade
<div
    x-data="liveSearch('{{ $apiEndpoint }}')"
    x-on:click.outside="open = false"
    class="relative"
>
    <label class="input input-bordered ...">
        <x-icon name="search" class="size-4 text-muted" />
        <input
            x-ref="input"
            type="search"
            name="filter[name]"
            class="grow text-sm"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            x-model="query"
            x-on:input="search()"
            x-on:keydown="navigate($event)"
            x-on:focus="results.length && query.trim().length >= 2 && (open = true)"
        />
    </label>

    {{-- Dropdown — replaces createDropdown() + renderResults() --}}
    <div
        x-ref="dropdown"
        x-show="open"
        x-transition
        role="listbox"
        class="rounded-box border border-base-300 bg-base-100 shadow-xl max-h-96 overflow-y-auto overflow-x-hidden"
        :style="dropdownStyle"
    >
        {{-- Empty state --}}
        <template x-if="results.length === 0">
            <div class="flex flex-col items-center gap-2 px-4 py-6 text-center">
                <x-icon name="search-x" class="size-6 text-muted" />
                <span class="text-sm text-muted">No results found</span>
            </div>
        </template>

        {{-- Results list --}}
        <ul x-show="results.length > 0" class="menu menu-sm p-1 gap-0.5 w-full">
            <template x-for="(item, idx) in results" :key="item.web_url">
                <li>
                    <a
                        :href="item.web_url"
                        role="option"
                        class="grid grid-cols-[auto_1fr] items-center gap-x-2 rounded-lg px-3 py-2 text-sm w-full"
                        :class="{ 'bg-base-200': idx === activeIndex }"
                        x-on:click.prevent="selectItem(idx)"
                        x-on:mouseenter="activeIndex = idx"
                    >
                        <span x-text="item.type_label" class="text-xs text-muted truncate max-w-20"></span>
                        <div class="flex items-center gap-2 min-w-0">
                            <x-icon name="arrow-right" class="size-3.5 shrink-0 text-muted" />
                            <span x-text="item.name ?? item.title" class="truncate"></span>
                            <span
                                x-show="item.classification || item.item_type_label || item.extra_label"
                                class="text-xs text-muted shrink-0 pl-1"
                                x-text="(item.classification || item.item_type_label || item.extra_label)
                                    ? `(${[item.classification, item.item_type_label, item.extra_label].filter(Boolean).join(' · ')})`
                                    : ''"
                            ></span>
                        </div>
                    </a>
                </li>
            </template>
        </ul>
    </div>
</div>
```

### Step 2.3: Update the header search input in `layouts/app.blade.php`

Wrap the existing `<label class="input ...">` search bar with the same `x-data="liveSearch('/api/search')"` pattern. Same markup as above, but without the `<form>` submit button (header search is live-only).

### Step 2.4: Delete `resources/js/live-search.js`

All rendering logic is now in Blade templates. Only `fetchResults()` moves to the new `liveSearch.js` Alpine component.

### Step 2.5: Remove `initLiveSearch()` from `app.js`

The `initLiveSearch()` import and call are no longer needed. Alpine auto-initializes from `x-data`.

### Step 2.6: Handle repositioning on scroll/resize

The dropdown positioning needs to update on scroll and resize. Add to the component:

```js
init() {
    this._reposition = () => { if (this.open) this.$refs.dropdown.style.cssText = this.dropdownStyle; };
    window.addEventListener('scroll', this._reposition, true);
    window.addEventListener('resize', this._reposition);
},
destroy() {
    window.removeEventListener('scroll', this._reposition, true);
    window.removeEventListener('resize', this._reposition);
},
```

### Files changed

| File | Change |
|---|---|
| `resources/js/liveSearch.js` | **New** — Alpine component (fetch + keyboard + position logic) |
| `resources/js/live-search.js` | **Delete** — replaced by Alpine component + Blade template |
| `resources/js/app.js` | Remove `initLiveSearch` import/call |
| `resources/views/components/resource-search.blade.php` | Full rewrite — Alpine directives |
| `resources/views/layouts/app.blade.php` | Header search wrapped with Alpine |

### What gets deleted / simplified

| Function | Before (lines) | After |
|---|---|---|
| `createDropdown()` | 15 | 0 — Blade template |
| `renderResults()` | 60 | 0 — `x-for` in Blade |
| `updateActiveItem()` | 8 | 0 — `:class` binding |
| `initLiveSearch()` setup | 70 | 0 — Alpine auto-inits |
| **Net** | **~153 lines of JS** | **~50 lines of JS + ~50 lines of Blade** |

---

## Phase 3 — Not Migrating (Tabulator)

`tables/baseTable.js` (~900 lines) stays vanilla JS. Rationale:

- Pure business logic (URL building, field mapping, history sync) with **no declarative DOM reactivity**
- Tabulator is its own rendering engine — Alpine would be a redundant layer
- No benefit to rewriting imperative API orchestration as Alpine components
- The external `<select>` filter population is driven by Tabulator's data lifecycle, not user reactivity

**Future consideration:** If Tabulator is ever replaced (e.g., with a native HTML table + Alpine-driven sorting/pagination), this module could be fully absorbed into Alpine.

---

## Migration Order & Checklist

```
□ Phase 0  — npm install, wire up Alpine in bootstrap.js, Alpine.start() in app.js
              Verify: no regressions, Alpine running (check window.Alpine in console)

□ Phase 1  — Theme toggle
   □ 1.1   Create themeToggle.js, import in app.js
   □ 1.2   Add x-data/x-init on <html> in layouts/app.blade.php
   □ 1.3   Replace data-theme-toggle inputs with x-model="isDark"
   □ 1.4   Trim inline <script> to FOUC-prevention only
   □ 1.5   Delete theme code from app.js
   □ 1.6   Test: toggle light/dark, refresh persists, mobile toggle synced

□ Phase 2  — Live search
   □ 2.1   Create liveSearch.js Alpine component (fetch, keyboard, position)
   □ 2.2   Rewrite resource-search.blade.php with Alpine directives
   □ 2.3   Update header search in layouts/app.blade.php
   □ 2.4   Delete resources/js/live-search.js
   □ 2.5   Remove initLiveSearch from app.js
   □ 2.6   Test: type → dropdown appears, arrow keys navigate, Enter selects,
              Escape closes, click-outside closes, scroll repositions

□ Cleanup
   □ Remove any unused data-* attributes (data-theme-toggle, data-live-search)
   □ Verify no console errors
   □ Run existing test suite (if any)
   □ Bundle size check: confirm Alpine adds ≤ 15KB gzipped
```

---

## Risk Assessment

| Risk | Likelihood | Mitigation |
|---|---|---|
| FOUC on theme toggle | Medium | Keep minimal inline `<script>` in `<head>` for initial paint |
| Dropdown positioning bugs | Low | Scroll/resize listeners carry over; `x-ref` replaces manual DOM lookup |
| Keyboard nav regression | Medium | Extract `navigate()` carefully with identical key-handling logic; test ArrowUp/Down/Enter/Escape |
| Bundle size increase | Low | Alpine is ~15KB gzipped; outweighed by JS code deletion |
| Lucide icon re-rendering | Low | Call `createIcons()` in `$nextTick` after results update |

---

## Post-Migration `app.js` Shape

```js
import './bootstrap';
import './themeToggle'; // registers Alpine component
import './liveSearch';  // registers Alpine component

import { initTabulatorTables } from './tables/baseTable';
import { createIcons, icons } from 'lucide';

document.addEventListener('DOMContentLoaded', () => {
    initTabulatorTables();
    createIcons({ icons });
});
```

Alpine auto-discovers `x-data` components on the page. No manual init calls needed for theme or search.
