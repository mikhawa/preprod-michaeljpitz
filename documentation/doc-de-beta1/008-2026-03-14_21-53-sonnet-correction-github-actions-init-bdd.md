# 008 — Correction GitHub Actions : initialisation BDD alignée avec le local

**Date** : 2026-03-14 à 21h53
**Moteur IA** : Claude Sonnet 4.6
**Branche** : `dev/mailpit-local`

---

## Problème

`doctrine:database:create` utilise les credentials de l'utilisateur applicatif (`preprod`) pour exécuter un `CREATE DATABASE`. Cet utilisateur n'a pas le droit `CREATE` au niveau global — uniquement sur sa propre base. La commande échoue même avec `--if-not-exists`.

## Solution

Remplacer `doctrine:database:create` par une commande `mysql` en root, identique à ce que fait `docker/mariadb/init.sql` en local.

## Modifications dans `.github/workflows/tests.yml`

### Service MariaDB

`MYSQL_DATABASE` corrigé : `preprod_test` → `preprod` (cohérence avec le local et le `dbname_suffix`).

```yaml
env:
  MYSQL_DATABASE: preprod   # était: preprod_test
  MYSQL_USER: preprod
  MYSQL_PASSWORD: preprod
```

### Étape d'initialisation (remplace `doctrine:database:create`)

```yaml
- name: Initialisation de la base de données de test
  run: |
    mysql -uroot -proot -h127.0.0.1 -e "
      CREATE DATABASE IF NOT EXISTS preprod_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
      GRANT ALL PRIVILEGES ON preprod_test.* TO 'preprod'@'%';
      FLUSH PRIVILEGES;
    "
```

### `DATABASE_URL` explicite sur toutes les étapes Symfony/PHP

Ajouté sur les étapes `migration` et `phpunit` pour éviter toute ambiguïté :

```yaml
env:
  DATABASE_URL: mysql://preprod:preprod@127.0.0.1:3306/preprod?serverVersion=10.11.0-MariaDB&charset=utf8mb4
```

## Cohérence local / CI

| Élément | Local (Docker) | CI (GitHub Actions) |
|---------|---------------|---------------------|
| Création BDD test | `docker/mariadb/init.sql` (root) | `mysql -uroot` dans le workflow |
| Base dans `DATABASE_URL` | `preprod` | `preprod` |
| Base réelle (après suffixe) | `preprod_test` | `preprod_test` |
| Hôte MariaDB | `mariadb` (réseau Docker) | `127.0.0.1` (service GitHub) |
