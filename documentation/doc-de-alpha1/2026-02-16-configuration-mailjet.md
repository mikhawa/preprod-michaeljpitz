# Configuration Mailjet pour l'envoi d'emails en production

> Date : 16 février 2026

## Contexte

Le projet utilise `symfony/mailer` pour l'envoi d'emails à 3 endroits :
- **Formulaire de contact** (ContactController)
- **Activation de compte** (RegistrationController)
- **Réinitialisation de mot de passe** (ResetPasswordController)

Mailjet est utilisé comme service de transport transactionnel, en développement comme en production.

## Pourquoi Mailjet

- Service français, conforme RGPD
- API transactionnelle fiable (meilleure délivrabilité que SMTP direct)
- Offre gratuite : 200 emails/jour, 6 000 emails/mois
- Bridge Symfony officiel (`symfony/mailjet-mailer`)

## Installation

Le package est déjà installé dans le projet :

```bash
composer require symfony/mailjet-mailer
```

> Note : `symfony/mailgun-mailer` a été retiré car non utilisé.

## Configuration

### Obtenir les clés API

1. Créer un compte sur [mailjet.com](https://www.mailjet.com/)
2. Aller dans **Paramètres du compte > Clés API** ou directement : https://app.mailjet.com/account/apikeys
3. Noter la **clé API** (ACCESS_KEY) et la **clé secrète** (SECRET_KEY)

### Vérifier le domaine expéditeur

1. Dans Mailjet, aller dans **Paramètres du compte > Domaines et adresses d'expédition**
2. Ajouter le domaine utilisé pour l'envoi d'emails
3. Configurer les enregistrements DNS : SPF, DKIM, et optionnellement DMARC
4. Attendre la validation par Mailjet

### Configurer le DSN sur le serveur

Dans le fichier `.env.local` du serveur de production :

```env
MAILER_DSN=mailjet+api://VOTRE_ACCESS_KEY:VOTRE_SECRET_KEY@default
```

Le format `mailjet+api://` utilise l'API REST de Mailjet (port 443, HTTPS), ce qui est plus fiable que SMTP et ne nécessite pas d'ouvrir le port 587.

### Alternative SMTP (si l'API est bloquée)

```env
MAILER_DSN=mailjet+smtp://VOTRE_ACCESS_KEY:VOTRE_SECRET_KEY@default
```

## Vérification

### En production

1. Vérifier la configuration :
   ```bash
   php bin/console debug:config framework mailer
   ```

2. Envoyer un email de test via le formulaire de contact du site

3. Vérifier la réception dans le dashboard Mailjet : https://app.mailjet.com/stats

## Fichiers concernés

| Fichier | Rôle |
|---------|------|
| `.env` | DSN Mailjet (clés à configurer dans `.env.local`) |
| `.env.prod.local.dist` | Template DSN Mailjet pour la production |
| `config/packages/mailer.yaml` | Lit `MAILER_DSN` (aucune modification) |
| Contrôleurs | Utilisent `MailerInterface` (aucune modification) |
