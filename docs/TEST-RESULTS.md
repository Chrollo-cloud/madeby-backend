# MADEBY test results

Date: 2026-10-22, Asia/Makassar. Local verification only. Laravel 9.52.22 / PHPUnit 9.6.38 / host PHP 8.5.10 / XAMPP MySQL. No deployment or push.

| Check | Actual result |
| --- | --- |
| Database creation | PASS — madeby created through configured connection; no credentials printed |
| Migrations | PASS — all 9 applied; no destructive reset used on madeby |
| MySQL tables | PASS — users, password_resets, failed_jobs, personal_access_tokens, creators, categories, listings, listing_images, migrations |
| Seed counts | PASS — 1 admin, 7 categories, 6 creators, 4 ready-made + 6 commission listings, 26 gallery images |
| Repeat seed | PASS — run repeatedly without duplicate records/password reset |
| Relationships/files | PASS — listing creator/category/gallery, reverse image relation, all 3 MySQL foreign keys, all listing/gallery image files present |
| Route inventory | PASS — `php artisan route:list --except-vendor`, 51 application routes; saved in route-list.txt |
| PHPUnit | PASS — `php -d error_reporting=22527 vendor/bin/phpunit`: 32 tests, 193 assertions |
| Artisan test | PASS — `php -d error_reporting=22527 artisan test`: 32 passed |
| Safe test isolation | PASS — PHPUnit forces SQLite :memory:, fake storage; madeby not reset |
| MySQL API CRUD | PASS — equivalent HTTP-kernel requests within MySQL transaction: guest POST 401, login 200, product/creator GET 200, create 201, detail 200, update 200 verified in DB, delete 204, logout 204, revoked-token /user 401; rollback preserved original counts |
| Web CRUD/admin | PASS — automated guest redirect/non-admin 403, dashboard database counts, list search/type filtering, creator pagination, create/edit/update/delete and form rendering |
| Upload/gallery | PASS — main/gallery save, replacement cleanup, owned-image removal, deletion cleanup, invalid file/fractional stock/foreign creator/gallery ID validation |
| REST filters | PASS — product/commission separation, search, creator/category filters, pagination, invalid type, missing and wrong-type 404 |
| Creator API | PASS — create/read/update/delete; referenced creator returns 409 and listings remain |
| Authentication | PASS — Breeze login/invalid password/logout, password/profile suite, Sanctum login/bearer requests/token revocation, admin gate |
| React default build | PASS — `npm run build`; only React Router use-client bundler warnings |
| Browser API integration | PASS — local React catalogue loaded MySQL products and creators, gallery URLs rendered |
| Browser saved/cart/checkout | PASS — save tee; add to cart; total Rp180,000 including shipping; checkout safe test details; mock QRIS success; order summary; saved item retained |
| Browser commission | PASS — type/search reduced studio result; five-image gallery; selecting image 3 changed main image; request modal rendered |
| Error/retry UI | PASS — connection failure displayed retry state during initial local server setup; catalogue loaded after retry/server correction |
| Cursor/touch preview | Source preserved; physical pointer/touch behavior not manually verified — unfinished |
| Responsive/mobile visual QA | Not exhaustively verified — unfinished |
| Laravel admin screenshots | HTTP response tests pass; manual browser screenshots not captured — unfinished |
| Real admin password setup | Random seed password intentionally not recorded; user must run interactive admin-password command |
| Production integration | Unfinished — no deployed backend, no Vercel configuration changes or deployment |

Initial failures were fixed: missing encryption key, missing AdminLayout title prop, and test guard isolation after Sanctum revocation. Local server was restarted from public/ after an incorrect verification router working directory. No application redesign was involved.

Screenshots: screenshots/react-checkout.png (before entering test details) and screenshots/react-order.png (mock order using example.test address). These show local prototype behavior only. Real payments, persisted orders, stock reservation and external commission messages are not implemented by the existing frontend and were not claimed.

Known environment limitation: PHP 8.5 produces vendor deprecation notices for the older Laravel dependency set. The commands above suppress deprecation output only; no vendor files were modified. XAMPP bundled PHP 8.0.28 is below the installed dependency requirement. Use a compatible PHP CLI; runtime/dependency modernization is outside this change.
