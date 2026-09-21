# BD-EX Cargo — Foundation & Pricing

*Design agreed 2026-09-21. Covers build-order phases 1–2. Later phases (orders,
booking wizard, carrier abstraction, notifications, documents) each get their own
spec.*

## Why this shape

BD-EX Cargo runs on SMS bookings and price-list images. The replacement has one
governing constraint: **operations must be able to change any price, fee, credential,
role or piece of copy without a developer**. Everything below is a consequence of
that, and every acceptance check is an automated test rather than a review note.

## Decisions

| Decision | Choice | Why |
|---|---|---|
| Starter | Fresh Laravel 13 in `/home/tonmoy/web/bdex`, dependencies cherry-picked from `expense-system` | Proven stack, none of the expense/inventory/payroll baggage. The static prototype moved to `docs/reference/prototype/` and is now rebuilt as Blade. |
| Queue / cache / session | `database` driver | Matches what `expense-system` actually runs and the Webuzo VPS reality. No code names Redis, so the switch is a `.env` edit if a persistent worker ever exists. Horizon deferred with it. |
| Admin theme | `expense-system`'s Filament theme stack, repaletted from orange to signal green | Same visual language, one token system, no second design to maintain. Build-time, so a marketing colour change never shifts admin chrome. |
| Config storage | `config_entries` (secret/operational) + `site_settings` (display) + `content_blocks` (copy) | Keeps the boundary a schema fact: the public request path never queries a table holding encrypted values. `spatie/laravel-settings` dropped — the existing cached-repository pattern does the job with one fewer dependency. |
| Roles | Filament Shield, one bootstrap super admin | No role enum, no role seeder anything depends on. 123 permissions generated from the panel itself. |

## Architecture

```
Public site (Blade + Livewire 4)          Filament panel (/admin)
  HomeController, quote calculators          Pricing · Content · System
            │                                          │
            │ reads                                    │ writes
            ▼                                          ▼
      SiteSettings · SiteContent  ◀── cache ──▶   MySQL / MariaDB
      (cached arrays, never models)                    │
            │                                          │
            └──────────── PricingEngine ◀──────────────┘
                     (uncached: a stale quote is worse than a query)
```

### The configuration layer

`DynamicConfig` is the only read path for a credential or operational number.
`ConfigEntry::plainValue()` / `fillPlainValue()` own the encryption, so no caller
needs to know whether a given key is a secret. Three tests enforce the boundary
mechanically: no `env(` in `app/`, no `config('services.` in `app/`, and no money
literal anywhere in `app/Services/Pricing` (comments stripped first, zero defaults
and the percentage divisor allowed).

The Configuration page builds its tabs from whatever rows exist — there is no field
hardcoded per provider. **The honest limit:** a vendor nobody has written a driver
class for still needs that one class before its credentials mean anything. Every
operational change after it — rotating keys, switching the active provider, adding a
second account — is admin data entry.

### The pricing engine

`PricingEngine` holds no rates. Base prices come from `item_category_zone_rates` (AU→BD,
per category per zone) or `weight_tiers` (BD→AU, banded by total weight); every fee is a
`surcharge_rules` row evaluated against a closed `QuoteContext`; the tax rate is a config
entry; conversion uses the newest `fx_rates` row.

Rules scoped to an item category are evaluated **per matching line** against that line's
own context, which is what makes "an extra fee per electrical item declared over $100"
expressible as data. `QuoteResult` carries a `QuoteLineItem` per applied rule with the
`source_code` that produced it, so the itemisation the customer reads is traceable to a
specific row.

Where the rate card is silent, the engine refuses rather than guesses: quoting a category
into a zone with no rate, or a weight no tier covers, throws with a message naming the
screen that fixes it.

### Deliberately unfinished data

Figures that were missing or contradictory in the source material are seeded as flagged
rows rather than invented numbers, and the `PricingReadiness` dashboard widget keeps them
visible:

- the BD→AU non-garment handling fee (never published) — seeded at 0, `is_placeholder_amount`
- the high-value electrical loading (published as "$15/$20 per item" without saying which applies when) — seeded at 15, flagged
- TV bands 28"–52" (government duty known, volume fee and local delivery never published) — seeded inactive
- published transit times disagree between sources — `shipping_routes.transit_time_text` is free text the business owns

### Theming

`site_settings` holds six colours and three font stacks, seeded from the prototype's
`:root`. `ThemeVars` writes them into the document head as CSS custom properties on every
request; Tailwind utilities reference the tokens, so a colour change needs no rebuild.
Saving clears the cache through `InvalidatesSiteCache`, so the next visitor sees it — a
test asserts exactly that, including that the old value is gone.

Font stacks legitimately contain quotes, so token values are emitted unescaped after
stripping the characters that could close the style block or start a new rule.

Caches store attribute arrays, never Eloquent instances: a serialised model came back
from the database cache store as an incomplete class and took the homepage down during
build-out.

## What exists now

- 11 domain tables, 6 enums, 11 models
- `DynamicConfig`, `SiteSettings`, `SiteContent`, `SiteCache`, `InvalidatesSiteCache`
- `PricingEngine`, `SurchargeEvaluator`, `TvCostCalculator`, `FxConverter` + DTOs
- 9 Filament resources, 2 settings pages, 1 readiness widget, 1 relation manager
- Shield: 123 permissions, 10 policies, one bootstrap super admin
- Public site: home, `/quote`, `/quote/tv`, two Livewire calculators
- 54 tests, 94 assertions, Pint clean

## Not in this phase

Orders, boxes, items, the booking wizard, the carrier abstraction and its sync job,
notifications, invoices and labels, the customer portal, payments, EN/BN localisation.
The schema and the engine were built so none of them requires reshaping this work.
