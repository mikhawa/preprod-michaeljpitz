# Phase 3.1 : Commentaires, Notes et Inscription

> Date : 31 janvier 2026

## Actions réalisées

### 1. Nouvelles entités

**Comment** (`src/Entity/Comment.php`) :
- Champs : id, content (TEXT, min 2 caractères), createdAt (DateTimeImmutable)
- Relations : ManyToOne vers User, ManyToOne vers Article
- Lifecycle callback : PrePersist pour createdAt automatique
- Suppression en cascade depuis Article (orphanRemoval)

**Rating** (`src/Entity/Rating.php`) :
- Champs : id, rating (entier 1-5, validé par Assert\Range), createdAt
- Relations : ManyToOne vers User, ManyToOne vers Article
- Contrainte d'unicité : un utilisateur ne peut noter un article qu'une seule fois (`UniqueConstraint` sur user+article)
- Lifecycle callback : PrePersist pour createdAt automatique

### 2. Repositories enrichis

**CommentRepository** (`src/Repository/CommentRepository.php`) :
- ServiceEntityRepository de base

**RatingRepository** (`src/Repository/RatingRepository.php`) :
- ServiceEntityRepository de base

### 3. Formulaires

**CommentType** (`src/Form/CommentType.php`) :
- Formulaire pour les commentaires avec champ TextareaType
- Protection CSRF activée

**RegistrationType** (`src/Form/RegistrationType.php`) :
- Formulaire d'inscription utilisateur
- Champs : email, userName, mot de passe (avec confirmation)

### 4. Inscription des utilisateurs

**RegistrationController** (`src/Controller/RegistrationController.php`) :
- Route `GET/POST /register` (app_register)
- Création de compte utilisateur avec hashage du mot de passe
- Rôle par défaut : ROLE_USER
- Redirection après inscription

**Template** `templates/security/register.html.twig` :
- Formulaire d'inscription stylisé avec Tailwind
- Lien vers la page de connexion

### 5. Fonctionnalité commentaires

Intégration dans `ArticleController` et `article/show.html.twig` :
- Formulaire de commentaire visible pour les utilisateurs connectés
- Affichage des commentaires existants sous l'article
- Persistance via Doctrine

### 6. Notation par étoiles

**Stimulus controller** `assets/controllers/star_rating_controller.js` :
- Interface interactive de notation 1 à 5 étoiles
- Requête POST vers `/articles/{slug}/rate`
- Réservé aux utilisateurs authentifiés

**Route** dans ArticleController :
- `POST /articles/{slug}/rate` (app_article_rate)
- Vérifie l'authentification (`#[IsGranted]`)
- Crée ou met à jour la note de l'utilisateur
- Calcul et affichage de la moyenne des notes

### 7. EasyAdmin enrichi

- **CommentCrudController** : CRUD pour gérer les commentaires depuis l'admin
- **RatingCrudController** : CRUD pour gérer les notes depuis l'admin
- Menus du dashboard mis à jour avec les nouvelles entrées

### 8. Corrections (commit 271ee87)

- Utilisation de `#[MapEntity]` pour la résolution du paramètre `slug` dans les routes article
- Correction de la notation par étoiles (affichage et persistance)
- Redirection vers la page de login corrigée (retour vers la page précédente après connexion)

## Migrations

- Nouvelles tables : `comment`, `rating`
- Clés étrangères vers `user` et `article`
- Index sur les relations
- Contrainte d'unicité sur `rating` (user_id + article_id)

## Vérifications Phase 3.1

| Point | Statut |
|-------|--------|
| Commentaires persistés en base | OK |
| Notation 1-5 étoiles fonctionnelle | OK |
| Unicité de la note par utilisateur/article | OK |
| Inscription utilisateur fonctionnelle | OK |
| CSRF sur formulaires commentaire et inscription | OK |
| Validation des données (Assert) | OK |
| Protection IsGranted sur la notation | OK |

## Fichiers créés/modifiés

```
# Nouveaux fichiers
src/Entity/Comment.php
src/Entity/Rating.php
src/Repository/CommentRepository.php
src/Repository/RatingRepository.php
src/Form/CommentType.php
src/Form/RegistrationType.php
src/Controller/RegistrationController.php
src/Controller/Admin/CommentCrudController.php
src/Controller/Admin/RatingCrudController.php
templates/security/register.html.twig
assets/controllers/star_rating_controller.js

# Fichiers modifiés
src/Controller/ArticleController.php (commentaires + notation)
src/Controller/Admin/DashboardController.php (menus commentaires/notes)
templates/article/show.html.twig (commentaires + étoiles)
```
