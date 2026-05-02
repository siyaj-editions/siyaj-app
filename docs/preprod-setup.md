# Preprod setup

Objectif :
- déployer une stack préprod isolée sur le même VPS
- exposée sur `dev.siyaj-editions.com`
- sans impacter la prod

## Branche de déploiement

La préprod se déploie depuis la branche :

```text
preprod
```

Le workflow utilisé est :

```text
.github/workflows/deploy-preprod.yml
```

## Secrets GitHub à ajouter

Secrets déjà réutilisés :

- `GHCR_USERNAME`
- `GHCR_TOKEN`
- `VPS_HOST`
- `VPS_USER`
- `VPS_SSH_KEY`

Nouveau secret requis :

- `VPS_PREPROD_APP_PATH`

Valeur conseillée :

```text
/home/ubuntu/siyaj-preprod
```

## Dossiers à créer sur le VPS

```bash
mkdir -p /home/ubuntu/siyaj-preprod
mkdir -p /home/ubuntu/siyaj-preprod/shared/uploads/books
mkdir -p /home/ubuntu/siyaj-preprod/shared/uploads/manuscripts
```

## Fichier d'environnement

Créer sur le VPS :

```text
/home/ubuntu/siyaj-preprod/.env.preprod
```

Tu peux partir de :

```text
.env.preprod.example
```

Points importants :
- utiliser une base PostgreSQL dédiée préprod
- utiliser des clés Stripe de test
- mettre `DEFAULT_URI=https://dev.siyaj-editions.com`
- utiliser `HOST_HTTP_PORT=8003`

## Cloudflare Tunnel

Ajouter une route :

- host : `dev.siyaj-editions.com`
- service : `http://127.0.0.1:8003`

## Déploiement

Le workflow préprod se lance :
- à chaque push sur `preprod`
- ou manuellement via `workflow_dispatch`

## Vérifications utiles

Sur le VPS :

```bash
cd /home/ubuntu/siyaj-preprod
docker compose --env-file .env.preprod -f compose.preprod.yml ps
docker compose --env-file .env.preprod -f compose.preprod.yml logs -n 100 app
docker compose --env-file .env.preprod -f compose.preprod.yml logs -n 100 nginx
```

Tests finaux :
- ouvrir `https://dev.siyaj-editions.com`
- vérifier le checkout avec les clés Stripe test
- vérifier les uploads en préprod
