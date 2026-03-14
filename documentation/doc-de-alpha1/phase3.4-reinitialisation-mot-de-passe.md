# Phase 3.4 : Réinitialisation de mot de passe

> Date : 1 février 2026

## Objectif

- Permettre aux utilisateurs ayant oublié leur mot de passe de le réinitialiser par email
- Implémenter un système « fait maison » suivant le même patron que l'activation de compte (Phase 3.3)
- Ne pas révéler l'existence des comptes (message de succès identique dans tous les cas)

## Actions réalisées

### 1. Entité User — nouveaux champs

**Fichier :** `src/Entity/User.php`

- Ajout de `resetPasswordToken` (`VARCHAR(64)`, nullable) : token aléatoire pour le lien de réinitialisation
- Ajout de `resetPasswordRequestedAt` (`DateTimeImmutable`, nullable) : horodatage de la demande, utilisé pour l'expiration
- Getters et setters correspondants

### 2. Migration Doctrine

- `Version20260201195226` : ajout de `reset_password_token` et `reset_password_requested_at` sur la table `user`

### 3. Formulaires

**Fichier :** `src/Form/ResetPasswordRequestType.php` (nouveau)

- Champ `email` (`EmailType`) avec contraintes `NotBlank` et `Email`
- Formulaire non lié à une entité (`data_class` = null)

**Fichier :** `src/Form/ResetPasswordType.php` (nouveau)

- Champ `plainPassword` (`RepeatedType` / `PasswordType`) avec contraintes `NotBlank` et `Length(min: 8, max: 255)`
- Formulaire non lié à une entité (`data_class` = null)

### 4. Contrôleur de réinitialisation

**Fichier :** `src/Controller/ResetPasswordController.php` (nouveau)

**Route `GET|POST /mot-de-passe-oublie` (`app_forgot_password`) :**

- Redirige vers l'accueil si l'utilisateur est déjà connecté
- Affiche le formulaire de demande (champ email)
- Recherche l'utilisateur par email
- Vérifie que le compte est actif (`status = 1`)
- Génère un token de 64 caractères hex via `bin2hex(random_bytes(32))`
- Stocke le token et `resetPasswordRequestedAt` en base
- Envoie un `TemplatedEmail` avec le lien de réinitialisation (URL absolue)
- Affiche toujours le même message de succès, que l'email existe ou non

**Route `GET|POST /reinitialiser-mot-de-passe/{token}` (`app_reset_password`) :**

- Redirige vers l'accueil si l'utilisateur est déjà connecté
- Recherche l'utilisateur par `resetPasswordToken`
- Vérifie l'expiration (1 heure depuis `resetPasswordRequestedAt`)
- Si expiré : efface le token et redirige vers la page de demande
- Affiche le formulaire de nouveau mot de passe (champ répété)
- Hache le mot de passe via `UserPasswordHasherInterface`
- Efface le token et redirige vers login avec message flash de succès

### 5. Templates

**Fichier :** `templates/security/forgot_password.html.twig` (nouveau)

- Page de demande de réinitialisation
- Même style que le formulaire de connexion (Tailwind, variables CSS)
- Texte explicatif, champ email, bouton de soumission, lien retour vers la connexion

**Fichier :** `templates/security/reset_password.html.twig` (nouveau)

- Page de saisie du nouveau mot de passe
- Deux champs mot de passe (nouveau + confirmation)
- Même style que les autres formulaires

**Fichier :** `templates/email/reset_password.html.twig` (nouveau)

- Email HTML avec le même style que `activation.html.twig`
- Nom de l'utilisateur, bouton de réinitialisation, lien texte de secours
- Mention du délai d'1 heure
- Message de sécurité si la demande n'a pas été faite par l'utilisateur

### 6. Lien « Mot de passe oublié ? » sur la page de connexion

**Fichier :** `templates/security/login.html.twig`

- Ajout d'un lien vers `app_forgot_password` à côté de la case « Se souvenir de moi »

## Vérifications

| Point | Statut |
|-------|--------|
| Token généré (64 caractères hex) | OK |
| Email envoyé avec lien de réinitialisation | OK |
| Message identique que l'email existe ou non | OK |
| Seuls les comptes actifs (status = 1) reçoivent l'email | OK |
| Token expiré après 1 heure | OK |
| Token effacé après utilisation | OK |
| Token effacé après expiration | OK |
| Mot de passe haché via bcrypt (cost 13) | OK |
| Protection CSRF sur les deux formulaires | OK |
| Lien « Mot de passe oublié ? » sur la page de connexion | OK |
| Schéma Doctrine synchronisé | OK |
| Lint Twig sans erreur | OK |
| Conteneur Symfony validé | OK |

## Fichiers créés

```
src/Controller/ResetPasswordController.php
src/Form/ResetPasswordRequestType.php
src/Form/ResetPasswordType.php
templates/security/forgot_password.html.twig
templates/security/reset_password.html.twig
templates/email/reset_password.html.twig
migrations/Version20260201195226.php
```

## Fichiers modifiés

```
src/Entity/User.php                     (resetPasswordToken, resetPasswordRequestedAt)
templates/security/login.html.twig      (lien « Mot de passe oublié ? »)
```
