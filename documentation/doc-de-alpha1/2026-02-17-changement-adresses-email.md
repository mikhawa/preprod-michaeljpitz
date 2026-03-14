# Changement des adresses email expéditrices

> Date : 17 février 2026

## Contexte

Les emails envoyés par l'application (contact, inscription, réinitialisation de mot de passe) utilisaient initialement `noreply@mikhawa.be` puis `michael.j.pitz@gmail.com` comme adresse expéditrice. Ces adresses posaient des problèmes de délivrabilité car elles n'étaient pas vérifiées dans Mailjet.

## Décision

Utiliser l'adresse `contact@alpha1.michaeljpitz.com` comme adresse expéditrice unique pour tous les emails de l'application. Le domaine `alpha1.michaeljpitz.com` est vérifié et authentifié (SPF/DKIM) dans Mailjet, ce qui garantit la délivrabilité.

## Modifications

### Fichiers modifiés

| Fichier | Modification |
|---------|-------------|
| `src/Controller/ContactController.php` | `from` : `contact@alpha1.michaeljpitz.com` / `to` : `contact@alpha1.michaeljpitz.com` |
| `src/Controller/RegistrationController.php` | `from` : `contact@alpha1.michaeljpitz.com` |
| `src/Controller/ResetPasswordController.php` | `from` : `contact@alpha1.michaeljpitz.com` |

### Historique des changements d'adresse

| Date | From (contact) | To (contact) | From (inscription/mdp) |
|------|---------------|--------------|----------------------|
| Initial | `noreply@mikhawa.be` | Admin en BDD | `noreply@mikhawa.be` |
| 16/02/2026 | `michael.j.pitz@gmail.com` | Admin en BDD | `noreply@mikhawa.be` |
| 17/02/2026 | `contact@alpha1.michaeljpitz.com` | `contact@alpha1.michaeljpitz.com` | `contact@alpha1.michaeljpitz.com` |

## Configuration Mailjet requise

Pour que les emails soient envoyés correctement :

1. Le domaine `alpha1.michaeljpitz.com` doit être ajouté et vérifié dans Mailjet
2. Les enregistrements DNS SPF et DKIM doivent être configurés
3. L'adresse `contact@alpha1.michaeljpitz.com` doit être ajoutée comme expéditeur autorisé

## Déploiement

Fichiers à transférer sur le serveur de production :
- `src/Controller/ContactController.php`
- `src/Controller/RegistrationController.php`
- `src/Controller/ResetPasswordController.php`

Après transfert : vider `var/cache/prod/` et OPcache (via `opcache_reset.php` temporaire ou redémarrage PHP-FPM).
