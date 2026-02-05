# Stack Research

**Domain:** Information-dense item detail page (Laravel Blade + Tailwind + DaisyUI)
**Researched:** 2026-02-05
**Confidence:** MEDIUM

## Recommended Stack

### Core Technologies

| Technology | Version | Purpose | Why Recommended |
|------------|---------|---------|-----------------|
| Laravel Framework | ^12.0 | Server-side rendering, routing, view composition | Already the app core; Blade keeps render fast and SEO-friendly for dense data; Confidence: HIGH (repo) |
| Blade Templates | Bundled with Laravel | View layer for item detail page | Existing view stack; keeps markup close to data with no SPA overhead; Confidence: HIGH (repo) |
| Tailwind CSS | ^4.0.0 | Utility-first styling for dense layouts | Excellent for compact, responsive grids and typography control; matches existing stack; Confidence: HIGH (repo) |
| DaisyUI | ^5.5.14 | UI primitives (stats, badges, tabs, collapse) | Speeds consistent, readable info density without custom CSS bloat; Confidence: HIGH (repo) |
| Vite | ^7.0.7 | Asset bundling for Tailwind + JS | Current frontend build system in repo; fast rebuilds for UI iteration; Confidence: HIGH (repo) |

### Supporting Libraries

| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| @tailwindcss/vite | ^4.0.0 | Tailwind v4 integration with Vite | Required for Tailwind build pipeline; Confidence: HIGH (repo) |
| lucide | ^0.562.0 | Icon set for compact visual cues | Use for category, role, performance indicators; low visual noise; Confidence: HIGH (repo) |
| luxon | ^3.7.2 | Date/time formatting | Use for availability windows, patch timestamps; Confidence: HIGH (repo) |
| tabulator-tables | ^6.3.1 | Dense, responsive data tables | Use when specs/variants need sortable or collapsible columns; Confidence: HIGH (repo) |
| axios | ^1.11.0 | HTTP client | Only if lightweight async UI needs small fetches; avoid for static detail render; Confidence: MEDIUM (repo, optional) |

### Development Tools

| Tool | Purpose | Notes |
|------|---------|-------|
| laravel-vite-plugin | ^2.0.0 | Laravel + Vite integration | Keep config as-is; ensures Blade hot reload; Confidence: HIGH (repo) |
| Pest + pest-plugin-browser | ^4.x | UI regression and smoke tests | Use for critical layouts/viewport checks; Confidence: HIGH (repo) |
| Playwright | ^1.57.0 | Browser automation engine | Used by Pest browser plugin; useful for mobile viewport verification; Confidence: HIGH (repo) |

## Installation

```bash
# Core (already in repo)
npm install

# Supporting (already in repo)
npm install

# Dev dependencies (already in repo)
npm install
```

## Alternatives Considered

| Recommended | Alternative | When to Use Alternative |
|-------------|-------------|-------------------------|
| Blade + Tailwind + DaisyUI | Livewire or Inertia SPA | Only if the page requires heavy client-side interactivity beyond scope (not required here) |
| Tabulator | jQuery DataTables | Use only if legacy jQuery code already exists (not in this repo) |
| lucide | Heroicons or Font Awesome | Use only if project already standardized on a different icon set |

## What NOT to Use

| Avoid | Why | Use Instead |
|-------|-----|-------------|
| React/Vue SPA rewrite | Adds bundle size and complexity for a server-rendered detail page | Blade + Tailwind + DaisyUI |
| Bootstrap/Flowbite | Conflicts with Tailwind design system and increases CSS payload | Tailwind utilities + DaisyUI components |
| Custom table JS for dense specs | Reinvents solved problems (sorting, column toggles) | tabulator-tables |

## Stack Patterns by Variant

**If the page is single-item detail (most cases):**
- Use Blade partials/components with DaisyUI stats, badges, tabs, and collapse sections.
- Because it keeps rendering fast and layout predictable on mobile without client JS.

**If specs or variants require multi-column comparison:**
- Use Tabulator with responsive columns and column toggles.
- Because it handles dense grids and mobile overflow more gracefully than hand-built tables.

**If mobile-first scanability is the main goal:**
- Use Tailwind grid with controlled typography scale and DaisyUI “collapse” for long sections.
- Because it preserves density while reducing scroll fatigue on small screens.

## Version Compatibility

| Package A | Compatible With | Notes |
|-----------|-----------------|-------|
| tailwindcss@4.x | @tailwindcss/vite@4.x | Tailwind v4 pipeline; validated in repo config |
| daisyui@5.x | tailwindcss@4.x | DaisyUI v5 targets Tailwind v4; validated in repo |
| laravel-vite-plugin@2.x | vite@7.x | Current repo pairing; keep aligned |
| laravel/framework@12.x | php@8.4+ | Composer constraint; runtime is 8.5.2 in env |

## Sources

- `package.json` — current UI tooling versions (HIGH confidence)
- `composer.json` — Laravel framework and PHP constraints (HIGH confidence)

---
*Stack research for: Information-dense item detail UI (Laravel Blade + Tailwind + DaisyUI)*
*Researched: 2026-02-05*
