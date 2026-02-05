# Star Citizen Item Detail Refactor

## What This Is

Refactor the Star Citizen item detail page (`resources/views/items/show.blade.php`) and its used components to be mobile friendly and information dense. The page should support fast scanning on mobile while still looking strong on tablets and desktops, using community sites as layout references.

## Core Value

Users can quickly scan an item on mobile to understand its role, key performance, and price/availability.

## Requirements

### Validated

- ✓ Item detail page renders core metadata (name, type, manufacturer, grade/class/size).
- ✓ Item detail page shows related items/variants and UEX prices when available.
- ✓ Item detail page includes description, ports, FPS/vehicle data, technical details, and raw payload.

### Active

- [ ] Mobile top summary block with a quick stats grid optimized for fast scan (role, key performance, price/availability).
- [ ] Collapsible detail sections on mobile (multiple sections can stay open) for existing content groups.
- [ ] Responsive layout remains information dense and readable on tablet and desktop.
- [ ] Information hierarchy aligns with common community patterns (Erkul, UEXCorp, StarCitizen.tools, Fleetyards).

### Out of Scope

- New data sources or backend behavior — focus is layout and presentation only.
- Replacing Blade with a new frontend framework — keep existing stack.
- Global site redesign outside the item detail page — limit to the item view and its components.

## Context

- Current layout feels sparse and hard to scan on mobile.
- Primary use is quick scanning, not deep reading.
- Use community sites as references for density and hierarchy.

## Constraints

- **Tech stack**: Keep Laravel Blade + Tailwind conventions — avoid new dependencies.
- **Responsiveness**: Must look good on mobile, tablet, and desktop — mobile first.
- **Scope**: Refactor `resources/views/items/show.blade.php` and its used components only.

## Key Decisions

| Decision | Rationale | Outcome |
|----------|-----------|---------|
| Mobile layout: summary block + quick stats grid, then collapsible sections | Fast scan priority on mobile | — Pending |
| Collapsible behavior allows multiple sections open | Quick comparison without toggling | — Pending |
| Quick scan priorities: item role, key performance, price/availability | Primary use case | — Pending |
| Community references: Erkul.games, UEXCorp.space, StarCitizen.tools, Fleetyards.net | Align with expected density patterns | — Pending |
| Responsive targets include tablet and desktop | Ensure layout scales beyond mobile | — Pending |

---
*Last updated: 2026-02-05 after initialization*
