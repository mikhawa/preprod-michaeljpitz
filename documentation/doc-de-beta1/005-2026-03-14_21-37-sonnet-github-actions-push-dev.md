# 005 — GitHub Actions : ajout du déclencheur `push` sur `dev/**`

**Date** : 2026-03-14 à 21h37
**Moteur IA** : Claude Sonnet 4.6
**Branche** : `dev/mailpit-local`

---

## Modification

Ajout du déclencheur `push` sur les branches `dev/**` dans `.github/workflows/tests.yml`.

```yaml
# Avant
on:
  pull_request:
    branches:
      - main
      - "dev/**"

# Après
on:
  push:
    branches:
      - "dev/**"
  pull_request:
    branches:
      - main
      - "dev/**"
```

## Tableau des déclencheurs

| Événement | Branches cibles | Effet |
|-----------|----------------|-------|
| `push` | `dev/**` | Tests lancés à chaque commit pushé sur une branche dev |
| `pull_request` | `main` | Tests lancés avant merge vers main |
| `pull_request` | `dev/**` | Tests lancés sur PR inter-branches dev |
