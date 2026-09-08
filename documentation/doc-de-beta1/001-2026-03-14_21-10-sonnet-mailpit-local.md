# 001 — Installation de Mailpit en développement local

**Date** : 2026-03-14 à 21h10
**Moteur IA** : Claude Sonnet 4.6
**Branche** : `dev/mailpit-local`

---

## Contexte

Le projet utilisait Mailjet comme transporteur d'emails, y compris en environnement de développement local. Cela obligeait à avoir des clés API valides pour envoyer des emails, et présentait un risque d'envoi accidentel vers de vraies adresses.

## Objectif

Remplacer Mailjet par **Mailpit** en développement local : intercepteur d'emails avec interface web, sans envoi réel. Travailler sur une branche `dev/*` dédiée.

## Actions réalisées

### 1. Création de la branche

```bash
git checkout -b dev/mailpit-local
```

### 2. Modification de `docker-compose.yml`

Ajout du service `mailpit` :

```yaml
mailpit:
  image: axllent/mailpit:latest
  ports:
    - "8025:8025"   # Interface web
    - "1025:1025"   # Serveur SMTP
  environment:
    MP_MAX_MESSAGES: 500
    MP_SMTP_AUTH_ACCEPT_ANY: 1
    MP_SMTP_AUTH_ALLOW_INSECURE: 1
```

### 3. Modification de `.env`

Remplacement du DSN Mailjet par le DSN Mailpit :

```dotenv
# Avant
MAILER_DSN=mailjet+api://ACCESS_KEY:SECRET_KEY@default

# Après
MAILER_DSN=smtp://mailpit:1025
```

Le DSN Mailjet est conservé en commentaire pour rappel. En production, il doit être défini dans `.env.local` (non commité).

### 4. Démarrage des conteneurs

```bash
docker compose up -d
```

Le conteneur `mailpit` démarre avec le statut `healthy`.

## Résultat

| Service | Port | URL |
|---------|------|-----|
| Interface web Mailpit | 8025 | http://localhost:8025 |
| SMTP Mailpit | 1025 | smtp://mailpit:1025 (interne Docker) |

Tous les emails envoyés par Symfony en dev sont capturés par Mailpit. Rien ne sort vers l'extérieur.

## Vérification

```bash
docker compose ps mailpit
# STATUS: Up (healthy)
```

## Sécurité

- Aucune clé API exposée en dev
- Emails interceptés localement, jamais envoyés vers de vraies adresses
- Les clés Mailjet restent dans `.env.local` (hors dépôt git)
