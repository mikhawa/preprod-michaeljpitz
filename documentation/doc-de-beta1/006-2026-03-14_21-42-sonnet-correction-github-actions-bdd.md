# 006 — Correction GitHub Actions : erreur `preprod_test_test` et warnings Node.js

**Date** : 2026-03-14 à 21h42
**Moteur IA** : Claude Sonnet 4.6
**Branche** : `dev/mailpit-local`

---

## Problèmes rencontrés

### 1. Erreur base de données `preprod_test_test`

```
Could not create database `preprod_test_test`
Access denied for user 'preprod'@'%' to database 'preprod_test_test'
```

**Cause** : `doctrine.yaml` contient dans `when@test` :

```yaml
when@test:
    doctrine:
        dbal:
            dbname_suffix: '_test%env(default::TEST_TOKEN)%'
```

Doctrine ajoute automatiquement le suffixe `_test` au nom de base issu de `DATABASE_URL`. Le `DATABASE_URL` contenait `preprod_test` → résultat final : `preprod_test_test`.

**Correction** : renommer la base dans `DATABASE_URL` de `preprod_test` → `preprod`. Doctrine ajoute `_test` → base finale : `preprod_test` (qui existe bien dans le service MariaDB).

### 2. Warning Node.js 20 deprecated

```
Warning: Node.js 20 actions are deprecated...
Actions will be forced to run with Node.js 24 by default starting June 2nd, 2026.
```

**Correction** : ajout de `FORCE_JAVASCRIPT_ACTIONS_TO_NODE24: true` dans l'environnement du job.

### 3. Warnings `io_uring` MariaDB

```
mariadbd: io_uring_queue_init() failed with EPERM
```

**Cause** : limitation du kernel des runners GitHub Actions (sysctl `kernel.io_uring_disabled`). MariaDB bascule automatiquement sur `libaio`. **Inoffensif**, aucune action requise.

---

## Fichiers modifiés

### `.env.test`

```dotenv
# Avant
DATABASE_URL="mysql://preprod:preprod@127.0.0.1:3306/preprod_test?..."

# Après
DATABASE_URL="mysql://preprod:preprod@127.0.0.1:3306/preprod?..."
```

### `.env.test.local`

```dotenv
# Avant
DATABASE_URL="mysql://preprod:preprod@mariadb:3306/preprod_test?..."

# Après
DATABASE_URL="mysql://preprod:preprod@mariadb:3306/preprod?..."
```

### `.github/workflows/tests.yml`

- `DATABASE_URL` dans l'étape PHPUnit : `preprod_test` → `preprod`
- Ajout au niveau `jobs.tests.env` :

```yaml
env:
  FORCE_JAVASCRIPT_ACTIONS_TO_NODE24: true
```

---

## Logique finale de nommage de la base de test

| Étape | Valeur |
|-------|--------|
| `DATABASE_URL` (base) | `preprod` |
| Suffixe Doctrine (`when@test`) | `_test` |
| Nom réel de la base utilisée | `preprod_test` |
| Base pré-créée dans le service MariaDB CI | `preprod_test` |
