# Roadmap

## Guiding principle
- Move business logic from controllers to services incrementally.
- Keep each step small and mergeable.
- Validate at each step to avoid regression in prod deploy.

## Phase 1 (done)
- Stabilize VPS deployment pipeline
- Fix assets generation/distribution issues
- Fix Cloudflare/Nginx/Compose runtime issues
- Restore storefront availability and checkout flow

## Phase 2 (in progress)
- Refactor architecture toward thin controllers

### Step A (done)
- Checkout -> `CheckoutService`

### Step B (done)
- Catalog -> `CatalogService`

### Step C (current local state)
- Admin -> dedicated services for each controller
- Pending: review + push + merge

## Phase 3 (next)
- Continue same extraction pattern outside admin:
  - Account/profile actions
  - Cart mutation flows
  - Newsletter/manuscript submit flows if needed
- Add focused unit tests for service-level logic
- Add integration tests for critical user journeys

## Phase 4
- Hardening and DX:
  - Better deploy diagnostics (explicit checks after deploy)
  - Optional smoke test endpoint
  - Better error logging strategy in prod
  - CI checks for lint/static analysis

## Definition of done for each refactor PR
- Behavior unchanged
- Controller complexity reduced
- Service has clear single responsibility
- Syntax + container lint pass
- Manual smoke checks for impacted routes
- Docs updated (`PROJECT_CONTEXT.md` + `TODO.md`, and `ARCHITECTURE.md`/`ROADMAP.md` if impacted)
