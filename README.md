# Preprod de michaeljpitz.com

Surnommé mikhawa, je suis un développeur web passionné avec une solide expérience en Symfony, Devops et bases de données. Ce projet représente la seconde version de mon site personnel, conçu pour mettre en valeur mes compétences et projets.
 
## Site de Michael J. Pitz

# TO DO :
installer Turnstile sur tous les formulaires (contact, login, register, password reset)

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

### Ne pas en preprod oublier les : 

    composer update
    # et
    php bin/console tailwind:build
    # et création d'un admin pour les tests :
    php bin/console app:create-admin michael.j.pitz@gmail.com 123NousIronsAuxBois321 Michael.J.Pitz 

#### Permissions

chown -R micha5214:micha5214 /home/michaeljpitz.com/preprod.michaeljpitz.com/preprod-michaeljpitz/public/uploads/


### URL de la version preprod :

https://preprod.michaeljpitz.com/

### Cloudflare

Votre domaine est désormais protégé par Cloudflare.


### Version 1

Il s'agit de la deuxième version du site, qui se trouve sur github à l'URL suivante : https://github.com/mikhawa/cv-mikhawa et qui se trouve en ligne à l'URL suivante : https://alpha1.michaeljpitz.com/

#### Admin

Pour créer un admin : 

```bash
php bin/console app:create-admin le_mail le_password le_username
```

### Utilisateurs de test

| Email                     | Mot de passe | Username |
|---------------------------|--------------|----------|
| m.ichaeljpitz@gmail.com   | 123Mickey    | Mickey   |
| m.ichael.j.pitz@gmail.com | mp3mp3mp3mp3 | Mikhawa3 |

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

| Date début            | Date courante     |
|-----------------------|-------------------|
| 2026-01-31            | 2026-02-11        |
| Version 1.0.0         | Version 1.7.1     |
| 2026-02-11            | 2026-02-11        |
| Version 2.0.0         | Version 2.0.0     |
| 2026-02-11            | 2026-02-12        |
 | Version Alpha.1-b     | Version Alpha.1-b |
| Version Preprod.1.0.0 | 2026-03-22        |

URL version alpha.1-b :

https://alpha1.michaeljpitz.com/

URL version en développement :
https://dev.michaeljpitz.com/


URL version preprod.1.0.0 :
https://preprod.michaeljpitz.com/

### Installation de Suneditor


php bin/console importmap:require suneditor

### Documentation Claude

https://github.com/mikhawa/claude-code-cheat-sheet
