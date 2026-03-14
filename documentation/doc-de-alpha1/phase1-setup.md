# Phase 1 : Setup du projet

> Date : 31 janvier 2026

## Actions réalisées

### 1. Structure Docker

- Création de `docker-compose.yml` avec 4 services :
  - **php** : PHP 8.3-FPM Alpine avec extensions (pdo_mysql, mbstring, gd, intl, zip, opcache, APCu, Xdebug)
  - **nginx** : Alpine, port 8080, configuration pour Symfony (try_files vers index.php)
  - **mariadb** : 10.11 LTS, port 3306, base "portfolio"
  - **phpMyAdmin** : port 8081 (administration BDD)
- Création de `docker/php/Dockerfile` : corrigé par rapport au PROJECT_SPEC (utilisation de `apk` au lieu de `apt-get` pour Alpine)
- Création de `docker/nginx/default.conf` : conforme au PROJECT_SPEC

### 2. Installation Symfony 7.4

- Installation via `composer create-project symfony/skeleton:"7.4.*"`
- Installation du pack webapp : `composer require webapp`
  - Inclut : Twig 3.x, Doctrine ORM 3.x, Security, AssetMapper, Stimulus, UX Turbo, Mailer, Messenger, PHPUnit, MakerBundle
- Suppression du service PostgreSQL ajouté automatiquement par la recipe Doctrine (remplacé par notre MariaDB)

### 3. Configuration base de données

- `DATABASE_URL` configuré pour MariaDB : `mysql://portfolio:portfolio@mariadb:3306/portfolio?serverVersion=10.11.0-MariaDB`
- `config/packages/doctrine.yaml` : `server_version` mis à `10.11.0-MariaDB`, suppression de la config PostgreSQL
- `MAILER_DSN` configuré pour Mailjet : `mailjet+api://ACCESS_KEY:SECRET_KEY@default`
- Connexion à la base vérifiée : `doctrine:database:create --if-not-exists` OK

### 4. Configuration AssetMapper + Tailwind

- Installation de `symfonycasts/tailwind-bundle` v0.12
- Tailwind v4 détecté automatiquement (pas de fichier tailwind.config.js nécessaire)
- Build Tailwind fonctionnel : `php bin/console tailwind:build`

### 5. Structure Twig de base

- `templates/base.html.twig` : layout principal avec header/nav/main/footer, support du thème via `data-theme`, classes Tailwind avec variables CSS
- `templates/home/index.html.twig` : page d'accueil placeholder
- `src/Controller/HomeController.php` : route `/` (name: `app_home`), `declare(strict_types=1)`

### 6. Mode sombre/clair

- `assets/styles/app.css` : palette de couleurs du PROJECT_SPEC en variables CSS (`:root` pour clair, `[data-theme="dark"]` pour sombre)
- `assets/controllers/theme_controller.js` : controller Stimulus conforme au PROJECT_SPEC
  - Détection de la préférence système (`prefers-color-scheme`)
  - Persistance du choix dans `localStorage`
  - Bouton toggle dans le header

### 7. Sécurité Phase 1

| Point | Statut |
|-------|--------|
| `.env.local` dans `.gitignore` | OK (géré par la recipe symfony/framework-bundle) |
| `APP_SECRET` généré et unique | OK (32 caractères hex dans `.env.local`) |
| `composer audit` sans vulnérabilité | OK |
| Conteneur Symfony valide | OK (`lint:container` passe) |
| Templates Twig valides | OK (`lint:twig` passe) |
| Config YAML valide | OK (`lint:yaml` passe) |

## Choix techniques

| Décision | Choix | Raison |
|----------|-------|--------|
| WYSIWYG | Trix | Recommandé par le PROJECT_SPEC, léger, intégré EasyAdmin |
| Dockerfile | Alpine avec apk | Le PROJECT_SPEC utilisait apt-get sur Alpine (incompatible), corrigé |
| Tailwind | symfonycasts/tailwind-bundle | Intégré au workflow AssetMapper, pas de CDN |
| Tailwind version | v4 (détectée automatiquement) | Version courante du bundle |

## Vérification

```bash
# Le site est accessible sur http://localhost:8080
# La page d'accueil affiche le layout avec header, contenu et footer
# Le bouton toggle change entre mode clair et sombre
# Le choix de thème persiste après rechargement de la page
```
