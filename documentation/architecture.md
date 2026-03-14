# Architecture du projet

> Dernière mise à jour : 11 février 2026

## Vue d'ensemble

Application monolithique Symfony 7.4 LTS (pas d'API Platform, pas de SPA). L'interactivité est gérée par Stimulus.js et Symfony UX Turbo.

## Stack technique

| Composant | Version | Rôle |
|-----------|---------|------|
| PHP | 8.3 | Langage serveur |
| Symfony | 7.4 LTS | Framework applicatif |
| Doctrine ORM | 3.x | Mapping objet-relationnel |
| Twig | 3.x | Moteur de templates |
| MariaDB | 10.11 LTS | Base de données |
| AssetMapper | 7.4 | Gestion des assets (sans build step) |
| Tailwind CSS | v4 | Framework CSS (via symfonycasts/tailwind-bundle) |
| Stimulus.js | current | Contrôleurs JavaScript |
| Symfony UX Turbo | current | Navigation SPA-like |
| EasyAdmin | 4.28 | Interface d'administration |
| VichUploader | 2.9 | Upload d'images |
| KnpPaginator | 6.10 | Pagination |

## Structure des dossiers

```
src/
├── Command/
│   └── CreateAdminCommand.php        # Commande CLI : création admin
├── Controller/
│   ├── HomeController.php            # GET / (accueil + CV + 3 articles)
│   ├── ArticleController.php         # GET /articles, /categorie/{slug}, /article/{slug}, POST /rate
│   ├── SecurityController.php        # GET /login, /logout
│   ├── RegistrationController.php    # GET/POST /register
│   ├── ResetPasswordController.php   # GET/POST /mot-de-passe-oublie, /reinitialiser-mot-de-passe/{token}
│   ├── CvController.php              # GET /cv (contenu depuis BDD ou fallback statique)
│   ├── RgpdController.php            # GET /rgpd (contenu depuis BDD ou fallback statique)
│   ├── ContactController.php         # GET/POST /contact (formulaire + Turnstile)
│   ├── ProfileController.php         # GET/POST /profil (profil utilisateur connecté)
│   ├── PublicProfileController.php   # GET /utilisateur/{userName} (profil public)
│   ├── SitemapController.php         # GET /sitemap.xml
│   └── Admin/
│       ├── DashboardController.php   # /admin (tableau de bord)
│       ├── ArticleCrudController.php # CRUD articles (Trix + upload image)
│       ├── PageCrudController.php    # CRUD pages statiques (CV, RGPD)
│       ├── TrixUploadController.php  # POST /admin/trix/upload (images + fichiers)
│       ├── CategoryCrudController.php# CRUD catégories
│       ├── UserCrudController.php    # CRUD utilisateurs
│       ├── CommentCrudController.php # CRUD commentaires
│       └── RatingCrudController.php  # CRUD notes
├── Entity/
│   ├── User.php                      # Utilisateur (UserInterface)
│   ├── Article.php                   # Article de blog (Vich\Uploadable)
│   ├── Category.php                  # Catégorie d'articles (level=0 racine, level=N enfant)
│   ├── Comment.php                   # Commentaire sur un article
│   ├── Rating.php                    # Note 1-5 sur un article
│   └── Page.php                      # Page statique éditable (CV, RGPD)
├── EventSubscriber/
│   └── SecurityHeadersSubscriber.php # Headers de sécurité HTTP
├── Form/
│   ├── CommentType.php               # Formulaire commentaire
│   ├── ContactType.php               # Formulaire de contact
│   ├── ProfileType.php               # Formulaire modification profil
│   ├── RegistrationType.php          # Formulaire inscription
│   ├── ResetPasswordRequestType.php  # Formulaire demande réinitialisation
│   └── ResetPasswordType.php         # Formulaire nouveau mot de passe
├── Security/
│   ├── AccessDeniedHandler.php       # Page 403 personnalisée (accès refusé)
│   └── UserChecker.php               # Vérifie le statut utilisateur avant connexion
├── Service/
│   ├── ImageResizer.php              # Redimensionnement images (GD, EXIF)
│   └── TurnstileValidator.php        # Validation captcha Cloudflare
├── Repository/
│   ├── UserRepository.php            # PasswordUpgraderInterface
│   ├── ArticleRepository.php         # Requêtes articles publiés
│   ├── CategoryRepository.php        # findCategoryTree() arbre récursif
│   ├── CommentRepository.php
│   ├── RatingRepository.php
│   └── PageRepository.php            # findOneBySlug()
├── Twig/
│   └── Components/
│       └── NavbarComponent.php       # Composant Twig : navigation + catégories
└── Kernel.php

templates/
├── base.html.twig                    # Layout principal (<twig:Navbar />, SEO, dark mode)
├── home/
│   └── index.html.twig              # Accueil (hero, CV, articles, contact)
├── article/
│   ├── index.html.twig              # Liste paginée avec filtres catégorie
│   └── show.html.twig               # Détail article (commentaires, notes, lightbox)
├── cv/
│   └── index.html.twig              # Page CV (dynamique + fallback statique)
├── rgpd/
│   └── index.html.twig              # Page RGPD (dynamique + fallback statique)
├── contact/
│   └── index.html.twig              # Formulaire de contact avec Turnstile
├── profile/
│   └── index.html.twig              # Page profil utilisateur (avatar crop, bio, liens)
├── public_profile/
│   └── show.html.twig               # Profil public d'un utilisateur
├── security/
│   ├── access_denied.html.twig      # Page accès refusé
│   ├── forgot_password.html.twig    # Formulaire mot de passe oublié
│   ├── login.html.twig              # Connexion
│   ├── register.html.twig           # Inscription
│   └── reset_password.html.twig     # Formulaire nouveau mot de passe
├── email/
│   ├── activation.html.twig         # Email d'activation de compte
│   ├── contact_notification.html.twig # Notification de contact
│   └── reset_password.html.twig     # Email réinitialisation mot de passe
├── components/
│   ├── ArticleCard.html.twig        # Carte article réutilisable
│   ├── CategoryBadge.html.twig      # Badge catégorie coloré
│   └── Navbar.html.twig             # Navigation responsive + catégories + dropdown user
└── sitemap.xml.twig                  # Sitemap XML dynamique

assets/
├── app.js                            # Point d'entrée Stimulus
├── styles/
│   └── app.css                       # Tailwind + variables CSS thème + @source templates
└── controllers/
    ├── avatar_crop_controller.js     # Recadrage avatar (Cropper.js, 320x320)
    ├── csrf_protection_controller.js # Protection CSRF JavaScript
    ├── external_link_controller.js   # Liens externes → nouvel onglet
    ├── lightbox_controller.js        # Lightbox images articles (zoom, navigation)
    ├── navbar_controller.js          # Menu déroulant + hamburger mobile + dropdown user
    ├── star_rating_controller.js     # Notation interactive par étoiles
    ├── theme_controller.js           # Toggle mode sombre/clair
    └── trix_upload_controller.js     # Upload images et fichiers dans l'éditeur Trix
```

## Modèle de données

```
┌──────────┐      ┌───────────┐      ┌────────────────┐
│ Category │ N──N │  Article   │ N──1 │      User      │
│          │      │            │      │                │
│ id       │      │ id         │      │ id             │
│ title    │      │ title      │      │ email          │
│ slug     │      │ slug       │      │ roles          │
│ color    │      │ content    │      │ password       │
│ desc     │      │ excerpt    │      │ userName       │
│ level    │      │ image      │      │ status (0/1/2) │
└──────────┘      │ published  │      │ avatarName     │
      │           │ createdAt  │      │ biography      │
      │           │ publishedAt│      │ externalLink1-3│
      │           │ updatedAt  │      │ activationToken│
      │           └─────┬──────┘      │ resetPwdToken  │
      │                 │             │ createdAt      │
      │  ┌──────────┐   │             └───────┬────────┘
      └──┤article_  │───┘                     │
         │category  │               ┌─────────┼─────────┐
         │(jointure)│               │         │         │
         └──────────┘        ┌──────┴──┐  ┌───┴────┐
                             │ Comment │  │ Rating │
                             │         │  │        │
                             │ id      │  │ id     │
                             │ content │  │ rating │
                             │ created │  │ created│
                             │isApproved│ │ user_id│
                             │ user_id │  │ art_id │
                             │ art_id  │  │ UNIQUE │
                             └─────────┘  └────────┘
```

```
┌──────────┐
│   Page   │
│          │
│ id       │
│ title    │
│ slug     │ (unique : cv, rgpd, ...)
│ content  │
│ image    │
│ createdAt│
│ updatedAt│
└──────────┘
```

Page est une entité indépendante (aucune relation). Identifiée par son slug unique.

### Relations

| Relation | Type | Détails |
|----------|------|---------|
| Article ↔ Category | ManyToMany | Un article peut avoir N catégories, une catégorie contient N articles (table de jointure `article_category`, owning side = Article) |
| Category (hiérarchie) | Via `level` | `level = 0` → racine, `level = N` → enfant de la catégorie d'id N (pas de FK, logique applicative) |
| User → Comment | OneToMany | Un utilisateur rédige N commentaires |
| Article → Comment | OneToMany | Un article a N commentaires (orphanRemoval) |
| User → Rating | OneToMany | Un utilisateur donne N notes |
| Article → Rating | OneToMany | Un article a N notes |
| Rating | UniqueConstraint | Un utilisateur ne peut noter qu'une fois par article |

### Statuts utilisateur

| Valeur | Signification |
|--------|---------------|
| 0 | Non activé (email non confirmé) |
| 1 | Activé (compte actif) |
| 2 | Banni (accès refusé) |

### Modération des commentaires

| `isApproved` | Comportement |
|--------------|--------------|
| `false` | Commentaire en attente de modération (non visible) |
| `true` | Commentaire approuvé (visible publiquement) |

Les commentaires des administrateurs (`ROLE_ADMIN`) sont automatiquement approuvés.

## Routes publiques

| Méthode | URL | Nom | Description |
|---------|-----|-----|-------------|
| GET | `/` | app_home | Accueil avec CV et 3 derniers articles |
| GET | `/articles` | app_article_index | Liste paginée (9/page) de tous les articles |
| GET | `/categorie/{slug}` | app_category_show | Articles d'une catégorie (paginés, 9/page) |
| GET | `/article/{slug}` | app_article_show | Détail article + commentaires + notes + lightbox |
| POST | `/article/{slug}/rate` | app_article_rate | Soumettre une note (authentifié) |
| GET | `/cv` | app_cv | Page CV (dynamique depuis BDD ou fallback) |
| GET | `/rgpd` | app_rgpd | Page RGPD (dynamique depuis BDD ou fallback) |
| GET | `/connexion` | app_login | Page de connexion |
| GET | `/deconnexion` | app_logout | Déconnexion |
| GET/POST | `/inscription` | app_register | Inscription + email activation |
| GET | `/activation/{token}` | app_activation | Activation du compte via token |
| GET/POST | `/mot-de-passe-oublie` | app_forgot_password | Demande de réinitialisation |
| GET/POST | `/reinitialiser-mot-de-passe/{token}` | app_reset_password | Saisie nouveau mot de passe |
| GET/POST | `/contact` | app_contact | Formulaire de contact avec Turnstile |
| GET/POST | `/profil` | app_profile | Profil utilisateur connecté (ROLE_USER) |
| GET | `/utilisateur/{userName}` | app_public_profile | Profil public d'un utilisateur |
| GET | `/sitemap.xml` | app_sitemap | Sitemap XML |

## Routes admin

| URL | Description |
|-----|-------------|
| `/admin` | Tableau de bord EasyAdmin |
| `/admin?crudAction=index&crudControllerFqcn=...` | CRUD articles, catégories, utilisateurs, commentaires, notes, pages |
| `/admin/page` | Liste des pages statiques éditables |
| `/admin/page/{id}/edit` | Édition d'une page (Trix + image) |

## Conventions de code

- Tous les fichiers PHP commencent par `declare(strict_types=1)`
- Attributs PHP 8 pour Doctrine, routing et validation (pas d'annotations)
- Configuration en YAML (packages) et attributs PHP (routes, entités)
- Requêtes Doctrine paramétrées (pas de concaténation SQL)
- Auto-escaping Twig activé par défaut
- Contenu WYSIWYG assaini par HtmlSanitizer

## Infrastructure Docker

```
┌────────────────────────────────────────────────────┐
│                  docker-compose                    │
├──────────┬──────────┬────────┬─────────────────────┤
│ php:8.3  │  nginx   │mariadb │    phpMyAdmin        │
│  FPM     │ :8080    │ 10.11  │      :8081           │
│ Alpine   │ Alpine   │ :3306  │                      │
└──────────┴──────────┴────────┴─────────────────────┘
```

| Service | Port externe | Rôle |
|---------|-------------|------|
| nginx | 8080 | Serveur web (reverse proxy vers PHP-FPM) |
| mariadb | 3306 | Base de données |
| phpMyAdmin | 8081 | Administration base de données |
