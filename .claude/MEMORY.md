# MEMORY.md — preprod-michaeljpitz

Contexte persistant du projet, versionné dans Git pour être utilisable sur plusieurs machines.

---

## Utilisateur

- Développeur PHP/Symfony expérimenté
- Travaille sous WSL2 (Ubuntu) avec Docker Compose
- Langue de travail : **français** (code, commits, docs, échanges)

---

## Projet

Site personnel en Symfony 7.4 LTS.

**Stack** : PHP 8.3 / Symfony 7.4 / Doctrine ORM 3.x / Twig 3.x / MariaDB 10.11 / AssetMapper / Tailwind CSS 3.x / Stimulus.js + Turbo / EasyAdmin 4.x / VichUploaderBundle / cropperjs / suneditor / Mailpit (dev) / Mailjet (prod)

**Docker Compose** — 5 services :
| Service | Port |
|---------|------|
| PHP 8.3-FPM | — |
| Nginx | 8080 |
| MariaDB 10.11 | 3307 |
| phpMyAdmin | 8081 |
| Mailpit (SMTP/UI) | 1025 / 8025 |

**Branches** :
- `main` : branche principale
- `dev/*` : branches de développement local
- `preprod/*` : branches de développement distant

**Emails** :
- Dev → Mailpit (`smtp://mailpit:1025`), interface sur http://localhost:8025
- Prod → Mailjet (DSN dans `.env.local`, non commité)

---

## Règles de travail (feedback)

### Confirmation avant action
Toujours demander confirmation à l'utilisateur avant de modifier ou créer un fichier, quelle que soit la tâche. Proposer le plan envisagé, attendre la validation explicite,
puis seulement agir. Ne pas prendre d'initiative sans validation préalable, même pour des tâches simples ou évidentes.
Ne jamais faire de commit, je m'en occupe moi-même.

### Langue
Tout en français : commentaires de code, documentation, réponses Claude.
> Voir `CLAUDE.md` — convention explicite du projet.

### Documentation des actions
La moindre action significative doit être documentée dans un fichier `.md` dédié, avec un nom de fichier structuré pour faciliter le suivi chronologique et thématique.
Il ne faut pas ma permission pour créer la documentation d'une action, mais il faut me demander avant de modifier ou supprimer une documentation existante.

Chaque action significative doit être tracée dans `documentation/doc-de-{phase}/` :

```
NNN-YYYY-MM-DD_HH-MM-moteur-sujet-court.md
```

- `NNN` : numéro d'ordre dans la session (001, 002, …)
- `YYYY-MM-DD_HH-MM` : date et heure réelle (récupérer avec `date`)
- `moteur` : `sonnet`, `haiku` ou `opus` selon le modèle utilisé
- `sujet-court` : description en kebab-case français

Phases : `doc-de-alpha1/`, `doc-de-beta1/`, etc.

### Choix du modèle Claude
Voir `.claude/models.md` pour le détail. En résumé :

| Complexité    | Modèle     | Exemples                                     |
|---------------|------------|----------------------------------------------|
| Complexe      | **Opus**   | Architecture, sécurité, refactoring majeur   |
| Intermédiaire | **Sonnet** | Controllers, Services, tests, GitHub Actions |
| Simple        | **Haiku**  | CRUD, migrations, typos, questions syntaxe   |

**Claude doit choisir lui-même le modèle le plus adapté** à chaque tâche, sans attendre que l'utilisateur le précise. Si la tâche en cours relève d'un modèle différent du modèle actif, Claude le signale et utilise l'`Agent` tool avec le paramètre `model` approprié (`opus`, `sonnet` ou `haiku`).

---

## À mettre à jour

Ce fichier doit être mis à jour à chaque nouvelle règle ou contexte significatif appris en session.
