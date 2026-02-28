# Project Context

## Project
- Name: SIYAJ Editions
- Type: Symfony 8 e-commerce for books
- Stack: PHP 8.4, Symfony 8, Twig, Tailwind v4, Doctrine ORM, PostgreSQL 16, Docker Compose
- Payments: Stripe Checkout + webhook

## Environments
- Local app URL: `http://127.0.0.1:8000`
- VPS app URL: `https://siyaj-editions.elydris.fr`
- Reverse proxy/tunnel: Cloudflare Tunnel -> `localhost:8002` on VPS

## Deployment model
- CI/CD via GitHub Actions (`.github/workflows/deploy-vps.yml`)
- Docker image built/pushed to GHCR (tag SHA + `latest`)
- VPS deploy steps:
  1. pull image
  2. start DB
  3. run migrations
  4. clear prod cache
  5. start app
  6. sync `/opt/public` -> `/var/www/html/public`
  7. run `importmap:install`, `tailwind:build`, `asset-map:compile`
  8. start nginx

## Recent production issues fixed
- `DATABASE_URL` parsing problems from special chars in password
- PostgreSQL auth mismatch due to reused data volume
- Port `8002` conflict with old systemd PHP server (`psychology-php.service`)
- Nginx upstream boot/order issues
- Missing compiled assets in containerized deploy
- Missing importmap vendor assets (`@hotwired/stimulus`, `@hotwired/turbo`)
- Nginx serving wrong root path in VPS setup

## Current branch status (work in progress)
- Branch: `refactor/account-service-step4`
- Goal: continue service-oriented refactor (thin controllers) outside admin
- Not yet pushed in this state

## Documentation discipline (mandatory)
- After each functional or technical change, update documentation in `docs/` before commit/push.
- Minimum expected update each time:
  - `docs/PROJECT_CONTEXT.md`: current state and decisions taken
  - `docs/TODO.md`: next actions and validation checklist
- If architecture or planning changed, also update:
  - `docs/ARCHITECTURE.md`
  - `docs/ROADMAP.md`

## Refactor status
Completed:
- Checkout logic extracted to `CheckoutService` + `CheckoutException`
- Catalog filtering/pagination logic extracted to `CatalogService`
- Admin area migrated to dedicated services:
  - `AdminDashboardService`
  - `AdminAuthorService`
  - `AdminBookService`
  - `AdminOrderService`
  - `AdminManuscriptService`
  - `AdminNewsletterService`

In progress (local, ready for review):
- Account area migration started:
  - `AccountService` created
  - `AccountController` now delegates order/address/profile business logic to service
- Controller still handles HTTP/form/CSRF/flash concerns
- Cart mutation flow migration started:
  - `CartActionService` + `CartActionResult` created
  - `CartController` delegates add/increment business checks to service

## Known functional conventions
- Keep CSRF validation in controllers
- Keep flash messaging in controllers
- Keep business/data orchestration in services
- Run `php -l` + `php bin/console lint:container --no-debug` after each refactor step
