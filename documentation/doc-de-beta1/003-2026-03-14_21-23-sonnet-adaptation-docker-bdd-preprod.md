# 003 — Adaptation Docker : base de données `preprod`

**Date** : 2026-03-14 à 21h23
**Moteur IA** : Claude Sonnet 4.6
**Branche** : `dev/mailpit-local`

---

## Contexte

Le fichier `.env` a été modifié manuellement pour changer les identifiants de la base de données :

```dotenv
# Avant
DATABASE_URL="mysql://portfolio:portfolio@mariadb:3306/portfolio?serverVersion=10.11.0-MariaDB&charset=utf8mb4"

# Après
DATABASE_URL="mysql://preprod:preprod@mariadb:3306/preprod?serverVersion=10.11.0-MariaDB&charset=utf8mb4"
```

## Problème

Le `docker-compose.yml` et phpMyAdmin utilisaient encore les anciens identifiants `portfolio`. Désynchronisation entre `.env` et Docker.

## Actions réalisées

### 1. Mise à jour de `docker-compose.yml`

**Service `mariadb`** — identifiants mis à jour :

```yaml
environment:
  MYSQL_ROOT_PASSWORD: root
  MYSQL_DATABASE: preprod   # était: portfolio
  MYSQL_USER: preprod       # était: portfolio
  MYSQL_PASSWORD: preprod   # était: portfolio
```

**Service `phpmyadmin`** — identifiants mis à jour :

```yaml
environment:
  PMA_HOST: mariadb
  PMA_USER: preprod         # était: portfolio
  PMA_PASSWORD: preprod     # était: portfolio
```

### 2. Suppression du volume MariaDB existant

Le volume `preprod-michaeljpitz_db_data` contenait encore la base `portfolio`. Il a été supprimé pour repartir proprement :

```bash
docker compose down
docker volume rm preprod-michaeljpitz_db_data
```

### 3. Redémarrage des conteneurs

```bash
docker compose up -d
```

Un nouveau volume est recréé avec la base `preprod` et l'utilisateur `preprod`.

## Résultat

Tous les conteneurs sont `Up` :

| Service | Port | Statut |
|---------|------|--------|
| nginx | 8080 | Up |
| php | 9000 | Up |
| mariadb | 3307 | Up |
| phpmyadmin | 8081 | Up |
| mailpit | 1025 / 8025 | Up (healthy) |

## Étape suivante

Rejouer les migrations Doctrine pour recréer le schéma dans la nouvelle base :

```bash
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
```
