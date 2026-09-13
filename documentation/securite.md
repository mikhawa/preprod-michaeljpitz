# Audit de sécurité

> Dernière mise à jour : 13 septembre 2026 (voir `042-2026-09-13_18-43-sonnet-audit-securite-global.md` pour le détail complet de l'audit)

## Résumé

Ce document recense les mesures de sécurité implémentées dans le projet, conformément aux exigences du PROJECT_SPEC.md et du CLAUDE.md.

## 1. Authentification

| Mesure | Implémentation | Fichier |
|--------|---------------|---------|
| Hashage bcrypt cost 13 | `config/packages/security.yaml` | security.yaml |
| Provider par email | Entity User, propriété `email` | security.yaml |
| CSRF sur le login | `enable_csrf: true` dans form_login | security.yaml |
| Remember-me sécurisé | `secure: true`, `httponly: true` | security.yaml |
| Protection de /admin | `access_control: ROLE_ADMIN` | security.yaml |

### Configuration

```yaml
# config/packages/security.yaml
security:
    password_hashers:
        Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface:
            algorithm: bcrypt
            cost: 13
    firewalls:
        main:
            form_login:
                enable_csrf: true
            remember_me:
                secure: true
                httponly: true
    access_control:
        - { path: ^/admin, roles: ROLE_ADMIN }
```

## 2. Protection contre les injections

### SQL Injection
- Toutes les requêtes utilisent Doctrine ORM avec des paramètres liés
- Aucune concaténation SQL dans le code
- Fichiers concernés : `ArticleRepository.php` (findPublishedQueryBuilder, findLatestPublished, findSimilarArticles)

### XSS (Cross-Site Scripting)
- Auto-escaping Twig activé par défaut (configuration non modifiée)
- Contenu WYSIWYG (Trix) assaini par `HtmlSanitizer`
- Filtre `|raw` utilisé uniquement sur le contenu déjà assaini

### Configuration HtmlSanitizer

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

Seuls les éléments listés sont autorisés. Les URLs sont forcées en HTTPS.

## 3. CSRF (Cross-Site Request Forgery)

| Formulaire | Protection |
|------------|-----------|
| Login | `enable_csrf: true` dans security.yaml |
| Commentaires | Token CSRF via CommentType (Symfony Form) |
| Inscription | Token CSRF via RegistrationType (Symfony Form) |
| Demande de réinitialisation | Token CSRF via ResetPasswordRequestType (Symfony Form) |
| Nouveau mot de passe | Token CSRF via ResetPasswordType (Symfony Form) |
| Contact | Token CSRF via ContactType (Symfony Form) |
| Profil utilisateur | Token CSRF via ProfileType (`csrf_token_id: 'profile_form'`) |
| Notation | Controller Stimulus avec csrf_protection_controller.js |
| EasyAdmin | Protection CSRF native du bundle |

## 4. Headers HTTP de sécurité

Implémentés via `SecurityHeadersSubscriber` (`src/EventSubscriber/SecurityHeadersSubscriber.php`) :

| Header | Valeur | Protection |
|--------|--------|-----------|
| X-Content-Type-Options | `nosniff` | MIME sniffing |
| X-Frame-Options | `SAMEORIGIN` | Clickjacking |
| X-XSS-Protection | `1; mode=block` | XSS réflexif (navigateurs anciens) |
| Referrer-Policy | `strict-origin-when-cross-origin` | Fuite du referrer |
| Permissions-Policy | `camera=(), microphone=(), geolocation=()` | API navigateur |
| Content-Security-Policy | `default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' data:; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; img-src 'self' data: blob: https:; frame-src https:; font-src 'self' data: https://fonts.gstatic.com` | Injection de ressources |

### Notes sur la CSP
- `unsafe-inline` et `unsafe-eval` sont requis pour Tailwind CSS et Stimulus
- `data:` dans `script-src` est requis pour certains scripts inline
- `https://fonts.googleapis.com` dans `style-src` et `https://fonts.gstatic.com` dans `font-src` pour Google Fonts (cf. Décision 12)
- Les images autorisent `data:` (base64), `blob:` (aperçu Trix, cf. Décision 11) et `https:` (images externes)
- Les frames sont limitées à `https:` (pour les iframes dans les articles)

## 5. Upload de fichiers

Configuration VichUploader (`config/packages/vich_uploader.yaml`) :

| Mapping | Types MIME | Taille max | Stockage |
|---------|-----------|------------|----------|
| `article_image` | JPEG, PNG, WebP | 2 Mo | `public/uploads/articles/` |
| `page_image` | JPEG, PNG, WebP | 2 Mo | `public/uploads/pages/` |
| `user_avatar` | JPEG, PNG, WebP | 10 Mo | `public/uploads/avatars/` |

Nommage : UniqidNamer (noms de fichiers uniques, non prédictibles).

### Upload Trix (contenu riche)

Endpoint : `POST /admin/trix/upload` (protégé par `#[IsGranted('ROLE_ADMIN')]`)

| Type | MIME autorisés | Traitement |
|------|---------------|------------|
| Images | JPEG, PNG, WebP, GIF | Redimensionnement max 1200px (ImageResizer) |
| Documents | PDF, DOC, DOCX, ODT, ZIP | Stockage direct sans traitement |

Taille maximale : 5 Mo. Validation du header `X-Requested-With: XMLHttpRequest`.

## 6. Validation des données

Toutes les entités utilisent les contraintes de validation Symfony (`Assert`) :

| Entité | Validations principales |
|--------|------------------------|
| User | NotBlank (email, password, userName), Email, Length, Regex (userName), Url (liens externes), Image (avatar) |
| Article | NotBlank (title, slug, content), Length (min/max), Image (MIME, taille) |
| Page | NotBlank (title, slug), Length (min/max), Image (MIME, taille) |
| Category | NotBlank (title, slug), Length (min/max) |
| Comment | NotBlank (content), Length (min 2) |
| Rating | Range (min 1, max 5) |

### Validation des formulaires

| Formulaire | Validations |
|------------|-------------|
| ContactType | name (2-100 chars), email (Email), message (10-5000 chars) |
| ProfileType | biography (500 chars max), externalLinks (Url), avatarFile (Image: JPEG/PNG/WebP, 10 Mo max) |

## 7. Données sensibles

| Point | Statut |
|-------|--------|
| `.env.local` dans `.gitignore` | OK |
| `APP_SECRET` unique | OK (généré, stocké dans .env.local) |
| Mot de passe admin en clair | Uniquement dans la doc (compte de développement) |
| Pas de secrets dans le code source | OK |
| `DATABASE_URL` dans `.env.local` | OK |

## 8. SEO et robots

- `robots.txt` : interdit l'indexation de `/admin` et `/login`
- Sitemap XML dynamique listant uniquement les articles publiés

## 9. Réinitialisation de mot de passe

| Mesure | Implémentation | Fichier |
|--------|---------------|---------|
| Token aléatoire 64 chars | `bin2hex(random_bytes(32))` | ResetPasswordController.php |
| Expiration à 1 heure | Comparaison `resetPasswordRequestedAt` | ResetPasswordController.php |
| Token usage unique | Effacé après utilisation ou expiration | ResetPasswordController.php |
| Non-divulgation des comptes | Message identique que l'email existe ou non | ResetPasswordController.php |
| Comptes actifs uniquement | Vérifie `status = 1` avant envoi | ResetPasswordController.php |
| CSRF sur les formulaires | Symfony Form natif | ResetPasswordRequestType, ResetPasswordType |
| Mot de passe haché bcrypt | `UserPasswordHasherInterface` (cost 13) | ResetPasswordController.php |

## 10. Formulaire de contact

| Mesure | Implémentation | Fichier |
|--------|---------------|---------|
| Captcha anti-bot | Cloudflare Turnstile | TurnstileValidator.php |
| Validation serveur | Contraintes Symfony (length, email) | ContactType.php |
| CSRF | Token via Symfony Form | ContactType.php |
| Email replyTo | Pas de spoofing (from = site, replyTo = expéditeur) | ContactController.php |
| Pas de stockage | Messages non persistés en base | ContactController.php |

## 11. Page de profil utilisateur

| Mesure | Implémentation | Fichier |
|--------|---------------|---------|
| Protection par rôle | `#[IsGranted('ROLE_USER')]` | ProfileController.php |
| CSRF | `csrf_token_id: 'profile_form'` | ProfileType.php |
| Validation avatar | Image (MIME, taille 10 Mo), recadrage 320x320 | ProfileType.php, avatar_crop_controller.js |
| Validation base64 | Vérification format et taille avant décodage | ProfileController.php |
| Validation URLs | `#[Assert\Url]` sur liens externes | User.php |
| Liens externes sécurisés | `target="_blank" rel="noopener noreferrer"` | profile/index.html.twig |

## 12. Profil public

| Mesure | Implémentation | Fichier |
|--------|---------------|---------|
| Utilisateurs actifs uniquement | Vérifie `status = 1` | PublicProfileController.php |
| Commentaires approuvés uniquement | Filtre `isApproved = true` | CommentRepository.php |
| Liens externes sécurisés | `target="_blank" rel="noopener noreferrer"` | public_profile/show.html.twig |

## 13bis. Authentification à deux facteurs (2FA)

Mise en place depuis l'audit du 22 mars 2026 (`018`), constatée lors de l'audit du 13 septembre 2026 (`042`) :

| Mesure | Implémentation | Fichier |
|--------|---------------|---------|
| 2FA par email, réservée aux ROLE_ADMIN | Code OTP 6 chiffres après connexion | `TwoFactorLoginSubscriber.php` |
| Code jamais stocké en clair | `hash('sha256', $code)` en session | `TwoFactorLoginSubscriber.php` |
| Comparaison en temps constant | `hash_equals()` | `TwoFactorController.php` |
| Expiration | 10 minutes | `TwoFactorController.php` |
| Anti-bruteforce | 5 tentatives max, puis invalidation de session | `TwoFactorController.php` |
| Blocage de toute route tant que non vérifié | `TwoFactorGateSubscriber` (KernelEvents::REQUEST) | `TwoFactorGateSubscriber.php` |
| Exemption "se souvenir de moi" | Pas de 2FA sur reconnexion RememberMe | `TwoFactorLoginSubscriber.php` |

## 13. Points d'amélioration (non implémentés)

Ces mesures sont recommandées :

| Mesure | Priorité | Description |
|--------|----------|-------------|
| `--append` sur `doctrine:fixtures:load` en CI (dev/preprod) | Haute — dès qu'il y aura des données réelles | Actuellement, chaque déploiement purge la BDD (`deploy-dev.yml`, `deploy-preprod.yml`). Accepté pour le moment (décision du 13/09/2026, voir `042-...-audit-securite-global.md` §3.1), à corriger avant mise en prod avec de vrais utilisateurs |
| `PASS_ADMIN` sur les `.env.local` des serveurs dev/preprod | Haute | Le secret a été retiré de `.env` (versionné) le 13/09/2026 ; les serveurs doivent avoir leur propre `.env.local` à jour avant le prochain déploiement, sinon `doctrine:fixtures:load --group=prod` échouera |
| Rotation du mot de passe admin réel + éventuelle réécriture de l'historique Git | Haute | La valeur committée dans `.env` reste lisible dans l'historique Git |
| CSP sans unsafe-inline/unsafe-eval | Moyenne | Utiliser des nonces pour scripts/styles (compatibilité Turbo + Suneditor à valider) |
| Voter Symfony pour l'autorisation par ressource | Moyenne | Aucun Voter dans `src/Security/` malgré la règle CLAUDE.md ; à ajouter avant toute nouvelle fonctionnalité utilisateur (édition/suppression de ses propres commentaires, etc.) |
| Migration de l'upload avatar vers VichUploaderBundle | Moyenne | `ProfileController` gère l'upload en base64/`file_put_contents` maison au lieu du bundle standard du projet |
| X-Frame-Options: DENY | Basse | Passer de SAMEORIGIN à DENY si pas d'usage iframe interne |
| Retrait de X-XSS-Protection | Basse | Header obsolète, ignoré par les navigateurs modernes |
| Tests de sécurité automatisés | Basse | Tests fonctionnels vérifiant les protections |
| Logs sans données personnelles | Basse | Vérifier les logs Symfony/Doctrine |

### Historique des audits
- 22/02/2026 (implicite, première rédaction) → 22/03/2026 : audit `018`, 6 points corrigés sur 10, 2FA proposée
- 13/09/2026 : audit `042` (vérification globale + suivi), 7 points sur 10 de l'audit `018` désormais corrigés, 2FA implémentée entre-temps, `PASS_ADMIN` sorti du fichier versionné
