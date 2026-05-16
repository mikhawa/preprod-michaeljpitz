# 031 — Menu desktop : remplacement flyout hover par accordion cliquable multi-niveaux

**Date** : 2026-05-16  
**Modèle** : Claude Sonnet 4.6  
**Branche** : `feature/08-create-admin-update`

---

## Problème

Le menu desktop (catégories) ne fonctionnait pas correctement avec les sous-menus. La version mobile (accordion cliquable) fonctionnait parfaitement, mais la version desktop (flyout au survol) présentait plusieurs bugs :

1. **Bug `_submenuTimeout` partagé** : un seul timer pour tous les niveaux. Quand la souris quitte le niveau 2 puis le niveau 1, le timer du niveau 2 est écrasé par celui du niveau 1. À la réouverture, les sous-menus du niveau 2 restaient ouverts de manière inattendue.

2. **Pas de nettoyage récursif** : `scheduleHideSubmenu` cachait uniquement le sous-menu direct, pas les sous-menus imbriqués.

3. **Pas d'annulation du timer principal** : `showSubmenu` ne clearait pas `_hideTimeout`, permettant au dropdown principal de se fermer pendant la navigation dans les niveaux.

## Décision technique

**Remplacement du système flyout hover par un accordion cliquable** (comme le mobile) à l'intérieur du dropdown desktop.

- Plus robuste : basé sur le clic, pas sur des timers `mouseenter`/`mouseleave`
- Cohérent avec le mobile : réutilise `toggleAccordion` déjà existant
- Multi-niveaux illimités : l'inclusion récursive de `_category_desktop_menu.html.twig` fonctionne nativement

## Ce qui a été fait

### 1. Réécriture de `_category_desktop_menu.html.twig`

**Avant** : `data-navbar-target="submenuContainer"` avec `mouseenter`/`mouseleave` + flyout `left-full`  
**Après** : `data-accordion-item` avec bouton cliquable + panel `accordionPanel`

Catégories sans enfants → simple lien `<a>`.  
Catégories avec enfants → bouton `<button>` avec `toggleAccordion` + sous-menu `accordionPanel` en retrait (border-l).

### 2. Mise à jour de `navbar_controller.js`

- **Suppression** des méthodes inutilisées : `showSubmenu`, `scheduleHideSubmenu`
- **Suppression** des targets inutilisés : `submenuContainer`, `submenu`, `_submenuTimeout`
- **Ajout** dans `scheduleHideDropdown` : réinitialisation de tous les `accordionPanel` (hidden) et `accordionIcon` (rotate-180) à la fermeture du dropdown

## Fichiers modifiés

| Fichier | Nature |
|---------|--------|
| `templates/components/_category_desktop_menu.html.twig` | Réécriture (flyout → accordion) |
| `assets/controllers/navbar_controller.js` | Nettoyage + reset accordion |

## Vérification

- Lint Twig : ✅ valide
- Classes Tailwind (`border-l`, `transition-transform`, `rotate-180`, `ml-2`) : ✅ présentes dans le CSS compilé
- Structure récursive multi-niveaux : ✅ fonctionne nativement via `{% include %}` récursif

## Comportement attendu

```
[ Catégories ▼ ]
────────────────────
  PHP
  ▼ Symfony          ← clic pour ouvrir
  │  Routing
  │  Security
  │  Forms
  JavaScript
────────────────────
```

À la fermeture du dropdown, tous les accordions internes se referment automatiquement.
