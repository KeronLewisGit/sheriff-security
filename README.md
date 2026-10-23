# Sheriff Security Services – website, procurement desk and back office

A concept rebuild for **Sheriff Security Services** (Sheriff Guyana group), Georgetown, Guyana: an ISO 9001:2015 / ISO 45001:2018 certified security contractor for the energy, maritime and commercial sectors that currently has no working website (sssgy.net shows a cPanel default page; sheriffguyana.com is parked).

Laravel 13 · PHP 8.3 · Tailwind CSS v4 · Alpine.js · SQLite locally, MySQL on Hostinger shared hosting.

## What it does

**Public site**: home, services (six sourced lines of business, each with its own page), industries, HSE & compliance, group companies, leadership, careers, contact, privacy, photo credits, sitemap. Every fact carries a source line.

**Procurement desk**
- **Request for proposal**: industry, site type (onshore, shorebase, offshore support, port…), location, posts, coverage, armed/unarmed, start date, term and document uploads. Reference `RFP-26-00012`, confirmation email, office alert.
- **Vendor prequalification pack**: procurement teams choose compliance documents (ISO certificates, insurance, HSE policy…). A manager approves each request; the requester gets a **temporary signed link** (default 7 days, editable) to a private download page. Links can be withdrawn; every download is logged with time, IP and browser.
- **Alarm service** (Sheriff Alarms customers): fault report with ticket number `SA-26-00042`; status lookup by ticket number + email; staff notes stay private, the public note shows to the customer.

**Careers**: postings, one-page application with police clearance, ID, certificates and CV uploads (private disk), recruitment pipeline New → Screening → Interview → Training → Hired/Rejected, drag-and-drop kanban, CSV export with formula-injection guard.

**Back office** (`/admin`): roles **admin / business development / HR**. The dashboard leads with the number to act on: proposal requests not answered within the response target (HR sees applications waiting more than two days). Proposal board, vendor-pack approvals and download log, compliance document library with expiry warnings, alarm tickets, messages, users and an editable settings table (phone, address, emails, response target, link lifetime).

## Run it locally

```bash
composer install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seeder=DemoSeeder
npm ci && npm run build
php artisan serve --port=8104
```

Demo logins (password `password`): `admin@sheriff.test`, `bd@sheriff.test`, `hr@sheriff.test`. Demo companies, people and postings are fictional; demo compliance files are one-page "SAMPLE" PDFs.

## Checks

```bash
php artisan test            # feature tests incl. signed-link expiry, revocation and role authorisation
vendor/bin/phpstan analyse  # level 6, analysed as PHP 8.3
vendor/bin/pint --test
```

## Deploy

`scripts/package-hostinger.sh` builds `dist/sheriff-hostinger.zip`, which extracts into `public_html/` with the app in `sheriff-app/`. See [INSTALL-HOSTINGER.md](INSTALL-HOSTINGER.md).

## Before launch (confirm with Sheriff)

- Phone, address and email are from the company's 2021 website (Admin → Settings).
- Leadership titles are as last reported in 2023.
- Upload the real compliance documents; replace the Wikimedia Commons photos with Sheriff's own; supply the vector logo.
