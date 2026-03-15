# 015 - Correction du profil utilisateur (avatar + sauvegarde)

**Date** : 2026-03-15
**Modèle** : Claude Sonnet
**Branche** : `dev/create-mail-system-for-dev`

---

## Problèmes identifiés et corrections

### 1. Dossier `public/uploads/avatars/` absent

**Symptôme** : L'image de profil ne s'affichait pas.
**Cause** : Le dossier `public/uploads/avatars/` n'existait pas sur le système de fichiers. Le `.gitignore` ignorait tout `/public/uploads/`, donc le dossier n'était jamais créé au clonage. Le `file_put_contents()` du contrôleur échouait silencieusement.
**Correction** :
- Création de `public/uploads/avatars/.gitkeep`
- Mise à jour de `.gitignore` : remplacement de `/public/uploads/` par des règles fines qui ignorent le contenu mais conservent les `.gitkeep` des trois sous-dossiers (`avatars/`, `articles/`, `pages/`)
- Ajout d'un `mkdir()` de sécurité dans `ProfileController::processAvatarUpload()` si le dossier venait à manquer

### 2. Permissions refusées sur `avatars/`

**Symptôme** : `Warning: file_put_contents(...): Failed to open stream: Permission denied`
**Cause** : Le dossier `avatars/` avait été créé par l'utilisateur host (`mikhawa:mikhawa`, permissions `755`). Le process PHP dans Docker (`www-data`) ne pouvait pas y écrire. Les autres dossiers (`articles/`, `pages/`) sont `root:root 777`.
**Correction** : `chmod 777 public/uploads/avatars/`

### 3. Formulaire de profil non sauvegardé (422 Unprocessable Entity)

**Symptôme** : Soumettre le formulaire de profil (biographie, liens) retournait un 422 sans message d'erreur visible.
**Cause** : Symfony valide l'entité entière (`User`) à la soumission du formulaire, pas seulement les champs présents. Le `userName` de l'utilisateur de test (`Michael.J.Pitz`) contient des points (`.`) qui échouaient la contrainte `#[Assert\Regex(pattern: '/^[a-zA-Z0-9_]+$/')]`. Aucune erreur n'était affichée car le template n'appelait pas `form_errors(form)` pour les erreurs racine.
**Corrections** :
- Ajout de `groups: ['Profile']` aux contraintes `biography` et `externalLink1/2/3` dans `User`
- Ajout de `'validation_groups' => ['Profile']` dans `ProfileType` : le formulaire ne valide plus que les champs qui le concernent
- Ajout du `.` dans la regex `userName` : `/^[a-zA-Z0-9_.]+$/` (le point est un caractère légitime dans un pseudo)
- Mise à jour du message d'erreur correspondant

---

## Fichiers modifiés

| Fichier | Modification |
|---------|-------------|
| `.gitignore` | Règles fines pour `public/uploads/` |
| `public/uploads/avatars/.gitkeep` | Création du dossier versionné |
| `src/Controller/ProfileController.php` | `mkdir()` de sécurité |
| `src/Entity/User.php` | Groupe `Profile` sur biography/externalLink + `.` dans regex userName |
| `src/Form/ProfileType.php` | `validation_groups: ['Profile']` |
