# Phase 4.1 - Profil public et commentaires administrateur

Date : 2026-02-05

## Objectifs

1. Permettre aux administrateurs de publier des commentaires sans modération
2. Créer un profil public accessible à tous les visiteurs
3. Ajouter des liens vers les profils des auteurs de commentaires

## Fonctionnalités implémentées

### 1. Approbation automatique des commentaires administrateur

Les commentaires postés par un utilisateur ayant le rôle `ROLE_ADMIN` sont automatiquement approuvés et visibles immédiatement.

**Fichier modifié :** `src/Controller/ArticleController.php`

```php
if ($commentForm->isSubmitted() && $commentForm->isValid()) {
    $comment->setUser($user);
    $comment->setArticle($article);

    // Les commentaires des administrateurs sont automatiquement approuvés
    if ($this->isGranted('ROLE_ADMIN')) {
        $comment->setIsApproved(true);
    }

    $entityManager->persist($comment);
    $entityManager->flush();

    if ($this->isGranted('ROLE_ADMIN')) {
        $this->addFlash('success', 'Votre commentaire a été publié.');
    } else {
        $this->addFlash('success', 'Votre commentaire a été soumis et sera visible après validation par un administrateur.');
    }

    return $this->redirectToRoute('app_article_show', ['slug' => $article->getSlug()]);
}
```

**Comportement :**
- Admin : commentaire publié immédiatement avec message "Votre commentaire a été publié."
- Utilisateur normal : commentaire en attente avec message "Votre commentaire a été soumis et sera visible après validation par un administrateur."

### 2. Profil public des utilisateurs

Un profil public est accessible pour chaque utilisateur actif, permettant aux visiteurs de voir leurs informations publiques.

**Route :** `/utilisateur/{userName}`

**Fichiers créés :**
- `src/Controller/PublicProfileController.php`
- `templates/public_profile/show.html.twig`

**Fichier modifié :**
- `src/Repository/CommentRepository.php` (ajout de `findApprovedByUser()`)

#### Contrôleur PublicProfileController

```php
#[Route('/utilisateur/{userName}', name: 'app_public_profile')]
public function show(
    #[MapEntity(mapping: ['userName' => 'userName'])] User $user,
    CommentRepository $commentRepository,
): Response {
    // Vérifier que l'utilisateur a un compte actif (status = 1)
    if ($user->getStatus() !== 1) {
        throw $this->createNotFoundException('Utilisateur introuvable.');
    }

    // Récupérer les commentaires approuvés de l'utilisateur
    $approvedComments = $commentRepository->findApprovedByUser($user);

    return $this->render('public_profile/show.html.twig', [
        'user' => $user,
        'comments' => $approvedComments,
    ]);
}
```

#### Informations affichées sur le profil public

| Champ | Description |
|-------|-------------|
| Avatar | Image de profil (ou initiale si absente) |
| Nom d'utilisateur | `userName` |
| Date d'inscription | `createdAt` formatée en français |
| Biographie | `biography` (si renseignée) |
| Liens externes | `externalLink1`, `externalLink2`, `externalLink3` (cliquables, avec extraction du domaine) |
| Commentaires | Liste des commentaires approuvés avec lien vers l'article |

#### Sécurité

- Seuls les utilisateurs avec `status = 1` (actifs) sont accessibles
- Seuls les commentaires approuvés (`isApproved = true`) sont affichés
- Les liens externes s'ouvrent dans un nouvel onglet avec `rel="noopener noreferrer"`

#### Méthode findApprovedByUser

```php
/** @return Comment[] */
public function findApprovedByUser(User $user, int $limit = 20): array
{
    return $this->createQueryBuilder('c')
        ->andWhere('c.user = :user')
        ->andWhere('c.isApproved = true')
        ->setParameter('user', $user)
        ->orderBy('c.createdAt', 'DESC')
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult();
}
```

### 3. Liens vers les profils dans les commentaires

Dans la section commentaires des articles, le nom de l'auteur est maintenant un lien cliquable vers son profil public.

**Fichier modifié :** `templates/article/show.html.twig`

```twig
<a href="{{ path('app_public_profile', {userName: comment.user.userName}) }}"
   class="font-medium text-[var(--accent)] hover:text-[var(--accent-hover)]">
    {{ comment.user.userName }}
</a>
```

### 4. Dossier uploads/avatars

Le dossier `public/uploads/avatars/` doit exister pour l'upload des avatars utilisateurs.

**Création manuelle si nécessaire :**
```bash
mkdir -p public/uploads/avatars
chmod 775 public/uploads/avatars
```

**Dans Docker :**
```bash
docker exec cv-mikhawa-php-1 mkdir -p /var/www/html/public/uploads/avatars
docker exec cv-mikhawa-php-1 chmod 777 /var/www/html/public/uploads/avatars
```

## Tri des commentaires

Les commentaires sont triés par date descendante (plus récents en premier) via `orderBy('c.createdAt', 'DESC')` dans les méthodes :
- `findByArticle()` - commentaires d'un article
- `findByUser()` - commentaires d'un utilisateur (page profil privé)
- `findApprovedByUser()` - commentaires approuvés d'un utilisateur (profil public)

## Tests effectués

| Test | Résultat |
|------|----------|
| Commentaire admin auto-approuvé | ✓ |
| Message flash différencié admin/user | ✓ |
| Page profil public `/utilisateur/admin` | 200 OK |
| Utilisateur inexistant | 404 |
| Utilisateur inactif | 404 |
| Affichage avatar sur profil public | ✓ |
| Liens externes cliquables | ✓ |
| Liens vers profils dans commentaires | ✓ |
| Upload avatar | ✓ |

## Service ImageResizer

**Fichier :** `src/Service/ImageResizer.php`

Service de redimensionnement d'images avec GD pour les uploads dans l'éditeur Trix.

### Fonctionnalités

- Redimensionne les images dépassant 1200px de largeur
- Préserve le ratio d'aspect
- Corrige automatiquement l'orientation EXIF (photos de smartphones)
- Préserve la transparence (PNG, WebP, GIF)
- Qualité JPEG/WebP : 90%

### Orientations EXIF supportées

| Orientation | Action |
|-------------|--------|
| 1 | Normal (pas de rotation) |
| 2 | Flip horizontal |
| 3 | Rotation 180° |
| 4 | Flip vertical |
| 5 | Rotation 90° CW + flip horizontal |
| 6 | Rotation 90° CW (portrait) |
| 7 | Rotation 90° CCW + flip horizontal |
| 8 | Rotation 90° CCW (portrait) |

### TrixUploadController modifié

**Fichier :** `src/Controller/Admin/TrixUploadController.php`

Utilise maintenant le service `ImageResizer` pour redimensionner les images uploadées via l'éditeur WYSIWYG.

---

## Lightbox pour les images des articles

**Fichier :** `assets/controllers/lightbox_controller.js`

Contrôleur Stimulus pour afficher les images en popup avec navigation.

### Fonctionnalités

- Clic sur une image : ouvre la lightbox
- Navigation : boutons ←/→ + flèches clavier
- Fermeture : bouton ×, clic en dehors, touche Escape
- Compteur : affiche "1 / 3" si plusieurs images
- **Zoom** : bouton pour voir l'image en taille réelle (avec scroll)
- **Réduire** : le bouton zoom devient "réduire" en mode plein écran
- Animation fluide d'ouverture/fermeture
- Responsive mobile/desktop

### Contrôles clavier

| Touche | Action |
|--------|--------|
| Escape | Réduire (si agrandi) ou Fermer |
| ← | Image précédente |
| → | Image suivante |

### Template modifié

**Fichier :** `templates/article/show.html.twig`

Ajout du contrôleur `lightbox` sur le conteneur du contenu de l'article :
```twig
data-controller="external-link lightbox"
```

---

## Traductions ajoutées

**Fichier :** `translations/messages.fr.yaml`

```yaml
# Formulaire de commentaire
"Écrivez votre commentaire...": "Écrivez votre commentaire..."

# Formulaire de contact
"Nom": "Nom"
"Votre nom": "Votre nom"
"votre@email.com": "votre@email.com"
"Message": "Message"
"Votre message...": "Votre message..."

# Formulaire de profil
"Présentation": "Présentation"
"Parlez-nous de vous en quelques mots...": "Parlez-nous de vous en quelques mots..."
"Lien externe 1": "Lien externe 1"
"Lien externe 2": "Lien externe 2"
"Lien externe 3": "Lien externe 3"
"https://exemple.com": "https://exemple.com"

# Formulaire d'inscription
"Confirmer le mot de passe": "Confirmer le mot de passe"
```

---

## Commits associés

- `4c53ddf` - admin'comment aitou published and comment order by date DESC
- `b4a6ce1` - Ajouter le profil public des utilisateurs
- `a41f0a7` - Ajouter la documentation phase 4.1
