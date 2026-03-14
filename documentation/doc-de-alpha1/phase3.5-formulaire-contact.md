# Phase 3.5 - Formulaire de contact avec captcha

## Objectif

Remplacer le simple lien email par un vrai formulaire de contact avec :
- Champs : nom, email, message
- Captcha gratuit (Cloudflare Turnstile)
- Envoi d'email vers l'administrateur

## Fichiers créés

### 1. `src/Form/ContactType.php`
Formulaire Symfony avec :
- Champ `name` (TextType) : 2-100 caractères
- Champ `email` (EmailType) : validation email
- Champ `message` (TextareaType) : 10-5000 caractères
- Protection CSRF activée

### 2. `src/Service/TurnstileValidator.php`
Service de validation du captcha Cloudflare Turnstile :
- Vérifie le token côté serveur via l'API Cloudflare
- Retourne `true` en mode développement (clés de test)
- Gère les erreurs silencieusement

### 3. `src/Controller/ContactController.php`
Contrôleur avec route `/contact` (GET|POST) :
- Affiche le formulaire
- Valide le captcha Turnstile
- Envoie un email à l'administrateur (ROLE_ADMIN)
- Messages flash de succès/erreur

### 4. `templates/contact/index.html.twig`
Template du formulaire avec :
- Style cohérent avec le reste du site (Tailwind + CSS vars)
- Widget Cloudflare Turnstile intégré
- Validation côté client désactivée (novalidate)

### 5. `templates/email/contact_notification.html.twig`
Template d'email HTML pour l'administrateur :
- Informations de l'expéditeur
- Message complet
- Bouton "Répondre" avec mailto

## Fichiers modifiés

### 1. `config/services.yaml`
Ajout de la configuration du service `TurnstileValidator` avec injection de la clé secrète.

### 2. `.env`
Ajout des variables d'environnement :
```env
TURNSTILE_SITE_KEY=1x00000000000000000000AA
TURNSTILE_SECRET_KEY=1x0000000000000000000000000000000AA
```
(Clés de test Cloudflare - toujours passent)

### 3. `templates/home/index.html.twig`
Section contact modifiée avec un lien vers `/contact` au lieu du mailto.

### 4. `templates/components/Navbar.html.twig`
Liens "Contact" mis à jour pour pointer vers `app_contact`.

## Configuration Cloudflare Turnstile

### Clés de développement (toujours passent)
- Site key : `1x00000000000000000000AA`
- Secret key : `1x0000000000000000000000000000000AA`

### Clés de production
1. Créer un compte Cloudflare (gratuit)
2. Aller sur https://dash.cloudflare.com/turnstile
3. Créer un widget "Managed" pour votre domaine
4. Copier les clés dans `.env.local` (ne pas commiter !)

```env
# .env.local (production)
TURNSTILE_SITE_KEY=votre_site_key_reel
TURNSTILE_SECRET_KEY=votre_secret_key_reel
```

## Sécurité

- [x] Protection CSRF sur le formulaire
- [x] Validation des données côté serveur (Constraints)
- [x] Captcha anti-bot (Cloudflare Turnstile)
- [x] Email envoyé avec `replyTo` (pas de spoofing)
- [x] Pas de stockage des messages en base (évite les fuites)
- [x] Requêtes Doctrine paramétrées

## Tests

### Test manuel
1. Accéder à http://localhost:8080/contact
2. Remplir le formulaire avec des données valides
3. Vérifier la réception dans le dashboard Mailjet (https://app.mailjet.com/stats)

### Vérifications
```bash
php bin/console lint:twig templates/
php bin/console lint:container
php bin/console lint:yaml config/
php bin/console debug:router | grep contact
```

## Routes

| Route | Méthode | URL | Description |
|-------|---------|-----|-------------|
| `app_contact` | GET, POST | `/contact` | Formulaire de contact |

## Dépendances

Aucune nouvelle dépendance composer requise. Utilise :
- `symfony/http-client` (déjà présent)
- `symfony/mailer` (déjà présent)
- `symfony/form` (déjà présent)
