# 027 — Alias `design` dans le Dockerfile PHP

**Date** : 2026-05-15  
**Modèle** : Claude Sonnet 4.6  
**Branche** : `design/02-upgrade-design`

---

## Ce qui a été fait

Ajout de l'alias `design` dans `docker/php/Dockerfile`, dans la section des alias Symfony.

**Fichier modifié** : `docker/php/Dockerfile`

```bash
alias design='php bin/console importmap:install && php bin/console tailwind:build -v && php bin/console cache:clear --env=dev && php bin/console asset-map:compile --env=dev'
```

### Ordre des commandes et justification

| Ordre | Commande | Rôle |
|-------|----------|------|
| 1 | `importmap:install` | Télécharge/met à jour les packages JS/CSS (dépendances disponibles avant de builder) |
| 2 | `tailwind:build -v` | Compile le CSS Tailwind en scannant les templates (doit être fait avant la publication des assets) |
| 3 | `cache:clear --env=dev` | Vide le cache Symfony pour que l'asset map soit recalculée proprement |
| 4 | `asset-map:compile --env=dev` | Fingerprinte et publie tous les assets dans `public/assets/` |

### Usage

Après rebuild du container (`docker compose build php && docker compose up -d`), exécuter depuis le container PHP :

```bash
design
```

---

## Fichiers modifiés

| Fichier | Type |
|---------|------|
| `docker/php/Dockerfile` | Modification |
