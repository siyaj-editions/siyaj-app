# TODO

## Immediate (next session)
- [ ] Review local refactor for all admin controllers/services
- [ ] Manual test admin routes:
  - [ ] `/admin`
  - [ ] `/admin/auteurs`
  - [ ] `/admin/livres`
  - [ ] `/admin/commandes`
  - [ ] `/admin/manuscrits`
  - [ ] `/admin/newsletter`
- [ ] Commit admin refactor changes
- [ ] Push branch and open/merge PR

## Validation checklist before push
- [ ] `php -l` on changed PHP files
- [ ] `php bin/console lint:container --no-debug`
- [ ] Quick UI smoke test desktop + mobile for impacted pages
- [ ] Update docs (`PROJECT_CONTEXT.md` + `TODO.md` mandatory, plus `ARCHITECTURE.md`/`ROADMAP.md` if impacted)

## After merge
- [ ] Pull `main`
- [ ] Start next extraction batch outside admin
- [ ] Keep one coherent PR per bounded context

## Ops/deploy follow-up
- [ ] Confirm deploy workflow still green after admin refactor merge
- [ ] Verify assets are still served correctly in prod
- [ ] Check login + checkout + admin smoke test on production URL

## Nice to have
- [ ] Add dedicated `make smoke` target for quick post-deploy checks
- [ ] Add service unit tests for checkout/catalog/admin flows
