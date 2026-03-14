# Notifications email automatiques

> Date : 17 février 2026

## Contexte

Ajout de notifications email pour informer l'administrateur des événements importants (inscriptions, activations, commentaires) et les utilisateurs de l'approbation de leurs commentaires.

Toutes les notifications utilisent l'adresse `contact@alpha1.michaeljpitz.com` comme expéditeur, domaine vérifié dans Mailjet.

## Notifications ajoutées

### 1. Notification admin — Nouvelle inscription

- **Déclencheur** : Un utilisateur soumet le formulaire d'inscription
- **Destinataire** : `contact@alpha1.michaeljpitz.com`
- **Contenu** : Nom d'utilisateur, email, date d'inscription
- **Fichiers** : `RegistrationController::register()`, `templates/email/new_user_notification.html.twig`

### 2. Notification admin — Compte activé

- **Déclencheur** : Un utilisateur clique sur le lien d'activation dans son email
- **Destinataire** : `contact@alpha1.michaeljpitz.com`
- **Contenu** : Nom d'utilisateur, email, date d'activation
- **Fichiers** : `RegistrationController::activate()`, `templates/email/user_activated_notification.html.twig`

### 3. Notification admin — Nouveau commentaire

- **Déclencheur** : Un utilisateur publie un commentaire sur un article
- **Destinataire** : `contact@alpha1.michaeljpitz.com`
- **Contenu** : Nom d'utilisateur, email, titre de l'article (avec lien), contenu du commentaire, date
- **Fichiers** : `ArticleController::show()`, `templates/email/new_comment_notification.html.twig`

### 4. Notification utilisateur — Commentaire approuvé

- **Déclencheur** : L'administrateur coche "Approuvé" dans EasyAdmin
- **Destinataire** : L'email de l'utilisateur auteur du commentaire
- **Contenu** : Nom d'utilisateur, titre de l'article (avec lien), contenu du commentaire
- **Implémentation** : Doctrine listener `postUpdate` qui détecte le passage de `isApproved` de `false` à `true`
- **Fichiers** : `src/EventSubscriber/CommentApprovedSubscriber.php`, `templates/email/comment_approved.html.twig`

## Fichiers créés

| Fichier | Rôle |
|---------|------|
| `src/EventSubscriber/CommentApprovedSubscriber.php` | Doctrine listener pour l'approbation de commentaire |
| `templates/email/new_user_notification.html.twig` | Template notification inscription |
| `templates/email/user_activated_notification.html.twig` | Template notification activation |
| `templates/email/new_comment_notification.html.twig` | Template notification commentaire |
| `templates/email/comment_approved.html.twig` | Template notification approbation |

## Fichiers modifiés

| Fichier | Modification |
|---------|-------------|
| `src/Controller/RegistrationController.php` | Ajout envoi notification admin à l'inscription et à l'activation |
| `src/Controller/ArticleController.php` | Ajout envoi notification admin lors d'un commentaire |

## Charte visuelle des emails

Chaque type de notification utilise une couleur distinctive :

| Notification | Couleur | Code |
|-------------|---------|------|
| Inscription | Bleu | `#2563eb` |
| Activation | Vert | `#16a34a` |
| Commentaire | Orange | `#f59e0b` |
| Approbation | Vert | `#16a34a` |
