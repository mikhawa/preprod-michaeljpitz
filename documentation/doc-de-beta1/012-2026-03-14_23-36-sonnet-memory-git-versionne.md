# Mise en place du MEMORY.md versionné dans git

## Actions réalisées

1. **Lecture de `.claude/models.md`** — vérification de la compréhension des règles d'attribution des modèles.

2. **Création de `.claude/MEMORY.md`** — fichier de contexte versionné dans git,
   transportable sur plusieurs machines. Contient : profil utilisateur, stack technique,
   ports Docker, config emails, règles de documentation, règles de choix de modèle.

3. **Ajout de la règle "Claude choisit son modèle"** — Claude peut et doit choisir
   lui-même le modèle le plus adapté à chaque tâche.

4. **Ajout de la règle "Confirmation avant action"** — Claude doit toujours demander
   confirmation avant de modifier ou créer un fichier.

5. **Mémorisation dans la mémoire utilisateur** (`~/.claude/projects/.../memory/`) —
   nouveau fichier `feedback_confirmation.md` + mise à jour de `MEMORY.md`.

6. **Mise à jour de `.claude/MEMORY.md`** — ajout de la branche `preprod/*`
   (modification utilisateur prise en compte).

## Fichiers concernés

- `.claude/MEMORY.md` (créé)
- `~/.claude/projects/-home-mikhawa-preprod-michaeljpitz/memory/feedback_confirmation.md` (créé)
- `~/.claude/projects/-home-mikhawa-preprod-michaeljpitz/memory/MEMORY.md` (mis à jour)

## Modèle utilisé

Claude Sonnet 4.6
