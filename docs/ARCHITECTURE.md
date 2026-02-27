# Architecture

## Runtime architecture

### Local
- Symfony app runs locally (`symfony serve`/PHP runtime)
- PostgreSQL via Docker Compose (`compose.yaml`)

### VPS
- `app` container: Symfony/PHP-FPM image from GHCR
- `database` container: PostgreSQL 16
- `nginx` container: serves static/public and forwards PHP to app
- `cloudflared` container (separate stack): ingress routing from public domain to `localhost:8002`

## Layering target

### Controller layer
Responsibilities:
- HTTP I/O (Request/Response)
- Security/authorization attributes
- Form handling and CSRF checks
- Flash messages and redirects

Must avoid:
- Query orchestration and branching business rules
- Data mutation rules duplicated across controllers

### Service layer
Responsibilities:
- Use-case/business orchestration
- Repository calls and data aggregation
- State transitions for domain actions

Current services:
- `CartService`
- `StripeService`
- `CheckoutService`
- `CatalogService`
- `AdminDashboardService`
- `AdminAuthorService` (new)
- `AdminBookService` (new)
- `AdminOrderService` (new)
- `AdminManuscriptService` (new)
- `AdminNewsletterService` (new)

### Repository layer
- Persistence queries only
- Reusable finder/count methods

## Deployment-sensitive files
- `Dockerfile`
- `compose.vps.yml`
- `deploy/nginx/default.conf`
- `.github/workflows/deploy-vps.yml`

## Current risks / watch points
- Asset pipeline consistency (`importmap`, `tailwind`, `asset-map:compile`)
- Container filesystem permissions when generating assets at runtime
- Nginx root/static path alignment with shared `public_data` volume
- Cloudflare tunnel config drift vs deployed services/ports

## Technical debt candidates
- Add service-level tests for extracted admin services
- Normalize status transition logic in a dedicated domain policy (optional)
- Add structured deploy smoke test command in CI

## Documentation rule
- Architecture notes must be updated whenever a refactor changes responsibilities, boundaries, or runtime behavior.
- No refactor PR is considered complete if architecture docs are stale.
