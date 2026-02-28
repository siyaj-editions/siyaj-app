# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

---

# PROJET : SIYAJ
**Type** : Site e-commerce – Maison d'édition
**Stack** : Symfony 8.0 + Doctrine ORM + PostgreSQL + Twig + Stimulus + Turbo + Stripe

---

## ARCHITECTURE TECHNIQUE

### Stack actuelle
- **PHP** : 8.4+
- **Framework** : Symfony 8.0
- **ORM** : Doctrine ORM 3.6 + Doctrine Migrations
- **Base de données** : PostgreSQL 16 (Docker)
- **Templates** : Twig
- **Frontend** : Asset Mapper + Stimulus + Hotwired Turbo
- **Tests** : PHPUnit 13
- **Paiement** : Stripe (à intégrer)

### Structure des répertoires
```
src/
├── Controller/     # Contrôleurs Symfony
├── Entity/         # Entités Doctrine
├── Repository/     # Repositories Doctrine
└── Kernel.php

config/
├── packages/       # Configuration des bundles
├── routes/         # Configuration des routes
└── services.yaml   # Container de services

assets/
├── app.js          # Point d'entrée JavaScript
├── controllers/    # Stimulus controllers
└── styles/         # CSS

migrations/         # Migrations Doctrine
templates/          # Templates Twig
tests/              # Tests PHPUnit
translations/       # Fichiers de traduction
```

### Configuration de l'environnement
- Les variables d'environnement sont dans `.env` (dev) et `.env.local` (non commité)
- La base de données PostgreSQL tourne via Docker Compose
- Le messenger utilise Doctrine comme transport (`MESSENGER_TRANSPORT_DSN=doctrine://default`)

---

## COMMANDES DE DÉVELOPPEMENT

### Installation et démarrage
```bash
# Installation des dépendances PHP
composer install

# Démarrer la base de données PostgreSQL
docker compose up -d

# Créer la base de données
php bin/console doctrine:database:create

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Démarrer le serveur Symfony
symfony serve
# ou
php -S localhost:8000 -t public/
```

### Base de données
```bash
# Créer une nouvelle migration
php bin/console make:migration

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Charger les fixtures (si configurées)
php bin/console doctrine:fixtures:load

# Vider le cache Doctrine
php bin/console doctrine:cache:clear-metadata
php bin/console doctrine:cache:clear-query
php bin/console doctrine:cache:clear-result
```

### Génération de code
```bash
# Créer une entité
php bin/console make:entity

# Créer un contrôleur
php bin/console make:controller

# Créer un formulaire
php bin/console make:form

# Créer un utilisateur
php bin/console make:user

# Créer l'authentification
php bin/console make:auth

# Créer un CRUD complet
php bin/console make:crud
```

### Tests
```bash
# Exécuter tous les tests
php bin/phpunit

# Exécuter un fichier de test spécifique
php bin/phpunit tests/Controller/BookControllerTest.php

# Exécuter un test spécifique
php bin/phpunit --filter testBookList

# Avec couverture de code
php bin/phpunit --coverage-html var/coverage
```

### Débogage et outils
```bash
# Lister toutes les routes
php bin/console debug:router

# Voir les détails d'une route
php bin/console debug:router book_show

# Lister tous les services
php bin/console debug:container

# Voir la configuration d'un bundle
php bin/console debug:config doctrine

# Vider le cache
php bin/console cache:clear
```

### Messenger (tâches asynchrones)
```bash
# Consommer les messages
php bin/console messenger:consume async

# Voir les messages en échec
php bin/console messenger:failed:show

# Réessayer les messages en échec
php bin/console messenger:failed:retry
```

---

## OBJECTIF DU PROJET

Développer un site e-commerce pour une société d'édition permettant :
- Présentation du catalogue de livres
- Création et gestion de comptes utilisateurs
- Gestion d'un panier dynamique (+ / − / supprimer)
- Paiement sécurisé via Stripe Checkout
- Espace client avec accès aux commandes
- Back-office administrateur

Le système doit être sécurisé, SEO-friendly et évolutif.

---

## FONCTIONNALITÉS PRINCIPALES

### 2.1 Catalogue public

**Pages publiques :**
- `/` Accueil
- `/catalogue` Liste des livres (pagination)
- `/livres/{slug}` Fiche livre
- `/auteurs/{slug}` (optionnel)

**Fiche livre :**
- Titre, Auteur(s), Résumé, Couverture
- ISBN, Prix, Format (Physique / Digital)
- Stock (si physique)
- Bouton "Ajouter au panier"

**Filtres catalogue :**
- Par auteur, collection / genre, prix
- Recherche texte (titre / ISBN)

### 2.2 Comptes utilisateurs

- Inscription / Connexion / Déconnexion
- Reset mot de passe
- Vérification email (recommandé)

### 2.3 Espace Client (OBLIGATOIRE)

**Route principale :** `/mon-compte`

**Dashboard :**
- Voir résumé compte
- Voir dernières commandes

**Profil :**
- Modifier prénom / nom / email / mot de passe

**Adresses (si vente physique) :**
- Ajouter / modifier / supprimer adresse
- Définir adresse par défaut

### 2.4 Commandes (OBLIGATOIRE)

**Liste commandes :** `/mon-compte/commandes`
- Numéro commande, Date, Statut, Total
- Statuts : PENDING / PAID / CANCELED / REFUNDED

**Détail commande :** `/mon-compte/commandes/{id}`
- Livres achetés (titre, quantité, prix snapshot)
- Total, informations paiement, adresse

**Règles de sécurité :**
- Un utilisateur ne peut voir que SES commandes
- Vérification côté serveur obligatoire

### 2.5 Produits numériques (si applicable)

- Téléchargement disponible uniquement si commande = PAID
- Route sécurisée avec vérification propriétaire

---

## PANIER (OBLIGATOIRE)

Le panier doit fonctionner en mode invité (session) et se fusionner au login.

**Vue panier :** `/panier`
- Image couverture, Titre, Prix unitaire, Quantité, Total ligne

**Actions obligatoires :**
- Bouton "+" → incrémenter quantité
- Bouton "-" → décrémenter quantité (si = 1 → supprime)
- Bouton "Supprimer"
- Total général dynamique

**Règles métier :**
- Quantité minimum = 1
- Ne pas dépasser le stock
- Vérification serveur obligatoire
- Panier vide → bouton paiement désactivé

---

## CHECKOUT STRIPE (OBLIGATOIRE)

Utilisation de Stripe Checkout (MVP recommandé).

**Processus :**
1. Utilisateur clique "Passer au paiement"
2. Backend crée Stripe Checkout Session
3. Redirection vers Stripe
4. Retour success / cancel
5. Validation finale via Webhook Stripe

**Sécurité critique :**
- Ne JAMAIS valider commande uniquement via success_url
- Validation officielle via webhook : `checkout.session.completed`

**Webhook :** `/stripe/webhook`
À réception :
- Vérifier signature Stripe
- Marquer commande = PAID
- Décrémenter stock
- Envoyer email confirmation
- Vider panier

---

## MODÈLE DE DONNÉES

### User
```php
- id
- email (unique)
- password (hashed)
- roles (array)
- firstname
- lastname
- created_at
```

### Author
```php
- id
- name
- bio
- photo
```

### Book
```php
- id
- title
- slug (unique)
- isbn
- description
- cover_image
- price_cents (int)
- currency (default: 'EUR')
- format (PHYSICAL / DIGITAL)
- stock (int, nullable)
- is_active (bool)
- published_at
- ManyToMany authors
```

### Order
```php
- id
- user_id (ManyToOne User)
- status (enum: PENDING/PAID/CANCELED/REFUNDED)
- total_cents (int)
- currency
- stripe_session_id
- created_at
- OneToMany orderItems
```

### OrderItem
```php
- id
- order_id (ManyToOne Order)
- book_id (ManyToOne Book)
- title_snapshot (string)
- price_snapshot (int)
- quantity (int)
```

**IMPORTANT :** Les prix doivent être enregistrés en snapshot dans OrderItem.

---

## ROUTES PRINCIPALES

### Public
```
GET  /
GET  /catalogue
GET  /livres/{slug}
```

### Auth
```
GET  /login
POST /login
GET  /register
POST /register
GET  /logout
```

### Panier
```
GET  /panier
POST /panier/add/{id}
POST /panier/increment/{id}
POST /panier/decrement/{id}
POST /panier/remove/{id}
```

### Checkout
```
POST /checkout/create
GET  /checkout/success
GET  /checkout/cancel
POST /stripe/webhook
```

### Espace client
```
GET /mon-compte
GET /mon-compte/commandes
GET /mon-compte/commandes/{id}
```

### Admin
```
GET /admin
```

---

## BACK-OFFICE

Accessible ROLE_ADMIN :
- CRUD Livres
- CRUD Auteurs
- Gestion stock
- Liste commandes
- Changement statut commande
- Export CSV (optionnel)

---

## RÈGLES MÉTIER IMPORTANTES

- Stock décrémenté uniquement après paiement confirmé
- Impossible d'accéder aux commandes d'un autre utilisateur
- Validation serveur obligatoire pour quantités
- Protection CSRF activée par défaut dans Symfony
- Protection contre manipulation du prix côté client
- Les prix sont stockés en centimes (int) pour éviter les problèmes de précision
- Utiliser des snapshots pour les prix dans OrderItem

---

## BONNES PRATIQUES SYMFONY

### Entités Doctrine
- Utiliser les attributs PHP 8 pour le mapping (`#[ORM\Entity]`, `#[ORM\Column]`)
- Définir les getters/setters uniquement si nécessaire
- Utiliser les énumérations PHP 8.1+ pour les statuts

### Contrôleurs
- Utiliser l'attribut `#[Route]` sur les méthodes
- Injecter les services via le constructeur ou les arguments de méthode
- Retourner des objets Response

### Services
- Les services dans `src/` sont autowirés par défaut
- Pas besoin de configuration explicite sauf cas particuliers

### Sécurité
- Utiliser `#[IsGranted('ROLE_USER')]` sur les contrôleurs/méthodes
- Utiliser les Voters pour les permissions complexes
- Vérifier la propriété des ressources (ex: commandes) dans les contrôleurs

### Formulaires
- Créer des FormTypes dédiés dans `src/Form/`
- Activer la validation CSRF (activée par défaut)
- Utiliser les contraintes de validation Symfony

---

## CRITÈRES D'ACCEPTATION

Le système est valide si :
1. Un utilisateur peut créer un compte
2. Il peut ajouter des livres au panier
3. Les boutons + / - fonctionnent correctement
4. Le total se met à jour correctement
5. Le paiement Stripe fonctionne
6. La commande passe en PAID via webhook
7. L'utilisateur voit ses commandes dans son espace
8. Le stock diminue correctement

---

## ÉVOLUTIONS FUTURES (HORS MVP)

- Codes promo
- Multi-langue
- Facture PDF automatique
- Abonnements
- Livraison avancée
- Avis clients
- Wishlist
