# BD-EX Cargo

Air freight and courier platform for parcels moving between **Australia and Bangladesh** —
a Filament admin back office, a public booking and quoting site, a customer and
reseller portal, and a carrier abstraction that keeps third-party carriers
invisible to customers.

**Author:** Moshiur Rahman · [rmedha037@gmail.com](mailto:rmedha037@gmail.com)

---

## What it does

| Area | Covered |
|---|---|
| **Booking** | Multi-step wizard, instant itemised quote, doorstep pickup slots, drafts saved in the browser, live prohibited-item checks, address coverage checks |
| **Pricing** | Per-category and per-weight rate cards, conditional surcharges, 10% GST / 15% VAT per corridor, volume tiers, customer contract rates, dual-currency display |
| **Tracking** | Six published milestones, BD-EX tracking numbers, public tracking, carrier polling every 12 hours, four notification channels |
| **Documents** | Invoices, barcoded labels, batch label printing, customs declarations, BD-EX air waybills, searchable invoice history |
| **Coverage** | Service areas per country, city and postcode, with express and standard transit times |
| **Reselling** | Seller accounts, own listings at own margin, ratings and reviews, seller performance |
| **Payments** | Stripe (AU), SSLCommerz and bKash (BD), or staff confirmation — swappable at runtime |
| **Languages** | English and Bengali, across the site, both panels and every notification |

## Stack

Laravel 13 · PHP 8.3+ · Filament 5 · Livewire 4 · Filament Shield · MySQL/MariaDB ·
Tailwind 4 + Vite · dompdf · Pest 4 · Pint

## Getting started

```bash
composer install
cp .env.example .env && php artisan key:generate

# create a database, set DB_* in .env, then:
php artisan migrate --seed          # schema, rate card, demo data
npm install && npm run build

php artisan shield:super-admin --user=1
composer dev                        # serve + queue listener + vite
```

- Public site — `/`
- Admin panel — `/admin`
- Customer & seller portal — `/portal`

One cron entry drives the scheduled work:

```
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

## The three rules this codebase is built around

### 1. Customers never learn which carrier we used

BD-EX books with Aramex, FedEx, DHL and UPS, and routinely consolidates several
customers' orders onto **one** carrier booking. The customer only ever sees a
BD-EX tracking number and BD-EX wording.

- An order gets its BD-EX number on creation, before any carrier exists.
- `CarrierConsignment` holds the carrier's own number, shared by every order on it.
- When a carrier reports movement, `StatusFanOutService` writes one history row
  **per order** — same status, same timestamp — so five customers see the
  identical milestone at the identical moment, and none is told why.
- `CustomerFacingOrder` is the only class allowed to build a customer-facing
  payload. It selects by column whitelist and never loads the carrier relation.

Twelve tests plant `FedEx`, its tracking number and its API credential in the
database, then assert none of it can be made to appear in the tracking endpoint,
the homepage, the portal, or the generated invoice and label PDFs — which are
parsed back to text and checked.

### 2. Nothing operational is hardcoded

Every price, fee, tax rate, credential, driver selection, role and piece of copy
is a database row, editable from the panel. Only Laravel's bootstrap config
(`APP_KEY`, `APP_ENV`, `APP_URL`, `APP_DEBUG`, `DB_*`, `CACHE_STORE`,
`QUEUE_CONNECTION`, `SESSION_DRIVER`) stays in `.env`.

- Credentials go through `App\Services\Config\DynamicConfig`, encrypted at rest.
- Prices go through `App\Services\Pricing\PricingEngine`, which holds no rates.
- Carriers, SMS, WhatsApp and payment gateways sit behind driver interfaces
  selected at runtime.

Three tests enforce this rather than a code review: no `env()` in `app/`, no
`config('services.*')` in `app/`, and no money literal anywhere in
`app/Services/Pricing`.

**The honest limit:** a provider nobody has written a driver class for still needs
one class implementing the relevant interface. A wire protocol cannot be invented
from admin clicks. Every operational change after that first class — rotating
keys, switching provider, adding a second account — is data entry.

### 3. The engine refuses rather than guesses

Quoting a category into a zone with no rate, or a weight no tier covers, throws
with a message naming the screen that fixes it. Figures the published rate card
never stated are seeded flagged and surfaced on the dashboard until someone
confirms them.

## Layout

```
app/
  Actions/            CreateOrderFromBooking
  Filament/           Resources, Pages, Widgets (admin) · Portal/ (customer & seller)
  Services/
    Carrier/          adapters, factory, normalised statuses
    Compliance/       prohibited items, service areas
    Config/           DynamicConfig
    Documents/        invoices, labels, customs, barcodes
    Notifications/    SMS and WhatsApp drivers
    Payments/         gateway drivers and processor
    Pricing/          PricingEngine and its DTOs
    Reporting/        metrics and report builder
    Tracking/         number generator, fan-out, CustomerFacingOrder
docs/
  reference/prototype/    the approved design this site is ported from
  superpowers/specs/      design decisions, per phase
```

## Commands

```bash
composer test     # Pest
composer lint     # Pint --test + prettier --check
composer fix      # Pint + prettier --write
```

## Design

The public site is a 1:1 port of the approved prototype in
`docs/reference/prototype/` — its CSS, its markup and all fourteen keyframes.
Colours, fonts and every word are driven from the database, so the look changes
from the panel rather than from the stylesheet.

## Licence

Proprietary. © BD-EX Cargo.
