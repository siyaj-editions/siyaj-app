# TODO

## Immediate (next session)
- [ ] Review local refactor for account + cart + submission/registration service extraction
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
- [ ] Commit account/cart/submission/registration refactor changes
- [ ] Push branch and open/merge PR

## Validation checklist before push
- [ ] `php -l` on changed PHP files
- [ ] `php bin/console lint:container --no-debug`
- [ ] Quick UI smoke test desktop + mobile for impacted pages
- [ ] Update docs (`PROJECT_CONTEXT.md` + `TODO.md` mandatory, plus `ARCHITECTURE.md`/`ROADMAP.md` if impacted)

## After merge
- [ ] Pull `main`
- [ ] Continue next extraction batch outside admin (stripe webhook + home/catalog minor cleanup)
- [ ] Keep one coherent PR per bounded context

## Ops/deploy follow-up
- [ ] Confirm deploy workflow still green after account refactor merge
- [ ] Verify assets are still served correctly in prod
- [ ] Check login + checkout + account smoke test on production URL

## Nice to have
- [ ] Add dedicated `make smoke` target for quick post-deploy checks
- [ ] Add service unit tests for checkout/catalog/admin flows
