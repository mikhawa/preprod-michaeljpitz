---
description: Validation complète du projet Symfony (composer, lint, phpstan, cs-fixer, audit)
allowed-tools: Bash
---

# Validation complète du projet

Exécute les commandes suivantes **séquentiellement** via `docker compose exec php` (depuis `/home/mikhawa/cv-mikhawa`). Arrête-toi dès qu'une commande échoue (code de sortie non nul) et rapporte l'erreur clairement.

1. `docker compose exec php composer validate --strict`
2. `docker compose exec php php bin/console lint:twig templates/`
3. `docker compose exec php php bin/console lint:yaml config/`
4. `docker compose exec php php bin/console lint:container`
5. `docker compose exec php vendor/bin/phpstan analyse src --level=8`
6. `docker compose exec php ./vendor/bin/php-cs-fixer fix --dry-run --diff`
7. `docker compose exec php composer audit`

Pour chaque étape réussie, affiche une ligne de confirmation.
En cas d'échec, affiche le détail de l'erreur et indique quelle étape a échoué.
À la fin, donne un résumé global.
