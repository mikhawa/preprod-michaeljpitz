# 004 — GitHub Actions : tests automatiques sur Pull Request

**Date** : 2026-03-14 à 21h31
**Moteur IA** : Claude Sonnet 4.6
**Branche** : `dev/mailpit-local`

---

## Contexte

Mise en place d'un pipeline CI (Continuous Integration) via GitHub Actions pour exécuter automatiquement les tests à chaque Pull Request vers `main` ou une branche `dev/**`.

## Fichiers créés / modifiés

### `.github/workflows/tests.yml` (créé)

Workflow déclenché sur `pull_request` vers `main` et `dev/**`.

**Étapes du pipeline :**

1. Checkout du code
2. Installation de PHP 8.3 (extensions : `ctype`, `iconv`, `intl`, `pdo_mysql`)
3. Cache du dossier `vendor/` (clé sur `composer.lock`)
4. `composer install --no-interaction --prefer-dist`
5. `composer validate --strict`
6. `php bin/console lint:twig templates/`
7. `php bin/console lint:yaml config/`
8. `php bin/console lint:container`
9. `vendor/bin/phpstan analyse src --level=8`
10. Création BDD de test + migrations Doctrine
11. `php bin/phpunit --no-coverage`

**Service MariaDB 10.11** lancé comme service GitHub Actions avec healthcheck.

```yaml
services:
  mariadb:
    image: mariadb:10.11
    env:
      MYSQL_DATABASE: preprod_test
      MYSQL_USER: preprod
      MYSQL_PASSWORD: preprod
```

### `.env.test` (modifié)

Mise à jour des identifiants de base de données (anciens : `portfolio`) et passage en `127.0.0.1` pour GitHub Actions (pas de résolution DNS Docker en CI) :

```dotenv
# Avant
DATABASE_URL="mysql://portfolio:portfolio@mariadb:3306/portfolio?..."

# Après
DATABASE_URL="mysql://preprod:preprod@127.0.0.1:3306/preprod_test?..."
```

## Déclencheurs

| Événement | Branches cibles |
|-----------|----------------|
| `pull_request` | `main` |
| `pull_request` | `dev/**` |

## Points de vigilance

- Le `DATABASE_URL` dans `.env.test` utilise `127.0.0.1` (CI) et non `mariadb` (Docker local). En local, surcharger via `.env.test.local` si nécessaire.
- PHPStan niveau 8 : toute erreur bloque le pipeline.
- `composer validate --strict` : le `composer.json` doit être valide avant merge.
- `--no-coverage` : la couverture de code n'est pas calculée en CI (performances).
