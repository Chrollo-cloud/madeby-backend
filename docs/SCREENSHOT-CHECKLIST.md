# MADEBY screenshot checklist

Use only local/demo accounts and safe test records. Never capture passwords, bearer tokens, .env contents or real customer data. Checkbox completion means a screenshot file was captured, not just an automated test passed.

## Basic CRUD Laravel

- [ ] Admin listing table showing ready-made and commission types, creator/category and pagination.
- [ ] Search plus creator/type filter with results.
- [ ] Create form and validation errors using disposable test listing.
- [ ] Edit form with uploaded main image and gallery removal controls.
- [ ] Successful update and removal of disposable test listing only.

## Laravel Backpage

- [ ] Breeze login screen, without credentials in the frame.
- [ ] Database-backed dashboard counters and recent listings.
- [ ] Non-admin 403 response and guest login redirect.
- [ ] Logout returning to public home.

## REST API

- [ ] Product GET with JSON data and pagination metadata.
- [ ] Commission GET proving separate type.
- [ ] Creator GET and database works count.
- [ ] Guest write request 401, without auth headers visible.
- [ ] Safe test create 201, update 200, delete 204.
- [ ] Invalid payload 422 and referenced creator 409.
- [ ] Revoked-token 401, with token concealed.

## React local integration

- [x] Checkout with API-backed tee and order totals — screenshots/react-checkout.png.
- [x] Existing mock order success — screenshots/react-order.png.
- [ ] Pointer-controlled commission card preview, before/after image.
- [ ] Touch cycling on a real touch device.
- [ ] Responsive home/explore/detail/cart layouts.
- [ ] Loading and backend-error/retry screen captures.
- [ ] Commission gallery and request-modal captures (browser behavior tested; screenshots not captured).

Production screenshots remain pending until a backend is deployed and integration is explicitly tested. Current screenshots are localhost only.
