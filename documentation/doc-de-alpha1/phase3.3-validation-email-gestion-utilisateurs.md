# Phase 3.3 : Validation par email et gestion des utilisateurs

> Date : 1 février 2026

## Objectif

- Les utilisateurs doivent valider leur compte par email dans les 48 heures suivant l'inscription
- L'administrateur peut activer, désactiver ou bannir un utilisateur depuis EasyAdmin
- Un utilisateur dont le statut n'est pas « Activé » ne peut pas se connecter

## Actions réalisées

### 1. Entité User — nouveaux champs

**Fichier :** `src/Entity/User.php`

- Ajout de `#[ORM\HasLifecycleCallbacks]`
- Ajout de `activationToken` (`VARCHAR(64)`, nullable) : clé secrète pour le lien d'activation
- Ajout de `status` (`SMALLINT UNSIGNED`, défaut 0) : 0 = non activé, 1 = activé, 2 = banni
- Ajout de `createdAt` (`DateTimeImmutable`, NOT NULL) avec `#[ORM\PrePersist]` automatique
- Getters et setters correspondants

### 2. Migrations Doctrine

- `Version20260201082340` : ajout de `activation_token` et `status` sur la table `user`
- `Version20260201082743` : ajout de `created_at` sur la table `user` (avec remplissage des données existantes)

### 3. Génération du token à l'inscription

**Fichier :** `src/Controller/RegistrationController.php`

- Génération d'un token aléatoire de 64 caractères hex via `bin2hex(random_bytes(32))`
- Assigné au User avant la persistance

### 4. Envoi de l'email d'activation

**Fichier :** `src/Controller/RegistrationController.php`

- Injection de `MailerInterface`
- Envoi d'un `TemplatedEmail` après inscription avec le lien d'activation (URL absolue)
- Expéditeur : `noreply@mikhawa.be`
- Message flash adapté : indique l'envoi de l'email et le délai de 48h

### 5. Template de l'email

**Fichier :** `templates/email/activation.html.twig`

- Email HTML avec nom de l'utilisateur, bouton d'activation et lien texte de secours
- Mention du délai de 48 heures

### 6. Route d'activation du compte

**Fichier :** `src/Controller/RegistrationController.php` — route `GET /activation/{token}`

- Recherche de l'utilisateur par `activationToken`
- Vérification de l'expiration (48h depuis `createdAt`)
- Passage du statut à 1 et suppression du token
- Gestion des cas d'erreur : token invalide, compte déjà activé, lien expiré

### 7. Blocage de la connexion selon le statut

**Fichier :** `src/Security/UserChecker.php` (nouveau)

- Implémente `UserCheckerInterface`
- `checkPreAuth()` : bloque la connexion si statut = 0 (non activé) ou statut = 2 (banni)
- Messages d'erreur distincts selon le cas

**Fichier :** `config/packages/security.yaml`

- Ajout de `user_checker: App\Security\UserChecker` dans le firewall `main`

### 8. Administration des utilisateurs — gestion du statut

**Fichier :** `src/Controller/Admin/UserCrudController.php`

- Ajout d'un `ChoiceField` « Statut » avec badges colorés (warning / success / danger)
- Ajout d'un `DateTimeField` « Inscrit le » (masqué sur les formulaires)
- Ajout d'un `ChoiceFilter` sur le statut pour filtrer la liste
- Protection contre l'auto-modification de l'admin :
  - Boutons Éditer et Supprimer masqués sur la propre ligne de l'admin via `configureActions()`
  - `updateEntity()` bloque la sauvegarde avec message flash
  - `deleteEntity()` bloque la suppression avec message flash

## Vérifications

| Point | Statut |
|-------|--------|
| Token généré à l'inscription (64 caractères hex) | OK |
| Email envoyé avec lien d'activation | OK |
| Lien valide active le compte (statut passe à 1) | OK |
| Lien expiré après 48h affiche un message d'erreur | OK |
| Connexion impossible si statut = 0 (non activé) | OK |
| Connexion impossible si statut = 2 (banni) | OK |
| Connexion possible si statut = 1 (activé) | OK |
| Admin peut changer le statut d'un utilisateur | OK |
| Admin ne peut pas modifier/supprimer son propre compte | OK |
| Filtre par statut dans EasyAdmin | OK |
| Schéma Doctrine synchronisé | OK |
| php-cs-fixer sans erreur | OK |
| Conteneur Symfony validé | OK |

## Fichiers créés

```
src/Security/UserChecker.php
templates/email/activation.html.twig
migrations/Version20260201082340.php
migrations/Version20260201082743.php
```

## Fichiers modifiés

```
src/Entity/User.php                              (activationToken, status, createdAt)
src/Controller/RegistrationController.php         (token, email, route activation)
src/Controller/Admin/UserCrudController.php       (statut, filtres, protection auto-modification)
config/packages/security.yaml                     (user_checker)
```
