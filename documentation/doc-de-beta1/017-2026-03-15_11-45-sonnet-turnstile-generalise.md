# 017 - Généralisation de Cloudflare Turnstile à tous les formulaires

**Date** : 2026-03-15
**Modèle** : Claude Sonnet
**Branche** : `dev/create-mail-system-for-dev`

---

## Contexte

Turnstile n'était présent que sur le formulaire de contact. L'objectif est de protéger tous les points d'entrée publics contre les bots, et d'ajouter une protection supplémentaire sur les formulaires accessibles aux utilisateurs connectés.

## Formulaires couverts

| Formulaire | Accès | Mécanisme de validation |
|-----------|-------|------------------------|
| Connexion (`/connexion`) | Public | `TurnstileLoginSubscriber` sur `CheckPassportEvent` |
| Inscription (`/inscription`) | Public | Validation dans `RegistrationController` |
| Contact (`/contact`) | Public | Déjà en place — inchangé |
| Profil (`/profil`) | Connecté | Validation dans `ProfileController` |
| Commentaire article | Connecté | Validation dans `ArticleController::show()` |

---

## Décisions techniques

### Login : EventSubscriber sur `CheckPassportEvent`

Le formulaire de connexion est intercepté par le firewall Symfony **avant** d'atteindre le controller. Il n'est donc pas possible d'y valider Turnstile dans le controller. La solution retenue est un `EventSubscriberInterface` écoutant `CheckPassportEvent` (priorité 10), qui :
- Vérifie que la requête cible `/connexion`
- N'agit que si la clé de site est réelle (pas de test)
- Lève une `CustomUserMessageAuthenticationException` en cas d'échec

### Bypass en développement et CI

La validation serveur est bypassée dès que `TURNSTILE_SITE_KEY` commence par `1x00000000000000000000` (clés de test Cloudflare). Cette logique existait déjà dans `TurnstileValidator::validate()` via la `$secretKey`, et a été répliquée dans chaque controller via `$turnstileEnabled`.

Le workflow CI (`tests.yml`) n'a **pas été modifié** : les clés de test du `.env` bypassent automatiquement la validation serveur, les tests fonctionnels passent sans toucher au token Turnstile.

### Widget affiché en développement

Initialement, les templates cachaient le widget avec les clés de test. Cette condition a été retirée pour permettre de voir et tester le widget en local. Les clés de test Cloudflare (`1x000...`) affichent un vrai widget interactif qui passe toujours, sans bloquer le flux de développement.

La seule condition restante pour afficher le widget : `TURNSTILE_SITE_KEY` doit être définie et différente de `YOUR_TURNSTILE_SITE_KEY`.

### `data-turbo="false"` sur les formulaires protégés

Ajouté sur tous les formulaires avec Turnstile pour éviter que Turbo ne soumette le formulaire sans recharger le script Cloudflare.

---

## Fichiers créés

| Fichier | Rôle |
|---------|------|
| `src/EventSubscriber/TurnstileLoginSubscriber.php` | Validation Turnstile au login via `CheckPassportEvent` |

## Fichiers modifiés

| Fichier | Modification |
|---------|-------------|
| `src/Controller/SecurityController.php` | Injection `turnstileSiteKey` + passage au template |
| `src/Controller/RegistrationController.php` | Injection `TurnstileValidator` + `turnstileSiteKey`, validation avant création du compte |
| `src/Controller/ProfileController.php` | Injection `TurnstileValidator` + `turnstileSiteKey`, validation avant sauvegarde |
| `src/Controller/ArticleController.php` | Injection `TurnstileValidator` + `turnstileSiteKey`, validation avant publication de commentaire |
| `templates/security/login.html.twig` | Widget Turnstile + script + `data-turbo="false"` |
| `templates/security/register.html.twig` | Widget Turnstile + script + `data-turbo="false"` |
| `templates/profile/index.html.twig` | Widget Turnstile + script + `data-turbo="false"` |
| `templates/article/show.html.twig` | Widget Turnstile + script + `data-turbo="false"` (uniquement si connecté) |
| `templates/contact/index.html.twig` | Suppression de la condition qui cachait le widget avec les clés de test |

---

## Configuration requise en production

Définir les vraies clés dans `.env.local` (non committé) :

```dotenv
TURNSTILE_SITE_KEY=<clé_de_site_cloudflare>
TURNSTILE_SECRET_KEY=<clé_secrète_cloudflare>
```

Les clés s'obtiennent sur [https://dash.cloudflare.com/turnstile](https://dash.cloudflare.com/turnstile).
