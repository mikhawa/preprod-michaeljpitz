# 035 — Galerie photo avec défilement automatique sur la page d'accueil

**Date** : 2026-05-17  
**Modèle** : Claude Sonnet  
**Branche** : dev/main

## Contexte

L'image statique `1773493021909.png` dans le cercle hero de la page d'accueil est remplacée par une galerie de 9 photos personnelles avec défilement automatique par fondu (alpha).

## Fichiers modifiés

- `assets/controllers/photo_gallery_controller.js` — nouveau contrôleur Stimulus
- `templates/home/index.html.twig` — section hero colonne droite
- `public/images/gallery/` — dossier créé avec 9 photos copiées depuis `datas/`

## Décisions techniques

### Contrôleur Stimulus `photo-gallery`
- Utilise `static targets = ['slide']` pour identifier chaque `<img>`
- Utilise `static values = { interval: Number }` pour configurer la durée (défaut 3000 ms)
- Toutes les images sont empilées en `position: absolute` dans le cercle
- La première image démarre à `opacity: 1`, les autres à `opacity: 0`
- `setInterval` alterne l'opacité entre les slides (transition CSS `duration-700`)
- `disconnect()` nettoie le timer pour éviter les fuites mémoire

### Template Twig
- La liste des photos est définie dans un `{% set photos = [...] %}`
- Chaque `<img>` reçoit `data-photo-gallery-target="slide"` et `style="opacity: 0;"`
- `object-cover object-top` pour centrer le haut du cadre (visage en haut des photos)
- L'anneau dégradé `hero-gradient-ring` est conservé

### Images
Copiées depuis `datas/` vers `public/images/gallery/` :
- `20260421_200546.jpg`
- `2023-10-08.jpg`
- `20260205_080955.jpg`
- `20251026_141752.jpg`
- `20260129_183210.jpg`
- `20260228_195756.jpg`
- `20260124_095850.jpg`
- `20251108_135106.jpg`
- `20251224_194330.jpg`

## Points de sécurité vérifiés

- Aucun input utilisateur traité
- Images servies statiquement par Nginx
- Pas de SQL, pas de formulaire
