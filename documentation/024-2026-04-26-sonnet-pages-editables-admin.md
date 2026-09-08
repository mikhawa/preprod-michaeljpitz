# 024 — Pages éditables depuis l'administration + fix pagination

**Date :** 2026-04-26  
**Modèle :** Claude Sonnet 4.6  
**Branche :** `dev/main`

---

## 1. Fix — Pagination articles (`/articles`)

### Problème

La page `/articles` levait une exception Twig :

```
Neither the property "hasPreviousPage" nor one of the methods "hasPreviousPage()",
"gethasPreviousPage()"... exist in class "Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination"
```

Les méthodes `hasPreviousPage()` et `hasNextPage()` n'existent pas dans `SlidingPagination` de KnpPaginatorBundle.

### Correction — `templates/article/index.html.twig`

```twig
{# Avant #}
{% if pagination.hasPreviousPage %}
{% if pagination.hasNextPage %}

{# Après #}
{% if pagination.currentPageNumber > 1 %}
{% if pagination.currentPageNumber < pagination.pageCount %}
```

---

## 2. Pages éditables depuis l'administration

### Contexte

Les pages CV et RGPD étaient déjà partiellement câblées sur l'entité `Page` (les controllers récupéraient la page par slug et les templates affichaient son contenu si elle existait). Il manquait les données en base et la même logique pour la page Contact.

L'entité `Page`, le `PageCrudController` et l'entrée **Pages** dans le menu EasyAdmin existaient déjà.

### Modifications

#### `src/Controller/ContactController.php`

Injection de `PageRepository` dans l'action `index()`. La page avec le slug `contact` est récupérée et transmise au template dans les trois chemins de retour (succès Turnstile, échec Turnstile, GET/POST normal).

```php
public function index(Request $request, PageRepository $pageRepository): Response
{
    $page = $pageRepository->findOneBySlug('contact');
    // ...
    return $this->render('contact/index.html.twig', [
        'contactForm' => $form,
        'turnstileSiteKey' => $this->turnstileSiteKey,
        'page' => $page,
    ]);
}
```

#### `templates/contact/index.html.twig`

- Titre dynamique : `{{ page ? page.title : 'Contact' }}`
- Bloc `meta_description` ajouté
- Si `page.content` existe : affichage du contenu HTML sanitisé avant le formulaire
- Le texte statique de repli n'est affiché que si aucune page n'est trouvée en base

### Résultat

| Page | URL | Éditable via | Fallback si pas de page en BDD |
|------|-----|-------------|-------------------------------|
| CV | `/cv` | Admin → Pages → `cv` | Contenu statique HTML du template |
| RGPD | `/rgpd` | Admin → Pages → `rgpd` | Contenu statique HTML du template |
| Contact | `/contact` | Admin → Pages → `contact` | Texte d'intro statique |

Le formulaire de contact reste toujours affiché sur `/contact`, quel que soit l'état de la page en base.

### Édition dans l'admin

Aller sur `/admin` → **Pages** → cliquer sur la page souhaitée → modifier le contenu avec Suneditor → Sauvegarder.

---

## Vérifications

```bash
php bin/console lint:twig templates/rgpd/index.html.twig templates/contact/index.html.twig templates/cv/index.html.twig
# → All 3 Twig files contain valid syntax.
```
