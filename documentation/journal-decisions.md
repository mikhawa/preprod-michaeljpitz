# Journal des décisions techniques

> Ce fichier recense les décisions techniques prises au fil du développement, avec leur contexte et justification. à partir du 15 mars 2026.

---

## 2026-06-14

### Recadrage optionnel des images dans Suneditor
**Décision** : À chaque upload d'image dans l'éditeur, un dialog de choix interrompt l'upload natif de Suneditor (`onImageUploadBefore` retourne `undefined`). L'utilisateur choisit entre insertion directe ou recadrage via Cropper.js (ratio : Libre, 16:9, 4:3, 1:1). Le canvas recadré est encodé en JPEG 92 % et uploadé via `_uploadBlob()` vers `/admin/editor/upload`. Cropper.js est chargé en lazy depuis `/public/js/` (déjà présent pour les avatars).  
Voir `documentation/036-2026-06-14-sonnet-crop-image-suneditor.md`.

---

## 2026-05-17

### Page d'accueil : galerie photo avec défilement automatique dans le hero
**Décision** : L'image statique du cercle hero est remplacée par une galerie de 9 photos personnelles avec transition alpha (fondu) toutes les 3 secondes. Contrôleur Stimulus `photo-gallery` avec `setInterval` + `opacity` CSS. Les images sont servies depuis `public/images/gallery/`.  
Voir `documentation/035-2026-05-17-sonnet-galerie-photo-hero-accueil.md`.

### Liens vers pages EasyAdmin : toujours passer par AdminUrlGenerator
**Décision** : Tout lien pointant vers une page qui étend `@EasyAdmin/page/content.html.twig` doit être généré via `AdminUrlGenerator::setRoute()` ou `setController()`, jamais via `$this->generateUrl()` ou `path()`. Un lien Symfony direct contourne le routeur EasyAdmin, laisse `ea()` à `null` et provoque une erreur sur `@EasyAdmin/layout.html.twig`.  
Voir `documentation/034-2026-05-17-sonnet-fix-ea-context-category-tree.md`.

### Dashboard EasyAdmin : cartes graphiques plutôt que redirection directe
**Décision** : `DashboardController::index()` rend désormais un template avec 8 cartes cliquables (une par entrée du menu) au lieu de rediriger vers la liste des articles. Les compteurs d'entrées sont injectés via les repositories en constructor injection. Le design utilise les classes Bootstrap 5.3 d'EasyAdmin avec des couleurs personnalisées et un dark mode via `[data-color-scheme="dark"]`.  
Voir `documentation/033-2026-05-17-sonnet-dashboard-admin-favicons-logo.md`.

### Page "Menu des catégories" : layout EasyAdmin natif
**Décision** : Remplacement du `<!DOCTYPE html>` autonome par `extends '@EasyAdmin/page/content.html.twig'`. Le CSRF token est conservé dans `{% block head_metas %}`, les boutons d'action dans `{% block page_actions %}`. Le dark mode est géré par `[data-color-scheme="dark"]` au lieu d'un CSS inline figé.  
Voir `documentation/033-2026-05-17-sonnet-dashboard-admin-favicons-logo.md`.

### Logo navbar : deux `<img>` + CSS plutôt que JavaScript
**Décision** : Le switch light/dark du logo est géré par 4 règles CSS (`display: none/inline`) ciblant `[data-theme="light"]` sur `<html>`, sans aucun JavaScript supplémentaire. Les images `@2x` sont servies via `srcset` pour les écrans Retina. `alt=""` + `aria-hidden="true"` car le texte "Michaël J. Pitz" adjacent porte l'information.  
Voir `documentation/033-2026-05-17-sonnet-dashboard-admin-favicons-logo.md`.

---

## 2026-05-15

### Mode clair : inversion fond/cartes pour simuler la profondeur du dark mode
**Décision** : `--bg-primary` passe à `#f1f5f9` (fond teinté) et `--bg-secondary` à `#ffffff` (cartes blanches), reproduisant la même logique de contraste qu'en dark mode. Ajout de blobs décoratifs sur le `body`, glow hero amplifié (0.04 → 0.12) avec second pseudo-élément `::before`, et ombres cartes via sélecteur CSS attribute `[class*="bg-[var(--bg-secondary)]"]` sans toucher aux templates.  
Voir `documentation/030-2026-05-15-sonnet-enrichissement-visuel-mode-clair.md`.

### Fil d'Ariane : TwigComponent réutilisable plutôt que HTML dupliqué
**Décision** : Création d'un `BreadcrumbComponent` (classe PHP + template unique) appelé depuis 7 templates avec un tableau `items`. Le dernier item sans `url` est rendu comme page courante (`aria-current="page"`), tous les autres comme liens. Le lien "← Retour" (basé sur le `Referer` HTTP, fragile) de `public_profile/show.html.twig` a été remplacé par le composant.  
Voir `documentation/029-2026-05-15-sonnet-fil-ariane-composant-twig.md`.

### Navbar sticky — fond opaque obligatoire
**Décision** : Ajout de `bg-[var(--bg-primary)]` en plus de `sticky top-0 z-50` sur le `<header>`. Sans fond explicite, le contenu défilant transparaît sous la navbar.  
Voir `documentation/026-2026-05-15_14-19-sonnet-navbar-sticky-workflow-tailwind.md`.

### Workflow assets Tailwind en dev : copie manuelle nécessaire
**Décision** : En environnement Docker, Nginx sert `public/assets/` directement sans passer par PHP. Après `tailwind:build`, le CSS compilé (`var/tailwind/app.built.css`) doit être copié manuellement vers `public/assets/styles/app-R3JrkgL.css`. L'alias `design` dans le Dockerfile automatise cette chaîne complète en 4 étapes ordonnées.  
Voir `documentation/027-2026-05-15_14-19-sonnet-dockerfile-alias-design.md`.

### Filtre catégories : arbre Stimulus avec animation `grid-template-rows`
**Décision** : La technique `grid-template-rows: 0fr → 1fr` est préférée au hack `max-height` pour animer les panneaux d'enfants — elle s'adapte à la hauteur naturelle du contenu sans valeur arbitraire. La restructuration parent→enfants est faite en Twig pur (pas de modification contrôleur/repository) grâce à `|merge` itératif.  
Voir `documentation/028-2026-05-15_14-19-sonnet-filtre-categories-arbre-stimulus.md`.

---

## 2026-04-26

### Sitemap dynamique : pages et catégories incluses, robots.txt corrigé
**Décision** : Le `SitemapController` injecte désormais `PageRepository` et `CategoryRepository` en plus de `ArticleRepository`. Les pages CV et RGPD sont récupérées par slug pour exposer leur `lastmod`. Les catégories sont filtrées via `findWithPublishedArticles()` pour exclure les pages vides.

**Contexte** : PageSpeed Insights signalait que `robots.txt` pointait vers `https://portfolio.local/sitemap.xml` (domaine local de dev), bloquant l'indexation. Le sitemap n'incluait ni les pages statiques ni les catégories.

**Raison du choix** : Le sitemap reste généré à la volée (pas de cache, pas de fichier statique) pour refléter immédiatement toute modification admin. Seules les catégories avec au moins un article publié sont indexées pour éviter les pages vides pénalisantes en SEO.

**Fichiers modifiés** : `public/robots.txt`, `src/Controller/SitemapController.php`, `src/Repository/CategoryRepository.php`, `templates/sitemap.xml.twig`. Voir `documentation/022-2026-04-26-sonnet-seo-sitemap-robots.md`.

---

## 2026-04-18

### Bouton PHP dans Suneditor : injection DOM plutôt qu'API plugin
**Décision** : Le bouton "PHP" est ajouté à la toolbar Suneditor par injection DOM directe (`querySelector('.se-btn-module').parentNode`) et utilise l'API publique `editor.insertHTML()`, et non le système de plugin interne de Suneditor.

**Contexte** : L'API plugin Suneditor (`display: 'command'` + `action()`) appelle la fonction avec `this` = core interne. `core.insertHTML()` échouait silencieusement sans aucun message d'erreur visible. Le bouton était rendu mais un clic ne produisait rien.

**Raison du choix** : L'injection DOM est indépendante du versioning interne de Suneditor et donne accès à l'API publique (`editor.insertHTML`), documentée et stable.

### Fix `buildWidget` : `</p><p>` → `\n` unique
**Décision** : Dans `PhpRunnerExtension::buildWidget()`, les paires `</p>\s*<p>` sont converties en `\n` unique **avant** `strip_tags`, et les `&nbsp;` (U+00A0) sont convertis en espaces normaux.

**Contexte** : Suneditor stocke le code multiligne en plusieurs `<p>` séparés par `\n`. L'ancien pipeline (`strip_tags` direct) conservait les `\n` entre les balises → 3–4 sauts parasites pour 1 ligne vide. Le Tab WYSIWYG insérait du CSS (`padding-left`) → indentation perdue après `strip_tags`.

**Raison du choix** : Traiter les balises bloc avant `strip_tags` est la seule façon de récupérer la structure de lignes sans dépendre du comportement de rendu Suneditor. La `<dialog>` native avec Tab→espaces résout définitivement l'indentation pour les nouveaux blocs.

---

## 2026-04-17

### Boucle infinie 2FA + "Se souvenir de moi" (admin)
**Décision** : Dans `TwoFactorLoginSubscriber::onLoginSuccess()`, ignorer les authentifications effectuées via `RememberMeAuthenticator` (early return avant toute logique 2FA).

**Contexte** : Le `LoginSuccessEvent` de Symfony se déclenche pour **toutes** les authentifications réussies, y compris les reconnexions automatiques par cookie "Se souvenir de moi". Cela provoquait un enchaînement de redirections infinies pour les admins :
1. Cookie remember-me → `LoginSuccessEvent` → `2fa_required = true` en session
2. `TwoFactorGateSubscriber` → redirige vers `/connexion/code-verification`
3. `#[IsGranted('IS_AUTHENTICATED_FULLY')]` échoue (remember-me = `IS_AUTHENTICATED_REMEMBERED`) → redirige vers `/connexion`
4. `SecurityController::login()` voit `getUser()` non null → redirige vers `app_home`
5. Retour à l'étape 2 → boucle infinie

**Raison du choix** : Le cookie "Se souvenir de moi" constitue un facteur de confiance volontaire (l'utilisateur a explicitement coché la case). Redéclencher le 2FA à chaque retour automatique est à la fois inutilisable et techniquement impossible dans le flux actuel. Le 2FA se redéclenche normalement à la prochaine connexion manuelle.

**Fichier modifié** : `src/EventSubscriber/TwoFactorLoginSubscriber.php` — ajout de `use Symfony\Component\Security\Http\Authenticator\RememberMeAuthenticator` et du guard `if ($event->getAuthenticator() instanceof RememberMeAuthenticator) { return; }`.

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

