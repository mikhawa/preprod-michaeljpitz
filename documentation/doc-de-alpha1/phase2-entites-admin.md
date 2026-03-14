# Phase 2 : Entités et Administration

> Date : 31 janvier 2026

## Actions réalisées

### 1. Installation des bundles

- `easycorp/easyadmin-bundle` v4.28 : interface d'administration
- `vich/uploader-bundle` v2.9 : gestion des uploads d'images
- `symfony/html-sanitizer` v7.4 : assainissement du contenu WYSIWYG
- Enregistrement manuel de VichUploaderBundle dans `config/bundles.php` (recipe ignorée)

### 2. Entités créées

Toutes les entités utilisent `declare(strict_types=1)` et les attributs PHP 8 (pas d'annotations).

**User** (`src/Entity/User.php`) :
- Implémente UserInterface + PasswordAuthenticatedUserInterface
- Champs : id (unsigned), email (unique, validé), roles, password, userName (regex alphanumeric+underscore)
- Table nommée `` `user` `` (échappée car mot réservé SQL)

**Article** (`src/Entity/Article.php`) :
- Champs : id, title (3-150), slug (7-154, unique), content (TEXT, min 20), excerpt (max 300), featuredImage, isPublished, createdAt, publishedAt, updatedAt
- Relation ManyToOne vers Category
- Lifecycle callbacks : PrePersist (createdAt), PreUpdate (updatedAt)
- Intégration VichUploader : `#[Vich\Uploadable]` + champ `imageFile` avec validation MIME (jpeg, png, webp) et taille max 2Mo
- Utilise `#[ORM\HasLifecycleCallbacks]`

**Category** (`src/Entity/Category.php`) :
- Champs : id, title (2-100), slug (2-104, unique), color (hex 7 chars), description (600), level (unsigned)
- Relation OneToMany inverse vers Article
- Méthode `__toString()` pour l'affichage dans les selects EasyAdmin

### 3. Repositories

- `UserRepository` : implémente PasswordUpgraderInterface pour la mise à jour automatique des mots de passe
- `ArticleRepository` et `CategoryRepository` : ServiceEntityRepository de base

### 4. Migration Doctrine

- Migration générée et exécutée : `Version20260131164708`
- 5 requêtes SQL : création des tables `user`, `article`, `category` + index + clés étrangères
- Schéma validé : mapping correct et en sync avec la base

### 5. Configuration sécurité

Fichier `config/packages/security.yaml` modifié :
- Password hasher : bcrypt cost 13
- Provider : entity User via propriété email
- Firewall main : form_login avec CSRF, logout, remember_me (secure + httponly)
- Access control : `/admin` requiert ROLE_ADMIN

### 6. Authentification

- `SecurityController` : routes `/login` (app_login) et `/logout` (app_logout)
- Template `templates/security/login.html.twig` : formulaire stylisé Tailwind avec CSRF token, remember_me
- Redirection vers /admin si déjà connecté

### 7. EasyAdmin

- **DashboardController** : route `/admin`, menu avec Articles, Catégories, Utilisateurs, retour au site
- **ArticleCrudController** : CRUD complet avec TextEditorField (Trix) pour le contenu, SlugField auto-généré, ImageField pour l'upload, tri par date décroissant
- **CategoryCrudController** : CRUD avec ColorField, SlugField auto-généré
- **UserCrudController** : CRUD avec hashage automatique du mot de passe via `persistEntity`/`updateEntity`, champ password en PasswordType

### 8. Upload d'images

Configuration VichUploader (`config/packages/vich_uploader.yaml`) :
- Mapping `article_image` : `public/uploads/articles/`
- Namer : UniqidNamer (noms de fichiers uniques)
- Validation sur l'entité : types MIME (jpeg, png, webp), taille max 2Mo

### 9. HtmlSanitizer

Configuration (`config/packages/html_sanitizer.yaml`) :
- Sanitizer `article_sanitizer` conforme au PROJECT_SPEC
- Éléments autorisés : `a` (href, title, target), `img` (src, alt, title), `iframe` (src, width, height)
- Force HTTPS sur les URLs

### 10. Commande d'administration

- `app:create-admin` (`src/Command/CreateAdminCommand.php`) : crée un utilisateur admin avec email, mot de passe hashé et nom d'utilisateur
- Admin initial créé : `admin@portfolio.local` / `admin123!`

## Vérifications sécurité Phase 2

| Point | Statut |
|-------|--------|
| Validation uploads (MIME, taille) | OK - Assert\Image sur Article.imageFile |
| HtmlSanitizer configuré | OK - article_sanitizer avec allowlist |
| CSRF sur formulaires | OK - enable_csrf: true dans security.yaml |
| Mots de passe bcrypt cost 13 | OK - security.yaml |
| Schéma Doctrine valide | OK - doctrine:schema:validate |
| Conteneur Symfony valide | OK - lint:container |
| YAML valide | OK - lint:yaml (29 fichiers) |
| Twig valide | OK - lint:twig (3 fichiers) |
| Audit sécurité | OK - 0 vulnérabilité |
| /login accessible | OK - HTTP 200 |
| /admin protégé | OK - HTTP 302 (redirection vers login) |

### 11. Corrections post-commit

**Dépréciations corrigées :**
- Suppression de `use_savepoints` dans `doctrine.yaml` (implicite avec DBAL 4)
- Suppression de `report_fields_where_declared` dans `doctrine.yaml` (implicite avec ORM 3)
- Remplacement de `Vich\UploaderBundle\Mapping\Annotation` par `Vich\UploaderBundle\Mapping\Attribute` dans `Article.php`

**Locale et traductions :**
- Passage de la locale par défaut de `en` à `fr` dans `config/packages/translation.yaml`
- Création de `translations/messages.fr.yaml` avec tous les labels EasyAdmin (dashboard, menus, champs des 3 entités) pour supprimer les erreurs de traduction

## Accès admin

```
URL : http://localhost:8080/admin
Email : admin@portfolio.local
Mot de passe : admin123!
```
