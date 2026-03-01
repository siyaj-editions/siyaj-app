# TODO

## Immediate
- [ ] Manual test account routes:
  - [ ] `/mon-compte`
  - [ ] `/mon-compte/adresse`
  - [ ] `/mon-compte/profil`
  - [ ] `/mon-compte/commandes`
  - [ ] `/mon-compte/commandes/{id}`
  - [ ] `/mon-compte/commandes/{id}/facture`
- [ ] Manual test cart mutation routes:
  - [ ] `POST /panier/add/{id}`
  - [ ] `POST /panier/increment/{id}`
  - [ ] `POST /panier/decrement/{id}`
  - [ ] `POST /panier/remove/{id}`
  - [ ] `POST /panier/clear`
- [ ] Manual test public auth/submission routes:
  - [ ] `/register`
  - [ ] `/login`
  - [ ] `/auteurs/soumettre-manuscrit`
- [ ] Manual test home + webhook routes:
  - [ ] `/` (newsletter inscription + doublon)
  - [ ] `POST /stripe/webhook` (signature absente -> 400)
  - [ ] `POST /checkout/webhook` (route supprimée -> 404)
- [ ] Manual test checkout flow routes:
  - [ ] `/checkout/informations`
  - [ ] `/checkout/start?session_id=...`
  - [ ] `/checkout/success?session_id=...`
  - [ ] `/checkout/debug?session_id=...`

## Validation
- [x] `php -l` on changed PHP files
- [x] `php bin/console lint:container --no-debug`
- [x] `php bin/phpunit`
- [x] `make smoke`
- [ ] Quick UI smoke test desktop + mobile for impacted pages

## Ops follow-up
- [ ] Confirm deploy workflow still green after latest merges
- [ ] Verify assets are still served correctly in prod
- [ ] Check login + checkout + account smoke test on production URL

## Next test hardening
- [x] Add deeper tests for catalog query/filter behavior (filter normalization)
- [ ] Add more integration tests (account/cart/checkout authenticated happy path)

## Completed
- [x] Add dedicated `make smoke` target for quick post-deploy checks
- [x] Add initial service unit tests baseline
- [x] Add deeper service unit tests for checkout/account/admin baseline edge cases
- [x] Remove legacy `/checkout/webhook` route (keep `/stripe/webhook` only)
