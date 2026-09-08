# 032 — Filtre catégories : multi-niveaux + alignement sous le parent

**Date** : 2026-05-16  
**Modèle** : Claude Sonnet 4.6  
**Branche** : `feature/08-create-admin-update`

---

## Contexte

Le filtre catégories affiché sous le fil d'Ariane sur `/articles` et `/categorie/*` ne supportait que 2 niveaux (racine → enfants directs). Les petits-enfants étaient ignorés. De plus, les panneaux enfants s'ouvraient systématiquement à gauche du conteneur, sans lien visuel avec le pill parent.

---

## Ce qui a été fait

### 1. Support multi-niveaux (N niveaux)

**Problème** : Le contrôleur passait `findAllHierarchical()` (liste plate avec profondeur) et le template reconstituait manuellement des groupes parent→enfants en Twig, perdant les niveaux 2+.

**Solution** :

- **`ArticleController.php`** (routes `index` et `category`) : remplacement de `findCategoryTree()` au lieu de `findAllHierarchical()`. La méthode `findCategoryTree()` existait déjà dans `CategoryRepository` et retourne un arbre imbriqué `{category, children: [...]}` récursif.

- **Nouveau fichier** `templates/components/_category_filter_level.html.twig` : partial Twig récursif qui s'inclut lui-même via `{% include ... only %}`. Rend une ligne de pills pour le niveau courant + un `cf-panel` animé par enfant qui a des descendants.

- **`templates/article/index.html.twig`** : toute la section filtre (60 lignes de logique Twig manuelle) remplacée par un simple `{% include '_category_filter_level.html.twig' with {nodes: categoryTree, isRoot: true} %}`.

### 2. Alignement desktop : panneau sous son pill parent

**Problème** : le panneau enfant s'affichait toujours au bord gauche du conteneur (`border-l-2 ml-1 pl-3`), sans rapport visuel avec le pill qui le déclenche.

**Solution** : calcul dynamique du décalage en JS.

Dans `show()`, sur desktop uniquement (`window.matchMedia('(min-width: 768px)')`):
```js
const offset = Math.round(
    trigger.getBoundingClientRect().left - scope.getBoundingClientRect().left
);
target.style.marginLeft = offset + 'px';
```

Le panneau s'aligne exactement sous le pill déclencheur. À la fermeture (`scheduleHide()`), `marginLeft` est remis à `''`.

**Mobile** : comportement inchangé — `border-l-2 ml-1 pl-3` conservé, aucun `marginLeft` appliqué.

Classes Tailwind dans `_category_filter_level.html.twig` pour les niveaux non-racine :
```
border-l-2 border-[var(--accent)] ml-1 pl-3 mt-2   ← mobile
md:border-l-0 md:ml-0 md:pl-0 md:pt-2              ← desktop (bordure supprimée, JS prend le relais)
```

### 3. Alignement au chargement de page (après clic sur sous-catégorie)

**Problème** : quand on arrivait sur `/categorie/slug`, le `connect()` de Stimulus ouvrait automatiquement le panneau de la catégorie active, mais sans appliquer le `marginLeft`. Le panneau s'affichait à gauche au lieu d'être aligné sous son parent.

**Solution** : le `connect()` retrouve le déclencheur correspondant à chaque panneau actif via :
```js
const trigger = scope.querySelector(`:scope > div > [data-group-id="${groupId}"]`);
```
puis applique le même calcul de décalage que lors du survol, avant d'ajouter `cf-open`.

---

## Fichiers modifiés / créés

| Fichier | Nature |
|---------|--------|
| `src/Controller/ArticleController.php` | `findAllHierarchical()` → `findCategoryTree()` (×2 routes) |
| `templates/components/_category_filter_level.html.twig` | **Nouveau** — partial récursif multi-niveaux |
| `templates/article/index.html.twig` | Section filtre remplacée par `{% include %}` |
| `assets/controllers/category_filter_controller.js` | Réécriture complète : scope `.cf-level`, alignement JS, `connect()` avec position |

---

## Architecture du JS (`category_filter_controller.js`)

| Méthode | Rôle |
|---------|------|
| `connect()` | Auto-ouvre + positionne les panneaux actifs au chargement |
| `show(event)` | Desktop : ferme frères, calcule `marginLeft`, ouvre le panneau |
| `scheduleHide()` | Ferme tout + remet `marginLeft = ''` après 200 ms |
| `cancelHide()` | Annule la fermeture si la souris réentre dans la nav |
| `toggle(event)` | Mobile : bascule le panneau sans repositionnement |

---

## Comportement attendu

**Desktop :**
```
[Tous] [PHP ▼] [JavaScript]
       [Routing] [Security]   ← aligné sous PHP, au survol ET au chargement
```

**Mobile (inchangé) :**
```
[Tous] [PHP ▼]
| Routing
| Security
```
