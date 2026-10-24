# MADEBY REST API

Local root: `http://127.0.0.1:8000/api`. This backend is not deployed. Send `Accept: application/json` on every request. JSON bodies use `Content-Type: application/json`; image requests use multipart/form-data.

## Public routes

| Method | Path | Result |
| --- | --- | --- |
| GET | /products | Ready-made listings only |
| GET | /products/{slug} | Ready-made detail; commissions return 404 |
| GET | /commissions | Commission listings only |
| GET | /commissions/{slug} | Commission detail; products return 404 |
| GET | /listings | Both listing types |
| GET | /listings/{slug} | Listing detail |
| GET | /creators | Creator catalogue |
| GET | /creators/{slug} | Creator profile and database work count |
| GET | /categories | Category IDs/names for forms |

Listing collection parameters: `type=product|commission` (only /listings), `search`, `creator` (slug), `category` (name), `page` (positive integer), `per_page` (1–100, default 20). Search matches title, description, tags or creator name. Creator collections accept `search`, `page`, `per_page`. Collection responses have `data`, `links`, and `meta`; categories return `data` only. Detail responses have `data`.

Listing response fields: `id` (slug), `databaseId`, `type`, `title`, `category`, `category_id`, `creatorId` (slug), `creator_id`, `price`, `priceFrom`, `stock`, `commissionSlots`, `deliveryTime`, `rating`, `description`, `image`, `images` (ordered URL array), `gallery` (id/url array), `tags`. Creator response fields: `id` (slug), `databaseId`, `name`, `role`, `bio`, `location`, `avatar`, `followers`, `likes`, `works`. Main and gallery images use backend `/storage/` URLs.

## Authentication

| Method | Path | Authentication / result |
| --- | --- | --- |
| POST | /login | JSON email/password; admin required; returns token/token_type (200) |
| GET | /user | Sanctum bearer token; JSON data |
| POST | /logout | Sanctum bearer token; revokes current token (204) |

Login permits 5 attempts/minute. Invalid credentials return 422; valid non-admin credentials return 403. Use `Authorization: Bearer <token>` for protected requests. Keep tokens out of source, frontend environment files, screenshots and shared exports. Initialize your administrator password using `php artisan madeby:admin-password` in a private terminal; no default password is documented or stored in source.

## Protected CRUD (Sanctum + admin gate)

| Method | Path | Success |
| --- | --- | --- |
| POST | /listings | 201 + data |
| PUT or PATCH | /listings/{slug} | 200 + data |
| DELETE | /listings/{slug} | 204, empty body |
| POST | /creators | 201 + data |
| PUT or PATCH | /creators/{slug} | 200 + data |
| DELETE | /creators/{slug} | 204, or 409 when creator still has listings |

Listing create/update fields (both PUT and PATCH require the full editable scalar fields):

- Required: `title` (max 255), `type` (product/commission), `category_id`, `creator_id` (existing database integer IDs), `description`, `rating` (0–5).
- Products: `price`, `stock` required nonnegative integers, maximum 4294967295.
- Commissions: `price_from`, `commission_slots` required nonnegative integers; `delivery_time` required string, max 255. Incompatible fields are set to null automatically.
- `tags`: optional comma-separated string. Response uses an array.
- `image`: required on create, optional on update; JPG/PNG, max 2048 KB.
- `images[]`: optional gallery files, at most 20 per request, same image rules.
- `remove_images[]`: optional gallery IDs belonging to this listing. Other listings' IDs fail validation. Obtain IDs from response `gallery`.

Slug is generated from title with a collision suffix; updating the title may change the slug, so use the returned `id` in later requests. JSON updates can omit image files. For multipart updates use POST to the same detail URL with `_method=PUT` (Laravel method override), since PHP does not reliably parse multipart PUT bodies.

Creator fields: required `slug` (unique alpha-dash, max 255), `name`, `role`, `bio`, `location`, `followers`, `likes`; all except bio max 255. `avatar` JPG/PNG max 2048 KB required on create, optional on update. Existing referenced avatars are preserved when still in use. Deleting a creator with listings returns 409 instead of cascading real listings.

## Examples without credentials

```sh
curl -H 'Accept: application/json' 'http://127.0.0.1:8000/api/products?per_page=2&page=1'
curl -H 'Accept: application/json' 'http://127.0.0.1:8000/api/listings?type=commission&creator=mira'
curl -H 'Accept: application/json' 'http://127.0.0.1:8000/api/creators/maylin'
```

For Postman: set a private `base_url` variable to the API root; create login with your own credentials; put the returned token in a private bearer auth variable; send multipart creation fields and image; read returned slug; update using JSON; delete only your disposable test listing; logout. Do not export real credential/token variables. Automated equivalent requests live in `tests/Feature/MarketplaceTest.php`.

## HTTP status codes

200 read/update/login; 201 create; 204 delete/logout; 401 unauthenticated or revoked token; 403 authenticated non-admin; 404 missing/wrong-type slug; 409 creator still referenced; 422 validation/invalid credentials; 429 rate limit. Validation errors have `message` and `errors`; errors are JSON when Accept is application/json.
