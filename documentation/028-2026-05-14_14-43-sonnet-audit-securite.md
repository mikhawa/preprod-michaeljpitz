# 028 — Audit de sécurité — Branche `fix/07-security-check`

**Date :** 2026-05-14  
**Modèle :** Claude Sonnet 4.6  
**Branche :** `fix/07-security-check`  
**Méthode :** Analyse statique multi-agents (identification + validation croisée en parallèle)

---

## Périmètre

Revue de sécurité focalisée sur les vulnérabilités concrètes et exploitables (confiance ≥ 80%) introduites sur cette branche. Exclusions appliquées : DoS, secrets disque, rate limiting, dépendances tierces, fichiers de tests.

Fichiers analysés : controllers, event subscribers, extensions Twig, fixtures, configuration `security.yaml`, `html_sanitizer.yaml`, templates Twig, tests fonctionnels.

---

## Résultat

**Aucune vulnérabilité actionnable identifiée.**

---

## Finding examiné et écarté

### XSS stocké via `|raw` sur le contenu des articles

**Fichier :** `templates/article/show.html.twig:99`  
**Code :** `{{ article.content|php_runner|raw }}`

**Constat technique :** Le filtre `|raw` désactive l'auto-échappement Twig. L'`article_sanitizer` défini dans `config/packages/html_sanitizer.yaml` n'est jamais branché (aucun appel dans les controllers ni les templates). Par symétrie, les commentaires sont eux correctement sanitizés via `sanitize_comment`.

**Pourquoi écarté :** Le contenu des articles est exclusivement écrit par `ROLE_ADMIN` via EasyAdmin. Si ce compte est compromis, l'attaquant dispose déjà d'un accès total à la base de données, aux migrations, aux uploads de fichiers et à l'ensemble du back-office. Dans ce contexte, un XSS stocké ne constitue pas un risque additionnel significatif. Ce finding sort du périmètre de menace réaliste pour un site personnel à administrateur unique.

**Note pour le futur :** Si le site évolue vers plusieurs rôles d'éditeurs (contributeurs pouvant créer du contenu sans être admin complet), il faudra brancher l'`article_sanitizer` et revoir sa configuration (autorisation actuelle de `<iframe>` et de l'attribut `style` inline).

---

## Vérifications validées

| Point | Statut |
|-------|--------|
| Injection SQL (Doctrine ORM + requêtes paramétrées) | ✅ Aucune requête brute constatée |
| CSRF (formulaires Symfony avec token automatique) | ✅ Protection active sur tous les formulaires |
| Authentification 2FA (TwoFactorGateSubscriber) | ✅ Flux contrôlé, early return sur RememberMe |
| Autorisation `#[IsGranted]` sur les controllers | ✅ Présent sur toutes les routes sensibles |
| Réinitialisation de mot de passe (token unique) | ✅ Token hashé, expiration en place |
| Activation de compte (token unique) | ✅ Flux correct |
| Upload de fichiers (VichUploader + validation MIME) | ✅ Types restreints |
| En-têtes de sécurité HTTP (CSP, HSTS, X-Frame-Options) | ✅ SecurityHeadersSubscriber actif |
| `PASS_ADMIN` externalisé via variable d'environnement | ✅ Plus de mot de passe en clair dans le code |
