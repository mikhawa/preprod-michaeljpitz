# 036 — Recadrage optionnel des images dans l'éditeur Suneditor

**Date** : 2026-06-14  
**Modèle** : Claude Sonnet  
**Branche** : dev/main

## Contexte

L'éditeur Suneditor permettait d'insérer une image en choisissant sa taille après insertion, mais il n'offrait aucune possibilité de recadrage. À chaque upload d'image, un dialog de choix est désormais présenté : l'utilisateur peut insérer l'image directement ou ouvrir un outil de recadrage avec sélection du ratio.

## Fichiers modifiés

- `assets/controllers/suneditor_controller.js` — ajout du flux de choix crop/direct et des méthodes associées

## Décisions techniques

### Interception de l'upload

`onImageUploadBefore` retourne `undefined` pour bloquer l'upload natif de Suneditor et reprendre la main. La méthode `_showCropChoice(file, uploadHandler)` est appelée à la place, avec le fichier sélectionné et le callback `uploadHandler` fourni par Suneditor.

### Dialog de choix (première étape)

Un `<dialog>` natif s'affiche avec deux options :

- **Insérer directement** → appelle `_uploadFile(file, uploadHandler)`, qui reproduit l'ancien comportement (POST vers `/admin/editor/upload`)
- **Recadrer l'image** → appelle `_openCropModal(file, uploadHandler)`

Le bouton **Annuler** appelle `uploadHandler('Upload annulé.')` pour libérer l'état interne de Suneditor.

### Chargement lazy de Cropper.js

`_loadCropperAssets()` injecte dynamiquement dans le `<head>` :

- `/css/cropper.min.css` (détecté via `link[data-cropper-css]`)
- `/js/cropper.min.js` (détecté via `window.Cropper`)

Les deux fichiers sont déjà présents dans `public/` (utilisés par `avatar_crop_controller.js`). Aucun ajout d'asset nécessaire.

### Modal de recadrage (Cropper.js)

`_openCropModal(file, uploadHandler)` :

1. Charge les assets Cropper.js via `await _loadCropperAssets()`
2. Lit le fichier sélectionné via `FileReader.readAsDataURL()` pour afficher un aperçu local (sans upload préalable)
3. Ouvre un `<dialog>` avec :
   - Boutons de ratio dans l'en-tête : **Libre** (NaN), **16:9**, **4:3**, **1:1**
   - Zone d'image avec Cropper.js initialisé (`aspectRatio: NaN`, `viewMode: 1`, `dragMode: 'move'`)
   - Boutons **Annuler** et **Valider le recadrage** dans le pied

Le ratio actif est mis en évidence visuellement (fond `--accent`). Un clic sur un autre bouton appelle `cropper.setAspectRatio(ratio)`.

### Upload du blob recadré

Au clic sur **Valider** :

1. `cropper.getCroppedCanvas({ maxWidth: 1200 })` génère un canvas (redimensionné à 1200 px max)
2. `canvas.toBlob(callback, 'image/jpeg', 0.92)` encode en JPEG qualité 92 %
3. `_uploadBlob(blob, originalName, uploadHandler)` envoie le blob via POST `/admin/editor/upload` avec le nom `nomoriginal_crop.jpg`
4. L'`ImageResizer` PHP côté serveur applique son redimensionnement habituel (1200 px max)
5. `uploadHandler({ result: [...] })` insère l'image dans l'éditeur

### Nettoyage

`cropper.destroy()` est appelé dans tous les cas de sortie (Valider, Annuler, fermeture du dialog via `close`). L'instance `dialog` est retirée du DOM avec `dialog.remove()`.

### Sécurité

- `_escapeHtml()` est utilisé pour afficher le nom du fichier dans les dialogs (protection XSS)
- L'endpoint `/admin/editor/upload` est protégé par `#[IsGranted('ROLE_ADMIN')]` et la vérification `X-Requested-With: XMLHttpRequest`
- Le contenu du canvas est généré côté client uniquement à partir du fichier sélectionné par l'utilisateur lui-même

## Flux complet

```
Clic bouton "image" Suneditor
    → Dialog natif Suneditor (sélection fichier / URL)
        → Sélection fichier
            → onImageUploadBefore intercepté
                → Dialog de choix
                    ├─ Insérer directement
                    │       → POST /admin/editor/upload (fichier original)
                    │       → uploadHandler({ result }) → image insérée
                    └─ Recadrer l'image
                            → FileReader (aperçu local)
                            → Modal Cropper.js (ratio : Libre / 16:9 / 4:3 / 1:1)
                            → Valider → canvas.toBlob() JPEG 92%
                            → POST /admin/editor/upload (blob _crop.jpg)
                            → uploadHandler({ result }) → image insérée
```

## Points de sécurité vérifiés

- Noms de fichiers échappés via `_escapeHtml()` avant injection dans le HTML des dialogs
- Upload protégé par `ROLE_ADMIN` + header `X-Requested-With`
- Aucune requête serveur avant validation du crop (lecture locale via FileReader)
- `cropper.destroy()` systématique pour éviter les fuites mémoire
