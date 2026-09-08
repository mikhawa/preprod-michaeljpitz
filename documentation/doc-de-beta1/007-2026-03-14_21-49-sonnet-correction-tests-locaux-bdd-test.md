# 007 — Correction des tests locaux : base `preprod_test` manquante

**Date** : 2026-03-14 à 21h49
**Moteur IA** : Claude Sonnet 4.6
**Branche** : `dev/mailpit-local`

---

## Problème

```
Access denied for user 'preprod'@'%' to database 'preprod_test'
```

92 tests sur 142 échouaient. L'utilisateur `preprod` n'avait pas accès à la base `preprod_test` car Docker ne crée automatiquement que la base définie dans `MYSQL_DATABASE` (`preprod`). Or Doctrine ajoute le suffixe `_test` en environnement test → base cible : `preprod_test`.

## Correction immédiate (manuelle)

```sql
CREATE DATABASE IF NOT EXISTS preprod_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON preprod_test.* TO 'preprod'@'%';
FLUSH PRIVILEGES;
```

Puis migrations appliquées :

```bash
docker compose exec php php bin/console doctrine:migrations:migrate --env=test --no-interaction
```

## Correction pérenne : script d'init MariaDB

Création de `docker/mariadb/init.sql` — exécuté automatiquement par MariaDB au premier démarrage du conteneur (via `docker-entrypoint-initdb.d/`) :

```sql
CREATE DATABASE IF NOT EXISTS preprod_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON preprod_test.* TO 'preprod'@'%';
FLUSH PRIVILEGES;
```

Montage dans `docker-compose.yml` :

```yaml
volumes:
  - db_data:/var/lib/mysql
  - ./docker/mariadb/init.sql:/docker-entrypoint-initdb.d/init.sql:ro
```

> **Note** : le script `init.sql` n'est exécuté que si le volume est vierge (premier démarrage). Sur un volume existant, MariaDB ne le rejoue pas.

## Résultat

```
OK (142 tests, 272 assertions)
```

## Rappel : après `docker compose down -v`

Si le volume est supprimé et recréé, les migrations doivent être rejouées :

```bash
docker compose exec php php bin/console doctrine:migrations:migrate --env=test --no-interaction
```
