# Project Research Summary

**Project:** Star Citizen Item Detail Refactor
**Domain:** Information-dense item detail UI for community data (Laravel Blade + Tailwind + DaisyUI)
**Researched:** 2026-02-05
**Confidence:** MEDIUM

## Executive Summary

This project is a mobile-first, information-dense item detail page for Star Citizen community data. Experts build these pages with server-rendered layouts that emphasize a first-viewport “quick scan” block (role, key performance, price/availability), followed by structured, collapsible sections for depth. The recommended approach is a Blade-composed page using Tailwind v4 and DaisyUI primitives to achieve compact, readable density without SPA overhead.

Research supports a phased rollout: ship the MVP layout and information hierarchy first, then refine interaction patterns (multi-open collapses, on-page navigation, mobile table handling), and only later invest in data normalization and comparison intelligence. The biggest risks are burying critical stats below the fold, disorienting accordions, and inconsistent units/freshness across sources. Mitigate by prioritizing quick-scan content, using predictable section shells, and enforcing unit/source metadata in the data layer.

## Key Findings

### Recommended Stack

Stack choice is stable and aligned with the repo: Laravel 12 + Blade for SSR, Tailwind v4 for dense layout control, and DaisyUI v5 for fast, consistent primitives. Optional additions like Tabulator make sense only when dense spec grids become unmanageable on mobile.

**Core technologies:**
- Laravel Framework ^12.0: server-side rendering and routing — already in repo and SEO-friendly for dense data
- Blade Templates: view composition — keeps layout close to data with no SPA overhead
- Tailwind CSS ^4.0.0: utility styling — precise, responsive control for dense grids
- DaisyUI ^5.5.14: UI primitives — stats, badges, and collapse components with minimal CSS bloat
- Vite ^7.0.7: asset bundling — existing pipeline and fast iteration

### Expected Features

The MVP must deliver a quick-scan summary, performance stats, price/availability, and collapsible detail sections. Differentiators like role-based stat prioritization and comparison chips depend on normalized schemas and baselines, so they belong after validation.

**Must have (table stakes):**
- Item name/type/role + hero image — fast identification
- Quick facts and key performance stats — core scan targets
- Price and availability (buy/rent/pledge) — primary decision data
- Collapsible, multi-open sections — mobile-friendly density
- Data freshness/patch label — trust and relevance

**Should have (competitive):**
- Scan-first summary layout with stat grid — mobile speed
- Role-based stat prioritization — relevance by role
- Price delta/availability badges — faster comparisons
- Component/slot summary tags — loadout snapshot
- Shareable deep links/codes — community sharing

**Defer (v2+):**
- Comparison chips/percentiles — requires baseline system
- Price history and trends — requires time-series data

### Architecture Approach

Use a composed Blade page with clear component boundaries: summary + quick stats at top, then section shells for dense content. Keep per-section content in focused components and reuse UI atoms (label/value rows, badges, empty states) to reduce duplication and preserve scan consistency.

**Major components:**
1. Item detail page (`resources/views/items/show.blade.php`) — layout orchestration and section order
2. Summary header + quick stats components — first-viewport scan targets
3. Section shell + section content components — consistent collapsible framing for dense data

### Critical Pitfalls

1. **Quick-scan priorities buried below the fold** — keep role/perf/price visible in the first viewport
2. **Disorienting accordions** — avoid auto-scroll, preserve open state, allow multiple sections open
3. **False floors and weak scroll cues** — let the next section peek and add clear in-page cues
4. **Mobile tables requiring horizontal scroll** — prefer stacked cards or column-priority toggles
5. **Inconsistent units and freshness** — enforce normalization with units, sources, and timestamps

## Implications for Roadmap

Based on research, suggested phase structure:

### Phase 1: MVP Information Hierarchy
**Rationale:** Core value is fast mobile scan; dependencies are minimal and match existing stack.
**Delivers:** Summary block, quick stats grid, price/availability, multi-open collapsible sections, freshness label.
**Addresses:** Top summary, quick stats, price/availability, collapsible sections, data freshness.
**Avoids:** Quick-scan buried, over-deferment, hidden navigation.

### Phase 2: Interaction + Navigation Patterns
**Rationale:** Once IA is proven, improve discoverability and mobile usability without data schema changes.
**Delivers:** On-page section navigation, refined collapse behavior, mobile-friendly dense tables.
**Uses:** DaisyUI collapse + Tailwind grids; optional Tabulator only for dense spec grids.
**Implements:** Section shell pattern and label/value grids.
**Avoids:** Disorienting accordions, false floors, horizontal scroll traps.

### Phase 3: Data Normalization + Advanced Insights
**Rationale:** Differentiators depend on normalized stats and baselines; add after MVP validation.
**Delivers:** Role-based stat prioritization, comparison chips, price deltas, optional price history.
**Addresses:** Normalized schema, baseline definitions, unit/source metadata, performance loading strategy.
**Avoids:** Inconsistent units, heavy payloads, misleading comparisons.

### Phase Ordering Rationale

- MVP must prove quick-scan value before investing in data normalization and comparison logic.
- Architecture favors componentized sections, enabling Phase 2 enhancements without rewrites.
- Pitfalls map directly to Phase 1 (IA) and Phase 2 (interaction), with Phase 3 reserved for data integrity and performance.

### Research Flags

Phases likely needing deeper research during planning:
- **Phase 3:** Data normalization, baselines, and comparison logic need validation against available datasets.

Phases with standard patterns (skip research-phase):
- **Phase 1:** Standard mobile-first layout and information hierarchy patterns.
- **Phase 2:** Established interaction patterns (accordions, section navigation) with known UX guidelines.

## Confidence Assessment

| Area | Confidence | Notes |
|------|------------|-------|
| Stack | HIGH | Verified against repo tooling and versions. |
| Features | MEDIUM | Based on competitor patterns and community expectations. |
| Architecture | LOW | Inferred patterns without external validation. |
| Pitfalls | MEDIUM | Backed by NN/g UX guidance and domain heuristics. |

**Overall confidence:** MEDIUM

### Gaps to Address

- Role taxonomy and stat normalization: define mappings and units before Phase 3.
- Baseline definitions for comparison chips: decide datasets and update cadence.
- Availability and location data quality: confirm completeness before adding acquisition tables.
- Performance targets: set LCP/TTI thresholds for mid-tier mobile devices.

## Sources

### Primary (HIGH confidence)
- `package.json` — UI tooling versions and build stack
- `composer.json` — Laravel and PHP constraints

### Secondary (MEDIUM confidence)
- https://starcitizen.tools/Drake_Cutlass_Black — quick facts and spec grouping
- https://api.uexcorp.space/2.0/commodities — commodity fields for price/availability
- https://www.nngroup.com/articles/progressive-disclosure/ — disclosure patterns
- https://www.nngroup.com/articles/mobile-accordions/ — accordion guidance

### Tertiary (LOW confidence)
- Architecture patterns inferred from common Blade composition practices

---
*Research completed: 2026-02-05*
*Ready for roadmap: yes*
