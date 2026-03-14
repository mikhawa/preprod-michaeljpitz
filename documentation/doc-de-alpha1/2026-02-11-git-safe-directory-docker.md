# Configuration Git safe.directory dans le conteneur PHP

**Date** : 11 février 2026
**Branche** : `main`
**Auteur** : Claude Opus 4.6

---

## Résumé

Ajout de la configuration `git safe.directory` dans le Dockerfile PHP pour éviter l'erreur `fatal: detected dubious ownership in repository` lors de l'utilisation de commandes git dans le conteneur Docker.

---

## Problème

Git refuse d'opérer dans `/var/www/html` à l'intérieur du conteneur PHP car le propriétaire du répertoire (monté via un volume Docker) ne correspond pas à l'utilisateur exécutant git. Cela provoque l'erreur :

```
fatal: detected dubious ownership in repository at '/var/www/html'
```

---

## Solution

Ajout d'une instruction `RUN` dans `docker/php/Dockerfile` pour marquer `/var/www/html` comme répertoire sûr :

```dockerfile
# Marquer le répertoire de travail comme sûr pour git
RUN git config --global --add safe.directory /var/www/html
```

Cette ligne a été insérée juste après l'instruction `WORKDIR /var/www/html`.

---

## Fichier modifié

- `docker/php/Dockerfile` : ajout de `git config --global --add safe.directory /var/www/html`

---

## Commandes exécutées

```bash
docker compose build php
docker compose up -d php
docker compose exec php php bin/phpunit
```

---

## Vérification

Les 140 tests (270 assertions) passent tous avec succès après le changement.
