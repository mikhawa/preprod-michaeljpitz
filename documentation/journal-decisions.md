# Journal des décisions techniques

> Ce fichier recense les décisions techniques prises au fil du développement, avec leur contexte et justification. à partir du 15 mars 2026.

---

## 2026-04-06

### PHP Runner WASM — exécution PHP côté client
**Décision finale** : marqueur texte `[php]...[/php]` dans Suneditor, converti en widget interactif par un filtre Twig `php_runner` + contrôleur Stimulus `php-runner` + php-wasm (CDN jsDelivr).

**Contexte** : L'objectif était de permettre aux visiteurs d'exécuter du code PHP directement dans les articles. `wasm/wasm` (Composer) est incompatible avec PHP 8.3. Un endpoint serveur présentait des risques de sécurité.

**Évolution de la solution** — trois approches testées et rejetées avant d'arriver à la solution finale :
1. `<pre class="php-runner"><?php...` → spec HTML5 convertit `<?...?>` en bogus comment `<!-- ?php ? -->`
2. `<pre class="php-runner">echo...` (sans `<?php`) → Suneditor supprime l'attribut `class`
3. `<div data-controller>` / `<textarea>` dans le contenu → Suneditor supprime les `data-*` et les éléments de formulaire
4. **Retenu** : marqueur texte `[php]...[/php]` — Suneditor le préserve tel quel, le filtre Twig le transforme côté serveur

**Conséquences** : nouveau `PhpRunnerExtension.php`, contrôleur Stimulus `php_runner_controller.js`, filtre `php_runner` dans le template article, CSP élargie. Voir `documentation/php-runner-wasm.md`.

---

## 2026-03-14

### MEMORY.md versionné dans git
**Décision** : Créer `.claude/MEMORY.md` dans le projet, versionné dans git.
**Raison** : Permettre à Claude Code de retrouver le contexte du projet sur n'importe quelle machine sans reconfiguration.

### Choix autonome du modèle Claude
**Décision** : Claude choisit lui-même le modèle le plus adapté (Opus/Sonnet/Haiku) selon la complexité de la tâche.
**Raison** : Éviter de solliciter l'utilisateur pour chaque changement de modèle. Règles définies dans `.claude/models.md`.

### ADMIN_EMAIL comme destinataire unique des notifications admin
**Décision** : Toutes les notifications email vers l'admin utilisent `ADMIN_EMAIL` (`.env`) via `#[Autowire]`.
**Raison** : Les adresses en dur `contact@alpha1.michaeljpitz.com` étaient des reliquats de l'environnement alpha, non adaptés à la preprod/prod.
**Fichiers** : `ArticleController`, `ContactController`, `RegistrationController`.

### Suppression de CONTACT_FALLBACK_EMAIL
**Décision** : Supprimer `CONTACT_FALLBACK_EMAIL` et la méthode `getAdminEmail()` dans `ContactController`.
**Raison** : Redondant avec `ADMIN_EMAIL`. La variable locale `$adminEmail` était calculée mais jamais utilisée.

---

## 2026-03-15

### Groupes de validation sur le formulaire de profil
**Décision** : Utiliser `validation_groups: ['Profile']` dans `ProfileType` et annoter les contraintes `biography`/`externalLink` avec `groups: ['Profile']` dans `User`.
**Raison** : Symfony valide l'entité entière à la soumission. Un `userName` avec des points échouait la regex et bloquait le formulaire silencieusement (422, sans message affiché). Le groupe `Profile` isole la validation aux seuls champs du formulaire.
**Fichiers** : `src/Entity/User.php`, `src/Form/ProfileType.php`.

### Autorisation du point (`.`) dans `userName`
**Décision** : Modifier la regex de `userName` de `/^[a-zA-Z0-9_]+$/` en `/^[a-zA-Z0-9_.]+$/`.
**Raison** : Le pseudo `Michael.J.Pitz` existait en base mais échouait la validation. Le point est un caractère légitime dans un pseudonyme.
**Fichier** : `src/Entity/User.php`.

### Création et permissions de `public/uploads/avatars/`
**Décision** : Versionner `public/uploads/avatars/.gitkeep` et affiner `.gitignore` pour conserver la structure des sous-dossiers d'uploads.
**Raison** : Le dossier n'existait pas après clonage (`/public/uploads/` était entièrement ignoré), ce qui causait un échec silencieux de `file_put_contents()`. Ajout d'un `mkdir()` de sécurité dans `ProfileController`.
**Fichiers** : `.gitignore`, `public/uploads/avatars/.gitkeep`, `src/Controller/ProfileController.php`.

