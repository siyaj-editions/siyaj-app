# SIYAG - E-commerce Maison d'Édition

Site e-commerce développé avec **Symfony 8.0** pour la vente de livres en ligne.

## 🚀 Installation Rapide

### Prérequis
- PHP 8.4+
- Composer
- Docker Desktop

### Installation complète (première fois)
```bash
make setup
```

Cette commande effectue :
- Installation des dépendances
- Démarrage de Docker (PostgreSQL)
- Création de la base de données
- Exécution des migrations
- Chargement des données de test

### Démarrer le serveur
```bash
make serve
# ou
php -S localhost:8000 -t public/
```

**L'application est accessible sur** : http://localhost:8000

## 📝 Commandes Make Disponibles

```bash
make help              # Affiche toutes les commandes disponibles
make install          # Installe les dépendances
make start            # Démarre Docker
make stop             # Arrête Docker
make serve            # Lance le serveur Symfony
make db-create        # Crée la base de données
make db-migrate       # Exécute les migrations
make db-reset         # Reset complet de la BDD
make fixtures         # Charge les données de test
make cache-clear      # Vide le cache
make test             # Lance les tests
make setup            # Installation complète (first install)
make reload           # Reset BDD + recharge fixtures
```

## 👥 Comptes de Test

Après `make setup` ou `make fixtures`, deux comptes sont disponibles :

**Administrateur**
- Email : `admin@siyag.com`
- Mot de passe : `admin123`
- Accès : http://localhost:8000/admin

**Client**
- Email : `client@test.com`
- Mot de passe : `client123`

## 📊 Données de Test

- **15 livres** (physiques et numériques)
- **8 auteurs** (classiques français)
- **2 utilisateurs** (admin + client)

## 🛠️ Stack Technique

- **Backend** : Symfony 8.0 + PHP 8.4
- **Base de données** : PostgreSQL 16
- **ORM** : Doctrine
- **Frontend** : Twig + Bootstrap 5 + Stimulus
- **Paiement** : Stripe Checkout

## ⚙️ Configuration Stripe

Pour tester les paiements, créer un fichier `.env.local` :

```env
STRIPE_PUBLIC_KEY=pk_test_votre_cle
STRIPE_SECRET_KEY=sk_test_votre_cle
STRIPE_WEBHOOK_SECRET=whsec_votre_secret
```

### Webhook en local
```bash
stripe listen --forward-to localhost:8000/checkout/webhook
```

### Carte de test Stripe
- Numéro : `4242 4242 4242 4242`
- Date : n'importe quelle date future
- CVC : `123`

## 📖 Fonctionnalités

### Public
- Catalogue de livres avec filtres (auteur, format, recherche)
- Fiche détaillée de livre
- Panier dynamique
- Inscription / Connexion

### Espace Client
- Dashboard personnel
- Gestion du profil
- Historique des commandes

### Paiement
- Intégration Stripe Checkout
- Validation du stock
- Confirmation par webhook
- Gestion des statuts de commande

### Back-Office Admin
- CRUD Livres
- CRUD Auteurs
- Gestion des commandes
- Dashboard avec statistiques

## 📂 Structure du Projet

```
src/
├── Controller/        # Contrôleurs (Public, Client, Admin, Checkout)
├── Entity/           # Entités Doctrine (User, Book, Author, Order, OrderItem)
├── Enum/             # Énumérations (BookFormat, OrderStatus)
├── Form/             # Formulaires Symfony
├── Repository/       # Repositories Doctrine
├── Service/          # Services métier (CartService, StripeService)
└── DataFixtures/     # Fixtures de données

templates/            # Templates Twig
config/               # Configuration Symfony
migrations/           # Migrations Doctrine
```

## 🔒 Sécurité

- Authentification par formulaire (form_login)
- Protection CSRF activée
- Hashage des mots de passe (bcrypt auto)
- Access Control :
  - `/admin` → ROLE_ADMIN
  - `/mon-compte` → ROLE_USER
  - `/checkout` → ROLE_USER

## 🧪 Tests

```bash
make test
```

## 📚 Documentation

Voir [CLAUDE.md](CLAUDE.md) pour la documentation complète du projet destinée à Claude Code.

## 🚧 Avant la Production

- [ ] Configurer les clés Stripe de production
- [ ] Configurer le webhook Stripe en production
- [ ] Générer un `APP_SECRET` sécurisé
- [ ] Activer HTTPS
- [ ] Optimiser l'autoloader : `composer install --no-dev --optimize-autoloader`
- [ ] Cache de production : `php bin/console cache:clear --env=prod`

## 📞 Support

Pour toute question concernant le développement, consultez [CLAUDE.md](CLAUDE.md).

## 📄 Licence

Propriétaire - SIYAG

---

**Développé avec Symfony 8.0** 🎵
