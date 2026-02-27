# SIYAG - E-commerce Maison d'Edition

Application e-commerce Symfony 8 pour la vente de livres (catalogue, panier, paiement Stripe, espace client et admin).

## Prerequis

- PHP 8.4+
- Composer
- Docker Desktop
- Make

## Installation rapide

```bash
make setup
```

Cette commande:
- installe les dependances
- demarre PostgreSQL via Docker
- cree la base
- execute les migrations
- charge les fixtures

Lancer l'application:

```bash
make serve
```

Application: `http://127.0.0.1:8000`

## Stack

- Symfony 8 / PHP 8.4
- Doctrine ORM + PostgreSQL 16
- Twig + Tailwind CSS v4
- Stripe Checkout + webhook

## Paiement Stripe

Creer `.env.local`:

```env
STRIPE_PUBLIC_KEY=pk_test_xxx
STRIPE_SECRET_KEY=sk_test_xxx
STRIPE_WEBHOOK_SECRET=whsec_xxx
```

Webhook local:

```bash
stripe listen --forward-to localhost:8000/stripe/webhook
```

Carte de test:
- `4242 4242 4242 4242`
- date future
- CVC `123`

## Fonctionnalites

### Front-office
- Accueil + catalogue filtre (recherche/auteur/format)
- Fiche livre
- Panier
- Checkout avec:
  - adresse de livraison
  - adresse de facturation
  - option "adresse de facturation identique"
- Authentification / inscription

### Espace client
- Profil utilisateur
- Champ `numero` (telephone)
- Historique des commandes
- Detail commande avec adresses livraison/facturation

### Admin
- Dashboard
- CRUD livres
- CRUD auteurs
- Gestion des commandes

## Modele de donnees (nouveau)

- `User.numero` ajoute
- Nouvelle entite `Address`
- `Order.shippingAddress`
- `Order.billingAddress`
- `Order.billingSameAsShipping`

## Commandes utiles

```bash
make help
make start
make stop
make db-migrate
make fixtures
make test
php bin/console tailwind:build
php bin/console lint:twig templates
```

## Comptes fixtures

- Admin: `admin@siyag.com` / `admin123`
- Client: `client@test.com` / `client123`

## Migrations

Apres update:

```bash
php bin/console doctrine:migrations:migrate
```

## Production checklist

- Configurer les cles Stripe prod
- Configurer le webhook prod
- HTTPS
- `composer install --no-dev --optimize-autoloader`
- `php bin/console cache:clear --env=prod`

## Deploiement VPS (GitHub Actions)

Le projet inclut:
- `Dockerfile` (image Symfony prod)
- `compose.vps.yml` (app + nginx + postgres)
- `.github/workflows/deploy-vps.yml` (build + push GHCR + deploy VPS)

### 1) Preparer le VPS (une fois)

Installer Docker + plugin Compose, puis creer un dossier de deploiement, par exemple:

```bash
mkdir -p /home/ubuntu/siyag
```

Copier `.env.prod.example` vers `/home/ubuntu/siyag/.env.prod`, puis adapter les valeurs:

```env
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=change-me
DATABASE_URL=postgresql://app:change-me@database:5432/app?serverVersion=16&charset=utf8

POSTGRES_DB=app
POSTGRES_USER=app
POSTGRES_PASSWORD=change-me

STRIPE_PUBLIC_KEY=pk_live_xxx
STRIPE_SECRET_KEY=sk_live_xxx
STRIPE_WEBHOOK_SECRET=whsec_xxx
```

### 2) Secrets GitHub a configurer

Dans le repository GitHub, ajouter:
- `VPS_HOST` (IP ou domaine du VPS)
- `VPS_USER` (ex: `ubuntu`)
- `VPS_SSH_KEY` (cle privee SSH)
- `VPS_APP_PATH` (ex: `/home/ubuntu/siyag`)
- `GHCR_USERNAME` (ton username GitHub, ex: `LudwigELATRE`)
- `GHCR_TOKEN` (token GitHub avec droit `read:packages` pour pull l'image)

### 3) Lancer le deploiement

Le workflow se lance:
- automatiquement a chaque push sur `main`
- ou manuellement via `workflow_dispatch`

Pipeline:
1. build image Docker
2. push image sur GHCR
3. copie `compose.vps.yml` + conf nginx sur le VPS
4. migration Doctrine
5. clear cache prod
6. restart `app` + `nginx`
