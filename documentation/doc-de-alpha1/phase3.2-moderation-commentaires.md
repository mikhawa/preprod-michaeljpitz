# Phase 3.2 : Modération des commentaires

> Date : 1 février 2026

## Objectif

Les commentaires postés par les utilisateurs doivent être validés par l'administrateur avant d'être visibles publiquement sur les articles.

## Actions réalisées

### 1. Entité Comment — champ `isApproved`

**Fichier :** `src/Entity/Comment.php`

- Ajout de la propriété `private bool $isApproved = false;` avec l'attribut `#[ORM\Column]`
- Ajout du getter `isApproved(): bool` et du setter `setIsApproved(bool): static`
- Par défaut, tout nouveau commentaire est non approuvé

### 2. Migration Doctrine

**Fichier :** `migrations/Version20260201081649.php`

- Ajout de la colonne `is_approved` (TINYINT NOT NULL, défaut 0) à la table `comment`
- Migration générée via `doctrine:migrations:diff` et exécutée via `doctrine:migrations:migrate`

### 3. Repository — filtrage des commentaires approuvés

**Fichier :** `src/Repository/CommentRepository.php`

- Modification de `findByArticle()` : ajout de la condition `c.isApproved = true`
- Seuls les commentaires approuvés sont désormais retournés côté public

### 4. Contrôleur Article — message flash adapté

**Fichier :** `src/Controller/ArticleController.php`

- Message flash modifié de `'Votre commentaire a été publié.'` en `'Votre commentaire a été soumis et sera visible après validation par un administrateur.'`

### 5. EasyAdmin — interface de modération

**Fichier :** `src/Controller/Admin/CommentCrudController.php`

- Ajout d'un `BooleanField` pour `isApproved` (libellé « Approuvé »)
- Réactivation de l'action `EDIT` (seule l'action `NEW` reste désactivée)
- Ajout d'un `BooleanFilter` sur `isApproved` pour trier les commentaires en attente

## Vérifications

| Point | Statut |
|-------|--------|
| Nouveau commentaire non visible sur l'article | OK |
| Message flash indiquant l'attente de validation | OK |
| Commentaire visible dans EasyAdmin avec `isApproved = false` | OK |
| Admin peut passer `isApproved` à `true` via EDIT | OK |
| Commentaire approuvé apparaît sur l'article | OK |
| Schéma Doctrine synchronisé avec la base | OK |
| php-cs-fixer sans erreur | OK |

## Fichiers modifiés

```
src/Entity/Comment.php                          (ajout propriété isApproved)
src/Repository/CommentRepository.php             (filtre isApproved = true)
src/Controller/ArticleController.php             (message flash modifié)
src/Controller/Admin/CommentCrudController.php   (BooleanField, EDIT, filtre)
migrations/Version20260201081649.php             (nouvelle migration)
```
