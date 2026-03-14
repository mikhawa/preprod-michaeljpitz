# Projet : Portfolio CV + Blog Personnel

> **Document de spécifications pour Claude.ai**  
> Version : 1.0 | Date : Janvier 2026

---

## 🎯 Objectif du projet

Site personnel présentant mon CV de développeur PHP/Symfony et un système de publication d'articles avec éditeur WYSIWYG. Design moderne, responsive, avec support mode sombre/clair.

---

## ⚠️ Règles obligatoires pour Claude

### À LIRE AVANT TOUTE RÉPONSE

1. **Toujours consulter la documentation officielle** avant de proposer du code
2. **Respecter strictement les versions** définies ci-dessous
3. **Ne pas inventer** de solutions non documentées
4. **Citer les sources** quand une solution provient de la doc officielle
5. **Proposer des alternatives** si une approche n'est plus recommandée

### Ce qui est INTERDIT

| ❌ Interdit | ✅ Utiliser à la place |
|-------------|------------------------|
| Annotations Doctrine | Attributs PHP 8 |
| FOSUserBundle | Symfony Security natif |
| Webpack Encore seul | AssetMapper + Symfony UX |
| FOSCKEditor | Trix ou TinyMCE via CDN |
| Code sans types stricts | `declare(strict_types=1)` |
| XML pour config services/routes | Configuration PHP ou Attributs |
| mysql_* ou requêtes SQL brutes | Doctrine ORM avec paramètres |

---

## 🔧 Stack technique imposée

### Backend

| Composant | Version | Documentation |
|-----------|---------|---------------|
| **PHP** | 8.3.x | https://www.php.net/releases/8.3/en.php |
| **Symfony** | 7.4 LTS | https://symfony.com/doc/7.4/index.html |
| **Doctrine ORM** | 3.x | https://www.doctrine-project.org/projects/doctrine-orm/en/3.3/index.html |
| **Twig** | 3.x | https://twig.symfony.com/doc/3.x/ |

### Base de données

| Composant | Version | Documentation |
|-----------|---------|---------------|
| **MariaDB** | 10.11 LTS | https://mariadb.com/kb/en/mariadb-10-11/ |

### Frontend

| Composant | Version | Documentation |
|-----------|---------|---------------|
| **Symfony AssetMapper** | 7.4 | https://symfony.com/doc/7.4/frontend/asset_mapper.html |
| **Symfony UX Turbo** | current | https://symfony.com/bundles/ux-turbo/current/index.html |
| **Symfony UX Twig Components** | current | https://symfony.com/bundles/ux-twig-component/current/index.html |
| **Stimulus** | current | https://symfony.com/bundles/StimulusBundle/current/index.html |
| **Tailwind CSS** | 3.x | https://tailwindcss.com/docs (via CDN ou importmap) |

### Éditeur WYSIWYG (choisir UN)

| Option | Avantages | Documentation |
|--------|-----------|---------------|
| **Trix** (recommandé) | Léger, intégré EasyAdmin, gratuit | https://trix-editor.org/ |
| **TinyMCE** | Complet, gratuit (core) | https://www.tiny.cloud/docs/ |

---

## 🐳 Environnement de développement

### Configuration requise

- **OS** : Ubuntu LTS sous Windows WSL2
- **Docker** : Docker Compose pour le développement
- **IDE** : PhpStorm ou VS Code avec extensions PHP

### Structure Docker

```yaml
# docker-compose.yml
services:
  php:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    volumes:
      - .:/var/www/html
    depends_on:
      - mariadb

  nginx:
    image: nginx:alpine
    ports:
      - "8080:80"
    volumes:
      - .:/var/www/html
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - php

  mariadb:
    image: mariadb:10.11
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: portfolio
      MYSQL_USER: portfolio
      MYSQL_PASSWORD: portfolio
    ports:
      - "3306:3306"
    volumes:
      - db_data:/var/lib/mysql

volumes:
  db_data:
```

### Dockerfile PHP

```dockerfile
# docker/php/Dockerfile
FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        intl \
        zip \
        opcache

# Install APCu and Xdebug via PECL
RUN pecl install apcu xdebug \
    && docker-php-ext-enable apcu xdebug

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
```

### Configuration Nginx

```nginx
# docker/nginx/default.conf
server {
    listen 80;
    server_name localhost;
    root /var/www/html/public;

    location / {
        try_files $uri /index.php$is_args$args;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass php:9000;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        internal;
    }

    location ~ \.php$ {
        return 404;
    }
}
```

---

## 🚀 Environnement de production

### Option A : VPS sans Docker

- **OS** : Ubuntu 24.04 LTS
- **Serveur web** : Nginx + PHP-FPM
- **Base de données** : MariaDB 10.11
- **SSL** : Let's Encrypt via Certbot

### Option B : VPS avec Docker

- Même stack que développement
- Ajout de Traefik pour reverse proxy + SSL automatique

---

## 📁 Architecture du projet

### Structure des dossiers

```
portfolio/
├── assets/
│   ├── controllers/          # Stimulus controllers
│   │   ├── theme_controller.js
│   │   └── editor_controller.js
│   └── styles/
│       └── app.css
├── config/
│   ├── packages/
│   └── routes/
├── docker/
│   ├── nginx/
│   └── php/
├── public/
│   └── uploads/              # Images uploadées
├── src/
│   ├── Controller/
│   │   ├── HomeController.php
│   │   ├── ArticleController.php
│   │   └── Admin/
│   ├── Entity/
│   │   ├── Article.php
│   │   ├── Category.php
│   │   └── User.php
│   ├── Repository/
│   ├── Service/
│   ├── Security/
│   │   └── Voter/
│   └── Twig/
│       └── Components/
├── templates/
│   ├── base.html.twig
│   ├── home/
│   ├── article/
│   └── components/
├── tests/
├── .env
├── .env.local               # Non versionné
├── docker-compose.yml
└── PROJECT_SPEC.md          # Ce fichier
```

---

## 🗄️ Modèle de données

### Entités

#### User (Administrateur unique)

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
// utilisation des contraintes de validation
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'L\'email ne peut pas être vide.')]
    #[Assert\Email(message: 'L\'email "{{ value }}" n\'est pas une adresse email valide.')]
    private ?string $email = null;

    /** @var list<string> */
    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le mot de passe ne peut pas être vide.')]
    #[Assert\Length(
        min: 8,
        max: 255,
        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.'
    )]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le nom d\'utilisateur ne peut pas être vide.')]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: 'Le nom d\'utilisateur doit contenir au moins {{ limit }} caractères.', maxMessage: 'Le nom d\'utilisateur ne peut pas dépasser {{ limit }} caractères.'
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9_]+$/',
        message: 'Le nom d\'utilisateur ne peut contenir que des lettres, des chiffres et des underscores.'
    )]
    private ?string $userName = null;

    // Getters et setters...
}
```

#### Article

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options: ['unsigned' => true])] // entier non signé
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre ne peut pas être vide.')]
    #[Assert\Length(
        min: 3,
        max: 150,
        minMessage: 'Le titre doit comporter au moins {{ limit }} caractères.',
        maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $title = null;

    #[ORM\Column(length: 154, unique: true)]
    #[Assert\NotBlank(message: 'Le slug ne peut pas être vide.')]
    #[Assert\Length(
        min: 7,
        max: 154,
        minMessage: 'Le slug doit comporter au moins {{ limit }} caractères.',
        maxMessage: 'Le slug ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Le texte ne peut pas être vide.')]
    #[Assert\Length(
        min: 20,
        minMessage: 'Le texte doit comporter au moins {{ limit }} caractères.'
    )]
    private ?string $content = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 300)]
    private ?string $excerpt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $featuredImage = null;

    #[ORM\Column(nullable: true, options: ['default' => false])]
    private bool $isPublished = false;

    #[ORM\Column(nullable: true, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'articles')]
    private ?Category $category = null;

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // Getters et setters...
}
```

#### Category

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
// utilisation des contraintes de validation
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le titre ne peut pas être vide.')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Le titre doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $title = null;

    #[ORM\Column(length: 104, unique: true)]
    #[Assert\NotBlank(message: 'Le slug ne peut pas être vide')]
    #[Assert\Length(
        min: 2,
        max: 104,
        minMessage: 'Le slug doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le slug ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $slug = null;

    #[ORM\Column(length: 7, nullable: true)]
    private ?string $color = null;

   #[ORM\Column(length: 600, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true, options: ['default' => 0, 'unsigned' => true])]
    private ?int $level = null;

    // Getters et setters...
}
```

---

## 🎨 Design et UI/UX

### Palette de couleurs

```css
:root {
  /* Mode clair */
  --bg-primary: #ffffff;
  --bg-secondary: #f8fafc;
  --text-primary: #1e293b;
  --text-secondary: #64748b;
  --accent: #3b82f6;
  --accent-hover: #2563eb;
  --border: #e2e8f0;
}

[data-theme="dark"] {
  /* Mode sombre */
  --bg-primary: #0f172a;
  --bg-secondary: #1e293b;
  --text-primary: #f1f5f9;
  --text-secondary: #94a3b8;
  --accent: #60a5fa;
  --accent-hover: #3b82f6;
  --border: #334155;
}
```

### Stimulus Controller pour le thème

```javascript
// assets/controllers/theme_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['toggle'];

    connect() {
        this.loadTheme();
    }

    toggle() {
        const currentTheme = document.documentElement.dataset.theme;
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        this.setTheme(newTheme);
    }

    loadTheme() {
        const saved = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const theme = saved || (prefersDark ? 'dark' : 'light');
        this.setTheme(theme);
    }

    setTheme(theme) {
        document.documentElement.dataset.theme = theme;
        localStorage.setItem('theme', theme);
        this.updateToggleIcon(theme);
    }

    updateToggleIcon(theme) {
        if (this.hasToggleTarget) {
            this.toggleTarget.innerHTML = theme === 'dark' ? '☀️' : '🌙';
        }
    }
}
```

### Structure des pages

1. **Page d'accueil** : Hero + Présentation + Compétences + Derniers articles
2. **CV** : Expériences + Formations + Compétences techniques
3. **Articles** : Liste paginée avec filtres par catégorie
4. **Article** : Contenu complet + Articles similaires
5. **Contact** : Formulaire + Liens sociaux

---

## 🔐 Sécurité

### Checklist par fonctionnalité

Pour chaque fonctionnalité développée, vérifier :

```markdown
## Audit sécurité : [Nom de la fonctionnalité]

### Injection SQL/XSS
- [ ] Toutes les requêtes utilisent les paramètres Doctrine
- [ ] Pas de concaténation SQL
- [ ] Échappement automatique Twig activé
- [ ] HtmlSanitizer pour le contenu WYSIWYG

### Authentification
- [ ] Hashage bcrypt (cost >= 13)
- [ ] CSRF sur tous les formulaires
- [ ] Rate limiting sur login
- [ ] Remember me sécurisé

### Autorisation
- [ ] Voters pour les ressources
- [ ] #[IsGranted] sur les controllers
- [ ] Vérification ownership

### Headers HTTP
- [ ] Content-Security-Policy
- [ ] X-Frame-Options: DENY
- [ ] X-Content-Type-Options: nosniff
- [ ] Strict-Transport-Security (prod)

### Données sensibles
- [ ] .env.local non versionné
- [ ] Pas de secrets en clair dans le code
- [ ] Logs sans données personnelles
```

### Configuration sécurité Symfony

```yaml
# config/packages/security.yaml
security:
    password_hashers:
        Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface:
            algorithm: bcrypt
            cost: 13

    providers:
        app_user_provider:
            entity:
                class: App\Entity\User
                property: email

    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false
        main:
            lazy: true
            provider: app_user_provider
            form_login:
                login_path: app_login
                check_path: app_login
                enable_csrf: true
            logout:
                path: app_logout
            remember_me:
                secret: '%kernel.secret%'
                secure: true
                httponly: true

    access_control:
        - { path: ^/admin, roles: ROLE_ADMIN }
```

### HtmlSanitizer pour le WYSIWYG

```yaml
# config/packages/html_sanitizer.yaml
framework:
    html_sanitizer:
        sanitizers:
            article_sanitizer:
                allow_safe_elements: true
                allow_elements:
                    a: ['href', 'title', 'target']
                    img: ['src', 'alt', 'title']
                    iframe: ['src', 'width', 'height']
                force_https_urls: true
```

---

## 🛠️ Commandes de vérification

### Développement quotidien

```bash
# Vérification du code
composer validate
php bin/console lint:twig templates/
php bin/console lint:yaml config/
php bin/console lint:container

# Analyse statique
vendor/bin/phpstan analyse src --level=8

# Tests
php bin/phpunit

# Sécurité
composer audit
php bin/console security:check

# Base de données
php bin/console doctrine:schema:validate
```

### Avant chaque commit

```bash
# Script à exécuter
#!/bin/bash
set -e

echo "🔍 Validation composer..."
composer validate --strict

echo "🔍 Lint Twig..."
php bin/console lint:twig templates/

echo "🔍 Lint YAML..."
php bin/console lint:yaml config/

echo "🔍 PHPStan niveau 8..."
vendor/bin/phpstan analyse src --level=8

echo "🔍 Audit sécurité..."
composer audit

echo "✅ Toutes les vérifications passées!"
```

---

## 📚 Documentation de référence

### Liens essentiels

| Sujet | URL |
|-------|-----|
| Symfony 7.4 | https://symfony.com/doc/7.4/index.html |
| Symfony Security | https://symfony.com/doc/7.4/security.html |
| Doctrine ORM | https://www.doctrine-project.org/projects/doctrine-orm/en/3.3/index.html |
| Symfony UX | https://ux.symfony.com/ |
| Twig Components | https://symfony.com/bundles/ux-twig-component/current/index.html |
| Live Components | https://symfony.com/bundles/ux-live-component/current/index.html |
| Turbo | https://symfony.com/bundles/ux-turbo/current/index.html |
| Stimulus | https://symfony.com/bundles/StimulusBundle/current/index.html |
| AssetMapper | https://symfony.com/doc/7.4/frontend/asset_mapper.html |
| Trix Editor | https://trix-editor.org/ |
| Tailwind CSS | https://tailwindcss.com/docs |

### Projets de référence

| Projet | Description | URL |
|--------|-------------|-----|
| Symfony Demo | Application officielle de démo | https://github.com/symfony/demo |
| Symfony UX | Tous les composants UX | https://github.com/symfony/ux |
| EasyAdmin Demo | Exemples administration | https://github.com/EasyCorp/EasyAdminBundle |

---

## 📋 Phases de développement

### Phase 1 : Setup (Semaine 1)

- [ ] Configuration Docker
- [ ] Installation Symfony 7.4
- [ ] Configuration AssetMapper + Tailwind
- [ ] Structure de base Twig
- [ ] Mode sombre/clair avec Stimulus

**Vérification sécurité Phase 1 :**
- [ ] .env.local créé et dans .gitignore
- [ ] APP_SECRET généré et unique
- [ ] HTTPS forcé en config (prod)

### Phase 2 : Entités et Admin (Semaine 2)

- [ ] Création des entités avec attributs PHP 8
- [ ] Migrations Doctrine
- [ ] Configuration EasyAdmin
- [ ] Intégration Trix/TinyMCE
- [ ] Upload d'images sécurisé

**Vérification sécurité Phase 2 :**
- [ ] Validation des uploads (types MIME, taille)
- [ ] HtmlSanitizer configuré pour WYSIWYG
- [ ] CSRF sur tous les formulaires admin

### Phase 3 : Frontend public (Semaine 3)

- [ ] Page d'accueil avec CV
- [ ] Liste des articles avec pagination
- [ ] Page article détaillée
- [ ] Composants Twig réutilisables
- [ ] SEO (meta, sitemap, robots.txt)

**Vérification sécurité Phase 3 :**
- [ ] Headers sécurité configurés
- [ ] Rate limiting sur les routes sensibles
- [ ] Cache HTTP approprié

### Phase 4 : Finalisation (Semaine 4)

- [ ] Tests fonctionnels
- [ ] Optimisation performances
- [ ] Documentation
- [ ] Préparation déploiement

**Vérification sécurité Phase 4 :**
- [ ] Audit complet de sécurité
- [ ] Tests de pénétration basiques
- [ ] Logs configurés sans données sensibles

---

## 📝 Format des réponses attendues de Claude

Quand tu génères du code, structure ta réponse ainsi :

1. **Documentation consultée** (liens vers les docs officielles utilisées)
2. **Fichiers à créer/modifier** (liste complète avec chemins)
3. **Code complet** (jamais de `...` ou de raccourcis)
4. **Commandes à exécuter** (dans l'ordre)
5. **Tests à effectuer** (comment vérifier que ça fonctionne)
6. **Points de sécurité vérifiés** (checklist appliquée)

### Exemple de demande bien formulée

```
Crée le contrôleur ArticleController avec :
- Route /articles (liste paginée)
- Route /article/{slug} (détail)
- Utilise la doc Symfony 7.4 pour les attributs de route
- Applique la checklist sécurité définie dans ce projet
```

---

## ❓ Questions/Réponses prédéfinies

**Q: Quel bundle pour l'admin ?**
R: EasyAdmin 4.x - https://symfony.com/bundles/EasyAdminBundle/4.x/index.html

**Q: Comment gérer les uploads d'images ?**
R: VichUploaderBundle - https://github.com/dustin10/VichUploaderBundle

**Q: Faut-il utiliser API Platform ?**
R: Non nécessaire pour ce projet. Le site est un monolithe avec Turbo pour l'interactivité.

**Q: Webpack Encore ou AssetMapper ?**
R: AssetMapper (recommandé pour Symfony 7.x, pas de build step nécessaire)

**Q: MySQL ou MariaDB ?**
R: MariaDB 10.11 LTS comme spécifié dans ce document.

---

*Dernière mise à jour : Janvier 2026*
