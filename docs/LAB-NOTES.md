# MADEBY lab notes

Verified locally on 2026-10-22 (Asia/Makassar). Existing Laravel 9.52.22 application at `/Users/chrollo/Documents/madeby-laravel`; React at `/Users/chrollo/Documents/e-commerce`. No new Laravel project, redesign, destructive migration, push, or deployment was performed.

## Database

The configured MySQL database `madeby` was missing. Created it with `CREATE DATABASE IF NOT EXISTS`, using the existing Laravel connection credentials without printing them. Ran `php artisan migrate --force`, then the additive `is_admin` migration and repeat-safe seeders. Generated the missing application encryption key and created the public storage symlink.

Current verified data: 9 tables including migrations, 1 seeded administrator, 7 categories, 6 creators, 10 listings (4 products / 6 commissions), 26 gallery images. All 9 migrations are applied. Verified all three foreign keys and every listing's creator/category/gallery relationship and image file. Repeat seeding preserves records and does not reset passwords or overwrite listing fields. GallerySeeder adds the existing portfolio images by seeded slug.

Never use destructive migration resets against this MySQL database. PHPUnit now forces SQLite `:memory:` and has a testing-only encryption key so its RefreshDatabase tests cannot reset madeby.

## Basic CRUD Laravel

Existing web listing CRUD retained. Shared ListingManager validates web/API input, saves main/gallery uploads, removes selected gallery entries owned by that listing, clears incompatible price/stock/commission fields when changing type, and removes unused image files. Referenced images shared by another listing or creator are retained. Creator API deletion returns 409 when listings still reference it.

Admin list supports title search, creator/type filters, five-row pagination with query parameters, and paginated creator listings. Forms show validation errors and gallery removal controls. The unused resource show route was removed; use edit or public listing detail.

## Laravel Backpage

Breeze login, logout, registration, password and profile tests pass. Administrative routes now require both authentication and an explicit `is_admin` flag. Registration cannot mass-assign that flag. Dashboard counts and recent listings come from Eloquent queries. Fixed the existing AdminLayout title binding.

The seeded administrator uses an unrecorded random password; no shared default password is installed. Set your own password locally using the hidden interactive prompts:

```sh
cd /Users/chrollo/Documents/madeby-laravel
php artisan madeby:admin-password
```

This changes only an existing administrator's password and revokes that user's API tokens. It does not promote ordinary users. Then sign in at `/login` with the seeded admin email `admin@madeby.test`. Password entry for this real seeded account remains a user setup step; tests use disposable accounts instead.

## REST API

Public JSON endpoints cover products, commissions, all listings, creators and categories. Admin-only listing and creator CRUD uses Sanctum authentication plus the same admin authorization gate as web management. Token login is rate-limited and logout revokes the active bearer token. See API-ENDPOINTS.md for routes, payloads, status codes, authentication and examples.

## React integration

CatalogueContext loads all paginated listings and creators from Laravel when `VITE_API_BASE_URL` is configured. Existing slug IDs, camelCase fields, component classes, gallery cursor handlers, saved items, cart, checkout and commission modal remain. Loading, error and retry states are included; configured backend failures are visible. No configured API URL means the bundled demo catalogue continues to work.

Local startup:

```sh
# Laravel terminal
cd /Users/chrollo/Documents/madeby-laravel
php artisan serve --host=127.0.0.1 --port=8000

# React terminal
cd /Users/chrollo/Documents/e-commerce
VITE_API_BASE_URL=http://127.0.0.1:8000/api npm run dev -- --host 127.0.0.1 --port 5173
```

Laravel CORS defaults permit localhost:5173, 127.0.0.1:5173 and the existing Vercel frontend. Override with comma-separated `FRONTEND_URLS`; set backend APP_URL to its real public origin when deployed so image URLs resolve correctly. Vite variables are build-time values; never put tokens or passwords in VITE variables.

Only local integration is verified. No backend was deployed and the existing Vercel deployment was not changed. React checkout/payment/orders and commission submission remain the existing client-side prototype flows, without real payments, persisted orders or external messages. The React admin overview displays catalogue data; protected write management is in Laravel.

## Verification and remaining work

See TEST-RESULTS.md for actual evidence and SCREENSHOT-CHECKLIST.md for captured and outstanding visual checks. Remaining: user chooses seeded admin password; manual Laravel admin screenshots, pointer/touch preview and responsive checks; production backend deployment and production integration verification if separately authorized. PHP 8.5 emits vendor deprecation notices with this older Laravel dependency set; XAMPP's PHP 8.0 is too old for the installed dependencies. No dependency upgrade was attempted.
