# 025 — Chargement des fixtures dans les workflows CI/CD

**Date :** 2026-04-26  
**Modèle :** Claude Sonnet 4.6  
**Branche :** `dev/main`

---

## Contexte

Les workflows de déploiement GitHub Actions (`deploy-dev.yml` et `deploy-preprod.yml`) appliquaient les migrations Doctrine mais ne rechargaient pas les fixtures après chaque déploiement.

---

## Déplacement du bundle dans `require`

`doctrine/doctrine-fixtures-bundle` était dans `require-dev`. Le workflow preprod utilise `composer install --no-dev`, ce qui rendait la commande `doctrine:fixtures:load` indisponible sur ce serveur.

Le bundle a été déplacé dans `require` :

```bash
docker compose exec php composer require doctrine/doctrine-fixtures-bundle
```

**Justification :** Les deux environnements ciblés (dev et preprod) sont des environnements de développement. Le bundle n'est pas déployé en production réelle. Si une vraie production venait à être ajoutée, il faudra conditionner l'étape ou re-basculer le bundle en `require-dev`.

---

## Modifications des workflows

### `.github/workflows/deploy-dev.yml`

```yaml
echo "==> Migrations Doctrine"
php bin/console doctrine:migrations:migrate --no-interaction --env=dev

echo "==> Chargement des fixtures"
php bin/console doctrine:fixtures:load --no-interaction --env=dev

echo "==> Compilation Tailwind CSS"
```

### `.github/workflows/deploy-preprod.yml`

```yaml
echo "==> Migrations Doctrine"
php bin/console doctrine:migrations:migrate --no-interaction --env=prod

echo "==> Chargement des fixtures"
php bin/console doctrine:fixtures:load --no-interaction --env=prod

echo "==> Compilation Tailwind CSS"
```

---

## Comportement

À chaque push sur `dev/main` ou `preprod/main`, la base de données est **purgée puis rechargée** avec les fixtures après les migrations. Toute donnée saisie manuellement sur le serveur est écrasée.

Si ce comportement devient indésirable (données preprod à conserver), remplacer `--no-interaction` par `--append --no-interaction` dans le workflow concerné.
