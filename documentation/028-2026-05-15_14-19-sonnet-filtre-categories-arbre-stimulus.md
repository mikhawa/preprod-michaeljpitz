# 028 — Filtre catégories hiérarchique avec animation Stimulus (page articles)

**Date** : 2026-05-15  
**Modèle** : Claude Sonnet 4.6  
**Branche** : `design/02-upgrade-design`

---

## Ce qui a été fait

Refonte du filtre de catégories sur la page `/articles` pour afficher l'arborescence comme le menu de navigation : les catégories racines (niveau 0) restent sur une ligne horizontale avec "Tous", et les sous-catégories (niveau 1+) apparaissent en-dessous au survol avec une animation, ou au clic sur mobile.

---

## Architecture de la solution

### 1. Restructuration Twig — groupement parent → enfants

La donnée `categoriesHierarchical` (liste plate avec `item.depth`) est transformée en groupes imbriqués directement dans le template Twig, sans modifier le contrôleur ni le repository :

```twig
{% set groups = [] %}
{% set cur = null %}
{% for item in categoriesHierarchical %}
    {% if item.depth == 0 %}
        {% if cur is not null %}{% set groups = groups|merge([cur]) %}{% endif %}
        {% set cur = {parent: item.category, children: []} %}
    {% else %}
        {% if cur is not null %}
            {% set cur = cur|merge({children: cur.children|merge([item.category])}) %}
        {% endif %}
    {% endif %}
{% endfor %}
{% if cur is not null %}{% set groups = groups|merge([cur]) %}{% endif %}
```

### 2. Controller Stimulus `category-filter`

Fichier créé : `assets/controllers/category_filter_controller.js`

| Méthode | Déclencheur | Comportement |
|---------|-------------|--------------|
| `show(event)` | `mouseenter` sur groupe parent | Affiche le panneau correspondant, cache les autres |
| `scheduleHide()` | `mouseleave` sur groupe ou panneau | Cache après 150ms (évite le clignotement) |
| `cancelHide()` | `mouseenter` sur panneau enfants | Annule le timer de masquage |
| `hideAll()` | Interne | Cache tous les panneaux |
| `toggle(event)` | `click` sur chevron mobile | Bascule le panneau (mobile uniquement) |
| `connect()` | Initialisation | Auto-ouvre le panneau si une sous-catégorie est active |

### 3. Animation CSS — `grid-template-rows`

Ajout dans `assets/styles/app.css` :

```css
.cf-panel {
    display: grid;
    grid-template-rows: 0fr;       /* hauteur 0 */
    opacity: 0;
    pointer-events: none;
    transition: grid-template-rows 0.2s ease, opacity 0.15s ease;
}
.cf-panel > .cf-panel-inner { overflow: hidden; }
.cf-panel.cf-open {
    grid-template-rows: 1fr;       /* hauteur naturelle */
    opacity: 1;
    pointer-events: auto;
}
```

Technique choisie : `grid-template-rows: 0fr → 1fr` (slide animé vers la hauteur naturelle du contenu, sans valeur fixe). Plus propre que le hack `max-height`.

### 4. Comportement responsive

| Contexte | Interaction |
|----------|-------------|
| Desktop (`md:`) | Survol du parent → panneau animé ; survol d'un autre parent → changement de panneau |
| Mobile | Clic sur le chevron `›` à droite du label → bascule le panneau |
| Les deux | Si la page est chargée sur une sous-catégorie active, son panneau parent s'ouvre automatiquement |

---

## Fichiers modifiés / créés

| Fichier | Type |
|---------|------|
| `templates/article/index.html.twig` | Modification (filtre) |
| `assets/controllers/category_filter_controller.js` | Création |
| `assets/styles/app.css` | Ajout (classes `.cf-panel`) |
