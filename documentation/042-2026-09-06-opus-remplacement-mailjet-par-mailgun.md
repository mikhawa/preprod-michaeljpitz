# Remplacement de Mailjet par Mailgun

**Date** : 2026-09-06
**Modèle** : Claude Opus
**Branche** : `fix/13-email-mailjet`

## Contexte

Le compte Mailjet de production est bloqué (`mj-0001`, HTTP 401 — voir
`documentation/041-2026-09-06-opus-incident-compte-mailjet-bloque.md`),
confirmé persistant en production après plusieurs tentatives et sans que le
projet en soit la cause. Décision : basculer vers **Mailgun** comme
transporteur de production, en attendant ou indépendamment du support
Mailjet. Mailpit reste l'intercepteur en développement local, inchangé.

## Documentation consultée

- Bridge officiel Symfony Mailgun : https://github.com/symfony/mailgun-mailer
- Symfony Mailer — transports tiers : https://symfony.com/doc/current/mailer.html#using-a-3rd-party-transport
- Clés API Mailgun : https://app.mailgun.com/app/account/security/api_keys

## Choix de transport

- **Transport** : API (`mailgun+api://`), HTTPS port 443 — pas de dépendance
  à un port SMTP sortant, contrairement au choix SMTP fait précédemment pour
  Mailjet.
- **Région** : EU (`?region=eu`) — le compte Mailgun est enregistré dans la
  région européenne. Une région erronée pointerait vers les mauvais serveurs
  API/SMTP et ferait échouer l'envoi silencieusement ou avec une erreur
  d'authentification trompeuse.

## Modifications

### Dépendances

```bash
docker compose exec php composer remove symfony/mailjet-mailer
docker compose exec php composer require symfony/mailgun-mailer:7.4.*
```

- `composer.json` : `symfony/mailjet-mailer` → `symfony/mailgun-mailer` (`7.4.*`)
- `composer.lock` : `symfony/mailgun-mailer` verrouillé, Mailjet retiré
- `symfony.lock` : entrée `symfony/mailjet-mailer` remplacée par `symfony/mailgun-mailer`

### Fichiers modifiés

| Fichier | Changement |
|---------|-----------|
| `composer.json` / `composer.lock` / `symfony.lock` | changement de bridge |
| `.env` | bloc `symfony/mailjet-mailer` supprimé ; bloc `symfony/mailgun-mailer` documenté en français (API, région EU) ; commentaire du bloc `symfony/mailer` mis à jour |
| `.env.local` | régénéré (non versionné) : gabarit commenté pour le DSN Mailgun |
| `README.md` | « Mailjet » → « Mailgun » dans les fonctionnalités |
| `.claude/MEMORY.md` | stack + section Emails |
| `documentation/deploiement.md` | exemples `MAILER_DSN` (`.env.local` dev et prod) |

Aucun code applicatif (`src/`, templates d'email) n'a été touché : l'abstraction
`Symfony\Component\Mailer` masque totalement le transport.

## Configuration du DSN

```env
# Développement local (inchangé) — Mailpit, défini dans .env
MAILER_DSN=smtp://mailpit:1025

# Production — Mailgun, à définir dans .env.local (NON versionné)
# Transport retenu : API (HTTPS 443), région EU
MAILER_DSN=mailgun+api://KEY:DOMAIN@default?region=eu

# Variante SMTP (si le port 443 sortant est bloqué et 587 disponible)
MAILER_DSN=mailgun+smtp://USERNAME:PASSWORD@default?region=eu
```

- Clé API + domaine : https://app.mailgun.com/app/account/security/api_keys
- Le domaine expéditeur (`EMAIL_NOTIFICATIONS_FROM`) doit être vérifié
  (SPF/DKIM) dans Mailgun, sinon les envois sont rejetés ou marqués comme spam.
- Les vraies clés ne doivent **jamais** figurer dans `.env` (versionné) :
  uniquement dans `.env.local` / `.env.prod.local` (couverts par `.gitignore`).
- En développement, ne **pas** activer le DSN Mailgun dans `.env.local` :
  ce fichier est chargé dans tous les environnements de la machine et
  court-circuiterait Mailpit.

## Vérifications effectuées

```bash
docker compose exec php composer validate --strict
# ./composer.json is valid

docker compose exec php php bin/console lint:yaml config/packages/mailer.yaml
# [OK] All 1 YAML files contain valid syntax.

docker compose exec php php bin/console cache:clear
# [OK] Cache for the "dev" environment (debug=true) was successfully cleared.

docker compose exec php php bin/phpunit
# OK (142 tests, 272 assertions)
```

Résolution des transports :

```
mailgun+api://key:domain@default?region=eu   => …\Bridge\Mailgun\Transport\MailgunApiTransport
mailgun+smtp://user:pass@default?region=eu   => …\Bridge\Mailgun\Transport\MailgunSmtpTransport
smtp://mailpit:1025                          => …\Transport\Smtp\EsmtpTransport
```

`composer audit` : aucune vulnérabilité signalée.

## Points de sécurité

- [x] Aucun secret Mailgun ajouté dans un fichier versionné (`.env` ne
      contient que des exemples commentés)
- [x] DSN de prod à placer dans `.env.local` (hors dépôt git), vérifié via
      `git check-ignore -v .env.local`
- [x] Transport API Mailgun en HTTPS (port 443)
- [x] Mailpit inchangé comme transport de développement
- [x] Bridge officiel Symfony, `composer audit` inchangé

## Reste à faire

1. Créer/vérifier les clés API Mailgun et le domaine expéditeur
   (`EMAIL_NOTIFICATIONS_FROM`) — validation SPF/DKIM dans Mailgun.
2. Confirmer la région du compte Mailgun (EU retenu ici sur indication de
   l'utilisateur) avant de renseigner `.env.local` en production.
3. Renseigner `MAILER_DSN` dans le `.env.local` du serveur de production.
4. Tester un envoi réel en production (inscription ou formulaire de contact)
   et confirmer la réception.
5. Suivre en parallèle la résolution de l'incident Mailjet
   (`documentation/041-2026-09-06-opus-incident-compte-mailjet-bloque.md`) —
   Mailgun est un remplacement, pas nécessairement définitif.
