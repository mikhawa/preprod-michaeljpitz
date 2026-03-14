# Journal des décisions techniques

> Ce fichier recense les décisions techniques prises au fil du développement, avec leur contexte et justification.

## Décision 1 : Tailwind v4 via symfonycasts/tailwind-bundle

**Date :** 31 janvier 2026 (Phase 1)
**Contexte :** Le PROJECT_SPEC préconisait Tailwind CSS 3.x via CDN ou importmap.
**Décision :** Utiliser `symfonycasts/tailwind-bundle` v0.12, qui a détecté Tailwind v4 automatiquement.
**Raison :** Intégration native avec AssetMapper, pas de CDN externe (meilleur pour la CSP), compilation locale.

## Décision 2 : Alpine apk au lieu de apt-get dans le Dockerfile

**Date :** 31 janvier 2026 (Phase 1)
**Contexte :** Le PROJECT_SPEC fournissait un Dockerfile utilisant `apt-get` avec l'image `php:8.3-fpm-alpine`.
**Décision :** Correction vers `apk add` (gestionnaire de paquets Alpine).
**Raison :** `apt-get` n'est pas disponible sur Alpine Linux. Le PROJECT_SPEC contenait une erreur.

## Décision 3 : Trix comme éditeur WYSIWYG

**Date :** 31 janvier 2026 (Phase 2)
**Contexte :** Choix entre Trix et TinyMCE pour l'édition des articles.
**Décision :** Trix.
**Raison :** Recommandé par le PROJECT_SPEC, léger, intégré nativement dans EasyAdmin via TextEditorField.

## Décision 4 : Suppression des dépréciations Doctrine

**Date :** 31 janvier 2026 (Phase 2)
**Contexte :** Les options `use_savepoints` et `report_fields_where_declared` généraient des avertissements de dépréciation.
**Décision :** Suppression de ces clés de configuration dans `doctrine.yaml`.
**Raison :** Implicites respectivement dans DBAL 4 et ORM 3.

## Décision 5 : Passage de Vich\Annotation à Vich\Attribute

**Date :** 31 janvier 2026 (Phase 2)
**Contexte :** VichUploaderBundle utilisait le namespace `Annotation` déprécié.
**Décision :** Utilisation de `Vich\UploaderBundle\Mapping\Attribute`.
**Raison :** Cohérence avec l'usage des attributs PHP 8 dans tout le projet.

## Décision 6 : KnpPaginatorBundle pour la pagination

**Date :** 31 janvier 2026 (Phase 3)
**Contexte :** Pagination des articles sur la page `/articles`.
**Décision :** Utiliser `knplabs/knp-paginator-bundle` v6.10.
**Raison :** Bundle mature, bien intégré avec Doctrine QueryBuilder et Twig.

## Décision 7 : X-Frame-Options SAMEORIGIN au lieu de DENY

**Date :** 31 janvier 2026 (Phase 3)
**Contexte :** Le CLAUDE.md préconisait `X-Frame-Options: DENY`.
**Décision :** Implémenté avec `SAMEORIGIN`.
**Raison :** Permet l'usage d'iframes internes si nécessaire. À passer en DENY en production si aucun iframe interne n'est utilisé.

## Décision 8 : Contrainte d'unicité sur Rating

**Date :** 31 janvier 2026 (Phase 3.1)
**Contexte :** Un utilisateur ne doit pouvoir noter un article qu'une seule fois.
**Décision :** `UniqueConstraint` Doctrine sur les colonnes `user_id` + `article_id` dans la table `rating`.
**Raison :** Garantie au niveau base de données, plus fiable qu'une vérification applicative seule.

## Décision 9 : MapEntity pour les routes article

**Date :** 31 janvier 2026 (Phase 3.1)
**Contexte :** La résolution automatique du paramètre `{slug}` vers l'entité Article ne fonctionnait pas correctement.
**Décision :** Utilisation de l'attribut `#[MapEntity]` avec `mapping: ['slug' => 'slug']`.
**Raison :** Résolution explicite du paramètre, évite l'ambiguïté avec le ParamConverter par défaut.

## Décision 10 : Ajout de phpMyAdmin au Docker Compose

**Date :** 31 janvier 2026
**Contexte :** Le PROJECT_SPEC ne listait que 4 services Docker (PHP, Nginx, MariaDB, Mailpit).
**Décision :** Ajout d'un 5e service phpMyAdmin sur le port 8081.
**Raison :** Facilite l'inspection et le débogage de la base de données en développement.

## Décision 11 : Upload d'images dans l'éditeur Trix (EasyAdmin)

**Date :** 1 février 2026
**Contexte :** L'éditeur Trix intégré à EasyAdmin ne permet pas nativement l'ajout d'images dans le contenu des articles.
**Décision :** Implémentation d'un système d'upload d'images complet : endpoint POST `/admin/trix/upload`, contrôleur Stimulus `trix_upload_controller.js`, bouton « Insérer une image » + glisser-déposer + copier-coller.
**Raison :** Enrichir le contenu des articles avec des images directement depuis l'interface d'administration.

### Problèmes rencontrés et solutions

1. **Stimulus non chargé dans EasyAdmin** : EasyAdmin utilise son propre layout HTML et ne charge pas l'importmap de l'application. Résolu en ajoutant `configureAssets()->addAssetMapperEntry('app')` dans `DashboardController`.

2. **CSRF stateless incompatible** : Le projet utilise `SameOriginCsrfTokenManager` (cookie-based), incompatible avec `CsrfTokenManagerInterface::getToken()`. Résolu en remplaçant la validation CSRF par une vérification du header `X-Requested-With: XMLHttpRequest` (protection CORS same-origin).

3. **Permissions d'écriture Docker** : Le répertoire `public/uploads/articles/content/` créé depuis l'hôte WSL (uid 1000, permissions 755) n'était pas accessible en écriture par PHP-FPM. Résolu par `chmod 777` + mise à jour du Dockerfile.

4. **CSP bloquant les blob: URLs** : Trix utilise des URLs `blob:` pour l'aperçu des images avant upload. Résolu en ajoutant `blob:` à la directive `img-src` dans `SecurityHeadersSubscriber`.

5. **Toolbar Trix masquée par EasyAdmin** : Le groupe `trix-button-group--file-tools` est masqué en CSS par EasyAdmin (`display: none`). Résolu en ajoutant un bouton indépendant au-dessus de l'éditeur via le contrôleur Stimulus.

### Correction du pattern de nommage de l'image mise en avant

Le pattern `[uniqueid].[extension]` utilisé dans `ArticleCrudController` n'est pas un token valide d'EasyAdmin. Le fichier était nommé littéralement `[uniqueid].png`, écrasant l'image précédente à chaque upload. Corrigé par `[randomhash].[extension]` qui génère un hash aléatoire unique (40 caractères hex).

### Fichiers créés

- `src/Controller/Admin/TrixUploadController.php` — Endpoint d'upload protégé par `#[IsGranted('ROLE_ADMIN')]`
- `assets/controllers/trix_upload_controller.js` — Contrôleur Stimulus (bouton, drag-and-drop, upload)
- `public/uploads/articles/content/.gitkeep` — Répertoire de destination des images

### Fichiers modifiés

- `src/Controller/Admin/DashboardController.php` — Ajout de `configureAssets()` avec `addAssetMapperEntry('app')`
- `src/Controller/Admin/ArticleCrudController.php` — Ajout de `row_attr` avec `data-controller="trix-upload"` sur le `TextEditorField`
- `src/EventSubscriber/SecurityHeadersSubscriber.php` — Ajout de `blob:` dans `img-src` de la CSP
- `config/packages/html_sanitizer.yaml` — Ajout de `width`, `height` sur `img`, ajout de `figure` et `figcaption`
- `docker/php/Dockerfile` — Création du répertoire d'upload avec les bonnes permissions
- `assets/styles/app.css` — Style du bouton image Trix
- `.gitignore` — Exclusion de `/public/uploads/`

## Décision 12 : Autoriser Google Fonts dans la CSP

**Date :** 1 février 2026
**Contexte :** La police Inter chargée depuis Google Fonts était bloquée par la Content Security Policy (`style-src` et `font-src` n'autorisaient que `'self'`).
**Décision :** Ajout de `https://fonts.googleapis.com` dans `style-src` et `https://fonts.gstatic.com` dans `font-src` dans `SecurityHeadersSubscriber.php`.
**Raison :** Google Fonts sert la feuille CSS depuis `fonts.googleapis.com` et les fichiers de police (woff2) depuis `fonts.gstatic.com`. Les deux domaines doivent être explicitement autorisés dans la CSP.

## Décision 13 : Liens externes ouverts dans un nouvel onglet

**Date :** 1 février 2026
**Contexte :** Les liens vers des sites externes dans le contenu des articles s'ouvraient dans le même onglet, ce qui éloignait le visiteur du site.
**Décision :** Création d'un contrôleur Stimulus `external_link_controller.js` qui détecte les liens externes (hostname différent du site) et leur ajoute `target="_blank"` et `rel="noopener noreferrer"`.
**Raison :** Traitement côté client via Stimulus plutôt que côté serveur, car le contenu HTML est stocké tel quel en base (édité via Trix). L'attribut `rel="noopener noreferrer"` protège contre l'accès à `window.opener` par la page cible.

### Fichiers créés

- `assets/controllers/external_link_controller.js` — Contrôleur Stimulus

### Fichiers modifiés

- `templates/article/show.html.twig` — Ajout de `data-controller="external-link"` sur le conteneur du contenu

## Décision 14 : Ajout de php-cs-fixer au workflow de vérification

**Date :** 1 février 2026
**Contexte :** Le workflow de vérification (linting, analyse statique, tests) ne comprenait pas de formatage automatique du code.
**Décision :** Ajout de `./vendor/bin/php-cs-fixer fix` dans le workflow, entre l'analyse statique (PHPStan) et les tests (PHPUnit).
**Raison :** Assurer un style de code uniforme selon les standards PSR-12 avant chaque commit.

### Fichiers modifiés

- `CLAUDE.md` — Ajout de la commande dans la section « Build & Verification Commands »
- `README.md` — Documentation de l'installation et de l'utilisation de php-cs-fixer

## Décision 15 : Modération des commentaires avant publication

**Date :** 1 février 2026
**Contexte :** Les commentaires étaient publiés immédiatement après soumission, sans validation préalable.
**Décision :** Ajout d'un champ `isApproved` (booléen, `false` par défaut) sur l'entité `Comment`. Le repository filtre les commentaires non approuvés côté public. L'administrateur approuve les commentaires via EasyAdmin.
**Raison :** Éviter la publication de spam ou de contenu inapproprié. Le workflow de modération est simple (un booléen) et gérable directement depuis l'interface EasyAdmin existante.

### Fichiers modifiés

- `src/Entity/Comment.php` — Ajout de la propriété `isApproved`
- `src/Repository/CommentRepository.php` — Filtre `isApproved = true` dans `findByArticle()`
- `src/Controller/ArticleController.php` — Message flash adapté
- `src/Controller/Admin/CommentCrudController.php` — `BooleanField`, réactivation de `EDIT`, filtre admin

## Décision 16 : Validation du compte utilisateur par email

**Date :** 1 février 2026
**Contexte :** Les comptes utilisateurs étaient actifs immédiatement après inscription, sans vérification de l'adresse email.
**Décision :** Ajout d'un workflow d'activation par email : token aléatoire (64 caractères hex via `bin2hex(random_bytes(32))`), envoi d'un `TemplatedEmail`, route `/activation/{token}` avec expiration à 48 heures. Un `UserChecker` bloque la connexion si le statut n'est pas 1.
**Raison :** Vérifier que l'utilisateur possède bien l'adresse email renseignée et prévenir les inscriptions abusives.

### Fichiers créés

- `src/Security/UserChecker.php` — Bloque la connexion si statut ≠ 1
- `templates/email/activation.html.twig` — Template HTML de l'email d'activation

### Fichiers modifiés

- `src/Entity/User.php` — Ajout de `activationToken`, `status`, `createdAt`
- `src/Controller/RegistrationController.php` — Génération du token, envoi de l'email, route d'activation
- `config/packages/security.yaml` — Ajout de `user_checker`

## Décision 17 : Gestion du statut utilisateur par l'administrateur

**Date :** 1 février 2026
**Contexte :** L'administrateur n'avait pas de moyen de désactiver ou bannir un utilisateur depuis l'interface d'administration.
**Décision :** Ajout d'un `ChoiceField` « Statut » (0 = Non activé, 1 = Activé, 2 = Banni) avec badges colorés dans `UserCrudController`. Protection contre l'auto-modification : boutons masqués, `updateEntity()` et `deleteEntity()` bloqués pour le compte de l'admin connecté.
**Raison :** Permettre à l'administrateur de gérer le cycle de vie des comptes (activation manuelle, désactivation, bannissement) tout en le protégeant d'une erreur sur son propre compte.

### Fichiers modifiés

- `src/Controller/Admin/UserCrudController.php` — `ChoiceField` statut, filtre, protection auto-modification

## Décision 18 : Envoi synchrone des emails en développement

**Date :** 1 février 2026
**Contexte :** Les emails (activation de compte) n'étaient pas envoyés. Le transport Messenger routait `SendEmailMessage` vers le transport `async` (queue Doctrine), mais aucun worker ne tournait pour consommer la queue.
**Décision :** Activation du transport `sync` dans `messenger.yaml` et routage de `SendEmailMessage` vers `sync` au lieu de `async`.
**Raison :** En développement, l'envoi synchrone est suffisant et ne nécessite pas de worker. Les emails arrivent immédiatement dans Mailpit. En production, il faudra repasser en `async` avec un worker Messenger.

### Fichiers modifiés

- `config/packages/messenger.yaml` — Activation du transport `sync`, routage de `SendEmailMessage` vers `sync`

## Décision 19 : CSRF session classique pour le formulaire de connexion

**Date :** 1 février 2026
**Contexte :** Le formulaire de connexion retournait « Jeton CSRF invalide » systématiquement. Le token ID `authenticate` était dans la liste `stateless_token_ids`, utilisant le `SameOriginCsrfTokenManager` (basé sur un cookie). Ce mécanisme posait déjà problème dans le projet (cf. décision 11).
**Décision :** Retrait de `authenticate` de la liste `stateless_token_ids` dans `csrf.yaml`. Le formulaire de connexion utilise désormais le gestionnaire CSRF classique basé sur la session.
**Raison :** Le CSRF stateless par cookie n'est pas fiable dans l'environnement de développement Docker/WSL2. Le CSRF session est éprouvé et fonctionne avec le `form_login` natif de Symfony.

### Fichiers modifiés

- `config/packages/csrf.yaml` — Retrait de `authenticate` des `stateless_token_ids`

## Décision 20 : Réinitialisation de mot de passe « fait maison »

**Date :** 1 février 2026
**Contexte :** Les utilisateurs n'avaient aucun moyen de récupérer l'accès à leur compte en cas d'oubli de mot de passe. Le bundle `symfonycasts/reset-password-bundle` existe, mais le projet privilégie une implémentation minimale et maîtrisée.
**Décision :** Implémentation d'un système de réinitialisation par email suivant le même patron que l'activation de compte (Phase 3.3) : token aléatoire de 64 caractères hex, email avec lien, expiration à 1 heure, effacement du token après usage. Le message de succès est toujours identique pour ne pas révéler l'existence des comptes. Seuls les comptes actifs (status = 1) reçoivent l'email.
**Raison :** Cohérence avec le système d'activation existant, pas de dépendance externe supplémentaire, contrôle total sur le flux et les messages. Le délai d'expiration d'1 heure (contre 48h pour l'activation) reflète la criticité supérieure d'une réinitialisation de mot de passe.

### Fichiers créés

- `src/Controller/ResetPasswordController.php` — Routes `/mot-de-passe-oublie` et `/reinitialiser-mot-de-passe/{token}`
- `src/Form/ResetPasswordRequestType.php` — Formulaire de demande (champ email)
- `src/Form/ResetPasswordType.php` — Formulaire nouveau mot de passe (RepeatedType, 8-255 chars)
- `templates/security/forgot_password.html.twig` — Page de demande
- `templates/security/reset_password.html.twig` — Page de saisie du nouveau mot de passe
- `templates/email/reset_password.html.twig` — Email avec bouton

### Fichiers modifiés

- `src/Entity/User.php` — Ajout de `resetPasswordToken` et `resetPasswordRequestedAt`
- `templates/security/login.html.twig` — Lien « Mot de passe oublié ? »

## Décision 21 : Dépréciation UserChecker – ajout du paramètre TokenInterface

**Date :** 2 février 2026
**Contexte :** Symfony 7.4 signalait une dépréciation sur `UserChecker::checkPostAuth()` qui nécessitera un argument `?TokenInterface $token` dans la prochaine version majeure de l'interface `UserCheckerInterface`.
**Décision :** Ajout du paramètre `?TokenInterface $token = null` aux méthodes `checkPreAuth()` et `checkPostAuth()`.
**Raison :** Anticiper la prochaine version majeure de Symfony et supprimer l'avertissement de dépréciation.

### Fichiers modifiés

- `src/Security/UserChecker.php` — Ajout du paramètre optionnel `$token` aux deux méthodes

## Décision 22 : URLs structurées pour les catégories et articles

**Date :** 2 février 2026
**Contexte :** Les catégories étaient filtrées via un query parameter (`/articles?category=slug`), ce qui n'est pas idéal pour le SEO. Les articles individuels utilisaient `/articles/{slug}` avec un 's'.
**Décision :** Création d'une route dédiée `/categorie/{slug}` pour les pages catégorie, et changement de `/articles/{slug}` vers `/article/{slug}` (singulier) pour les articles individuels. La liste reste à `/articles`.
**Raison :** URLs plus propres et sémantiques. Le singulier `/article/{slug}` reflète qu'on consulte un seul article. La route `/categorie/{slug}` est une vraie page avec son propre pattern d'URL.

### Fichiers modifiés

- `src/Controller/ArticleController.php` — Nouvelle méthode `category()` avec route `/categorie/{slug}`, simplification de `index()`, routes article au singulier
- `templates/article/index.html.twig` — Liens catégorie et pagination vers la nouvelle route
- `templates/components/CategoryBadge.html.twig` — Lien du badge vers `/categorie/{slug}`

## Décision 23 : Relation Article-Category ManyToMany

**Date :** 2 février 2026
**Contexte :** Un article ne pouvait appartenir qu'à une seule catégorie (ManyToOne). Un article peut logiquement relever de plusieurs thématiques (ex. un article sur Doctrine concerne à la fois « Bases de données » et « Symfony »).
**Décision :** Transformation de la relation ManyToOne en ManyToMany. Article est le côté propriétaire (owning side). Table de jointure `article_category` générée par Doctrine.
**Raison :** Plus de souplesse dans le classement des articles, meilleure navigation par catégorie.

### Changements de requêtes

- `findPublishedQueryBuilder()` : utilise `MEMBER OF` au lieu de `a.category = :category`
- `findSimilarArticles()` : utilise un `INNER JOIN` sur `a.categories` avec `GROUP BY` pour éviter les doublons

### Migration des données

La migration inclut un `INSERT INTO article_category SELECT id, category_id FROM article WHERE category_id IS NOT NULL` pour transférer les associations existantes avant suppression de la colonne `category_id`.

### Fichiers modifiés

- `src/Entity/Article.php` — `ManyToOne $category` → `ManyToMany $categories` avec `getCategories()`, `addCategory()`, `removeCategory()`
- `src/Entity/Category.php` — `OneToMany` → `ManyToMany(mappedBy: 'categories')`
- `src/Repository/ArticleRepository.php` — Requêtes adaptées
- `src/Controller/Admin/ArticleCrudController.php` — `AssociationField::new('categories', 'Catégories')`
- `templates/article/show.html.twig` — Boucle sur `article.categories`
- `templates/components/ArticleCard.html.twig` — Boucle sur `article.categories`
- `migrations/Version20260202114109.php` — Table de jointure + migration des données

## Décision 24 : Menu navigation avec catégories hiérarchiques via le champ `level`

**Date :** 2 février 2026
**Contexte :** Les catégories n'étaient accessibles que depuis la page `/articles` (boutons filtre). L'utilisateur souhaitait les intégrer dans le menu principal avec une hiérarchie parent/enfant et un menu responsive.
**Décision :** Utiliser le champ `level` existant de l'entité `Category` comme pointeur vers l'id du parent : `level = 0` signifie catégorie racine, `level = N` (N > 0) signifie enfant de la catégorie d'id N. Un composant Twig `Navbar` (`#[AsTwigComponent]`) charge l'arbre des catégories via `CategoryRepository::findCategoryTree()` (construction récursive). Un contrôleur Stimulus `navbar_controller.js` gère les interactions (dropdown hover desktop, hamburger mobile, accordéon).
**Raison :** Pas de migration nécessaire (le champ `level` existe déjà en base). L'approche composant Twig permet d'injecter les catégories dans toutes les pages sans modifier chaque contrôleur. Le menu reste horizontal sur desktop avec les catégories racines séparées par `|`, et devient un hamburger avec accordéon sur mobile.

### Problème rencontré : Tailwind v4 ne scannait pas les templates

Après création du composant `Navbar.html.twig`, les classes responsive (`md:flex`, `md:hidden`) n'étaient pas générées dans le CSS compilé. Tailwind v4 via `symfonycasts/tailwind-bundle` ne scannait pas automatiquement le dossier `templates/`. Résolu en ajoutant `@source "../../templates"` dans `assets/styles/app.css`.

### Hiérarchie des catégories

```
level = 0 → catégorie racine (visible dans le menu)
level = N → enfant de la catégorie dont id = N
```

La fonction `findCategoryTree()` charge toutes les catégories puis les organise récursivement en arbre. Supporte N niveaux de profondeur.

### Fichiers créés

- `src/Twig/Components/NavbarComponent.php` — Composant Twig avec injection de `CategoryRepository`
- `templates/components/Navbar.html.twig` — Navigation desktop (dropdown hover) + mobile (hamburger accordéon)
- `assets/controllers/navbar_controller.js` — Contrôleur Stimulus (dropdown, submenu, mobile, click outside)

### Fichiers modifiés

- `src/Repository/CategoryRepository.php` — `findCategoryTree()` et `buildTree()` récursif
- `templates/base.html.twig` — Header remplacé par `<twig:Navbar />`
- `assets/styles/app.css` — Ajout de `@source "../../templates"` pour Tailwind v4

## Décision 25 : Formulaire de contact avec Cloudflare Turnstile

**Date :** 2 février 2026
**Contexte :** Le site affichait un simple lien mailto pour contacter l'administrateur, ce qui n'est pas idéal (spam, pas de validation, pas de trace).
**Décision :** Création d'un formulaire de contact (`/contact`) avec captcha Cloudflare Turnstile. Le service `TurnstileValidator` vérifie le token côté serveur. L'email est envoyé à l'administrateur via `symfony/mailer`.
**Raison :** Turnstile est gratuit, respectueux de la vie privée (pas de CAPTCHA visuel intrusif), et simple à intégrer. En développement, les clés de test (toujours passent) permettent de travailler sans compte Cloudflare.

### Fichiers créés

- `src/Controller/ContactController.php` — Route `/contact` (GET|POST)
- `src/Form/ContactType.php` — Formulaire (nom, email, message)
- `src/Service/TurnstileValidator.php` — Validation token Cloudflare
- `templates/contact/index.html.twig` — Formulaire avec widget Turnstile
- `templates/email/contact_notification.html.twig` — Email HTML pour l'admin

### Fichiers modifiés

- `config/services.yaml` — Configuration du service `TurnstileValidator`
- `.env` — Variables `TURNSTILE_SITE_KEY` et `TURNSTILE_SECRET_KEY`
- `templates/home/index.html.twig` — Lien vers `/contact` au lieu de mailto
- `templates/components/Navbar.html.twig` — Lien "Contact" vers `app_contact`

## Décision 26 : Page de profil utilisateur avec recadrage d'avatar

**Date :** 4 février 2026
**Contexte :** Les utilisateurs ne pouvaient pas personnaliser leur profil (avatar, biographie, liens externes).
**Décision :** Création d'une page `/profil` protégée par `ROLE_USER`. Upload d'avatar avec outil de recadrage Cropper.js (320x320 pixels). Biographie (500 caractères) et 3 liens externes optionnels. Affichage des derniers commentaires avec leur statut de modération.
**Raison :** Personnalisation du profil attendue par les utilisateurs. Cropper.js permet un recadrage côté client qui réduit la taille des images avant l'envoi. Les fichiers Cropper.js sont stockés localement pour respecter la CSP.

### Points techniques

1. **Cropper.js local** : Stocké dans `/public/js/` et `/public/css/` pour éviter les problèmes CSP avec les CDN externes.
2. **Image en base64** : Le contrôleur Stimulus recadre l'image et l'envoie en base64 dans un champ caché. Le serveur décode et sauvegarde en fichier.
3. **Validation stricte** : Vérification du format base64 et limite de taille avant décodage.

### Fichiers créés

- `src/Controller/ProfileController.php` — Route `/profil` (GET|POST)
- `src/Form/ProfileType.php` — Formulaire profil
- `assets/controllers/avatar_crop_controller.js` — Contrôleur Stimulus Cropper.js
- `templates/profile/index.html.twig` — Template profil
- `public/js/cropper.min.js` — Cropper.js v1.6.2
- `public/css/cropper.min.css` — Styles Cropper.js

### Fichiers modifiés

- `src/Entity/User.php` — Nouveaux champs : `avatarName`, `biography`, `externalLink1-3`, `updatedAt`
- `config/packages/vich_uploader.yaml` — Mapping `user_avatar`
- `templates/components/Navbar.html.twig` — Menu déroulant utilisateur (Profil / Déconnexion)
- `assets/controllers/navbar_controller.js` — Gestion du dropdown utilisateur

## Décision 27 : Profil public des utilisateurs

**Date :** 5 février 2026
**Contexte :** Les noms d'utilisateurs affichés dans les commentaires n'étaient pas cliquables. Impossible de voir les informations publiques d'un utilisateur.
**Décision :** Création d'une route `/utilisateur/{userName}` affichant le profil public d'un utilisateur : avatar, date d'inscription, biographie, liens externes, et commentaires approuvés.
**Raison :** Permet aux visiteurs de découvrir les contributeurs. Seuls les utilisateurs actifs (`status = 1`) et les commentaires approuvés (`isApproved = true`) sont visibles.

### Fichiers créés

- `src/Controller/PublicProfileController.php` — Route `/utilisateur/{userName}`
- `templates/public_profile/show.html.twig` — Template profil public

### Fichiers modifiés

- `src/Repository/CommentRepository.php` — Méthode `findApprovedByUser()`
- `templates/article/show.html.twig` — Liens cliquables vers les profils des auteurs de commentaires

## Décision 28 : Approbation automatique des commentaires administrateur

**Date :** 5 février 2026
**Contexte :** Les commentaires des administrateurs passaient par le même workflow de modération que les utilisateurs normaux.
**Décision :** Les commentaires postés par un utilisateur ayant `ROLE_ADMIN` sont automatiquement approuvés (`isApproved = true`). Message flash différencié selon le rôle.
**Raison :** Un administrateur n'a pas besoin d'attendre la validation de ses propres commentaires.

### Fichiers modifiés

- `src/Controller/ArticleController.php` — Vérification `$this->isGranted('ROLE_ADMIN')` avant persistance

## Décision 29 : Service ImageResizer pour les uploads Trix

**Date :** 5 février 2026
**Contexte :** Les images uploadées via l'éditeur Trix pouvaient être très grandes (photos de smartphone en haute résolution).
**Décision :** Création d'un service `ImageResizer` utilisant GD pour redimensionner les images dépassant 1200px de largeur. Le service corrige également l'orientation EXIF (rotation automatique des photos de smartphone).
**Raison :** Réduction de la bande passante et du temps de chargement des articles. La correction EXIF évite les images affichées de travers.

### Fichiers créés

- `src/Service/ImageResizer.php` — Service GD avec support EXIF

### Fichiers modifiés

- `src/Controller/Admin/TrixUploadController.php` — Utilisation du service `ImageResizer`

## Décision 30 : Lightbox pour les images des articles

**Date :** 5 février 2026
**Contexte :** Les images insérées dans le contenu des articles s'affichaient en taille réduite sans possibilité de les agrandir.
**Décision :** Création d'un contrôleur Stimulus `lightbox_controller.js` pour afficher les images en popup avec navigation (flèches, clavier), compteur, bouton zoom, et fermeture (clic extérieur, Escape).
**Raison :** Amélioration de l'expérience utilisateur pour la consultation des images. Pas de dépendance externe, tout est géré en Stimulus vanilla.

### Fonctionnalités

- Clic sur une image : ouvre la lightbox
- Navigation : boutons ←/→ + flèches clavier
- Fermeture : bouton ×, clic en dehors, touche Escape
- Compteur : affiche "1 / 3" si plusieurs images
- Zoom : bouton pour voir l'image en taille réelle (avec scroll)

### Fichiers créés

- `assets/controllers/lightbox_controller.js` — Contrôleur Stimulus

### Fichiers modifiés

- `templates/article/show.html.twig` — Ajout de `data-controller="lightbox"` sur le conteneur du contenu

## Décision 31 : Entité Page générique pour les pages statiques éditables

**Date :** 6 février 2026
**Contexte :** Les pages CV et RGPD avaient leur contenu codé en dur dans les templates Twig. L'administrateur ne pouvait pas les modifier sans toucher au code.
**Décision :** Création d'une entité `Page` identifiée par un `slug` unique (`cv`, `rgpd`). Les contrôleurs chargent le contenu depuis la base de données. Les templates conservent un fallback statique si la page n'existe pas en base.
**Raison :** Une entité générique permet de réutiliser la même logique pour toute future page statique. Le fallback garantit que le site fonctionne même sans données en base.

### Fichiers créés

- `src/Entity/Page.php` — Entité avec VichUploader
- `src/Repository/PageRepository.php` — `findOneBySlug()`
- `src/Controller/Admin/PageCrudController.php` — CRUD EasyAdmin
- `migrations/Version20260206130201.php` — Table `page`
- `migrations/Version20260206131000.php` — Données initiales
- `tests/Functional/AdminPageEditTest.php` — Tests fonctionnels

### Fichiers modifiés

- `config/packages/vich_uploader.yaml` — Mapping `page_image`
- `src/Controller/Admin/DashboardController.php` — Menu « Pages »
- `src/Controller/CvController.php` — Chargement depuis la BDD
- `src/Controller/RgpdController.php` — Chargement depuis la BDD
- `templates/cv/index.html.twig` — Contenu dynamique + fallback
- `templates/rgpd/index.html.twig` — Contenu dynamique + fallback

## Décision 32 : Support des fichiers téléchargeables dans l'éditeur Trix

**Date :** 6 février 2026
**Contexte :** L'éditeur Trix ne permettait que l'insertion d'images. L'administrateur avait besoin d'insérer des liens vers des fichiers (PDF, DOC, etc.) dans le contenu des pages et articles.
**Décision :** Ajout des types MIME documents (PDF, DOC, DOCX, ODT, ZIP) dans `TrixUploadController`. Ajout d'un bouton « Insérer un fichier » dans le contrôleur Stimulus. Les fichiers non-image sont stockés sans redimensionnement et insérés comme liens HTML cliquables.
**Raison :** Permettre l'insertion de documents téléchargeables directement dans le contenu riche, avec un workflow identique à l'insertion d'images.

### Fichiers modifiés

- `src/Controller/Admin/TrixUploadController.php` — Types MIME documents, stockage sans redimensionnement
- `assets/controllers/trix_upload_controller.js` — Bouton « Insérer un fichier », insertion de `<a>` au lieu de `<img>`

## Décision 33 : Désactivation de Turbo dans l'interface EasyAdmin

**Date :** 16 février 2026
**Contexte :** Sur mobile, le menu hamburger d'EasyAdmin ne fonctionnait pas au premier chargement — il fallait actualiser la page manuellement. Le problème venait de Turbo, chargé via l'entry point `app` dans `configureAssets()` (cf. décision 11), qui interceptait la navigation et empêchait le JavaScript d'EasyAdmin de s'initialiser correctement. EasyAdmin met `data-turbo="false"` sur sa balise `<html>`, mais Turbo étant déjà initialisé via l'importmap, il continuait d'interférer.
**Décision :** Ajout de deux meta tags dans le `<head>` de l'admin via `configureAssets()->addHtmlContentToHead()` :
- `<meta name="turbo-visit-control" content="reload">` — force un rechargement complet au lieu d'une navigation Turbo
- `<meta name="turbo-root" content="/DO_NOT_INTERCEPT">` — restreint Turbo à un scope inexistant, l'empêchant d'intercepter les clics

L'entry point `app` est conservé pour que Stimulus (et donc SunEditor) continue de fonctionner.
**Raison :** Turbo est nécessaire côté public (navigation fluide) mais incompatible avec le JavaScript interne d'EasyAdmin (menu responsive, toggles). Plutôt que de retirer l'entry point `app` (ce qui désactiverait aussi SunEditor), on désactive uniquement Turbo dans le contexte admin.

### Fichiers modifiés

- `src/Controller/Admin/DashboardController.php` — Ajout de meta tags pour désactiver Turbo dans `configureAssets()`
