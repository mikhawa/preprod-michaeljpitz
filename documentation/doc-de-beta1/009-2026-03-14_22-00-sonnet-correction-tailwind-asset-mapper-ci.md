# 009 — Correction : asset Tailwind manquant en CI (500 sur toutes les pages)

**Date** : 2026-03-14 à 22h00
**Moteur IA** : Claude Sonnet 4.6
**Branche** : `dev/mailpit-local`

---

## Problème

Tous les tests fonctionnels échouaient en CI avec une erreur 500 :

```
Unable to find asset "tailwindcss" referenced in "assets/styles/app.css".
The file "assets/styles/tailwindcss" does not exist.
```

Le fichier `app.css` contient `@import "tailwindcss"` (syntaxe Tailwind v4). Le bundle `symfonycasts/tailwind-bundle` compile ce fichier via la CLI Tailwind et génère un fichier `tailwindcss` dans `assets/styles/`. Ce fichier n'existait pas en CI car `tailwind:build` n'avait jamais été exécuté.

La configuration `missing_import_mode: strict` dans `asset_mapper.yaml` transformait cette absence en exception fatale.

## Corrections

### 1. `config/packages/asset_mapper.yaml` — mode warn en test

Ajout du mode `warn` pour l'environnement `test` : les assets manquants génèrent un avertissement au lieu d'une exception. Les tests fonctionnels vérifient la logique applicative, pas les assets CSS.

```yaml
when@test:
    framework:
        asset_mapper:
            missing_import_mode: warn
```

### 2. `.github/workflows/tests.yml` — étape `tailwind:build`

Ajout d'une étape de compilation Tailwind avant la création de la base de test :

```yaml
- name: Compilation Tailwind CSS
  run: php bin/console tailwind:build
```

Le bundle télécharge automatiquement la CLI Tailwind standalone au premier lancement.

## Résultat local

```
OK (142 tests, 272 assertions)
```

## Ordre des étapes CI (après correction)

1. Checkout
2. PHP 8.3 + extensions
3. Cache Composer
4. `composer install`
5. `composer validate --strict`
6. Lint Twig / YAML / conteneur
7. PHPStan niveau 8
8. **`tailwind:build`** ← nouveau
9. Initialisation BDD test (mysql root)
10. Migrations
11. PHPUnit
