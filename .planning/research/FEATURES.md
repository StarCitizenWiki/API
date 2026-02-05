# Feature Research

**Domain:** Star Citizen community item detail UI (ships, components, commodities)
**Researched:** 2026-02-05
**Confidence:** MEDIUM

## Feature Landscape

### Table Stakes (Users Expect These)

Features users assume exist. Missing these = product feels incomplete.

| Feature | Why Expected | Complexity | Notes |
|---------|--------------|------------|-------|
| Item name, type, and role | Primary scan target; users need to know what it is fast | LOW | Surface in top summary block with role chips (e.g., freight, combat) |
| Hero image + thumbnail | Visual identification and quick recognition | LOW | Usually a single hero image with optional gallery |
| Quick facts block (manufacturer, size/class, crew/capacity, cargo, etc.) | Standard for SC community pages; provides fast scan | MEDIUM | Use compact list or grid to reduce scroll |
| Key performance stats (speed, DPS, shields, quantum, etc.) | Users compare performance quickly | MEDIUM | Must be scoped by item type (ship vs component vs commodity) |
| Price and availability (buy/rent/pledge where relevant) | Critical decision info | MEDIUM | Include currency and availability status; show missing data gracefully |
| Acquisition/locations | Where to buy or find is core utility | MEDIUM | For commodities, include buy/sell locations or terminals when available |
| Patch/version or data freshness | Users expect data to match current patch | LOW | Date or patch label near pricing/stats |
| Structured sections for details | Long lists must be organized | LOW | Collapsible sections (multi-open) on mobile |

### Differentiators (Competitive Advantage)

Features that set the product apart. Not required, but valuable.

| Feature | Value Proposition | Complexity | Notes |
|---------|-------------------|------------|-------|
| Scan-first summary layout (top summary + stat grid) | Faster decision-making on mobile | MEDIUM | Aligns with project goal for dense mobile layout |
| Stat prioritization by role | Highlights the most relevant metrics | MEDIUM | Requires role taxonomy and mapping to stat groups |
| Contextual comparison chips (percentile or baseline) | Adds meaning without needing full compare page | HIGH | Requires normalized dataset and baseline definitions |
| Price delta and availability badges | Helps plan purchases quickly | MEDIUM | Works well when data shows buy/rent/pledge separately |
| Compact component/slot summary | Fast loadout understanding without full loadout UI | MEDIUM | Show counts and sizes as quick tags |
| Save/share deep links and copyable codes | Community sharing and cross-site referencing | LOW | Include ship code or UUID where possible |

### Anti-Features (Commonly Requested, Often Problematic)

Features that seem good but create problems.

| Feature | Why Requested | Why Problematic | Alternative |
|---------|---------------|-----------------|-------------|
| Raw data dumps without grouping | Shows everything | Overwhelms mobile users and hides key stats | Group stats by role and collapse sections |
| Single-open accordion only | Keeps UI clean | Slows cross-section scanning | Allow multiple sections open on mobile |
| Infinite scrolling tables for stats | Avoids pagination | Hard to scan and kills performance | Use summary + targeted tables per section |
| Overemphasis on lore text | Adds flavor | Pushes key stats below the fold | Keep lore in a collapsible section |

## Feature Dependencies

```
[Role taxonomy]
    └──requires──> [Role-based stat prioritization]
                       └──requires──> [Normalized stat schema]

[Price and availability]
    └──requires──> [Data source mapping (buy/rent/pledge)]

[Acquisition/locations]
    └──requires──> [Location and terminal data]

[Comparison chips]
    └──requires──> [Baseline definitions and percentile calculations]
```

### Dependency Notes

- **Role taxonomy requires role-based stat prioritization:** Roles drive which stats surface in the quick grid for fast scan.
- **Role-based stat prioritization requires normalized stat schema:** Stats must be comparable across items and types.
- **Price and availability requires data source mapping:** Different data sources supply buy, rent, or pledge pricing.
- **Acquisition/locations requires location and terminal data:** Without location data, the section becomes empty or misleading.
- **Comparison chips requires baselines:** Percentiles need a defined comparison set per item category.

## MVP Definition

### Launch With (v1)

Minimum viable product — what's needed to validate the concept.

- [ ] Top summary block (name, role, type, image) — critical for fast scan
- [ ] Quick stats grid with key performance + capacity — main decision drivers
- [ ] Price and availability (buy/rent/pledge) — core utility
- [ ] Collapsible detail sections (multi-open) — mobile-friendly density
- [ ] Data freshness/patch label — trust and relevance

### Add After Validation (v1.x)

Features to add once core is working.

- [ ] Acquisition/locations table — when reliable location data is wired
- [ ] Component/slot summary tags — when slot data is normalized

### Future Consideration (v2+)

Features to defer until product-market fit is established.

- [ ] Comparison chips or percentiles — requires baseline system and tuning
- [ ] Price history and trend indicators — needs time-series data

## Feature Prioritization Matrix

| Feature | User Value | Implementation Cost | Priority |
|---------|------------|---------------------|----------|
| Top summary block | HIGH | LOW | P1 |
| Quick stats grid | HIGH | MEDIUM | P1 |
| Price and availability | HIGH | MEDIUM | P1 |
| Collapsible sections (multi-open) | MEDIUM | LOW | P1 |
| Data freshness label | MEDIUM | LOW | P1 |
| Acquisition/locations | HIGH | MEDIUM | P2 |
| Component/slot summary | MEDIUM | MEDIUM | P2 |
| Comparison chips | MEDIUM | HIGH | P3 |
| Price history | LOW | HIGH | P3 |

**Priority key:**
- P1: Must have for launch
- P2: Should have, add when possible
- P3: Nice to have, future consideration

## Competitor Feature Analysis

| Feature | Competitor A | Competitor B | Our Approach |
|---------|--------------|--------------|--------------|
| Quick facts block | StarCitizen.tools shows a Quick facts section with role, size, crew, cargo, prices | UEX API exposes commodity buy/sell, legality, availability flags | Use compact summary + quick stats grid across item types |
| Pricing and availability | StarCitizen.tools includes purchase, rental, and pledge prices plus availability | UEX provides buy/sell prices and availability flags | Show buy/rent/pledge with badges and latest patch |
| Structured specs sections | StarCitizen.tools groups specs (dimensions, hull, speed, fuel) | UEX data is field-based (no layout) | Use collapsible sections with multiple open on mobile |
| Variants and related items | StarCitizen.tools lists variants and series | UEX data links to wiki entries | Provide related items section (variants, series, similar role) |
| External links | StarCitizen.tools links to RSI, Erkul, FleetYards | UEX includes wiki URLs | Provide clear external resource links in a footer section |

## Sources

- https://starcitizen.tools/Drake_Cutlass_Black (Quick facts, specs grouping, pricing and availability sections)
- https://api.uexcorp.space/2.0/commodities (Commodity fields for price/availability and flags; API output)
- https://fleetyards.net/ships/cutlass-black/ (JS-rendered; page content not accessible via webfetch)
- https://www.erkul.games/live/ships (JS-rendered; page content not accessible via webfetch)
- https://uexcorp.space/commodities (blocked by 403 via webfetch)

---
*Feature research for: Star Citizen community item detail UI*
*Researched: 2026-02-05*
