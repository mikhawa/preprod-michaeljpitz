# Remplacement de Trix par SunEditor

**Date :** 2026-02-13
**Branche :** v2.1.0

## Contexte

L'éditeur WYSIWYG Trix, intégré automatiquement par EasyAdmin via `TextEditorField`, offrait des fonctionnalités limitées (pas de tableaux, pas de couleurs, toolbar basique). Il a été remplacé par **SunEditor** (v2.47.8), un éditeur plus complet en vanilla JavaScript.

## Stratégie adoptée

EasyAdmin charge Trix automatiquement avec `TextEditorField`. Pour le contourner, on utilise `TextareaField` avec `renderAsHtml()` et on initialise SunEditor via un contrôleur Stimulus dédié.

## Fichiers créés

| Fichier | Rôle |
|---------|------|
| `assets/controllers/suneditor_controller.js` | Contrôleur Stimulus : initialise SunEditor, gère l'upload d'images, synchronise le contenu vers le textarea |
| `src/Controller/Admin/EditorUploadController.php` | Endpoint d'upload de fichiers/images (renommé depuis TrixUploadController) |
| `tests/Functional/EditorUploadControllerTest.php` | Tests fonctionnels de l'endpoint d'upload (renommé depuis TrixUploadControllerTest) |

## Fichiers modifiés

| Fichier | Modification |
|---------|-------------|
| `importmap.php` | Ajout de `suneditor`, `suneditor/src/plugins`, `suneditor/dist/css/suneditor.min.css` |
| `src/Controller/Admin/ArticleCrudController.php` | `TextEditorField` remplacé par `TextareaField` + `renderAsHtml()` + `data-controller: suneditor` |
| `src/Controller/Admin/PageCrudController.php` | Même modification |
| `assets/styles/app.css` | Suppression des styles Trix (`.trix-button-group--image-tools`, `.trix-button--icon-image`) |
| `config/packages/html_sanitizer.yaml` | Ajout des balises produites par SunEditor : `table`, `thead`, `tbody`, `tr`, `th`, `td`, `video`, `source`, `span`, `div`, `hr`, `sub`, `sup` |

## Fichiers supprimés

| Fichier | Raison |
|---------|--------|
| `src/Controller/Admin/TrixUploadController.php` | Remplacé par `EditorUploadController` |
| `tests/Functional/TrixUploadControllerTest.php` | Remplacé par `EditorUploadControllerTest` |
| `assets/controllers/trix_upload_controller.js` | Remplacé par `suneditor_controller.js` |

## Détails techniques

### Contrôleur Stimulus (`suneditor_controller.js`)

- **Plugins** : Import de tous les plugins SunEditor (`suneditor/src/plugins`) requis pour les boutons image, vidéo, tableau, lien, etc.
- **Toolbar** : undo/redo, police, taille, format bloc, gras/italique/souligné/barré/indice/exposant, couleurs, retrait, alignement, ligne horizontale, listes, tableaux, liens, images, vidéos, plein écran, blocs, code source HTML.
- **Upload d'images** : Géré via `onImageUploadBefore` qui envoie le fichier en POST vers `/admin/editor/upload` et retourne le format attendu par SunEditor (`{ result: [{ url, name, size }] }`).
- **Synchronisation** : Le contenu HTML est écrit dans le textarea original (celui avec le `name` Symfony) via `getContents(true)` à trois moments :
  1. `onChange` — à chaque modification de l'éditeur
  2. `onImageUpload` — après insertion d'image
  3. Événement `submit` du formulaire — filet de sécurité
- **Localisation** : Interface entièrement en français (toolbar, boîtes de dialogue, contrôleurs).
- **Accessibilité** : Attributs `aria-label`, `role="textbox"`, `aria-multiline="true"` sur la zone éditable.

### Route d'upload

| Avant | Après |
|-------|-------|
| `/admin/trix/upload` | `/admin/editor/upload` |
| `admin_trix_upload` | `admin_editor_upload` |
| `TrixUploadController` | `EditorUploadController` |

La logique d'upload reste identique : validation du type MIME, limite de 5 Mo, redimensionnement des images via `ImageResizer`, support des documents (PDF, DOC, DOCX, ODT, ZIP).

### Sanitizer HTML

Balises ajoutées pour supporter le HTML produit par SunEditor :

```yaml
table: ['class', 'style']
thead: []
tbody: []
tr: []
th: ['style', 'colspan', 'rowspan']
td: ['style', 'colspan', 'rowspan']
video: ['src', 'controls', 'width', 'height']
source: ['src', 'type']
span: ['style', 'class']
div: ['style', 'class']
hr: []
sub: []
sup: []
```

L'attribut `style` a aussi été ajouté sur `img` pour le redimensionnement dans l'éditeur.

## Vérification

- PHPStan niveau 8 : aucune erreur
- 140 tests PHPUnit : tous passent (270 assertions)
- Aucune référence résiduelle à Trix dans `src/`, `templates/`, `config/`, `tests/`
