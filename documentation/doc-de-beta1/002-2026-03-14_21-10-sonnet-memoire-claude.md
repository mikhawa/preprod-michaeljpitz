# 002 — Mise en place de la mémoire persistante Claude

**Date** : 2026-03-14 à 21h10
**Moteur IA** : Claude Sonnet 4.6
**Branche** : `dev/mailpit-local`

---

## Contexte

Claude Code dispose d'un système de mémoire fichier persistante, stockée dans :

```
/home/mikhawa/.claude/projects/-home-mikhawa-preprod-michaeljpitz/memory/
```

Ces fichiers sont rechargés automatiquement à chaque nouvelle conversation pour conserver le contexte du projet.

## Objectif

Mémoriser le contexte de la session courante afin que les prochaines conversations disposent d'une vue complète du projet, des préférences de l'utilisateur et des conventions à respecter.

## Fichiers créés

### `MEMORY.md` — Index des mémoires

Fichier d'index pointant vers les autres fichiers de mémoire.

### `user_profile.md` — Profil utilisateur (type : `user`)

- Développeur PHP/Symfony expérimenté
- Projet personnel (portfolio/CV + blog)
- Environnement : WSL2 (Ubuntu) + Docker Compose
- Langue de travail : français

### `project_context.md` — Contexte projet (type : `project`)

- Stack complète : PHP 8.3 / Symfony 7.4 LTS / MariaDB 10.11 / EasyAdmin 4.x / AssetMapper / Tailwind CSS / Stimulus + Turbo
- Services Docker : PHP-FPM, Nginx (8080), MariaDB (3307), phpMyAdmin (8081), Mailpit (1025/8025)
- Convention de branches : `main` + `dev/*`
- Emails : Mailpit en dev, Mailjet en prod (via `.env.local`)

### `feedback_langue.md` — Règle de langue (type : `feedback`)

- Tout en français : commits, documentation, réponses, commentaires de code
- Convention définie dans `CLAUDE.md`

## Format des fichiers mémoire

Chaque fichier suit ce format :

```markdown
---
name: Nom de la mémoire
description: Description courte
type: user | feedback | project | reference
---

Contenu...
```

## Convention de nommage des documentations

Format retenu pour les fichiers de documentation de session :

```
NNN-YYYY-MM-DD_HH-MM-moteur-sujet-court.md
```

Exemple : `001-2026-03-14_21-10-sonnet-mailpit-local.md`

- `NNN` : numéro d'ordre dans la session (001, 002, …)
- `YYYY-MM-DD_HH-MM` : date et heure
- `moteur` : `sonnet`, `haiku` ou `opus`
- `sujet-court` : description en kebab-case

Les fichiers sont regroupés par phase dans des sous-dossiers (`doc-de-alpha1/`, `doc-de-beta1/`, etc.).
