# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Langue du projet

Ce projet se fait entièrement en français : code (commentaires, messages de validation, noms de commits), documentation, et échanges. Toutes les actions réalisées (étapes, décisions, résumés) doivent être consignées dans des fichiers `.md` dans le dossier `documentation/`.

## Project Overview

Portfolio CV + blog website for a PHP/Symfony developer. French-language site with light/dark mode, WYSIWYG article editor, and EasyAdmin back-office. Monolithic Symfony architecture with Turbo for interactivity (no API Platform, no SPA).

## Tech Stack

- **PHP 8.3** / **Symfony 7.4 LTS** / **Doctrine ORM 3.x** / **Twig 3.x**
- **MariaDB 10.11 LTS** (not MySQL)
- **AssetMapper** for frontend assets (no Webpack Encore, no build step)
- **Tailwind CSS 3.x** (via CDN or importmap)
- **Stimulus.js** + **Symfony UX Turbo** + **Twig Components**
- **EasyAdmin 4.x** for admin interface
- **VichUploaderBundle** for image uploads
- **suneditor** for WYSIWYG editing (via importmap)
- **Cloudflare Turnstile** for CAPTCHA in contact form
- **HtmlSanitizer** for sanitizing WYSIWYG content
- **PHPUnit 10.x** for testing
- **GitHub Actions** for CI/CD (linting, tests, security checks)

## Development Environment

Docker Compose with 4 services: PHP 8.3-FPM, Nginx (port 8080), MariaDB 10.11 (port 3306), phpMyAdmin (port 8081). Run under Ubuntu WSL2.

## Choix du modèle Claude : 

Voir les règles d'attribution des modèles dans `.claude/models.md`. En résumé :
- Tâches complexes (architecture, sécurité, refactoring majeur) : Claude Opus
- Tâches intermédiaires (controllers avec logique métier, services, tests fonctionnels) : Claude Sonnet
- Tâches simples/répétitives (CRUD basique, migrations, composants Stimulus simples) : Claude Haiku

## Build & Verification Commands

```bash
# Linting
composer validate
php bin/console lint:twig templates/
php bin/console lint:yaml config/
php bin/console lint:container

# Static analysis
vendor/bin/phpstan analyse src --level=8

# Code style
./vendor/bin/php-cs-fixer fix

# Tests
php bin/phpunit

# Security
composer audit

# Database validation
php bin/console doctrine:schema:validate
```

Pre-commit: run all of the above in sequence. `composer validate --strict` for commits.

## Architecture

```
src/
├── Command/             # CLI commands (CreateAdminCommand)
├── Controller/          # Route handlers (HomeController, ArticleController, Admin/)
├── Entity/              # Doctrine entities: User, Article, Category, Comment, Rating, Page
├── EventSubscriber/     # HTTP event subscribers (SecurityHeadersSubscriber)
├── Form/                # Symfony form types (Contact, Profile, Registration, etc.)
├── Repository/          # Doctrine repositories
├── Security/            # UserChecker, AccessDeniedHandler
├── Service/             # Business logic (ImageResizer, TurnstileValidator)
└── Twig/Components/     # Reusable Twig components (NavbarComponent)

templates/               # Twig templates (base.html.twig, home/, article/, components/)
assets/controllers/      # Stimulus controllers (theme_controller.js, editor_controller.js)
assets/styles/           # CSS (app.css)
docker/                  # Nginx config + PHP Dockerfile
```

### Data Model

- **User**: Implements UserInterface + PasswordAuthenticatedUserInterface. Auth via email. Fields: userName, status (0/1/2), avatarName, biography, externalLink1-3, activationToken, resetPasswordToken.
- **Article**: Blog posts with title, slug (unique), content, excerpt, featuredImage, isPublished, timestamps. ManyToMany with Category. Uses lifecycle callbacks for createdAt/updatedAt.
- **Category**: title, slug (unique), color (hex), description, level (0=racine, N=enfant de id N).
- **Comment**: content, isApproved (modération). ManyToOne with User and Article.
- **Rating**: rating (1-5). ManyToOne with User and Article. UniqueConstraint(user, article).
- **Page**: Pages statiques éditables (CV, RGPD). title, slug (unique), content, featuredImage.

## Mandatory Coding Rules

**All PHP files must start with `declare(strict_types=1)`.**

| Forbidden | Use instead |
|-----------|-------------|
| Doctrine annotations | PHP 8 attributes |
| FOSUserBundle | Native Symfony Security |
| Webpack Encore alone | AssetMapper + Symfony UX |
| FOSCKEditor | Trix or TinyMCE via CDN |
| XML config for services/routes | PHP config or attributes |
| Raw SQL / mysql_* | Doctrine ORM with parameters |

## Security Requirements

- Password hashing: bcrypt with cost >= 13
- CSRF protection on all forms
- Rate limiting on login
- Use `#[IsGranted]` attributes on controllers, Voters for resource authorization
- HtmlSanitizer for all WYSIWYG content (configured in `config/packages/html_sanitizer.yaml`)
- HTTP security headers: CSP, X-Frame-Options: DENY, X-Content-Type-Options: nosniff, HSTS in production
- All Doctrine queries must use parameterized queries (no concatenation)
- Twig auto-escaping must remain enabled

## Response Format

When generating code, structure responses as:
1. Official documentation links consulted
2. Files to create/modify (full paths)
3. Complete code (never abbreviate with `...`)
4. Commands to execute (in order)
5. How to verify it works
6. Security checklist points verified
