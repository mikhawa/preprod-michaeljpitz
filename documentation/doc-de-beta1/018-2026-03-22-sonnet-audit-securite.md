# 018 - Audit de sécurité complet

**Date** : 2026-03-22
**Modèle** : Claude Sonnet 4.6
**Branche** : `dev/main`

---

## Contexte

Audit de sécurité exhaustif réalisé avant déploiement en préprod avec activation de Mailjet. Analyse de 40+ fichiers : contrôleurs, entités, formulaires, services, templates, configuration serveur et dépendances.

---

## CRITIQUE — À corriger avant mise en production

### 1. XSS sur le contenu des articles

**Fichier** : `templates/article/show.html.twig:72`

```twig
{{ article.content|raw }}
```

Le sanitizer HTML Symfony est **configuré** (`html_sanitizer.yaml`) mais **jamais appliqué en sortie**. Tout HTML injecté via Suneditor est rendu tel quel. Un contenu malveillant stocké en base peut exécuter du JavaScript arbitraire.

**Correction** : Créer un filtre Twig custom qui appelle `HtmlSanitizerInterface::sanitize()`.

---

### 2. XSS sur le contenu des commentaires

**Fichier** : `templates/article/show.html.twig:138`

```twig
{{ comment.content|nl2br }}
```

`nl2br` convertit les sauts de ligne mais n'échappe pas le HTML. Un utilisateur peut injecter `<script>alert(1)</script>` dans un commentaire. Même si les commentaires passent par modération, le risque existe côté admin.

**Correction** : Appliquer le sanitizer ou forcer du texte pur (pas de HTML dans les commentaires).

---

### 3. XSS sur les pages statiques (CV, RGPD)

Les contrôleurs `CvController` et `RgpdController` rendent le contenu HTML des pages sans sanitization. Seul un admin peut modifier ces pages, mais c'est un risque en cas de compromission du compte admin.

---

## HAUTE — À traiter rapidement

### 4. Absence de rate limiting sur le login

Aucun limiteur de débit n'est configuré sur le formulaire de connexion. Turnstile (CAPTCHA) est présent mais ne remplace pas un rate limiter côté serveur (attaques distribuées, tokens Turnstile rejoués, etc.).

**Correction** : Ajouter `RateLimiter` Symfony sur `CheckPassportEvent` ou dans `SecurityController`.

---

### 5. Tokens de réinitialisation de mot de passe stockés en clair

**Fichier** : `src/Entity/User.php` — champ `resetPasswordToken VARCHAR(64)`

Si la base de données est compromise, tous les tokens de réinitialisation sont lisibles directement.

**Correction** : Stocker `hash('sha256', $token)` en base, comparer le hash à la vérification.

---

### 6. CSP trop permissive

**Fichier** : `src/EventSubscriber/SecurityHeadersSubscriber.php`

```
script-src 'self' 'unsafe-inline' 'unsafe-eval' ...
```

`unsafe-inline` et `unsafe-eval` annulent l'essentiel de la protection CSP. Imposés par Suneditor.

**Correction à terme** : Passer aux nonces CSP (plus complexe à implémenter avec Turbo).

---

### 7. Absence de 2FA

Aucune double authentification. Pour un site avec back-office admin, c'est un risque notable.

---

## MOYEN — À planifier

### 8. Validation de `User.status` manquante

**Fichier** : `src/Entity/User.php`

`$status = 0` accepte n'importe quelle valeur entière. Une valeur incohérente peut bypasser les contrôles de `UserChecker`.

**Correction** : Ajouter `#[Assert\Choice(choices: [0, 1, 2])]`.

---

### 9. Upload avatar via base64 (regex MIME type insuffisante)

**Fichier** : `src/Controller/ProfileController.php:95`

La validation du MIME type en base64 repose sur une regex `\w+` qui pourrait accepter des variantes non attendues.

**Correction** : Whitelist stricte : `['image/jpeg', 'image/png', 'image/webp']`.

---

### 10. `unlink()` manuel pour l'ancien avatar

**Fichier** : `src/Controller/ProfileController.php:119`

Un chemin construit manuellement avec `unlink()` est moins sûr que la gestion intégrée de VichUploaderBundle.

---

## Points positifs (ce qui est bien fait)

| Aspect | État |
|--------|------|
| Bcrypt cost=13 | ✅ Conforme |
| CSRF sur tous les formulaires | ✅ Actif globalement |
| UserChecker (statuts 0/1/2) | ✅ Opérationnel |
| Tokens d'activation avec `random_bytes(32)` | ✅ Entropie suffisante (256 bits) |
| Doctrine ORM + requêtes paramétrées | ✅ Pas d'injection SQL possible |
| Twig auto-escaping activé | ✅ Par défaut |
| Headers HTTP (X-Frame, X-Content-Type, HSTS) | ✅ Bien configurés |
| X-Frame-Options: SAMEORIGIN | ✅ |
| Turnstile Cloudflare sur tous les formulaires | ✅ Login, inscription, contact, profil, commentaire |
| VichUploader avec whitelist MIME | ✅ jpeg/png/webp seulement |
| Modération des commentaires | ✅ `isApproved=false` par défaut |
| Pas de secrets dans le repo git | ✅ `.env.local` non commité |
| Nginx : front controller, blocage PHP direct | ✅ |
| .htaccess : blocage `.env`, `composer.json` | ✅ |

---

## Score synthétique

| Domaine | Note |
|---------|------|
| Authentification | 8/10 |
| Autorisation | 8/10 |
| Protection XSS (output) | **3/10** |
| Formulaires & CSRF | 8/10 |
| Uploads | 7/10 |
| Headers HTTP | 7/10 |
| Base de données | 9/10 |
| Dépendances | 8/10 |
| **Global** | **6.5/10** |

---

## Décisions et suites

| Priorité | Action | Ticket |
|----------|--------|--------|
| CRITIQUE | Créer filtre Twig `sanitize` + l'appliquer sur `article.content` et `comment.content` | À faire |
| CRITIQUE | Appliquer sanitizer sur pages statiques CV/RGPD | À faire |
| HAUTE | Ajouter rate limiting Symfony sur le login | À planifier |
| HAUTE | Hacher les tokens de réinitialisation avant stockage | À planifier |
| MOYEN | `Assert\Choice([0,1,2])` sur `User.status` | À planifier |
| MOYEN | Whitelist stricte MIME type upload avatar | À planifier |
