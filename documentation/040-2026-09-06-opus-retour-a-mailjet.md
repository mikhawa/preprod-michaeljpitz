# Retour à Mailjet comme serveur d'envoi d'emails

**Date** : 2026-09-06
**Modèle** : Claude Opus
**Branche** : `fix/13-email-mailjet`

## Contexte

Le projet utilisait le bridge `symfony/mailer-send-mailer` (MailerSend) depuis
le 2026-08-28 (voir `039-2026-08-28_16-08-sonnet-remplacement-mailjet-par-mailersend.md`).
Demande : **réinstaller Mailjet** comme serveur de mail de production. Mailpit
reste l'intercepteur en développement local, inchangé.

Opération inverse de la doc 039.

## Documentation consultée

- Bridge officiel Symfony Mailjet : https://github.com/symfony/mailjet-mailer
- Symfony Mailer — transports tiers : https://symfony.com/doc/current/mailer.html#using-a-3rd-party-transport
- Clés API Mailjet : https://app.mailjet.com/account/apikeys

## Incident de sécurité découvert

Le fichier `.env` — **versionné** — contenait un vrai jeton API MailerSend en
clair :

```
MAILER_DSN=mailersend+api://mlsn.a038c483…@default
```

Ce jeton est présent dans l'historique git (commit `d85a8ef`, mergé dans `main`
via la PR #84) et doit donc être considéré comme **compromis**.

Deux conséquences :

1. **Action requise hors dépôt** : révoquer ce jeton sur
   https://app.mailersend.com/api-tokens. Le retirer de `.env` ne suffit pas,
   il reste lisible dans l'historique.
2. **Effet de bord corrigé** : ce bloc étant placé **après** le bloc
   `###> symfony/mailer ###`, il écrasait `MAILER_DSN=smtp://mailpit:1025`.
   Les emails émis en développement local partaient donc réellement via
   MailerSend au lieu d'être interceptés par Mailpit. La suppression du bloc
   rétablit Mailpit comme transport de développement.

## Modifications

### Dépendances

```bash
docker compose exec php composer remove symfony/mailer-send-mailer
docker compose exec php composer require symfony/mailjet-mailer:7.4.*
```

> Les commandes composer doivent être lancées **dans le conteneur PHP** :
> `vendor/` appartient à `root` (écrit par le conteneur), un `composer` lancé
> depuis WSL échoue sur `Could not delete …/LICENSE`.

- `composer.json` : `symfony/mailer-send-mailer` → `symfony/mailjet-mailer` (`7.4.*`)
- `composer.lock` : `symfony/mailjet-mailer` v7.4.12 verrouillé, MailerSend retiré
- `symfony.lock` : entrée `symfony/mailer-send-mailer` remplacée par `symfony/mailjet-mailer`

**État de départ divergent** : le HEAD de la branche `fix/13-email-mailjet`
(`403c102`) référençait **déjà** `symfony/mailjet-mailer` dans `composer.json`,
`composer.lock` et `symfony.lock`, mais la copie de travail (`vendor/` et `.env`)
était restée sur MailerSend — le bridge n'avait jamais été réellement installé.
Après l'opération, ces trois fichiers ne ressortent donc plus en `git status`
(hors changement de fins de ligne CRLF global au dépôt) : ils sont désormais
cohérents avec `vendor/`, ce qui n'était pas le cas avant.

### Fichiers modifiés

| Fichier | Changement |
|---------|-----------|
| `composer.json` / `composer.lock` / `symfony.lock` | réalignés sur Mailjet (contenu déjà conforme dans HEAD, `vendor/` ne l'était pas) |
| `.env` | suppression du bloc `symfony/mailer-send-mailer` (**et du jeton**) ; bloc `symfony/mailjet-mailer` documenté en français ; commentaire du bloc `symfony/mailer` mis à jour |
| `.env.local` | **créé** (non versionné) : gabarit commenté pour le DSN de production |
| `README.md` | « MailerSend » → « Mailjet » dans les fonctionnalités |
| `.claude/MEMORY.md` | stack + section Emails |
| `documentation/deploiement.md` | exemples `MAILER_DSN` (`.env.local` dev et prod) |

Aucun code applicatif (`src/`, templates d'email) n'a été touché : l'abstraction
`Symfony\Component\Mailer` masque totalement le transport. Les cinq points
d'injection (`ContactController`, `ArticleController`, `RegistrationController`,
`ResetPasswordController`, `TwoFactorLoginSubscriber`, `CommentApprovedSubscriber`)
n'utilisent que `MailerInterface` et `%env(EMAIL_NOTIFICATIONS_FROM)%`.

## Configuration du DSN

Le transport est sélectionné par le schéma du `MAILER_DSN` :

```env
# Développement local (inchangé) — Mailpit, défini dans .env
MAILER_DSN=smtp://mailpit:1025

# Production — Mailjet, à définir dans .env.local (NON versionné)
# Transport retenu : SMTP (port 587 sortant requis)
MAILER_DSN=mailjet+smtp://PUBLIC_KEY:PRIVATE_KEY@default

# Variante API (HTTPS 443, si le port 587 sortant est bloqué)
MAILER_DSN=mailjet+api://PUBLIC_KEY:PRIVATE_KEY@default
```

- Clés API : https://app.mailjet.com/account/apikeys (clé publique + clé privée)
- Le domaine expéditeur (`EMAIL_NOTIFICATIONS_FROM`) doit être validé
  (SPF/DKIM) dans Mailjet, sinon les envois sont rejetés.
- Les vraies clés ne doivent **jamais** figurer dans `.env` (versionné) :
  uniquement dans `.env.local` / `.env.prod.local` (couverts par `.gitignore`).
- En développement, ne **pas** activer le DSN Mailjet dans `.env.local` :
  ce fichier est chargé dans tous les environnements de la machine et
  court-circuiterait Mailpit (c'est précisément le bug corrigé ci-dessus).

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
mailjet+smtp://pub:priv@default  => …\Bridge\Mailjet\Transport\MailjetSmtpTransport
mailjet+api://pub:priv@default   => …\Bridge\Mailjet\Transport\MailjetApiTransport
smtp://mailpit:1025              => …\Transport\Smtp\EsmtpTransport
```

`composer audit` : aucune vulnérabilité signalée.

## Points de sécurité

- [x] Jeton MailerSend retiré de `.env` (fichier versionné)
- [ ] **À faire manuellement** : révoquer le jeton `mlsn.a038c483…` sur MailerSend
      (présent dans l'historique git, donc compromis)
- [x] `.env` ne contient plus que des exemples commentés, aucun secret d'envoi
- [x] `.env.local` créé avec des placeholders uniquement, couvert par `.gitignore`
      (vérifié via `git check-ignore -v .env.local`)
- [x] Mailpit rétabli comme transport de développement (plus de fuite d'emails
      de test vers un service tiers)
- [x] Bridge officiel Symfony, `composer audit` inchangé

## Reste à faire

1. Révoquer le jeton MailerSend compromis.
2. Créer les clés API Mailjet et valider le domaine expéditeur (SPF/DKIM).
3. Renseigner `MAILER_DSN` dans le `.env.local` du serveur de production.
4. Tester un envoi réel en production (inscription ou formulaire de contact).
