# 011 — Correction aliases : disponibles dans `sh` (commande `uphp`)

**Date** : 2026-03-14 à 22h13
**Moteur IA** : Claude Sonnet 4.6
**Branche** : `dev/mailpit-local`

---

## Problème

`uphp` (`docker compose exec php sh`) utilise `sh` (Alpine ash). Contrairement à `bash`, `ash` ne charge pas `/etc/profile.d/` sauf pour les shells de **login** (`sh -l`). Les aliases n'étaient donc pas disponibles via `uphp`.

## Correction

Ajout de la variable d'environnement `ENV` dans le Dockerfile :

```dockerfile
ENV ENV=/etc/profile.d/aliases.sh
```

`$ENV` est un mécanisme POSIX : quand `sh`/`ash` démarre en mode **interactif** (avec ou sans login), il charge le fichier pointé par `$ENV`. Cela couvre `uphp` (`sh`) et `dphp` (`bash`).

## Compatibilité

| Commande d'entrée | Shell | Aliases chargés via |
|-------------------|-------|---------------------|
| `docker compose exec php sh` (`uphp`) | ash/sh | `$ENV` → `/etc/profile.d/aliases.sh` |
| `docker compose exec php bash` (`dphp`) | bash | `/root/.bashrc` → `/etc/profile.d/aliases.sh` |

## Rebuild

```bash
docker compose build php
docker compose up -d php
```
