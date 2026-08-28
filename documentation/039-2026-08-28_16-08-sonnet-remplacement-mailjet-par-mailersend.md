# Remplacement de Mailjet par MailerSend

**Date** : 2026-08-28
**Modèle** : Claude Sonnet
**Branche** : `dev/main`

## Contexte

Le projet utilisait le bridge Symfony `symfony/mailjet-mailer` pour l'envoi
d'emails transactionnels en production (Mailpit restant l'intercepteur en
développement local). Objectif : passer à **MailerSend** en conservant
exactement le même fonctionnement (Mailpit en dev, service tiers en prod via
un DSN défini hors dépôt).

## Documentation consultée

- Intégration Symfony de MailerSend : https://www.mailersend.com/integrations/symfony
- Bridge officiel Symfony : https://github.com/symfony/mailer-send-mailer
- Symfony Mailer : https://symfony.com/doc/current/mailer.html

## Modifications

### Dépendances

```bash
composer remove symfony/mailjet-mailer
composer require symfony/mailer-send-mailer:7.4.*
composer recipes:install symfony/mailer-send-mailer --force
```

- `composer.json` : `symfony/mailjet-mailer` → `symfony/mailer-send-mailer` (`7.4.*`)
- `composer.lock` : `symfony/mailer-send-mailer` v7.4.9 verrouillé
- `symfony.lock` : entrée `symfony/mailjet-mailer` supprimée, `symfony/mailer-send-mailer` ajoutée

### Fichiers modifiés

| Fichier | Changement |
|---------|-----------|
| `composer.json` / `composer.lock` / `symfony.lock` | changement de bridge |
| `.env` | bloc `symfony/mailjet-mailer` → `symfony/mailer-send-mailer` ; exemple de DSN prod mis à jour |
| `README.md` | « Mailjet » → « MailerSend » dans les fonctionnalités |
| `.claude/MEMORY.md` | stack + section Emails |
| `documentation/deploiement.md` | exemples `MAILER_DSN` (`.env.local` dev et prod) |

Aucun code applicatif (`src/`, templates d'email) n'a eu besoin d'être touché :
l'abstraction `Symfony\Component\Mailer` masque totalement le transport.

## Configuration du DSN

Le transport est sélectionné par le schéma du `MAILER_DSN` :

```env
# Développement local (inchangé) — Mailpit
MAILER_DSN=smtp://mailpit:1025

# Production — MailerSend, à définir dans .env.local (NON versionné)
MAILER_DSN=mailersend+api://mlsn.XXXXXXXX@default
# Variante SMTP
MAILER_DSN=mailersend+smtp://USERNAME:PASSWORD@default
```

- Jeton API : https://app.mailersend.com/api-tokens
- Le domaine expéditeur doit être vérifié (SPF/DKIM) dans MailerSend.
- Le vrai jeton ne doit **jamais** figurer dans `.env` (versionné) : uniquement
  dans `.env.local` / `.env.prod.local` (couverts par `.gitignore`).

## Vérifications effectuées

```bash
composer validate --strict          # ./composer.json is valid
php bin/console lint:yaml config/packages/mailer.yaml   # OK
php bin/console cache:clear          # OK
php bin/phpunit                      # OK (142 tests, 272 assertions)
```

Test de résolution du transport :

```
Transport::fromDsn("mailersend+api://testkey@default")
=> Symfony\Component\Mailer\Bridge\MailerSend\Transport\MailerSendApiTransport
```

## Points de sécurité

- [x] Aucun secret ajouté dans un fichier versionné (`.env` ne contient que des exemples commentés)
- [x] DSN de prod à placer dans `.env.local` (hors dépôt git)
- [x] Transport API MailerSend en HTTPS (port 443)
- [x] `composer audit` inchangé (bridge officiel Symfony)
