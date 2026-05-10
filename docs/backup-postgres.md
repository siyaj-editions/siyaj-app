# Backup PostgreSQL

## Objectif
Mettre en place un backup simple et fiable de la base PostgreSQL de production, hors conteneur et hors dossier de deploiement.

## Emplacements retenus
- Script actif sur le VPS : `/home/ubuntu/scripts/siyaj-db-backup.sh`
- Dossier de backups sur le VPS : `/home/ubuntu/backups/siyaj/postgres/`
- Version source du script dans le repo : [`ops/backup/siyaj-db-backup.sh.example`](/Users/ludwigelatre/Desktop/siyag-projet/siyag/ops/backup/siyaj-db-backup.sh.example)

## Fonctionnement
Le script :
- charge les variables PostgreSQL depuis `/home/ubuntu/siyaj/.env.prod`
- execute `pg_dump` dans le conteneur `siyaj-database-1`
- compresse le dump en `.sql.gz`
- conserve les backups localement
- supprime ceux qui ont plus de `RETENTION_DAYS`
- peut optionnellement envoyer le fichier vers un stockage externe via `rclone`

## Variables attendues
Le script lit ces variables depuis le fichier d'environnement :
- `POSTGRES_DB`
- `POSTGRES_USER`
- `POSTGRES_PASSWORD`

Variables optionnelles du script :
- `APP_PATH`
- `ENV_FILE`
- `DB_CONTAINER`
- `BACKUP_DIR`
- `RETENTION_DAYS`
- `RCLONE_REMOTE`

## Lancer un backup manuellement
```bash
/home/ubuntu/scripts/siyaj-db-backup.sh
```

## Exemple de cron
Tous les jours a `02:00` :
```cron
0 2 * * * /home/ubuntu/scripts/siyaj-db-backup.sh >> /home/ubuntu/backups/siyaj/backup.log 2>&1
```

## Restaurer un dump
Exemple de restauration vers la base de production :
```bash
gunzip -c /home/ubuntu/backups/siyaj/postgres/app_YYYY-MM-DD_HH-MM-SS.sql.gz \
  | docker exec -i \
      -e PGPASSWORD="$POSTGRES_PASSWORD" \
      siyaj-database-1 \
      psql -U "$POSTGRES_USER" -d "$POSTGRES_DB"
```

## Bonnes pratiques
- Garder une retention locale courte sur le VPS
- Envoyer les dumps hors du VPS via `rclone` ou stockage objet
- Tester regulierement une restauration sur un environnement de test
- Ne pas compter uniquement sur le volume Docker comme strategie de sauvegarde
