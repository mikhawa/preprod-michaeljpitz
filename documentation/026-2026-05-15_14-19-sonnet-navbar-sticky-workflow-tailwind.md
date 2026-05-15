# 026 — Navbar sticky + correctif workflow assets Tailwind

**Date** : 2026-05-15  
**Modèle** : Claude Sonnet 4.6  
**Branche** : `design/02-upgrade-design`

---

## Ce qui a été fait

### 1. Menu de navigation rendu sticky

Ajout des classes `sticky top-0 z-50 bg-[var(--bg-primary)]` sur le `<header>` du composant `Navbar.html.twig`.

**Fichier modifié** : `templates/components/Navbar.html.twig` (ligne 1)

```diff
- <header class="border-b border-[var(--border)]" data-controller="navbar">
+ <header class="sticky top-0 z-50 bg-[var(--bg-primary)] border-b border-[var(--border)]" data-controller="navbar">
```

- `sticky top-0` : colle le header en haut au scroll
- `z-50` : passe au-dessus du contenu de la page
- `bg-[var(--bg-primary)]` : fond opaque (respecte le thème clair/sombre)

### 2. Correctif du workflow assets Tailwind en dev

**Problème identifié** : La configuration Docker + Nginx sert les fichiers statiques directement depuis `public/assets/` sans passer par PHP. Après un `tailwind:build`, le CSS compilé est écrit dans `var/tailwind/app.built.css` mais le fichier servi (`public/assets/styles/app-R3JrkgL.css`) n'est pas mis à jour automatiquement.

**Solution appliquée** : Copier manuellement après chaque rebuild Tailwind :

```bash
php bin/console tailwind:build && cp var/tailwind/app.built.css public/assets/styles/app-R3JrkgL.css
```

**Note** : `asset-map:compile` échoue en dev à cause de permissions Docker sur certains fichiers (`root` owner). La copie directe du fichier CSS contourne ce problème sans affecter le fonctionnement.

---

## Fichiers modifiés

| Fichier | Type |
|---------|------|
| `templates/components/Navbar.html.twig` | Modification |
| `public/assets/styles/app-R3JrkgL.css` | Mise à jour manuelle |
