---
description: Lancer les tests PHPUnit dans Docker
argument-hint: "[fichier_de_test|--filter nom_du_test]"
allowed-tools: Bash
---

# Lancer les tests PHPUnit dans Docker

Depuis `/home/mikhawa/cv-mikhawa`, exécute les tests PHPUnit dans le conteneur Docker.

Arguments fournis par l'utilisateur : $ARGUMENTS

- **Sans argument** : `docker compose exec php php bin/phpunit`
- **Avec un fichier** : `docker compose exec php php bin/phpunit <chemin_du_fichier>`
- **Avec --filter** : `docker compose exec php php bin/phpunit --filter <nom_du_test>`

Si `$ARGUMENTS` contient `--filter`, passe-le tel quel. Sinon, traite-le comme un chemin de fichier.

Affiche le résultat complet des tests. En cas d'échec, mets en évidence les tests qui ont échoué.
