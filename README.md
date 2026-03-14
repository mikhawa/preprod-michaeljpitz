# Preprod de michaeljpitz.com

Surnommé mikhawa, je suis un développeur web passionné avec une solide expérience en Symfony, Devops et bases de données. Ce projet représente la seconde version de mon site personnel, conçu pour mettre en valeur mes compétences et projets.
 
## Site de Michael J. Pitz

### Version 2

Copié de la section "Version 2" du README.md de mon projet GitHub, voici une présentation détaillée de la seconde version de mon site personnel, développée avec Symfony 7.4, Tailwind CSS et Docker. Le projet est hébergé sur GitHub :

https://github.com/mikhawa/preprod-michaeljpitz

### URL de la version de développement :
| Service               | Port | URL                                  |
|-----------------------|------|--------------------------------------|
 | Site web              | 8080 | http://localhost:8080                |
 | phpMyAdmin            | 8081 | http://localhost:8081/               |
 | SMTP Mailpit          | 1025 | smtp://mailpit:1025 (interne Docker) |
| Interface web Mailpit | 8025 | http://localhost:8025                |


### URL de la version preprod :

https://preprod.michaeljpitz.com/

### Version 1

Il s'agit de la deuxième version du site, qui se trouve sur github à l'URL suivante : https://github.com/mikhawa/cv-mikhawa et qui se trouve en ligne à l'URL suivante : https://alpha1.michaeljpitz.com/

#### Admin

Email : michaeljpitz@gmail.com

Password : erapacha1988ZZZ

#### User de test

Email : michael.pitz@cf2m.be

Nom d'utilisateur : Mikhawa

Password : bébéTest1234!

Mail :

https://nicolaspitz.be:8090/snappymail/

Email : contact@alpha1.michaeljpitz.com

Password : 1UgOWwYRSaieQT4kn1zd

### Raccourcis

- [raccourcis](RACCOURCIS.md)

### Pour le développement distant changer ces fichiers en retirant le .back
/.env.local.back
/.env.local.php.back

## Installation et démarrage

### Lancer les conteneurs Docker
```bash
docker compose up -d --build
```

### URL d'accès au site

- http://localhost:8080 (Site web)
- http://localhost:8081/ phpMyAdmin (MariaDB)

### Accéder au conteneur PHP
```bash 
docker compose exec php sh
```

### Installer les dépendances Composer
```bash
composer install
```
### Créer la base de données et exécuter les migrations
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```
### tailwind

```bash
php bin/console tailwind:build
```
### php-cs-fixer (optionnel)
```bash
composer require --dev friendsofphp/php-cs-fixer
```
Pour formater le code selon les standards PSR-12 :
```bash
./vendor/bin/php-cs-fixer fix
```

### Débogage traductions

```bash
php bin/console debug:translation fr
```

### Comptes utilisateurs locaux

Admin initial : 

Email :

    admin@portfolio.local 

UserName :

    admin

Password :

    admin123!  

---

User de test :

Email :



UserName :

    mikhawa

Password :

    Test1234!

---

User de test :

Email :

    michael.j.pitz@gmail.com

UserName :

    MikePitz

Password :

    Test12345678910!

---

User de test :

Email :

    michael.j.pitz@gmail.com

UserName :

    ElisaPitz

Password :

    mp3mp3mp3

Merci de changer le mot de passe après la première connexion.

### Fonctionnalités implémentées

- [x] Liens externes ouverts dans un nouvel onglet
- [x] Mailjet pour l'envoi d'emails transactionnels
- [x] Redirection vers la page demandée après connexion
- [x] Modification du mot de passe dans EasyAdmin
- [x] Réinitialisation de mot de passe par email
- [x] Validation de compte par email
- [x] Formulaire de contact avec captcha Turnstile
- [x] Page de profil utilisateur avec avatar et biographie
- [x] Profil public des utilisateurs
- [x] Modération des commentaires
- [x] Lightbox pour les images des articles
- [x] Service ImageResizer pour les uploads
- [x] Pages éditables (CV, RGPD) depuis EasyAdmin
- [x] Support fichiers téléchargeables (PDF, DOC, ZIP) dans l'éditeur Trix
- [x] Tests unitaires et fonctionnels PHPUnit (24 fichiers de test)

## Gestion des mails en développement

### Mail Turnstile Cloudflare

Pour la production, remplacez les clés de test dans .env.local :


TURNSTILE_SITE_KEY=votre_vraie_clé

TURNSTILE_SECRET_KEY=votre_vraie_clé_secrète

CONTACT_FALLBACK_EMAIL=votre@email.com

Clés disponibles gratuitement sur https://dash.cloudflare.com/turnstile

### Version de développement

| Date début        | Date courante |
|-------------------|---------------|
| 2026-01-31        | 2026-02-11    |
| Version 1.0.0     | Version 1.7.1 |
| 2026-02-11        | 2026-02-11    |
| Version 2.0.0     | Version 2.0.0 |
| 2026-02-11        | 2026-02-12    |
 | Version Alpha.1-b | Version Alpha.1-b |

URL version alpha.1-b :

https://alpha1.michaeljpitz.com/

### Installation de suneditor

php bin/console importmap:require suneditor

### Documentation Claude

https://github.com/mikhawa/claude-code-cheat-sheet
