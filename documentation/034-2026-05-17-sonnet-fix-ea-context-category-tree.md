# 034 — Fix : contexte EasyAdmin null sur /admin/category-tree

**Date** : 2026-05-17  
**Modèle** : Claude Sonnet 4.6  
**Branche** : `dev/main`

---

## Problème

Cliquer sur la carte "Menu des catégories" du dashboard admin provoquait l'erreur :

```
Impossible to access an attribute ("i18n") on a null variable
in @EasyAdmin/layout.html.twig at line 3.
```

L'erreur n'apparaissait **pas** en passant par le menu de gauche EasyAdmin (lien généré sous la forme `admin?routeName=admin_category_tree`).

---

## Cause

`templates/admin/category_tree.html.twig` étend désormais `@EasyAdmin/page/content.html.twig`, qui appelle `ea()` (la fonction Twig retournant l'`AdminContext` EasyAdmin). Ce contexte n'existe que si la requête est traitée par le framework EasyAdmin.

- **Menu de gauche** → URL `admin?routeName=admin_category_tree` → passe par le routeur EasyAdmin → contexte `ea()` initialisé ✅  
- **Carte dashboard** → URL `/admin/category-tree` générée par `$this->generateUrl('admin_category_tree')` → requête Symfony classique → contexte `ea()` absent → `null` → erreur ❌

---

## Correction

**`DashboardController.php`** — une seule ligne modifiée dans la carte "Menu des catégories" :

```php
// Avant : URL Symfony directe, sans contexte EasyAdmin
'url' => $this->generateUrl('admin_category_tree'),

// Après : URL via AdminUrlGenerator, passe par le routeur EasyAdmin
'url' => $gen->setRoute('admin_category_tree')->generateUrl(),
```

`AdminUrlGenerator::setRoute()` génère une URL de la forme `admin?routeName=admin_category_tree`, identique à celle produite par `MenuItem::linkToRoute()`. EasyAdmin intercepte cette URL, initialise le contexte `ea()`, puis sert la réponse.

---

## Règle à retenir

Tout lien vers une page qui étend `@EasyAdmin/page/content.html.twig` (ou `@EasyAdmin/layout.html.twig`) **doit** être généré via `AdminUrlGenerator` (avec `setController()` ou `setRoute()`), jamais via `$this->generateUrl()` ou `path()` directement.

---

## Fichier modifié

| Fichier | Modification |
|---------|-------------|
| `src/Controller/Admin/DashboardController.php` | `generateUrl('admin_category_tree')` → `$gen->setRoute('admin_category_tree')->generateUrl()` |
