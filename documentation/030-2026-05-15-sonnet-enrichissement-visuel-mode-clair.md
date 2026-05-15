# 030 — Enrichissement visuel du mode clair (contrastes, dégradés, ombres)

**Date** : 2026-05-15  
**Modèle** : Claude Sonnet 4.6  
**Branche** : `design/02-upgrade-design`

---

## Ce qui a été fait

Amélioration visuelle du mode clair uniquement via `assets/styles/app.css`, sans modification de structure HTML. L'objectif était de réduire l'aspect "vide" du mode clair en s'inspirant de la logique de contraste du mode sombre.

---

## Fichier modifié

`assets/styles/app.css`

---

## Détail des 4 changements

### 1. Variables `:root` — inversion de la logique fond/cartes

Avant : fond `#ffffff` et cartes `#f8fafc` quasi identiques → rendu plat, cartes invisibles.  
Après : même logique que le dark mode (fond teinté, cartes plus claires qui ressortent).

| Variable | Avant | Après |
|---|---|---|
| `--bg-primary` | `#ffffff` | `#f1f5f9` (slate-100, fond teinté) |
| `--bg-secondary` | `#f8fafc` | `#ffffff` (blanc pur, cartes élevées) |
| `--text-primary` | `#1e293b` | `#0f172a` (slate-900, plus profond) |
| `--text-secondary` | `#64748b` | `#475569` (slate-600) |
| `--accent` | `#3b82f6` (blue-500) | `#2563eb` (blue-600, plus vif sur clair) |
| `--accent-hover` | `#2563eb` | `#1d4ed8` (blue-700) |
| `--border` | `#e2e8f0` (quasi invisible) | `#cbd5e1` (slate-300, perceptible) |

Le dark mode reste inchangé.

### 2. Blobs décoratifs sur le `body` en mode clair

```css
html:not([data-theme="dark"]) body {
    background-image:
        radial-gradient(ellipse at 78% 0%, rgba(59, 130, 246, 0.09) 0%, transparent 45%),
        radial-gradient(ellipse at 8%  90%, rgba(139, 92, 246, 0.06) 0%, transparent 40%);
}
```

Deux sources lumineuses simulées : bleu en haut à droite (9%), violet en bas à gauche (6%). La `background-color` Tailwind (`bg-[var(--bg-primary)]`) et la `background-image` CSS coexistent sans conflit car ce sont deux propriétés distinctes.

### 3. Section hero — glow amplifié + second blob

Le glow droit existant passe de `rgba(96, 165, 250, 0.04)` (invisible) à `rgba(37, 99, 235, 0.12)` en mode clair, adapté à la couleur accent de ce mode.

Un second pseudo-élément `::before` est ajouté côté gauche :

```css
.hero-section::before {
    content: '';
    position: absolute;
    top: 0%;
    left: -8%;
    width: 38%;
    height: 100%;
    background: radial-gradient(ellipse, rgba(139, 92, 246, 0.07) 0%, transparent 60%);
    pointer-events: none;
}
[data-theme="dark"] .hero-section::before {
    background: radial-gradient(ellipse, rgba(167, 139, 250, 0.08) 0%, transparent 60%);
}
```

`overflow: hidden` sur `.hero-section` contient les débordements. `pointer-events: none` préserve l'interactivité du contenu.

### 4. Ombres sur les cartes en mode clair

Via sélecteur CSS attribute sans toucher aux templates :

```css
html:not([data-theme="dark"]) .rounded-lg[class*="bg-[var(--bg-secondary)]"] {
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.07), 0 2px 12px rgba(15, 23, 42, 0.05);
}
```

Cible : toute carte/panneau combinant `rounded-lg` et la couleur secondaire (articles, CV, contact, dropdowns navbar). Les inputs de formulaire utilisent `bg-[var(--bg-primary)]` → non ciblés. Le footer n'a pas `rounded-lg` → non ciblé.

---

## Build

```bash
# Dans le conteneur PHP :
php bin/console tailwind:build -v
php bin/console cache:clear --env=dev
php bin/console asset-map:compile --env=dev
# → Fichier généré : public/assets/styles/app-Nt4LBp5.css (37 302 bytes)
```
