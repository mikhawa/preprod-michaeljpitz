---
description: Vérification pré-commit complète avec verdict OK/KO
allowed-tools: Bash
---

# Vérification pré-commit complète

Depuis `/home/mikhawa/cv-mikhawa`, exécute les étapes suivantes **séquentiellement**. Arrête-toi à la première erreur.

### Étape 1 — Validation
1. `docker compose exec php composer validate --strict`
2. `docker compose exec php php bin/console lint:twig templates/`
3. `docker compose exec php php bin/console lint:yaml config/`
4. `docker compose exec php php bin/console lint:container`

### Étape 2 — Analyse statique et style
5. `docker compose exec php vendor/bin/phpstan analyse src --level=8`
6. `docker compose exec php ./vendor/bin/php-cs-fixer fix --dry-run --diff`

### Étape 3 — Tests
7. `docker compose exec php php bin/phpunit`

### Étape 4 — Sécurité
8. `docker compose exec php composer audit`

Pour chaque étape, affiche un indicateur de succès ou d'échec.

À la fin, donne un **verdict clair** :
- **OK** : Toutes les vérifications sont passées, le code est prêt à être committé.
- **KO** : Indique quelle(s) étape(s) ont échoué et ce qu'il faut corriger.
