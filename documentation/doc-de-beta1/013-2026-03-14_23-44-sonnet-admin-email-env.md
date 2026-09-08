# Remplacement des adresses email admin en dur par ADMIN_EMAIL

## Problème
Les notifications email à destination de l'admin utilisaient une adresse
en dur `contact@alpha1.michaeljpitz.com` au lieu de la variable d'environnement
`ADMIN_EMAIL` définie dans `.env`.

## Solution
Injection de `ADMIN_EMAIL` via `#[Autowire('%env(ADMIN_EMAIL)%')]` dans les
controllers concernés, en propriété `$adminEmail`.

## Fichiers modifiés

| Fichier | Lignes corrigées |
|---------|-----------------|
| `src/Controller/ArticleController.php` | Notification nouveau commentaire |
| `src/Controller/ContactController.php` | Notification formulaire de contact |
| `src/Controller/RegistrationController.php` | Notifications inscription + activation |

## Valeur en vigueur
`ADMIN_EMAIL=mikhawa@cf2m.be` (`.env`)

## Modèle utilisé
Claude Sonnet 4.6
