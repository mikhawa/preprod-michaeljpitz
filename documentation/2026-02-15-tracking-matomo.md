# Intégration du code de suivi Matomo

**Date** : 2026-02-15

## Contexte

Besoin d'un code de suivi statistique (Matomo) fonctionnant à la fois sur le site public et dans l'interface d'administration EasyAdmin. Le problème : EasyAdmin utilise son propre template de base, indépendant de `base.html.twig`, ce qui empêche une simple inclusion dans le template principal.

## Solution retenue

Utilisation d'un **EventSubscriber** sur `kernel.response` qui injecte automatiquement le script Matomo dans toutes les réponses HTML, quel que soit le template utilisé.

### Avantages de cette approche

- **Couverture complète** : fonctionne sur toutes les pages (publiques, admin, pages d'erreur)
- **Maintenance centralisée** : un seul fichier à modifier, pas de duplication de code
- **Configuration par environnement** : actif uniquement en production via `APP_ENV=prod`
- **Paramétrage via `.env`** : URL et site ID configurables sans toucher au code

## Fichiers créés/modifiés

### Nouveau fichier

- `src/EventSubscriber/TrackingCodeSubscriber.php` : subscriber qui injecte le script Matomo avant `</head>`

### Fichiers modifiés

- `.env` : ajout des variables `MATOMO_URL` et `MATOMO_SITE_ID`
- `src/EventSubscriber/SecurityHeadersSubscriber.php` : mise à jour de la CSP pour autoriser `stats.michaeljpitz.com` dans `script-src` et `connect-src`

## Configuration

Variables d'environnement ajoutées dans `.env` :

```dotenv
MATOMO_URL=//stats.michaeljpitz.com/
MATOMO_SITE_ID=2
```

En production, ces valeurs peuvent être surchargées via `.env.local` ou `.env.prod.local`.

## Fonctionnement technique

1. Le subscriber écoute `kernel.response` avec une priorité de `-10` (s'exécute après le `SecurityHeadersSubscriber`)
2. Il vérifie que :
   - C'est la requête principale (`isMainRequest()`)
   - L'environnement est `prod`
   - Les variables Matomo sont configurées (non vides)
   - La réponse est de type `text/html`
3. Il injecte le script Matomo juste avant la balise `</head>`
4. Les valeurs sont échappées avec `htmlspecialchars()` pour la sécurité

## Sécurité

La Content-Security-Policy a été mise à jour pour autoriser le domaine Matomo :

- `script-src` : ajout de `https://stats.michaeljpitz.com` (chargement de `matomo.js`)
- `connect-src` : ajout de `https://stats.michaeljpitz.com` (envoi des données de suivi vers `matomo.php`)

## Vérification

- PHPStan niveau 8 : aucune erreur
- PHP CS Fixer : style conforme
- 142 tests passent (aucune régression)
- Le subscriber ne s'active pas en dev/test, donc aucun impact sur les tests fonctionnels
