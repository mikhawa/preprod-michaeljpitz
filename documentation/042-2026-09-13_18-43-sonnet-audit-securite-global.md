# Audit sécurité + vérification globale — 2026-09-13

> Fait suite à l'audit `018-2026-03-22-sonnet-audit-securite.md`. Objectif : vérifier que le site fonctionne correctement (lint, analyse statique, tests) et refaire un point sécurité complet, six mois après le premier audit.

## 1. Vérifications techniques

| Commande | Résultat |
|---|---|
| `php bin/console lint:twig templates/` | OK — 33 fichiers |
| `php bin/console lint:yaml config/` | OK — 29 fichiers |
| `php bin/console lint:container` | OK |
| `vendor/bin/phpstan analyse src --level=8` | OK — 0 erreur |
| `./vendor/bin/php-cs-fixer fix --dry-run` | OK — 0 fichier à corriger |
| `composer validate --strict` | OK |
| `composer audit` | OK — aucune vulnérabilité connue |
| `doctrine:schema:validate` (dans Docker) | OK — mapping + schéma synchronisés |
| `bin/phpunit` (dans Docker) | OK — 142 tests, 272 assertions |

Le site est dans un état technique sain : aucune régression, aucune dépendance vulnérable connue, code conforme au niveau 8 de PHPStan et au style défini par `.php-cs-fixer.dist.php`.

## 2. Suivi des corrections de l'audit `018`

| # | Point | État au 2026-03-22 | État au 2026-09-13 |
|---|---|---|---|
| 1 | XSS contenu articles | Risque accepté (admin seul) | Inchangé — accepté |
| 2 | XSS commentaires | À corriger | ✅ Corrigé — filtre `sanitize_comment` appliqué dans `templates/article/show.html.twig` |
| 3 | XSS pages statiques | Risque accepté (admin seul) | Inchangé — accepté |
| 4 | Rate limiting login | À corriger | ✅ Corrigé — `login_throttling` (5 tentatives/minute) dans `config/packages/security.yaml` |
| 5 | Tokens reset password en clair | À corriger | ✅ Corrigé — `hash('sha256', $token)` dans `ResetPasswordController.php` |
| 6 | CSP trop permissive | Reporté | Toujours reportée (voir §3.3) |
| 7 | Absence de 2FA | Propositions à faire | ✅ Implémentée entre-temps — 2FA par email (voir §4) |
| 8 | Validation `User.status` | À corriger | ✅ Corrigé — `#[Assert\Choice(choices: [0, 1, 2])]` sur `User::$status` |
| 9 | Upload avatar base64 | À corriger | ✅ Corrigé — whitelist stricte + double vérification MIME réelle (`finfo`) dans `ProfileController.php` |
| 10 | `unlink()` manuel avatar | À corriger | ⚠️ Toujours non migré vers VichUploaderBundle (voir §3.5) |

7 points sur 10 traités depuis mars. Bon rythme de résorption de la dette sécurité.

## 3. Nouveaux constats (2026-09-13)

### 3.1 🔴 Purge de la base de données à chaque déploiement — accepté, pas de correction pour l'instant

`.github/workflows/deploy-dev.yml` et `deploy-preprod.yml` exécutent :
```
php bin/console doctrine:fixtures:load --group=prod|dev --no-interaction --env=prod|dev
```
sans l'option `--append`. Par défaut, `doctrine:fixtures:load` **purge toutes les tables** avant de recharger les fixtures. Confirmé par dry-run :
```
(dry-run)
> purging database
> loading App\DataFixtures\Prod\ProdAdminFixtures
...
```
Donc à chaque push sur `dev/main` ou `preprod/main`, tous les articles, commentaires, notes et comptes utilisateurs réels sont effacés et remplacés par les fixtures.

**Décision utilisateur (2026-09-13)** : ce n'est pas encore un problème à ce stade du projet (pas de données réelles à préserver actuellement sur dev/preprod). **Pas de correction pour le moment.** À revoir avant toute mise en production réelle avec de vrais visiteurs/comptes, en ajoutant `--append` (et en excluant les fixtures `Prod*` d'un rechargement systématique une fois des données réelles présentes).

### 3.2 🔴 Mot de passe admin en clair versionné dans Git — corrigé

`PASS_ADMIN` était défini en clair dans `.env`, fichier suivi par Git (contrairement à `.env.local`, ignoré), depuis le tout premier commit du dépôt (`26f77a7`). Ce secret était donc exposé dans l'historique Git à quiconque a accès au dépôt.

**Correction appliquée** :
- Retrait de la valeur de `.env` (remplacée par un commentaire renvoyant vers `.env.local`).
- Ajout de `PASS_ADMIN` dans `.env.local` (non versionné), qui a priorité sur `.env`.
- Vérifié : `doctrine:fixtures:load --group=prod --dry-run` résout toujours la variable et charge correctement `ProdAdminFixtures`.

**Reste à faire (recommandé, non fait ici)** :
- **Faire de même sur les serveurs dev/preprod** : le déploiement se limite à un `git pull`, il n'injecte aucun secret. Si le `.env.local` du serveur ne contient pas déjà `PASS_ADMIN`, le prochain `doctrine:fixtures:load --group=prod` échouera. À vérifier/ajouter manuellement sur le serveur avant le prochain déploiement.
- **Changer le mot de passe admin réel** : la valeur qui était commitée reste lisible dans l'historique Git tant qu'elle n'a pas été remplacée par autre chose en base. Recommandé de la changer dès que possible (ex. via le formulaire de profil ou reset password).
- **Réécriture de l'historique Git** (`git filter-repo` ou équivalent) pour purger définitivement le secret de l'historique : action destructive, à décider séparément avec l'utilisateur si le dépôt a une visibilité qui le justifie.

### 3.3 🟠 CSP avec `unsafe-inline` / `unsafe-eval` — toujours reportée

`SecurityHeadersSubscriber` : `script-src 'self' 'unsafe-inline' 'unsafe-eval' ...`. Nécessaire aujourd'hui pour Tailwind CDN/Stimulus/Suneditor tels qu'intégrés, mais affaiblit fortement la protection XSS de la CSP. Reportée depuis l'audit `018` (point 6). Passer à des nonces par requête est un chantier plus conséquent (compatibilité Turbo + Suneditor à valider), non traité dans cet audit.

### 3.4 🟠 Absence de Voter Symfony

`src/Security/` ne contient que `AccessDeniedHandler` et `UserChecker`, aucun Voter, alors que CLAUDE.md prescrit « Voters for resource authorization ». Risque réel limité aujourd'hui : les seules actions utilisateur écrites (notation d'article, commentaire) sont systématiquement liées à `$this->getUser()`, aucun IDOR trouvé. Mais l'inscription publique (`/inscription`, rôle `ROLE_USER`) crée des comptes non-admin, donc le gap est à combler avant d'ajouter de nouvelles fonctionnalités utilisateur (édition/suppression de ses propres commentaires, par exemple).

### 3.5 🟠 Upload avatar hors VichUploaderBundle

`ProfileController::processAvatarUpload()` gère l'upload d'avatar en base64 avec `file_put_contents()`/`unlink()` manuels, alors que VichUploaderBundle est la brique retenue par CLAUDE.md pour tous les uploads (et déjà utilisée pour articles/pages). Point ouvert depuis l'audit `018` (point 10). Pas de faille identifiée (whitelist MIME stricte + vérification binaire réelle en place), mais incohérence d'architecture à résorber.

### 3.6 🟡 Header `X-XSS-Protection` obsolète

Toujours envoyé par `SecurityHeadersSubscriber`. Header retiré des navigateurs modernes (Chrome/Firefox/Edge l'ignorent), sans effet ni risque, mais n'apporte plus rien. Nettoyage mineur possible.

### 3.7 🟡 Tests de sécurité automatisés

Toujours absents (point ouvert de `securite.md` §13 depuis février). À envisager pour couvrir : throttling login, expiration 2FA, protection CSRF, sanitisation des commentaires.

## 4. 2FA — implémentation constatée (non prévue par l'audit `018`)

Une 2FA par email a été mise en place entre les deux audits (hors du périmètre demandé alors, simplement constatée ici) :
- `TwoFactorLoginSubscriber` : génère un code OTP à 6 chiffres après connexion réussie, réservé aux comptes `ROLE_ADMIN`, désactivé pour les reconnexions "se souvenir de moi".
- Code stocké haché (`sha256`) en session, jamais en clair.
- `TwoFactorGateSubscriber` : bloque l'accès à toute route tant que la vérification n'est pas passée.
- `TwoFactorController::verify()` : comparaison en temps constant (`hash_equals`), expiration à 10 minutes, limite de 5 tentatives avant invalidation de session.

Implémentation propre, aucune faiblesse identifiée.

## 5. Synthèse des actions

| Action | Statut |
|---|---|
| Secret `PASS_ADMIN` déplacé vers `.env.local` | ✅ Fait dans cette session |
| Correction du typo `EMAIL_NOTIFICATI    ONS_FROM` dans `.env` (clé cassée par des espaces, cassait le parsing Dotenv dans Docker) | ✅ Fait dans cette session (corrigé au passage, hors périmètre initial) |
| Purge BDD au déploiement (`--append` manquant) | Décision : pas de correction pour le moment |
| Ajouter `PASS_ADMIN` au `.env.local` des serveurs dev/preprod | À faire manuellement, hors CI |
| Rotation du mot de passe admin réel | Recommandé, non fait |
| Réécriture de l'historique Git | À décider séparément (action destructive) |
| CSP sans `unsafe-inline`/`unsafe-eval` | Reporté |
| Voter Symfony pour l'autorisation par ressource | Proposition, non fait |
| Migration avatar vers VichUploaderBundle | Proposition, non fait |
| Retrait de `X-XSS-Protection` | Proposition mineure, non fait |
| Tests de sécurité automatisés | Proposition, non fait |
