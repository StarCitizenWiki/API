# Pitfalls Research

**Domain:** Info-dense mobile item detail pages (community data)
**Researched:** 2026-02-05
**Confidence:** MEDIUM

## Critical Pitfalls

### Pitfall 1: Quick-scan priorities buried below the fold

**What goes wrong:**
Role, key performance, and price/availability are not visible in the first screen, so users bounce or miss critical comparisons.

**Why it happens:**
Desktop layouts get scaled down without re-prioritizing content for mobile, and teams assume users will scroll.

**How to avoid:**
Design a top “quick-scan” block that fits in the first viewport: role + 2–4 primary stats + price/availability. Use progressive disclosure for everything else.

**Warning signs:**
Scroll depth is shallow; users tap multiple times before finding price; customer feedback says “can’t find the basics.”

**Phase to address:**
Phase 1 — Content inventory + priority IA

---

### Pitfall 2: Accordions that disorient or hide too much

**What goes wrong:**
Accordions auto-scroll content to the top, lose context, and make users feel like they navigated away; users miss sections or get stuck.

**Why it happens:**
Accordions are used as a compression shortcut without accounting for mobile navigation patterns and state management.

**How to avoid:**
Keep accordion headers in view, avoid auto-scroll on open, and preserve open/closed state across navigation. Provide a mini-IA at top so users know what exists.

**Warning signs:**
Users hit Back to close sections; support questions about “missing sections”; high accordion open/close churn.

**Phase to address:**
Phase 2 — Interaction patterns + component behavior

---

### Pitfall 3: False floors and weak scroll cues

**What goes wrong:**
Large hero panels, separators, or whitespace make the page look complete; users never discover deeper stats or notes.

**Why it happens:**
Visual hierarchy and spacing are tuned for aesthetics, not for mobile discovery.

**How to avoid:**
Ensure the next section peeks into the first viewport, use partial cards/edges, and add clear in-page cues (“Specs”, “Details”).

**Warning signs:**
Heatmaps show strong drop after first screen; important sections receive near-zero engagement.

**Phase to address:**
Phase 2 — Layout + visual hierarchy

---

### Pitfall 4: Hidden navigation for on-page sections

**What goes wrong:**
Specs, variants, and loadout details are hidden behind hamburger or secondary menus; discoverability drops and task time increases.

**Why it happens:**
Mobile-first navigation patterns are copied without considering high-density, goal-oriented tasks.

**How to avoid:**
Expose a visible on-page table of contents or section chips; keep global navigation separate from intra-page navigation.

**Warning signs:**
Users rely on search to find in-page info; navigation usage is low but scroll depth is high and inefficient.

**Phase to address:**
Phase 1 — IA + navigation strategy

---

### Pitfall 5: Mobile data tables that require horizontal scrolling

**What goes wrong:**
Critical stats are cut off or hidden in sideways scroll containers with no cues, causing missed comparisons.

**Why it happens:**
Desktop tables are squeezed into mobile without a responsive pattern or alternative structure.

**How to avoid:**
Convert core stats into stacked cards, split into sections, or allow column priority toggles. Use explicit “scroll for more” indicators if horizontal scroll is unavoidable.

**Warning signs:**
Users report missing fields; analytics show low interaction with table containers.

**Phase to address:**
Phase 2 — Data presentation patterns

---

### Pitfall 6: Inconsistent units, naming, and data freshness

**What goes wrong:**
Stats come from multiple sources with different units or meanings; users compare apples to oranges and lose trust.

**Why it happens:**
Data normalization is deferred, and UI labels are reused across sources without validation.

**How to avoid:**
Establish a normalization layer with explicit units, source badges, and last-updated metadata. Provide unit toggles (e.g., SCU, m/s) when relevant.

**Warning signs:**
Conflicting numbers across sections; frequent “wrong data” reports; inconsistent formatting across items.

**Phase to address:**
Phase 3 — Data model + normalization

---

### Pitfall 7: Over-deferment (progressive disclosure misuse)

**What goes wrong:**
Too much information is hidden behind “more” or separate screens, forcing extra taps and harming trust in an info-dense domain.

**Why it happens:**
Teams interpret progressive disclosure as “hide everything” instead of “prioritize and preview.”

**How to avoid:**
Show summaries + preview values for each section with a clear affordance to expand. Validate the primary/secondary split with analytics or quick tests.

**Warning signs:**
High tap counts before meaningful content appears; users skip sections entirely.

**Phase to address:**
Phase 1 — Content prioritization

---

### Pitfall 8: Heavy payloads for a single item page

**What goes wrong:**
Large images, 3D assets, or full-spec datasets load up front, causing slow mobile performance and layout shifts.

**Why it happens:**
Desktop performance assumptions are reused; no staged loading or skeleton strategy.

**How to avoid:**
Lazy-load secondary sections, precompute summaries, and use image variants. Defer heavy assets behind user intent.

**Warning signs:**
TTI > 3s on mid-tier phones; noticeable CLS; user complaints about slowness.

**Phase to address:**
Phase 3 — Performance + loading strategy

---

## Technical Debt Patterns

Shortcuts that seem reasonable but create long-term problems.

| Shortcut | Immediate Benefit | Long-term Cost | When Acceptable |
|----------|-------------------|----------------|-----------------|
| Hard-coded stat ordering in templates | Faster build | Reordering requires code changes; inconsistencies across items | Only for a temporary prototype |
| Client-side unit conversions without source metadata | Quick UI polish | Trust issues when conversions are wrong or unclear | Never (needs source + unit) |
| One-off CSS overrides per item category | Visual speed | Unmaintainable layout variants | MVP only with strict cleanup plan |
| Dumping full spec lists into the initial payload | Simplifies rendering | Slow loads and high memory use on mobile | Never; stage or paginate |

## Integration Gotchas

Common mistakes when connecting to external services.

| Integration | Common Mistake | Correct Approach |
|-------------|----------------|------------------|
| Multiple data sources (e.g., prices vs stats) | Merging by display name or fuzzy match | Use canonical IDs + mapping tables |
| Availability/price feeds | No “last updated” indicator | Show timestamps + fallback messaging |
| Image/CDN assets | Using desktop-only sizes on mobile | Use responsive sizes + lazy loading |
| Community-sourced stats | No validation or provenance | Label source and apply sanity checks |

## Performance Traps

Patterns that work at small scale but fail as usage grows.

| Trap | Symptoms | Prevention | When It Breaks |
|------|----------|------------|----------------|
| Rendering huge stat tables in one DOM block | Slow scroll, input lag | Section split + virtualization | 150+ rows on mid-tier phones |
| Hydrating every widget on load | Long main-thread blocks | Defer non-critical widgets | Multiple interactive modules |
| Image-heavy headers for each item | High LCP, data usage | Use smaller variants + placeholder | On cellular connections |

## Security Mistakes

Domain-specific security issues beyond general web security.

| Mistake | Risk | Prevention |
|---------|------|------------|
| Exposing third-party API keys in client | Key abuse and quota exhaustion | Proxy via server + rate limits |
| Rendering external HTML/markdown without sanitization | XSS or malicious links | Sanitize and whitelist elements |
| Auto-linking community URLs without checks | Phishing risk | Validate domains or add warnings |

## UX Pitfalls

Common user experience mistakes in this domain.

| Pitfall | User Impact | Better Approach |
|---------|-------------|-----------------|
| Icon-only stats with no labels | Misinterpretation, mistrust | Always show label + unit |
| Too many badges/labels in one cluster | Visual noise; key data lost | Limit to 1–2 critical badges |
| Sticky bars that consume >25% height | Reduced reading space | Condense sticky UI or make it dismissible |
| Tooltips for critical data | Missed information on touch | Inline explanations or info rows |

## "Looks Done But Isn't" Checklist

Things that appear complete but are missing critical pieces.

- [ ] **Quick-scan block:** Role/perf/price visible without scroll and includes units
- [ ] **Availability:** Timestamp and source shown, not just a number
- [ ] **Specs:** Section anchors account for sticky header offset
- [ ] **Mobile tables:** Key stats readable without horizontal scroll
- [ ] **Accordions:** Open state persists on back/forward navigation

## Recovery Strategies

When pitfalls occur despite prevention, how to recover.

| Pitfall | Recovery Cost | Recovery Steps |
|---------|---------------|----------------|
| Quick-scan data buried | MEDIUM | Rework top summary, move key stats above fold, add analytics check |
| Disorienting accordions | LOW | Adjust open behavior, keep headers visible, preserve state |
| Inconsistent units | HIGH | Implement normalization layer, backfill data, annotate sources |

## Pitfall-to-Phase Mapping

How roadmap phases should address these pitfalls.

| Pitfall | Prevention Phase | Verification |
|---------|------------------|--------------|
| Quick-scan priorities buried | Phase 1 | First viewport contains role/perf/price on test devices |
| Over-disclosure via accordions | Phase 2 | Usability test shows low back-button misuse |
| False floors | Phase 2 | Scroll depth > 60% for primary sections |
| Hidden section navigation | Phase 1 | Users can reach any section within 2 taps |
| Mobile tables with horizontal scroll | Phase 2 | Key stats readable without sideways scroll |
| Inconsistent units and freshness | Phase 3 | All stats show units + source + updated time |
| Over-deferment | Phase 1 | Primary/secondary split validated with analytics |
| Heavy payloads | Phase 3 | LCP/TTI within targets on mid-tier phones |

## Sources

- https://www.nngroup.com/articles/scrolling-and-attention/ (LOW-MEDIUM)
- https://www.nngroup.com/articles/progressive-disclosure/ (MEDIUM)
- https://www.nngroup.com/articles/defer-secondary-content-for-mobile/ (MEDIUM)
- https://www.nngroup.com/articles/mobile-accordions/ (MEDIUM)
- https://www.nngroup.com/articles/illusion-of-completeness/ (MEDIUM)
- https://www.nngroup.com/articles/hamburger-menus/ (MEDIUM)
- https://www.nngroup.com/articles/infinite-scrolling/ (MEDIUM)

---
*Pitfalls research for: info-dense mobile item detail pages*
*Researched: 2026-02-05*
