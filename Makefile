.PHONY: help install start stop db-create db-migrate db-reset fixtures cache-clear test stripe-listen

##
## SIYAJ - Commandes Make
##

help: ## Affiche cette aide
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-20s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

##
## Installation et démarrage
##

install: ## Installation complète du projet
	@echo "📦 Installation des dépendances..."
	composer install
	@echo "✅ Dépendances installées"

start: ## Démarre Docker et le serveur Symfony
	@echo "🐳 Démarrage de Docker..."
	docker compose up -d
	@echo "⏳ Attente du démarrage de PostgreSQL (5s)..."
	sleep 5
	@echo "✅ Docker démarré"

stop: ## Arrête Docker
	@echo "🛑 Arrêt de Docker..."
	docker compose down
	@echo "✅ Docker arrêté"

serve: ## Lance le serveur Symfony
	@echo "🚀 Démarrage du serveur Symfony..."
	@echo "📍 Application disponible sur http://localhost:8000"
	symfony serve

##
## Base de données
##

db-create: ## Crée la base de données
	@echo "🗄️  Création de la base de données..."
	php bin/console doctrine:database:create --if-not-exists
	@echo "✅ Base de données créée"

db-migrate: ## Exécute les migrations
	@echo "⚡ Exécution des migrations..."
	php bin/console doctrine:migrations:migrate --no-interaction
	@echo "✅ Migrations exécutées"

db-reset: ## Reset complet de la base de données
	@echo "⚠️  Reset de la base de données..."
	php bin/console doctrine:database:drop --force --if-exists
	php bin/console doctrine:database:create
	php bin/console doctrine:migrations:migrate --no-interaction
	@echo "✅ Base de données réinitialisée"

fixtures: ## Charge les fixtures (données de test)
	@echo "📊 Chargement des fixtures..."
	php bin/console doctrine:fixtures:load --no-interaction
	@echo "✅ Fixtures chargées"
	@echo "👤 Admin: admin@siyaj.com / admin123"
	@echo "👤 Client: client@test.com / client123"

##
## Développement
##

cache-clear: ## Vide le cache
	@echo "🧹 Nettoyage du cache..."
	php bin/console cache:clear
	@echo "✅ Cache vidé"

test: ## Lance les tests
	@echo "🧪 Exécution des tests..."
	php bin/phpunit
	@echo "✅ Tests terminés"

stripe-listen: ## Lance Stripe CLI et forwarde les webhooks vers l'app
	@echo "🔔 Stripe CLI -> http://localhost:8000/stripe/webhook"
	stripe listen --forward-to http://localhost:8000/stripe/webhook

##
## Setup complet (First install)
##

setup: install start db-create db-migrate fixtures ## Installation complète du projet (first time)
	@echo ""
	@echo "✅ Installation terminée avec succès!"
	@echo ""
	@echo "📍 Pour démarrer le serveur:"
	@echo "   make serve"
	@echo ""
	@echo "📍 Ou avec PHP:"
	@echo "   php -S localhost:8000 -t public/"
	@echo ""
	@echo "👤 Comptes de test:"
	@echo "   Admin: admin@siyaj.com / admin123"
	@echo "   Client: client@test.com / client123"
	@echo ""

##
## Reset et rechargement
##

reload: db-reset fixtures ## Reset la BDD et recharge les fixtures
	@echo "✅ Base de données rechargée avec succès!"
