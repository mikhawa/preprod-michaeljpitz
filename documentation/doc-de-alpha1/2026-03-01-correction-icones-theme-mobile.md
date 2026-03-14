# Correction : icônes de changement de thème sur mobile

**Date :** 1er mars 2026
**Branche :** v2.2.3
**Fichier modifié :** `assets/controllers/theme_controller.js`

## Problème constaté

Sur smartphone, les boutons de bascule de thème (dark/light mode) affichaient un rond à la place des icônes soleil/lune attendues.

## Cause

La méthode `updateToggleIcon()` injectait des emojis Unicode via `textContent` :

```javascript
this.toggleTarget.textContent = theme === 'dark' ? '\u2600\uFE0F' : '\uD83C\uDF19';
```

Deux problèmes coexistaient :

1. **Rendu des emojis non fiable sur mobile** : les emojis Unicode (☀️ `\u2600\uFE0F` et 🌙 `\uD83C\uDF19`) dépendent de la police système. Sur Android et certains navigateurs mobiles, ils s'affichent sous forme d'un rond générique faute de glyphe emoji disponible.

2. **Un seul bouton mis à jour** : `this.toggleTarget` (singulier, API Stimulus) ne cible que le **premier** élément `data-theme-target="toggle"` trouvé dans le DOM. Or la navbar contient deux boutons distincts : un pour le desktop (`hidden md:flex`) et un pour le mobile (`flex md:hidden`). Le bouton mobile n'était donc jamais mis à jour.

## Solution appliquée

Remplacement des emojis par des **SVG Heroicons inline** injectés via `innerHTML`, et itération sur **tous les targets** via `this.toggleTargets.forEach()` :

```javascript
updateToggleIcon(theme) {
    const sunSvg = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8z"/>
    </svg>`;
    const moonSvg = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M20.354 15.354A9 9 0 0 1 8.646 3.646 9.003 9.003 0 0 0 12 21a9.003 9.003 0 0 0 8.354-5.646z"/>
    </svg>`;
    const icon = theme === 'dark' ? sunSvg : moonSvg;
    this.toggleTargets.forEach(btn => { btn.innerHTML = icon; });
}
```

## Logique des icônes

| Thème actif | Icône affichée | Action au clic |
|-------------|---------------|----------------|
| Clair       | Lune          | Passer en sombre |
| Sombre      | Soleil        | Passer en clair  |

## Avantages de la solution

- **SVG inline** : rendu identique sur tous les navigateurs et OS, indépendant de la police système
- **`stroke="currentColor"`** : les icônes héritent de la couleur du texte et s'adaptent automatiquement aux deux thèmes
- **`aria-hidden="true"`** : le SVG est masqué aux lecteurs d'écran (le bouton possède déjà `aria-label="Basculer le thème"`)
- **Style cohérent** : même famille d'icônes Heroicons que le reste de la navbar (hamburger, chevrons, croix)
- **`toggleTargets` (pluriel)** : les deux boutons (desktop et mobile) sont mis à jour simultanément
