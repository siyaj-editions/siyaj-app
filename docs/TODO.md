# TODO

## Immediate (next session)
- [ ] Review local refactor for account + cart + submission/registration + home/webhook + checkout-flow extraction
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
  - [ ] `POST /checkout/webhook` (route legacy toujours active)
- [ ] Manual test checkout flow routes:
  - [ ] `/checkout/informations`
  - [ ] `/checkout/start?session_id=...`
  - [ ] `/checkout/success?session_id=...`
  - [ ] `/checkout/debug?session_id=...`
- [ ] Commit current refactor + tests changes
- [ ] Push branch and open/merge PR

## Validation checklist before push
- [ ] `php -l` on changed PHP files
- [ ] `php bin/console lint:container --no-debug`
- [ ] `php bin/phpunit`
- [ ] Quick UI smoke test desktop + mobile for impacted pages
- [ ] Update docs (`PROJECT_CONTEXT.md` + `TODO.md` mandatory, plus `ARCHITECTURE.md`/`ROADMAP.md` if impacted)

## After merge
- [ ] Pull `main`
- [ ] Continue test hardening batch (increase service coverage + critical integration flows)
- [ ] Keep one coherent PR per bounded context

## Ops/deploy follow-up
- [ ] Confirm deploy workflow still green after account refactor merge
- [ ] Verify assets are still served correctly in prod
- [ ] Check login + checkout + account smoke test on production URL

## Nice to have
- [x] Add dedicated `make smoke` target for quick post-deploy checks
- [x] Add initial service unit tests baseline
- [ ] Add deeper service unit tests for checkout/catalog/admin edge cases
- [ ] Decide if `/checkout/webhook` legacy route should stay protected by `ROLE_USER` or be made public
