# Mailjet comme mailer par défaut en production

**Date :** 2026-09-07
**Contexte :** préparation du mode dev/prod. On veut que la production envoie
ses emails via Mailjet « par défaut », avec une configuration propre et un
gabarit de secrets.

## État initial

Tout l'outillage Mailjet était déjà présent :

- `symfony/mailjet-mailer` 7.4.12 installé (`composer show`)
- `config/packages/mailer.yaml` : `dsn: '%env(MAILER_DSN)%'` (aucune surcharge par env)
- `.env` : `MAILER_DSN=mailjet+api://ACCESS_KEY:SECRET_KEY@default`

Ce qui manquait : un environnement `prod` explicite. Le fichier
`.env.prod.local.dist` était mentionné dans
`documentation/doc-de-alpha1/2026-02-16-configuration-mailjet.md` mais n'existait pas.

## Modifications

### Nouveau : `.env.prod` (versionné, sans secret)

Valeurs par défaut de production chargées automatiquement quand `APP_ENV=prod` :

- `APP_DEBUG=0`
- `DEFAULT_URI=https://preprod.michaeljpitz.com`
- `MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0`
- `MAILER_DSN=mailjet+api://ACCESS_KEY:SECRET_KEY@default` (gabarit ; les vraies
  clés sont dans `.env.prod.local`)

### Nouveau : `.env.prod.local.dist` (versionné, gabarit de secrets)

Gabarit à copier vers `.env.prod.local` (jamais versionné, couvert par
`/.env.*.local` dans `.gitignore`) : `APP_ENV`, `APP_SECRET`, `DATABASE_URL`,
`MAILER_DSN` (variantes `mailjet+api` et `mailjet+smtp`), clés Turnstile,
`CONTACT_FALLBACK_EMAIL`, Matomo.

### `documentation/deploiement.md`

- Date de mise à jour → 7 septembre 2026
- Section production : passage de `.env.local` à `.env.prod` + `.env.prod.local`
  (copie depuis `.dist`), ajout de `composer dump-env prod`
- Nouvelle sous-section « Envoi des emails : Mailjet » (clés API, vérification
  domaine SPF/DKIM, DSN api vs smtp, commande de vérification, test)
- Nom de base de données aligné `portfolio` → `preprod` (cohérence avec la
  correction du 2026-09-07 sur la connexion DB)
- `composer dump-env prod` ajouté à la procédure de mise à jour

## Ordre de chargement Symfony (rappel)

`.env` → `.env.local` → `.env.prod` → `.env.prod.local` (le dernier gagne).
Donc `.env.prod.local` (secrets) surcharge bien le gabarit de `.env.prod`.

## Vérifications effectuées

```
docker exec -e APP_ENV=prod php bin/console debug:dotenv
  -> APP_ENV=prod, APP_DEBUG=0,
     DEFAULT_URI=https://preprod.michaeljpitz.com,
     MAILER_DSN=mailjet+api://ACCESS_KEY:SECRET_KEY@default

php bin/console debug:config framework mailer --env=prod
  -> dsn: '%env(MAILER_DSN)%', enabled: true

git check-ignore .env.prod.local   -> ignoré
git check-ignore .env.prod .env.prod.local.dist   -> versionnés
```

## À faire côté serveur de production

1. `cp .env.prod.local.dist .env.prod.local`
2. Renseigner `APP_SECRET`, `DATABASE_URL`, les vraies clés Mailjet et Turnstile
3. Vérifier le domaine expéditeur (SPF/DKIM) dans Mailjet
4. `composer dump-env prod`
5. Email de test via le formulaire de contact + contrôle sur https://app.mailjet.com/stats
