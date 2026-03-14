# Guide de déploiement

> Dernière mise à jour : 16 février 2026

## Prérequis

### Environnement de développement (Docker)

- Docker et Docker Compose installés
- Ubuntu WSL2 (Windows) ou Linux natif
- Ports disponibles : 8080 (nginx), 3306 (MariaDB), 8081 (phpMyAdmin)

### Environnement de production (VPS)

- Ubuntu 24.04 LTS
- PHP 8.3 avec extensions : pdo_mysql, mbstring, gd, intl, zip, opcache, apcu
- Nginx
- MariaDB 10.11 LTS
- Composer 2.x
- Certbot (Let's Encrypt) pour SSL

## Installation en développement

### 1. Cloner le dépôt

```bash
git clone <url-du-depot> cv-mikhawa
cd cv-mikhawa
```

### 2. Configurer les variables d'environnement

```bash
cp .env .env.local
```

Modifier `.env.local` avec les valeurs appropriées :

```env
APP_ENV=dev
APP_SECRET=<générer-une-clé-unique>
DATABASE_URL="mysql://portfolio:portfolio@mariadb:3306/portfolio?serverVersion=10.11.0-MariaDB"
MAILER_DSN=mailjet+api://ACCESS_KEY:SECRET_KEY@default
TURNSTILE_SITE_KEY=1x00000000000000000000AA
TURNSTILE_SECRET_KEY=1x0000000000000000000000000000000AA
CONTACT_FALLBACK_EMAIL=admin@portfolio.local
```

### 3. Lancer les conteneurs Docker

```bash
docker compose up -d
```

### 4. Installer les dépendances

```bash
docker compose exec php composer install
```

### 5. Créer la base de données et exécuter les migrations

```bash
docker compose exec php php bin/console doctrine:database:create --if-not-exists
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
```

### 6. Créer le compte administrateur

```bash
docker compose exec php php bin/console app:create-admin
```

### 7. Compiler Tailwind CSS

```bash
docker compose exec php php bin/console tailwind:build
```

### 8. Vérifier l'installation

```bash
docker compose exec php php bin/console doctrine:schema:validate
docker compose exec php php bin/console lint:container
```

Le site est accessible sur `http://localhost:8080`.

## Déploiement en production (VPS sans Docker)

### 1. Installer les dépendances système

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx mariadb-server php8.3-fpm php8.3-cli \
    php8.3-mysql php8.3-mbstring php8.3-gd php8.3-intl php8.3-zip \
    php8.3-opcache php8.3-apcu php8.3-xml php8.3-curl \
    git unzip certbot python3-certbot-nginx
```

### 2. Configurer MariaDB

```bash
sudo mysql_secure_installation
sudo mysql -u root -p
```

```sql
CREATE DATABASE portfolio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'portfolio'@'localhost' IDENTIFIED BY '<mot-de-passe-fort>';
GRANT ALL PRIVILEGES ON portfolio.* TO 'portfolio'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Cloner et configurer l'application

```bash
cd /var/www
sudo git clone <url-du-depot> cv-mikhawa
sudo chown -R www-data:www-data cv-mikhawa
cd cv-mikhawa
```

Créer `.env.local` :

```env
APP_ENV=prod
APP_SECRET=<clé-secrète-générée>
DATABASE_URL="mysql://portfolio:<mot-de-passe>@127.0.0.1:3306/portfolio?serverVersion=10.11.0-MariaDB"
MAILER_DSN=mailjet+api://<ACCESS_KEY>:<SECRET_KEY>@default
TURNSTILE_SITE_KEY=<votre-clé-turnstile>
TURNSTILE_SECRET_KEY=<votre-clé-secrète-turnstile>
CONTACT_FALLBACK_EMAIL=<votre-email-admin>
```

### 4. Installer les dépendances (production)

```bash
composer install --no-dev --optimize-autoloader
```

### 5. Préparer l'application

```bash
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console app:create-admin
php bin/console tailwind:build --minify
php bin/console asset-map:compile
php bin/console cache:clear --env=prod
```

### 6. Configurer Nginx

```nginx
# /etc/nginx/sites-available/cv-mikhawa
server {
    listen 80;
    server_name votre-domaine.fr www.votre-domaine.fr;
    root /var/www/cv-mikhawa/public;

    location / {
        try_files $uri /index.php$is_args$args;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        internal;
    }

    location ~ \.php$ {
        return 404;
    }

    client_max_body_size 12M;

    # Uploads
    location /uploads/ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # Assets compilés
    location /assets/ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/cv-mikhawa /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 7. Certificat SSL (Let's Encrypt)

```bash
sudo certbot --nginx -d votre-domaine.fr -d www.votre-domaine.fr
```

### 8. Permissions

```bash
sudo chown -R www-data:www-data /var/www/cv-mikhawa/var
sudo chown -R www-data:www-data /var/www/cv-mikhawa/public/uploads
sudo chmod -R 775 /var/www/cv-mikhawa/var
sudo chmod -R 775 /var/www/cv-mikhawa/public/uploads
```

## Commandes de vérification

```bash
# Validation complète (développement)
composer validate --strict
php bin/console lint:twig templates/
php bin/console lint:yaml config/
php bin/console lint:container
vendor/bin/phpstan analyse src --level=8
php bin/phpunit
composer audit
php bin/console doctrine:schema:validate
```

## Mise à jour en production

```bash
cd /var/www/cv-mikhawa
git pull origin main
composer install --no-dev --optimize-autoloader
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console tailwind:build --minify
php bin/console asset-map:compile
php bin/console cache:clear --env=prod
sudo systemctl reload php8.3-fpm
```

## URLs utiles

| Service | URL développement |
|---------|------------------|
| Site web | http://localhost:8080 |
| Administration | http://localhost:8080/admin |
| phpMyAdmin | http://localhost:8081 |
