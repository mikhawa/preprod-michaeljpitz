# Correction : erreur « Access denied for user 'portfolio' » sur phpMyAdmin

**Date :** 2026-09-07
**Contexte :** connexion locale à http://localhost:8081/ impossible.

## Symptôme

```
mysqli::real_connect(): (HY000/1045): Access denied for user 'portfolio'@'172.19.0.4' (using password: YES)
```

L'application Symfony (Doctrine) ne pouvait pas non plus se connecter.

## Cause

Le volume Docker `preprod-michaeljpitz_db_data` a été initialisé à une époque où
`docker-compose.yml` utilisait `preprod` / `preprod`. MariaDB n'applique
`MYSQL_USER` / `MYSQL_PASSWORD` / `MYSQL_DATABASE` **qu'à la première création**
du répertoire de données. Un commit ultérieur a renommé ces variables en
`portfolio`, mais le volume (et donc l'utilisateur `preprod`, la base `preprod`
avec ses 10 tables et 2 utilisateurs) n'a pas changé.

Résultat : l'utilisateur `portfolio` n'existe pas dans la base.

Vérification :

```
SELECT User, Host FROM mysql.user;   -- -> preprod, root uniquement
SHOW DATABASES;                      -- -> preprod, preprod_test
```

## Décision

Réaligner la configuration sur le volume existant (`preprod`) pour ne perdre
aucune donnée, plutôt que recréer le volume.

## Modifications

| Fichier | Changement |
|---|---|
| `.env` | `DATABASE_URL` : `portfolio:portfolio@.../portfolio` → `preprod:preprod@.../preprod` |
| `.env.test` | idem (le suffixe `_test` de `config/packages/doctrine.yaml` donne `preprod_test`) |
| `docker-compose.yml` | `MYSQL_DATABASE`, `MYSQL_USER`, `MYSQL_PASSWORD`, `PMA_USER`, `PMA_PASSWORD` : `portfolio` → `preprod` |

## Application

```bash
docker compose up -d --force-recreate phpmyadmin php
```

## Vérifications effectuées

- `mariadb -upreprod -ppreprod -e "SELECT 1"` → OK
- phpMyAdmin (http://localhost:8081/) → connexion automatique, panneau de navigation affiché, plus d'« Access denied »
- `php bin/console dbal:run-sql "SELECT COUNT(*) FROM user"` → 2
- `php bin/console doctrine:schema:validate` → mapping OK ; le schéma signale des migrations en attente (sujet distinct, non traité ici).
