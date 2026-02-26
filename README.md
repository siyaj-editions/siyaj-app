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
