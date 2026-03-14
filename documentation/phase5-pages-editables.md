# Phase 5 : Pages CV et RGPD éditables depuis l'administration

> Date : 6 février 2026

## Contexte

Les pages CV et RGPD étaient statiques (contenu codé en dur dans les templates Twig). L'objectif est de pouvoir les modifier depuis l'interface d'administration EasyAdmin avec un titre, une image, un contenu riche (éditeur Trix identique aux articles), et la possibilité d'insérer des liens vers des fichiers téléchargeables (PDF, etc.).

## Architecture choisie

Création d'une entité **Page** générique identifiée par un `slug` unique (`cv`, `rgpd`). Cela permet de réutiliser la même logique pour toute page statique future. Les templates conservent un fallback statique si la page n'existe pas encore en base.

## Actions réalisées

### 1. Entité Page

**`src/Entity/Page.php`** — Nouvelle entité avec :
- `id` (int, auto-increment, unsigned)
- `title` (VARCHAR 255, NotBlank, Length 3-255)
- `slug` (VARCHAR 154, unique, NotBlank, Length 2-154)
- `content` (LONGTEXT, nullable) — contenu HTML Trix
- `featuredImage` (VARCHAR 255, nullable) — nom du fichier image
- `imageFile` (File, non mappé) — champ VichUploader (`page_image`)
- `updatedAt` (DateTimeImmutable, nullable) — requis par VichUploader
- `createdAt` (DateTimeImmutable) — via PrePersist

Attributs : `#[Vich\Uploadable]`, `#[ORM\HasLifecycleCallbacks]`, mêmes patterns que Article.

**`src/Repository/PageRepository.php`** — Repository avec méthode `findOneBySlug(string $slug): ?Page`.

### 2. Configuration VichUploader

Ajout du mapping `page_image` dans `config/packages/vich_uploader.yaml` :
```yaml
page_image:
    uri_prefix: /uploads/pages
    upload_destination: '%kernel.project_dir%/public/uploads/pages'
    namer: Vich\UploaderBundle\Naming\UniqidNamer
```

### 3. CRUD EasyAdmin

**`src/Controller/Admin/PageCrudController.php`** — CRUD avec :
- `TextField` pour le titre
- `SlugField` auto-généré depuis le titre
- `TextEditorField` avec `data-controller: trix-upload` (éditeur Trix + upload)
- `ImageField` pour l'image mise en avant (`/uploads/pages`)
- `DateTimeField` pour `createdAt` et `updatedAt` (masqués en formulaire)

**`src/Controller/Admin/DashboardController.php`** — Ajout du menu « Pages » (`fa fa-file-alt`).

### 4. Contrôleurs front modifiés

**`src/Controller/CvController.php`** et **`src/Controller/RgpdController.php`** :
- Injection de `PageRepository`
- Recherche de la page par slug (`cv` ou `rgpd`)
- Passage de l'entité `page` au template

### 5. Templates avec fallback statique

**`templates/cv/index.html.twig`** et **`templates/rgpd/index.html.twig`** :
- Si `page` est définie et a du contenu : affichage dynamique (titre, image, contenu riche avec classes `prose`)
- Sinon : conservation du contenu statique actuel comme fallback

Le contenu dynamique utilise les mêmes classes CSS que `article/show.html.twig` (`prose prose-lg`, `data-controller="external-link lightbox"`).

### 6. Support des fichiers dans Trix

**`src/Controller/Admin/TrixUploadController.php`** — Types MIME supplémentaires :
- `application/pdf`
- `application/msword`
- `application/vnd.openxmlformats-officedocument.wordprocessingml.document`
- `application/vnd.oasis.opendocument.text`
- `application/zip`

Les fichiers non-image sont stockés sans redimensionnement (simple `move()`).

**`assets/controllers/trix_upload_controller.js`** — Nouveau bouton « Insérer un fichier » :
- Accept : `.pdf,.doc,.docx,.odt,.zip`
- Pour les fichiers non-image : insertion d'un lien HTML cliquable (`<a href="..." target="_blank">nom_du_fichier</a>`) au lieu d'un `<img>`

### 7. Migrations Doctrine

- **`migrations/Version20260206130201.php`** — Création de la table `page`
- **`migrations/Version20260206131000.php`** — Insertion des pages CV et RGPD avec leur contenu HTML initial (converti depuis les templates statiques)

### 8. Tests fonctionnels

**`tests/Functional/AdminPageEditTest.php`** — 2 tests :
- `testAdminCanAccessPageEditForm` : vérifie que l'admin peut accéder au formulaire d'édition d'une page (HTTP 200, formulaire présent)
- `testAdminCanAccessPageIndex` : vérifie que la liste des pages affiche CV et RGPD

**`tests/Functional/TestDatabaseTrait.php`** — Ajout de `TRUNCATE TABLE page` dans `cleanDatabase()`.

## Fichiers créés

| Fichier | Description |
|---------|-------------|
| `src/Entity/Page.php` | Entité Page |
| `src/Repository/PageRepository.php` | Repository avec `findOneBySlug()` |
| `src/Controller/Admin/PageCrudController.php` | CRUD EasyAdmin |
| `migrations/Version20260206130201.php` | Création table `page` |
| `migrations/Version20260206131000.php` | Données initiales CV et RGPD |
| `tests/Functional/AdminPageEditTest.php` | Tests fonctionnels admin |
| `public/uploads/pages/.gitkeep` | Dossier d'upload |

## Fichiers modifiés

| Fichier | Modification |
|---------|-------------|
| `config/packages/vich_uploader.yaml` | Mapping `page_image` |
| `src/Controller/Admin/DashboardController.php` | Menu « Pages » |
| `src/Controller/CvController.php` | Chargement depuis la BDD |
| `src/Controller/RgpdController.php` | Chargement depuis la BDD |
| `templates/cv/index.html.twig` | Contenu dynamique + fallback |
| `templates/rgpd/index.html.twig` | Contenu dynamique + fallback |
| `src/Controller/Admin/TrixUploadController.php` | Support fichiers PDF/DOC/ZIP |
| `assets/controllers/trix_upload_controller.js` | Bouton « Insérer un fichier » |
| `tests/Functional/TestDatabaseTrait.php` | Nettoyage table `page` |

## Vérifications

| Vérification | Résultat |
|-------------|----------|
| `doctrine:schema:validate` | OK — mapping correct, schéma synchronisé |
| `lint:twig templates/` | OK — 21 templates valides |
| `lint:container` | OK — tous les services injectés correctement |
| Tests PHPUnit | OK — 140 tests, 270 assertions, 0 erreurs |
| Page `/cv` front | OK — contenu dynamique affiché |
| Page `/rgpd` front | OK — contenu dynamique affiché |
| Admin `/admin/page` | OK — liste avec CV et RGPD |
| Admin `/admin/page/{id}/edit` | OK — formulaire d'édition fonctionnel |

## Problème rencontré : permissions Docker

Le dossier `public/uploads/pages/` créé depuis l'hôte WSL avait les permissions `755` (uid 1000). PHP-FPM dans le conteneur Docker (uid `www-data`) ne pouvait pas y écrire. Résolu par `chmod 777` dans le conteneur. Même problème déjà rencontré pour `public/uploads/articles/content/` (cf. Décision 11).
