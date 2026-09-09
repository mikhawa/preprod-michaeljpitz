# Passage de Mailjet à la messagerie Hostinger (SMTP)

**Date** : 2026-09-09
**Modèle** : Claude Sonnet 5
**Branche** : `preprod/main`

## Contexte

L'utilisateur héberge sa messagerie chez Hostinger et souhaite l'utiliser pour
**envoyer** (et lire) les emails du site, en abandonnant Mailjet. Il a repéré la
bibliothèque `hostinger/mail-api-php-sdk` et demande si elle est nécessaire.

**Réponse : non.** Le code applicatif n'utilise que l'abstraction
`Symfony\Component\Mailer` (`MailerInterface` + `TemplatedEmail`), qui est
agnostique du transport. Envoyer via Hostinger se fait avec un simple DSN
`smtp://` — aucun pont ni SDK tiers. Le SDK `hostinger/mail-api-php-sdk` cible
l'**API de gestion** Hostinger (création de boîtes, redirections, DNS), pas
l'envoi de mails transactionnels : l'ajouter serait une dépendance non standard
et inutile.

Mailpit reste l'intercepteur en développement local (inchangé).

## Documentation consultée

- Symfony Mailer — transports : https://symfony.com/doc/current/mailer.html
- Symfony Mailer — DSN SMTP : https://symfony.com/doc/current/mailer.html#using-a-3rd-party-transport
- Hostinger — paramètres SMTP/IMAP : https://support.hostinger.com/en/articles/1583298-how-to-set-up-email-on-third-party-apps

## Envoi — configuration

Le transport est déduit du schéma du `MAILER_DSN` :

```env
# Développement local (inchangé) — Mailpit, défini dans .env
MAILER_DSN=smtp://mailpit:1025

# Production — SMTP de la boîte mail Hostinger, à définir dans .env.local (NON versionné)
# hôte  : smtp.hostinger.com
# port  : 465 (SSL) ou 587 (STARTTLS)
# login : adresse email complète, le « @ » encodé en %40
# passe : mot de passe de la boîte mail Hostinger
MAILER_DSN=smtp://contact%40michaeljpitz.com:MOT_DE_PASSE@smtp.hostinger.com:465
```

Résolution vérifiée : `smtp://…@smtp.hostinger.com:465` =>
`Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport` (transport natif, aucun
pont).

### Conditions de délivrabilité

- `EMAIL_NOTIFICATIONS_FROM` (dans `.env`) doit être une adresse **du domaine
  Hostinger** : un `From:` étranger est rejeté ou classé en spam. Le `replyTo`
  du `ContactController` porte déjà l'adresse du visiteur.
- Activer **SPF** et **DKIM** pour le domaine dans le panneau Hostinger
  (Emails → Configuration DNS).
- Le mot de passe du DSN est celui de la **boîte mail** (hPanel → Emails →
  Comptes email).

## Réception

Une application Symfony ne reçoit pas d'emails. Le formulaire de contact
**envoie** vers `ADMIN_EMAIL` (boîte Hostinger). La lecture se fait :

- via le **webmail Hostinger**, ou
- en **IMAP** dans un client mail : `imap.hostinger.com`, port 993 (SSL).

Rien à coder côté site.

## Modifications

### Dépendances

```bash
docker compose exec php composer remove symfony/mailjet-mailer
```

> À lancer **dans le conteneur PHP** : `vendor/` appartient à `root`, un
> `composer` lancé depuis WSL échoue sur la suppression de fichiers.

- `composer.json` : retrait de `symfony/mailjet-mailer`
- `composer.lock` : `symfony/mailjet-mailer` v7.4.12 retiré (0 occurrence restante)
- `symfony.lock` : entrée `symfony/mailjet-mailer` retirée (recette dé-configurée)

### Fichiers modifiés

| Fichier | Changement |
|---------|-----------|
| `composer.json` / `composer.lock` / `symfony.lock` | retrait du pont Mailjet |
| `.env` | bloc `symfony/mailer` : commentaires de production réécrits pour le SMTP Hostinger ; bloc `symfony/mailjet-mailer` supprimé |
| `.env.local` | **créé** (non versionné) : gabarit commenté du DSN Hostinger, avec l'avertissement de ne pas le décommenter en dev (casserait Mailpit) |
| `README.md` | « Mailjet » → « Messagerie Hostinger (SMTP) » dans les fonctionnalités |
| `documentation/deploiement.md` | exemples `MAILER_DSN` (dev : garder Mailpit ; prod : SMTP Hostinger) + encadré délivrabilité (SPF/DKIM, `mailer:test`, IMAP) |
| `documentation/journal-decisions.md` | entrée 2026-09-09 |
| `.claude/MEMORY.md` | stack + section Emails |

Aucun fichier de `src/` ni template d'email touché. Points d'injection
inchangés : `ContactController`, `ArticleController`, `RegistrationController`,
`ResetPasswordController`, `TwoFactorLoginSubscriber`, `CommentApprovedSubscriber`
— tous sur `MailerInterface` + `%env(EMAIL_NOTIFICATIONS_FROM)%`.

## Vérifications effectuées

```bash
docker compose exec php composer validate --strict     # ./composer.json is valid
docker compose exec php php bin/console lint:yaml config/packages/mailer.yaml
                                                       # [OK] All 1 YAML files contain valid syntax.
docker compose exec php php bin/console cache:clear     # [OK]
docker compose exec php composer audit                  # No security vulnerability advisories found.
docker compose exec php php bin/phpunit                 # OK (142 tests, 272 assertions)
```

Résolution des transports :

```
smtp://mailpit:1025                          => …\Transport\Smtp\EsmtpTransport
smtp://user%40michaeljpitz.com:***@smtp.hostinger.com:465 => …\Transport\Smtp\EsmtpTransport
```

## Points de sécurité

- [x] Aucun secret d'envoi dans `.env` (versionné) : uniquement des exemples commentés
- [x] `.env.local` (placeholders seulement) couvert par `.gitignore`
      (`git check-ignore -v .env.local`)
- [x] Mailpit conservé comme transport de développement (pas de fuite d'emails de test)
- [x] Pont retiré, `composer audit` sans alerte
- [ ] **À faire hors dépôt** : révoquer les clés API Mailjet devenues inutiles
      (https://app.mailjet.com/account/apikeys)

## Reste à faire (déploiement)

1. Créer / réinitialiser le mot de passe de la boîte mail Hostinger dans hPanel.
2. Activer SPF + DKIM pour le domaine (panneau Hostinger).
3. Renseigner `MAILER_DSN` (SMTP Hostinger) dans le `.env.local` du serveur.
4. Tester : `php bin/console mailer:test destinataire@exemple.com`, puis un envoi
   réel (inscription ou formulaire de contact).
5. Révoquer les clés API Mailjet.
