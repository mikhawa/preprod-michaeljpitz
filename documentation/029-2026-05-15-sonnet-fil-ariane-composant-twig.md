# 029 — Fil d'Ariane : composant TwigComponent réutilisable sur toutes les pages

**Date** : 2026-05-15  
**Modèle** : Claude Sonnet 4.6  
**Branche** : `design/02-upgrade-design`

---

## Ce qui a été fait

Création d'un composant Twig réutilisable `Breadcrumb` et intégration sur toutes les pages publiques du site (sauf page d'accueil et pages de sécurité où il n'a pas de sens).

---

## Architecture de la solution

### 1. Classe PHP — `src/Twig/Components/BreadcrumbComponent.php`

Composant sans dépendance injectée. Une seule propriété publique `$items` reçoit le tableau d'items transmis depuis le template appelant :

```php
#[AsTwigComponent('Breadcrumb')]
class BreadcrumbComponent
{
    /** @var list<array{label: string, url?: string}> */
    public array $items = [];
}
```

### 2. Template — `templates/components/Breadcrumb.html.twig`

Rendu d'une liste `<ol>` accessible (`aria-label`, `aria-current="page"`). La logique est simple :
- Tous les items sauf le dernier sont des liens cliquables.
- Le dernier item est du texte en gras (page courante), avec un `title` si le label dépasse 30 caractères.
- Les séparateurs `/` sont masqués aux lecteurs d'écran (`aria-hidden="true"`).

### 3. Appel dans les templates

L'API d'appel utilise la syntaxe Twig Component avec liaison dynamique `:items` :

```twig
<twig:Breadcrumb :items="[
    {label: 'Accueil', url: path('app_home')},
    {label: 'Articles', url: path('app_article_index')},
    {label: article.title}
]" />
```

Pour les items dynamiques (catégories imbriquées dans `article/show.html.twig`), le tableau est construit en Twig avant l'appel :

```twig
{% set breadcrumbItems = [
    {label: 'Accueil', url: path('app_home')},
    {label: 'Articles', url: path('app_article_index')}
] %}
{% for category in breadcrumbCategories %}
    {% set breadcrumbItems = breadcrumbItems|merge([{label: category.title, url: path('app_category_show', {slug: category.slug})}]) %}
{% endfor %}
{% set breadcrumbItems = breadcrumbItems|merge([{label: article.title}]) %}
<twig:Breadcrumb :items="breadcrumbItems" />
```

---

## Pages concernées

| Template | Fil d'Ariane généré |
|---|---|
| `article/show.html.twig` | Accueil / Articles / [Catégorie(s)] / Titre de l'article |
| `article/index.html.twig` | Accueil / Articles ou Accueil / Articles / Catégorie |
| `contact/index.html.twig` | Accueil / Contact (ou titre de la page éditable) |
| `cv/index.html.twig` | Accueil / [Titre de la page CV] |
| `rgpd/index.html.twig` | Accueil / [Titre de la page RGPD] |
| `profile/index.html.twig` | Accueil / Mon profil |
| `public_profile/show.html.twig` | Accueil / Profil de [nom] |

**Pages exclues volontairement :** page d'accueil (racine), login, register, mot de passe oublié, réinitialisation, double facteur, accès refusé, emails, sitemap.

---

## Décision de conception

Le lien "← Retour" sur `public_profile/show.html.twig` (basé sur le `Referer` HTTP) a été remplacé par le fil d'Ariane. Le `Referer` est fragile (absent si navigation directe, falsifiable) ; un chemin fixe `Accueil → Profil de X` est plus fiable et cohérent avec le reste du site.

---

## Vérifications effectuées

```bash
php bin/console lint:twig templates/  # OK
php bin/console lint:container        # OK
```
