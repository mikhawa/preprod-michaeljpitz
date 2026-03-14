# CLAUDE_MODEL.md — Modele de travail avec Claude Code

> Modele transposable pour tout projet web. Issu du retour d'experience du projet cv-mikhawa (Symfony 7.4, PHP 8.3, MariaDB, Docker).
> Derniere mise a jour : 6 fevrier 2026

---

## 1. Structure documentaire du projet

Organiser la documentation en fichiers `.md` specialises, chacun avec un role precis :

| Fichier | Role | Contenu |
|---------|------|---------|
| `CLAUDE.md` | **Instructions obligatoires** pour Claude Code | Stack technique, regles de codage, commandes de verification, architecture, securite. Lu automatiquement a chaque session. |
| `PROJECT_SPEC.md` | **Cahier des charges** complet | Objectif, stack imposee, modele de donnees, design, phases de developpement, checklist securite. Document de reference stable. |
| `README.md` | **Guide d'installation** pour les developpeurs | Demarrage rapide, comptes de test, fonctionnalites implementees, liens utiles. |
| `RACCOURCIS.md` | **Aide-memoire** personnel | Alias shell, commandes frequentes, chemins WSL. |
| `documentation/architecture.md` | **Cartographie technique** a jour | Arborescence `src/`, `templates/`, `assets/`, modele de donnees (schema), routes, conventions. Mis a jour a chaque phase. |
| `documentation/journal-decisions.md` | **ADR** (Architecture Decision Records) | Chaque decision numerotee : date, contexte, decision, raison, fichiers crees/modifies. |
| `documentation/securite.md` | **Audit de securite** permanent | Mesures implementees par categorie (auth, injection, CSRF, headers, uploads, validation). Points d'amelioration. |
| `documentation/deploiement.md` | **Guide de deploiement** | Installation dev (Docker), production (VPS), commandes de verification, mise a jour. |
| `documentation/phase*.md` | **Journal de phase** | Une page par phase/fonctionnalite : objectif, actions realisees, fichiers crees/modifies, verifications. |

### Bonnes pratiques documentaires

- **Un fichier = un sujet**. Ne pas melanger securite et architecture.
- **Chaque phase produit sa documentation** avant de passer a la suivante.
- **`journal-decisions.md` est cumulatif** : on ajoute, on ne supprime pas.
- **`architecture.md` est ecrase** a chaque mise a jour (il reflete l'etat actuel).
- **Les verifications sont listees** en fin de chaque document de phase (tableau passe/echoue).

---

## 2. Fichier CLAUDE.md — Structure recommandee

Le `CLAUDE.md` est le fichier le plus important : il est lu automatiquement par Claude Code a chaque session. Il doit etre **concis** et **prescriptif**.

```markdown
# CLAUDE.md

## Langue du projet
[Langue de travail : code, commits, documentation, echanges]

## Vue d'ensemble du projet
[1-2 phrases : type d'application, architecture, public cible]

## Stack technique
[Liste precise avec versions : langage, framework, BDD, frontend, bundles]

## Environnement de developpement
[Docker Compose : services, ports, OS hote]

## Commandes de verification
[Linting, analyse statique, style, tests, securite, BDD — dans l'ordre d'execution]

## Architecture
[Arborescence src/ simplifiee avec commentaires]

## Modele de donnees
[Entites principales et leurs relations]

## Regles de codage obligatoires
[Tableau interdit/utiliser a la place]

## Exigences de securite
[Liste precise : hashing, CSRF, rate limiting, headers, sanitizer, escaping]

## Format de reponse attendu
[Structure des reponses : docs consultees, fichiers, code, commandes, verifications, securite]
```

### Principes cles

1. **Pas de code dans CLAUDE.md** — le code va dans PROJECT_SPEC.md ou la documentation.
2. **Versions exactes** — `PHP 8.3`, pas `PHP 8.x`.
3. **Tableau interdit/autorise** — format visuel pour les regles strictes.
4. **Commandes copiables** — pret a executer, pas de pseudo-code.

---

## 3. Workers (agents) par type de tache

Claude Code dispose de sous-agents specialises. Voici comment les utiliser efficacement selon le type de tache :

### 3.1 Agent `Explore` — Recherche et comprehension du code

**Quand l'utiliser :**
- Comprendre comment une fonctionnalite existante est implementee
- Trouver tous les fichiers lies a un concept (ex: "comment fonctionne l'authentification ?")
- Analyser les patterns utilises dans le projet

**Quand NE PAS l'utiliser :**
- Pour chercher un fichier precis dont on connait le nom (utiliser `Glob`)
- Pour chercher une chaine de caracteres precise (utiliser `Grep`)

**Bonnes pratiques :**
- Specifier le niveau de profondeur : "quick", "medium" ou "very thorough"
- Donner un contexte precis : "cherche comment les commentaires sont moderes" plutot que "cherche les commentaires"

### 3.2 Agent `Plan` — Planification d'implementation

**Quand l'utiliser :**
- Avant toute fonctionnalite non triviale (plus de 2-3 fichiers)
- Quand plusieurs approches sont possibles
- Quand l'architecture est impactee

**Quand NE PAS l'utiliser :**
- Pour un simple bugfix sur un fichier
- Pour de la recherche pure (utiliser `Explore`)

**Bonnes pratiques :**
- L'agent explore le code puis propose un plan structure
- Le plan doit etre approuve AVANT l'implementation
- Inclure les fichiers a creer ET a modifier

### 3.3 Agent `Bash` — Execution de commandes

**Quand l'utiliser :**
- Operations git (status, diff, commit, push)
- Commandes Docker (`docker compose exec ...`)
- Execution de tests, linters, analyse statique
- Installation de dependances

**Quand NE PAS l'utiliser :**
- Pour lire des fichiers (utiliser `Read`)
- Pour chercher des fichiers (utiliser `Glob`/`Grep`)
- Pour editer des fichiers (utiliser `Edit`/`Write`)

### 3.4 Agent `general-purpose` — Taches complexes multi-etapes

**Quand l'utiliser :**
- Recherche qui necessite plusieurs iterations
- Taches combinant recherche + analyse + synthese
- Questions complexes sur le codebase

**Bonnes pratiques :**
- Donner un prompt detaille avec tout le contexte necessaire
- Preciser si on attend du code ou juste de la recherche

### 3.5 Parallelisation des agents

**Principe :** Lancer plusieurs agents en parallele quand les taches sont independantes.

**Exemples de parallelisation :**
- Recherche dans `src/` ET dans `templates/` simultanement
- Lecture de 3 fichiers independants en un seul appel
- Verification git status + git diff + git log en parallele

**Ne PAS paralleliser si :**
- Le resultat d'un agent conditionne l'appel suivant
- Les agents modifient les memes fichiers

---

## 4. Workflow de developpement recommande

### 4.1 Ajout d'une nouvelle fonctionnalite

```
1. PLANIFICATION
   - Entrer en mode Plan (EnterPlanMode)
   - Explorer le code existant (agent Explore ou Glob/Grep)
   - Identifier les fichiers a creer/modifier
   - Proposer un plan au developpeur
   - Obtenir l'approbation

2. IMPLEMENTATION
   - Creer les fichiers dans l'ordre des dependances :
     a. Entite / Migration
     b. Repository
     c. Service
     d. Formulaire
     e. Controleur
     f. Template
     g. Assets JS/CSS
   - Respecter les regles de codage du CLAUDE.md
   - Ne jamais abbrevier le code (`...`)

3. VERIFICATION
   - Executer les commandes de verification (lint, phpstan, tests)
   - Corriger les erreurs avant de continuer
   - Verifier manuellement si necessaire

4. DOCUMENTATION
   - Mettre a jour `documentation/architecture.md` si la structure change
   - Ajouter une entree dans `documentation/journal-decisions.md` si une decision technique est prise
   - Creer `documentation/phase-X.md` avec le detail des actions
   - Mettre a jour `documentation/securite.md` si la securite est impactee

5. COMMIT (uniquement si demande)
   - git status + git diff + git log
   - Message de commit en francais, concis
   - Ne pas pousser sauf demande explicite
```

### 4.2 Correction de bug

```
1. DIAGNOSTIC
   - Lire le fichier concerne
   - Comprendre le comportement attendu vs observe
   - Identifier la cause racine

2. CORRECTION
   - Modifier le minimum necessaire
   - Ne pas refactorer le code environnant
   - Ne pas ajouter de fonctionnalites

3. VERIFICATION
   - Executer les tests existants
   - Verifier que la correction ne casse rien d'autre

4. DOCUMENTATION (si decision technique)
   - Ajouter une entree dans journal-decisions.md
```

### 4.3 Checklist de securite par fonctionnalite

A verifier pour chaque fonctionnalite implementee :

```
[ ] Requetes Doctrine parametrees (pas de concatenation SQL)
[ ] Echappement Twig actif (pas de |raw sur du contenu non assaini)
[ ] CSRF sur tous les formulaires
[ ] Validation des donnees (Assert sur les entites)
[ ] Protection par role (#[IsGranted]) si necessaire
[ ] Headers de securite presents
[ ] Uploads valides (MIME, taille)
[ ] Contenu WYSIWYG assaini (HtmlSanitizer)
[ ] Pas de secrets en clair dans le code
[ ] Liens externes avec rel="noopener noreferrer"
```

---

## 5. Pieges courants et solutions

### 5.1 Tests

| Piege | Solution |
|-------|----------|
| Timestamps identiques dans les tests | Forcer des timestamps differents via SQL direct |
| JSON avec accents (`\u00e9`) | `json_decode()` avant `assertStringContainsString` |
| Mauvais nom de bouton dans le crawler | Verifier le texte exact dans le template Twig |
| PHPUnit Notices sur les mocks | `createStub()` au lieu de `createMock()` si pas d'expectation |
| Depreciation `Length` constraint | Utiliser des arguments nommes au lieu d'un array |
| Tests fonctionnels hors Docker | Executer dans Docker (`docker compose exec php php bin/phpunit`) |

### 5.2 Docker / WSL

| Piege | Solution |
|-------|----------|
| Permissions d'ecriture uploads | `chmod 777` dans le conteneur ou ajuster le Dockerfile |
| `apt-get` sur Alpine | Utiliser `apk add` (Alpine Linux) |
| Locale ICU en anglais | Installer `icu-data-full` + `intl.default_locale = fr` |
| Limite d'upload trop basse | Configurer `upload_max_filesize`, `post_max_size` (PHP) et `client_max_body_size` (Nginx) |
| Worker Messenger non demarre | Utiliser le transport `sync` en dev |

### 5.3 Symfony / Doctrine

| Piege | Solution |
|-------|----------|
| CSRF stateless invalide | Retirer le token des `stateless_token_ids` si necessaire |
| Stimulus non charge dans EasyAdmin | `configureAssets()->addAssetMapperEntry('app')` dans DashboardController |
| CSP bloquant blob: | Ajouter `blob:` a `img-src` dans la CSP |
| VichUploader Annotation deprecated | Utiliser `Vich\UploaderBundle\Mapping\Attribute` |
| ImageField EasyAdmin `[uniqueid]` | Utiliser `[randomhash].[extension]` |
| Tailwind v4 ne scanne pas templates | Ajouter `@source "../../templates"` dans app.css |

---

## 6. Memoire persistante (MEMORY.md)

Claude Code dispose d'un repertoire `.claude/projects/<project>/memory/` qui persiste entre les sessions.

### Organisation recommandee

```
memory/
  MEMORY.md              # Resume principal (< 200 lignes, charge automatiquement)
  debugging.md           # Notes de debug specifiques
  patterns.md            # Patterns et conventions du projet
```

### Que stocker dans MEMORY.md

- **Architecture de tests** : ou sont les tests, quels traits utiliser, comment les executer
- **Pieges courants** : erreurs deja rencontrees et leurs solutions
- **Environnement** : services Docker, configuration specifique
- **Decisions importantes** : choix qui impactent le travail futur

### Bonnes pratiques

- Garder MEMORY.md sous 200 lignes (le reste est tronque)
- Mettre a jour quand une erreur est rencontree et resolue
- Supprimer les informations obsoletes
- Organiser par theme, pas par date

---

## 7. Organisation des phases de developpement

### Nommage des fichiers de phase

```
documentation/
  phase1-setup.md                              # Setup initial
  phase2-entites-admin.md                      # Entites et admin
  phase3-frontend-public.md                    # Frontend
  phase3.1-commentaires-notes-inscription.md   # Sous-fonctionnalite
  phase3.2-moderation-commentaires.md          # Sous-fonctionnalite
  phase4-profil-utilisateur.md                 # Fonctionnalite majeure
  2026-02-02-corrections-traductions-tests.md  # Corrections ponctuelles
```

### Structure d'un fichier de phase

```markdown
# Phase X : [Nom de la fonctionnalite]

> Date : [date]

## Objectif
[1-3 phrases]

## Actions realisees

### 1. [Action]
**Fichier :** `chemin/du/fichier.php`
- Detail de l'implementation
- Choix techniques et justifications

## Fichiers crees
[Liste avec description courte]

## Fichiers modifies
[Liste avec description de la modification]

## Verifications
| Point | Statut |
|-------|--------|
| [Verification] | OK / KO |

## Problemes rencontres (si applicable)
[Description + solution]
```

---

## 8. Format des decisions techniques (ADR)

Chaque decision technique est numerotee dans `journal-decisions.md` :

```markdown
## Decision N : [Titre court]

**Date :** [date]
**Contexte :** [Pourquoi cette decision est necessaire]
**Decision :** [Ce qui a ete choisi]
**Raison :** [Justification technique]

### Fichiers crees
- `chemin/fichier.php` — Description

### Fichiers modifies
- `chemin/fichier.php` — Ce qui a change
```

### Quand creer une ADR

- Choix de technologie ou de bibliotheque
- Changement d'architecture (relation BDD, nouvelle entite)
- Contournement d'un bug ou d'une limitation
- Choix de securite non evident
- Tout ce qui pourrait surprendre un futur developpeur

---

## 9. Commandes de verification — Ordre d'execution

L'ordre est important : les verifications rapides d'abord, les lentes ensuite.

```bash
# 1. Validation du projet (rapide)
composer validate --strict

# 2. Linting syntaxique (rapide)
php bin/console lint:twig templates/
php bin/console lint:yaml config/
php bin/console lint:container

# 3. Analyse statique (moyen)
vendor/bin/phpstan analyse src --level=8

# 4. Formatage du code (moyen)
./vendor/bin/php-cs-fixer fix

# 5. Tests (lent)
php bin/phpunit

# 6. Audit de securite (reseau)
composer audit

# 7. Validation du schema BDD (BDD)
php bin/console doctrine:schema:validate
```

Si une etape echoue, corriger avant de passer a la suivante.

---

## 10. Resume : les 10 regles d'or

1. **Lire avant d'ecrire** — Toujours lire un fichier avant de le modifier.
2. **Planifier avant d'implementer** — Mode Plan pour toute fonctionnalite non triviale.
3. **Documenter chaque phase** — Un fichier `.md` par fonctionnalite.
4. **Tracer chaque decision** — ADR dans `journal-decisions.md`.
5. **Verifier systematiquement** — Executer les commandes de verification apres chaque changement.
6. **Ne pas sur-ingenierer** — Le minimum necessaire pour la tache demandee.
7. **Securite par defaut** — Checklist securite pour chaque fonctionnalite.
8. **Paralleliser les recherches** — Plusieurs `Read`/`Glob`/`Grep` en un seul appel.
9. **Mettre a jour la memoire** — MEMORY.md quand un piege est decouvert.
10. **Ne committer que sur demande** — Ne jamais pousser automatiquement.
