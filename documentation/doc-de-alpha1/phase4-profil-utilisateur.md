# Phase 4 - Page de profil utilisateur

## Objectif

Créer une page de profil pour les utilisateurs connectés avec :
- Menu déroulant dans la navbar (Profil / Déconnexion)
- Affichage des informations du compte (lecture seule)
- Modification de l'avatar avec outil de recadrage (320x320 px)
- Ajout d'une biographie (500 caractères max)
- 3 liens vers des sites externes
- Liste des derniers commentaires avec leur statut

## Fichiers créés

### 1. `src/Controller/ProfileController.php`
Contrôleur avec route `/profil` (GET|POST) :
- Protégé par `#[IsGranted('ROLE_USER')]`
- Gère le formulaire de modification du profil
- Traitement de l'avatar en base64 (depuis l'outil de crop JS)
- Récupération des commentaires de l'utilisateur

### 2. `src/Form/ProfileType.php`
Formulaire Symfony avec :
- `avatarFile` (FileType) : sélection de l'image, non mappé
- `croppedAvatarData` (HiddenType) : données base64 du crop, non mappé
- `biography` (TextareaType) : 500 caractères max
- `externalLink1`, `externalLink2`, `externalLink3` (UrlType) : liens externes
- Protection CSRF (`csrf_token_id: 'profile_form'`)

### 3. `assets/controllers/avatar_crop_controller.js`
Contrôleur Stimulus pour le recadrage d'image :
- Charge Cropper.js dynamiquement depuis `/js/cropper.min.js`
- Ratio 1:1, sortie 320x320 pixels
- Génère une image base64 en JPEG (qualité 90%)
- Modal de recadrage avec prévisualisation
- Limite d'upload : 10 Mo

### 4. `templates/profile/index.html.twig`
Template du profil avec :
- Section informations (lecture seule) : userName, email, date d'inscription
- Formulaire de modification avec outil de crop d'avatar
- Affichage des liens externes si renseignés
- Liste des commentaires avec statut (validé/en attente)
- Modal de recadrage d'image (styles inline pour compatibilité)

### 5. `public/js/cropper.min.js`
Bibliothèque Cropper.js v1.6.2 en local (évite les problèmes CSP).

### 6. `public/css/cropper.min.css`
Styles CSS de Cropper.js v1.6.2 en local.

## Fichiers modifiés

### 1. `src/Entity/User.php`
Nouveaux champs ajoutés :

| Champ | Type | Description |
|-------|------|-------------|
| `avatarFile` | File (Vich) | Fichier uploadé (non persisté) |
| `avatarName` | string(255), nullable | Nom du fichier stocké |
| `biography` | string(500), nullable | Présentation de l'utilisateur |
| `externalLink1` | string(255), nullable | Lien externe 1 |
| `externalLink2` | string(255), nullable | Lien externe 2 |
| `externalLink3` | string(255), nullable | Lien externe 3 |
| `updatedAt` | DateTimeImmutable, nullable | Pour VichUploader |

Validations :
- `#[Assert\Image]` sur avatarFile : JPEG, PNG, WebP, max 10 Mo
- `#[Assert\Length(max: 500)]` sur biography
- `#[Assert\Url]` sur les 3 liens externes

### 2. `config/packages/vich_uploader.yaml`
Ajout du mapping `user_avatar` :
```yaml
user_avatar:
    uri_prefix: /uploads/avatars
    upload_destination: '%kernel.project_dir%/public/uploads/avatars'
    namer: Vich\UploaderBundle\Naming\UniqidNamer
```

### 3. `config/services.yaml`
Configuration du `ProfileController` avec le chemin du dossier avatars.

### 4. `src/Repository/CommentRepository.php`
Ajout de la méthode `findByUser(User $user, int $limit = 10)` pour récupérer les commentaires d'un utilisateur.

### 5. `templates/components/Navbar.html.twig`
Modifications :
- **Desktop** : Le username devient un bouton avec menu déroulant (Profil / Déconnexion)
- **Mobile** : Ajout du lien "Profil" dans la section utilisateur
- Affichage de l'avatar miniature si présent

### 6. `assets/controllers/navbar_controller.js`
Ajout des targets et méthodes pour le dropdown utilisateur :
- `userDropdownContainer`, `userDropdown`
- `showUserDropdown()`, `scheduleHideUserDropdown()`

## Dossiers créés

- `public/uploads/avatars/` : stockage des avatars uploadés
- `public/js/` : fichiers JavaScript locaux (Cropper.js)
- `public/css/` : fichiers CSS locaux (Cropper.css)

## Migration Doctrine

Une migration a été générée et exécutée pour ajouter les nouveaux champs à la table `user` :
- `avatar_name` VARCHAR(255) NULL
- `biography` VARCHAR(500) NULL
- `external_link1` VARCHAR(255) NULL
- `external_link2` VARCHAR(255) NULL
- `external_link3` VARCHAR(255) NULL
- `updated_at` DATETIME NULL

## Cropper.js

### Installation locale
Les fichiers Cropper.js v1.6.2 sont stockés localement pour éviter les problèmes de Content Security Policy (CSP) :
- `/public/js/cropper.min.js` : bibliothèque JavaScript
- `/public/css/cropper.min.css` : styles CSS

### Chargement dynamique
Le contrôleur Stimulus charge Cropper.js dynamiquement uniquement sur la page de profil, évitant ainsi de l'inclure sur toutes les pages.

## Limites d'upload

| Paramètre | Valeur |
|-----------|--------|
| Taille max fichier original | 10 Mo |
| Taille image finale | 320x320 pixels |
| Format de sortie | JPEG (qualité 90%) |
| Types acceptés | JPEG, PNG, WebP |

## Sécurité

- [x] Protection par rôle : `#[IsGranted('ROLE_USER')]` sur la route `/profil`
- [x] Protection CSRF sur le formulaire (`csrf_token_id: 'profile_form'`)
- [x] Validation des URLs avec `#[Assert\Url]`
- [x] Validation des images : type MIME, taille max (10 Mo)
- [x] Validation base64 avant décodage (vérification du format, limite de taille)
- [x] Liens externes ouverts avec `target="_blank" rel="noopener noreferrer"`
- [x] Nettoyage de l'ancien avatar avant remplacement
- [x] Échappement Twig par défaut pour la biographie
- [x] Ressources Cropper.js en local (pas de CDN externe)

## Permissions

Le dossier `public/uploads/avatars/` doit avoir les bonnes permissions :
```bash
docker compose exec php chown -R www-data:www-data /var/www/html/public/uploads/avatars
docker compose exec php chmod -R 775 /var/www/html/public/uploads/avatars
```

## Tests

### Test manuel
1. Se connecter avec un compte utilisateur
2. Cliquer sur le nom d'utilisateur dans la navbar
3. Vérifier l'apparition du menu déroulant (Profil / Déconnexion)
4. Cliquer sur "Profil"
5. Vérifier l'affichage des informations (userName, email, date)
6. Tester l'upload et le recadrage d'avatar :
   - Cliquer sur "Changer l'image"
   - Sélectionner une image (jusqu'à 10 Mo)
   - Recadrer dans la modal
   - Valider et soumettre le formulaire
7. Modifier la biographie et les liens externes
8. Vérifier l'affichage des commentaires

### Vérifications
```bash
php bin/console lint:twig templates/
php bin/console lint:container
php bin/console lint:yaml config/
php bin/console doctrine:schema:validate
php bin/console debug:router | grep profile
vendor/bin/phpstan analyse src --level=8
```

## Routes

| Route | Méthode | URL | Description |
|-------|---------|-----|-------------|
| `app_profile` | GET, POST | `/profil` | Page de profil utilisateur |

## Architecture

```
src/
├── Controller/
│   └── ProfileController.php     # Nouveau
├── Entity/
│   └── User.php                  # Modifié (nouveaux champs)
├── Form/
│   └── ProfileType.php           # Nouveau
└── Repository/
    └── CommentRepository.php     # Modifié (findByUser)

templates/
├── components/
│   └── Navbar.html.twig          # Modifié (dropdown utilisateur)
└── profile/
    └── index.html.twig           # Nouveau

assets/controllers/
├── avatar_crop_controller.js     # Nouveau
└── navbar_controller.js          # Modifié (dropdown utilisateur)

config/
├── packages/
│   └── vich_uploader.yaml        # Modifié (mapping user_avatar)
└── services.yaml                 # Modifié (ProfileController config)

public/
├── css/
│   └── cropper.min.css           # Nouveau (Cropper.js CSS)
├── js/
│   └── cropper.min.js            # Nouveau (Cropper.js)
└── uploads/
    └── avatars/                  # Nouveau dossier
```

## Points d'attention

1. **Taille des avatars** : Les images sont redimensionnées côté client à 320x320 pixels avant l'upload, ce qui réduit la bande passante.

2. **Format base64** : L'image est envoyée en base64 dans un champ caché. Le contrôleur la décode et la sauvegarde en fichier.

3. **Cropper.js local** : Pour respecter la CSP du projet, Cropper.js est stocké localement dans `/public/js/` au lieu d'utiliser un CDN.

4. **Modal avec styles inline** : La modal utilise des styles inline pour garantir la compatibilité avec Tailwind CSS et éviter les conflits de styles.

5. **Responsive** : La navbar desktop utilise un hover dropdown, tandis que la version mobile affiche directement les liens.
