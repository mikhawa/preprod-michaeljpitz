# Incident : compte Mailjet bloqué (mj-0001 / HTTP 401)

**Date** : 2026-09-06
**Modèle** : Claude Opus
**Branche** : `fix/13-email-mailjet`
**Statut** : en attente d'action côté compte Mailjet (hors projet)

## Contexte

Après réinstallation du bridge `symfony/mailjet-mailer`
(`documentation/040-2026-09-06-opus-retour-a-mailjet.md`), le premier envoi
réel via le DSN `mailjet+api://…` échoue avec :

```
Unable to send an email: "{"ErrorIdentifier":"f606106f-43be-4646-9945-128fef0e8012",
"ErrorCode":"mj-0001","StatusCode":401,
"ErrorMessage":"Your account has been temporarily blocked. Please contact our support team to get assistance."}" (code 401).
```

## Diagnostic

Vérifications côté projet, toutes correctes :

- `MAILER_DSN` bien renseigné dans `.env.local` (non versionné), format
  `mailjet+api://PUBLIC_KEY:PRIVATE_KEY@default`.
- `Transport::fromDsn()` résout bien vers
  `Symfony\Component\Mailer\Bridge\Mailjet\Transport\MailjetApiTransport`
  (vérifié lors de l'opération 040).
- Aucune clé mal formée, aucun caractère spécial non échappé dans le DSN.

L'erreur `mj-0001` avec un statut HTTP 401 est renvoyée **par l'API Mailjet
elle-même**, avant toute tentative d'envoi de message : c'est un refus au
niveau du compte, pas de la requête. Ce n'est donc pas un défaut de
configuration ni un bug du bridge Symfony.

## Causes possibles côté Mailjet (à vérifier avec leur support)

- Vérification d'identité ou de domaine expéditeur incomplète (un compte
  Mailjet récent doit généralement valider son domaine — SPF/DKIM — avant
  de pouvoir envoyer via l'API).
- Détection anti-fraude automatique sur compte nouvellement créé ou avec peu
  d'historique d'envoi.
- Problème de facturation (moyen de paiement, plan expiré).

## Action requise (hors dépôt)

1. Se connecter à https://app.mailjet.com et vérifier les bannières
   d'alerte sur le compte.
2. Contacter le support Mailjet (https://app.mailjet.com/support) en
   fournissant l'`ErrorIdentifier` ci-dessus (`f606106f-43be-4646-9945-128fef0e8012`)
   pour accélérer le diagnostic.
3. Vérifier/finaliser la validation du domaine expéditeur
   (`EMAIL_NOTIFICATIONS_FROM`, actuellement `noreply@preprod.michaeljpitz.com`)
   dans Mailjet (SPF/DKIM), si ce n'est pas déjà fait.
4. Une fois le compte débloqué, retester l'envoi (formulaire de contact ou
   inscription) et confirmer la réception.

## Points de sécurité

- [x] Aucune clé API Mailjet n'a été partagée ou journalisée en clair dans ce
      diagnostic.
- [x] Aucune modification de code ou de configuration effectuée : l'incident
      est confirmé comme externe au projet.
