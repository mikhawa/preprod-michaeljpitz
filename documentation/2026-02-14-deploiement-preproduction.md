# Déploiement en préproduction — Alpha.1

> Date : 2026-02-14
> Environnement cible : Serveur PHP 8.3 + Apache + MariaDB (sans Docker)

---

## Résumé des fichiers créés ou modifiés

### Fichiers créés

| Fichier | Rôle |
|---|---|
| `.env.prod.local.dist` | Template des variables d'environnement pour le serveur (à copier en `.env.local`) |
| `.gitattributes` | Exclut les fichiers de développement des archives `git archive` (docker, tests, docs, .claude) |
| `public/.htaccess` | Réécriture d'URL Apache, blocage PHP, cache assets, compression gzip |
| `documentation/2026-02-14-deploiement-preproduction.md` | Ce fichier de documentation |

### Fichiers modifiés

| Fichier | Modification | Raison |
|---|---|---|
| `src/EventSubscriber/SecurityHeadersSubscriber.php` | Ajout du header HSTS (`Strict-Transport-Security`) activé uniquement en `prod` via injection de `%kernel.environment%` | Sécurité HTTPS obligatoire en production |
| `config/packages/monolog.yaml` | Section `when@prod` : remplacement de `php://stderr` par `rotating_file` (14 jours erreurs, 7 jours dépréciations) | Logs dans des fichiers avec rotation automatique sur serveur classique |
| `tests/Unit/EventSubscriber/SecurityHeadersSubscriberTest.php` | Passage du paramètre `$environment` au constructeur + 2 nouveaux tests (`testHstsNotAddedOutsideProd`, `testHstsAddedInProd`) | Adaptation au nouveau constructeur du subscriber |
| `assets/app.js` | Import direct de `startStimulusApp` depuis `@symfony/stimulus-bundle` (au lieu de passer par `stimulus_bootstrap.js`) | Correction du doublon `startStimulusApp` lors de la compilation AssetMapper |

### Fichiers supprimés

| Fichier | Raison |
|---|---|
| `assets/stimulus_bootstrap.js` | Fichier intermédiaire inutile : son contenu (import + appel de `startStimulusApp`) a été déplacé dans `assets/app.js`. AssetMapper compilait ce fichier séparément, causant une double déclaration de `startStimulusApp` en production |

---

## 1. Prérequis serveur

### Extensions PHP requises

```bash
php8.3-fpm          # ou mod_php / php8.3-cgi selon la configuration Apache
php8.3-mysql        # ou php8.3-pdo-mysql
php8.3-intl
php8.3-mbstring
php8.3-xml
php8.3-curl
php8.3-gd           # pour ImageResizer
```

### Modules Apache requis

```bash
sudo a2enmod rewrite expires deflate headers
sudo systemctl restart apache2
```

### Outils

- Composer 2.x
- MariaDB 10.11+
- Apache 2.4+ avec `AllowOverride All`
- Certbot (Let's Encrypt) pour HTTPS

---

## 2. Variables d'environnement

Copier `.env.prod.local.dist` en `.env.local` à la racine du projet sur le serveur et remplir les valeurs. Ce fichier n'est jamais commité (déjà dans `.gitignore`).

```bash
cp .env.prod.local.dist .env.local
# Puis éditer .env.local avec les vraies valeurs
```

### Valeurs à remplir

| Variable | Description |
|---|---|
| `APP_SECRET` | Générer avec : `php -r "echo bin2hex(random_bytes(16));"` |
| `DATABASE_URL` | Connexion MariaDB du serveur (`127.0.0.1` au lieu de `mariadb`) |
| `MAILER_DSN` | Vrai serveur SMTP (OVH, Gmail, etc.) |
| `TURNSTILE_SITE_KEY` | Vraie clé site Cloudflare (pas la clé de test) |
| `TURNSTILE_SECRET_KEY` | Vraie clé secrète Cloudflare |
| `CONTACT_FALLBACK_EMAIL` | Email de réception des formulaires de contact |
| `DEFAULT_URI` | URL publique du site (`https://votre-domaine.com`) |

### Différences clés avec le développement Docker

| Variable | Docker (dev) | Serveur (prod) |
|---|---|---|
| `APP_ENV` | `dev` | `prod` |
| `DATABASE_URL` | `...@mariadb:3306/...` | `...@127.0.0.1:3306/...` |
| `MAILER_DSN` | `smtp://mailpit:1025` | `smtp://user:pass@smtp.provider:587` |
| `TURNSTILE_*` | Clés de test Cloudflare | Vraies clés de production |
| `DEFAULT_URI` | `http://localhost` | `https://votre-domaine.com` |

---

## 3. Commandes de déploiement

Exécuter dans l'ordre strict :

```bash
# 1. Installer les dépendances SANS les packages dev
composer install --no-dev --optimize-autoloader

# 2. Compiler le .env en fichier PHP (plus rapide que parser les fichiers .env)
composer dump-env prod

# 3. Vider et préchauffer le cache
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

# 4. Compiler Tailwind CSS (minifié pour la prod)
php bin/console tailwind:build --minify

# 5. Supprimer les anciens assets compilés avant recompilation
rm -rf public/assets/

# 6. Compiler les assets AssetMapper
php bin/console asset-map:compile --env=prod

# 7. Installer les assets publics (bundles)
php bin/console assets:install public

# 8. Exécuter les migrations de base de données
php bin/console doctrine:migrations:migrate --no-interaction

# 9. Valider le schéma Doctrine
php bin/console doctrine:schema:validate
```

### Piège AssetMapper : doublon Stimulus

**Ne jamais lancer `asset-map:compile` en mode `dev`** (quand `APP_ENV=dev`). AssetMapper sert les assets à la volée en dev, et les fichiers compilés dans `public/assets/` en prod. Si les deux coexistent, les scripts sont chargés deux fois, provoquant l'erreur :

```
Uncaught SyntaxError: Identifier 'startStimulusApp' has already been declared
```

**Solution** : toujours s'assurer que `APP_ENV=prod` est actif avant `asset-map:compile`, et supprimer `public/assets/` avant recompilation.

**En local (dev)** : si `asset-map:compile` a été lancé par erreur, supprimer `public/assets/` pour revenir au fonctionnement à la volée.

### Correction appliquée : suppression de `stimulus_bootstrap.js`

Le fichier `assets/stimulus_bootstrap.js` importait `startStimulusApp` depuis `@symfony/stimulus-bundle` et l'appelait. Ce fichier était lui-même importé par `assets/app.js`. Lors de la compilation AssetMapper en production, le contenu était inliné dans le fichier compilé, mais le module `@symfony/stimulus-bundle` exportait déjà le même identifiant, provoquant :

```
Uncaught SyntaxError: Identifier 'startStimulusApp' has already been declared
```

**Correction** : le contenu de `stimulus_bootstrap.js` a été fusionné directement dans `assets/app.js`, et le fichier intermédiaire supprimé. Le fichier `app.js` résultant :

```javascript
import { startStimulusApp } from '@symfony/stimulus-bundle';
import './styles/app.css';

const app = startStimulusApp();
```

---

## 4. Configuration Apache

### VirtualHost

```apache
<VirtualHost *:80>
    ServerName votre-domaine.com
    DocumentRoot /chemin/vers/cv-mikhawa/public

    <Directory /chemin/vers/cv-mikhawa/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Bloquer l'accès aux dossiers hors public
    <DirectoryMatch "/chemin/vers/cv-mikhawa/(config|src|templates|var|vendor)">
        Require all denied
    </DirectoryMatch>
</VirtualHost>
```

**Points critiques** :
- Le `DocumentRoot` doit pointer vers le dossier **`public/`** (pas la racine du projet)
- `AllowOverride All` est **obligatoire** pour que le `.htaccess` soit pris en compte
- Sans `AllowOverride All`, Apache ignore les règles de réécriture et les routes Symfony ne fonctionnent pas

### Fichier `public/.htaccess`

Le fichier `.htaccess` créé gère :

1. **Réécriture d'URL** — Toutes les requêtes qui ne correspondent pas à un fichier existant sont redirigées vers `index.php`
2. **Blocage PHP** — Seul `index.php` est accessible, tous les autres `.php` retournent 403
3. **Fichiers sensibles** — `.env`, `composer.json`, etc. bloqués
4. **Redirection HTTPS** — 3 lignes à décommenter une fois le certificat SSL installé
5. **Cache statique** — 1 an pour CSS/JS/images (AssetMapper gère le versioning via les noms de fichiers)
6. **Compression gzip** — Pour les fichiers texte (HTML, CSS, JS, JSON, SVG)

---

## 5. Permissions des fichiers

L'utilisateur du serveur web doit pouvoir écrire dans `var/` (cache, logs, sessions) et `public/uploads/` (images). Sur le serveur de préproduction `alpha1.michaeljpitz.com`, l'utilisateur est `micha5214`.

```bash
# Identifier l'utilisateur du serveur web
ps aux | grep apache
# ou : ps aux | grep httpd

# Donner les droits à l'utilisateur du serveur web
# Remplacer micha5214:micha5214 par l'utilisateur/groupe de votre serveur
sudo chown -R micha5214:micha5214 var/
sudo chown -R micha5214:micha5214 public/uploads/

# Permissions restrictives sur le reste du projet
sudo find . -type f -exec chmod 644 {} \;
sudo find . -type d -exec chmod 755 {} \;

# Écriture pour le cache, les logs et les uploads
sudo chmod -R 775 var/
sudo chmod -R 775 public/uploads/
```

### Problème rencontré : erreur 500 par permissions

Lors du premier déploiement, toutes les pages d'administration (notamment `/admin/article/new`) retournaient une erreur 500. Les logs Symfony n'étaient pas écrits non plus, avec l'erreur :

```
The stream or file "var/log/prod-*.log" could not be opened in append mode:
Failed to open stream: Permission denied
```

**Cause** : le dossier `var/` (cache + logs) n'était pas accessible en écriture par l'utilisateur Apache du serveur.

**Solution** : appliquer les commandes `chown` et `chmod` ci-dessus, puis reconstruire le cache :

```bash
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod
```

---

## 6. Dossiers d'upload

Créer les répertoires attendus par VichUploaderBundle :

```bash
mkdir -p public/uploads/articles
mkdir -p public/uploads/avatars
mkdir -p public/uploads/pages
```

---

## 7. HTTPS et sécurité

### Certificat SSL

```bash
sudo certbot --apache -d votre-domaine.com
```

Puis décommenter les lignes de redirection HTTPS dans `public/.htaccess` :

```apache
RewriteCond %{HTTPS} off
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### Headers de sécurité

Le `SecurityHeadersSubscriber` ajoute automatiquement sur toutes les réponses :

| Header | Valeur | Environnement |
|---|---|---|
| `X-Content-Type-Options` | `nosniff` | Tous |
| `X-Frame-Options` | `SAMEORIGIN` | Tous |
| `X-XSS-Protection` | `1; mode=block` | Tous |
| `Referrer-Policy` | `strict-origin-when-cross-origin` | Tous |
| `Permissions-Policy` | `camera=(), microphone=(), geolocation=()` | Tous |
| `Content-Security-Policy` | `default-src 'self'; ...` | Tous |
| **`Strict-Transport-Security`** | **`max-age=31536000; includeSubDomains`** | **`prod` uniquement** |

Le header HSTS est ajouté côté PHP (dans le subscriber) et non dans la config Apache, ce qui garantit qu'il est présent sur toutes les réponses y compris les erreurs Symfony.

### Rappel

Le cookie `remember_me` a `secure: true` dans `security.yaml` → il ne fonctionne **qu'en HTTPS**.

---

## 8. Logs en production

La configuration `monolog.yaml` section `when@prod` a été modifiée pour utiliser `rotating_file` au lieu de `php://stderr` :

- **Erreurs applicatives** : `var/log/prod.log` — rotation automatique sur 14 jours
- **Dépréciations** : `var/log/deprecation.log` — rotation automatique sur 7 jours

Les fichiers rotés sont nommés `prod-YYYY-MM-DD.log`. Les anciens fichiers sont supprimés automatiquement par Monolog au-delà du `max_files` configuré.

---

## 9. Messenger (optionnel)

Si des tâches asynchrones sont utilisées, configurer un worker Supervisor :

```ini
# /etc/supervisor/conf.d/messenger-worker.conf
[program:messenger-consume]
command=php /chemin/vers/cv-mikhawa/bin/console messenger:consume async --time-limit=3600
user=www-data
autostart=true
autorestart=true
stderr_logfile=/var/log/messenger-worker.err.log
stdout_logfile=/var/log/messenger-worker.out.log
```

---

## 10. Tailwind CSS — Binaire

Le `symfonycasts/tailwind-bundle` télécharge automatiquement un binaire Tailwind lors de `tailwind:build`. Vérifier que l'architecture du serveur est compatible (Linux x86_64). Le binaire est stocké dans `var/tailwind/`.

---

## 11. Fichiers exclus du déploiement

Le fichier `.gitattributes` créé à la racine exclut automatiquement ces fichiers/dossiers des archives `git archive` :

```
/.claude                 # Configuration Claude Code
/.env.test               # Variables d'environnement de test
/.php-cs-fixer.dist.php  # Configuration PHP CS Fixer
/docker                  # Configuration Docker
/docker-compose.yml      # Docker Compose
/documentation           # Documentation interne
/phpunit.dist.xml        # Configuration PHPUnit
/tests                   # Tests unitaires et fonctionnels
/CLAUDE.md               # Instructions Claude Code
/CLAUDE_MODEL.md         # Modèle Claude
/RACCOURCIS.md           # Raccourcis développeur
```

---

## 12. Checklist finale avant mise en ligne

### Environnement

- [ ] `.env.local` créé avec les bonnes valeurs de production
- [ ] `APP_ENV=prod` et `APP_DEBUG=0`
- [ ] `APP_SECRET` généré (unique, aléatoire, 32 caractères hex)
- [ ] `DATABASE_URL` pointe vers le bon serveur MariaDB (`127.0.0.1`, pas `mariadb`)
- [ ] `MAILER_DSN` configuré avec un vrai SMTP
- [ ] Clés Turnstile de production configurées
- [ ] `CONTACT_FALLBACK_EMAIL` défini

### Déploiement

- [ ] `composer install --no-dev` exécuté
- [ ] `composer dump-env prod` exécuté
- [ ] Cache vidé et préchauffé (`cache:clear` + `cache:warmup`)
- [ ] Tailwind compilé avec `--minify`
- [ ] `public/assets/` supprimé puis `asset-map:compile --env=prod` exécuté
- [ ] Migrations exécutées
- [ ] Schéma Doctrine validé

### Apache

- [ ] `DocumentRoot` pointe vers `public/`
- [ ] `AllowOverride All` configuré dans le VirtualHost
- [ ] Modules `rewrite`, `expires`, `deflate` activés
- [ ] Fichiers `.php` autres que `index.php` bloqués (via `.htaccess`)

### Sécurité

- [ ] HTTPS activé (Let's Encrypt / Certbot)
- [ ] Redirection HTTP → HTTPS décommentée dans `.htaccess`
- [ ] Header HSTS actif (vérifié via les DevTools navigateur)
- [ ] Dossiers `var/` et `public/uploads/` accessibles en écriture par l'utilisateur Apache (`micha5214` sur le serveur de préprod)
- [ ] Dossiers hors `public/` inaccessibles depuis le web

### Application

- [ ] Compte administrateur créé (`php bin/console app:create-admin email motdepasse username`)
- [ ] Formulaire de contact fonctionnel (test d'envoi d'email)
- [ ] Pas d'erreur JavaScript dans la console navigateur
- [ ] Pas de barre de debug Symfony visible (mode prod)
