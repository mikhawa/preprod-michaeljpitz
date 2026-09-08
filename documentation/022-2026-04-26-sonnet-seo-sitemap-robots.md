# 022 — SEO : sitemap dynamique et correction robots.txt

**Date :** 2026-04-26  
**Modèle :** Claude Sonnet 4.6  
**Branche :** `fix/06-SEO-firs-step`

---

## Problèmes identifiés

PageSpeed Insights signalait que le fichier `robots.txt` pointait vers une URL de sitemap invalide (`https://portfolio.local/sitemap.xml`), bloquant l'indexation du site par les moteurs de recherche.

Par ailleurs, le `SitemapController` existant n'incluait que les articles publiés, omettant les pages statiques (CV, RGPD) et les catégories.

---

## Corrections apportées

### 1. `public/robots.txt`

Correction de l'URL du sitemap :

```
# Avant
Sitemap: https://portfolio.local/sitemap.xml

# Après
Sitemap: https://preprod-michaeljpitz.com/sitemap.xml
```

### 2. `src/Repository/CategoryRepository.php`

Ajout de la méthode `findWithPublishedArticles()` qui retourne uniquement les catégories ayant au moins un article publié, pour éviter d'indexer des pages vides.

```php
/** @return Category[] */
public function findWithPublishedArticles(): array
{
    return $this->createQueryBuilder('c')
        ->innerJoin('c.articles', 'a')
        ->where('a.isPublished = true')
        ->distinct()
        ->getQuery()
        ->getResult();
}
```

### 3. `src/Controller/SitemapController.php`

Ajout de l'injection de `PageRepository` et `CategoryRepository`. Le contrôleur récupère désormais :
- Les pages CV et RGPD par leur slug (pour le `lastmod`)
- Les catégories avec au moins un article publié

### 4. `templates/sitemap.xml.twig`

Le template génère maintenant les URLs suivantes :

| URL | Priorité | `changefreq` | `lastmod` |
|-----|----------|--------------|-----------|
| `/` (accueil) | 1.0 | weekly | — |
| `/articles` | 0.8 | daily | — |
| `/cv` | 0.7 | monthly | `updatedAt` de la page en BDD |
| `/rgpd` | 0.3 | yearly | `updatedAt` de la page en BDD |
| `/categorie/{slug}` | 0.5 | weekly | — |
| `/article/{slug}` | 0.6 | monthly | `updatedAt` ou `publishedAt` |

---

## Comportement dynamique

Le sitemap est généré à la volée à chaque requête sur `/sitemap.xml`. Toute modification d'un article, d'une page CV/RGPD ou création d'une nouvelle catégorie avec articles est immédiatement reflétée, sans cache ni génération manuelle.

---

## Vérifications

- `php bin/console lint:twig templates/sitemap.xml.twig` → OK
- `vendor/bin/phpstan analyse src/Controller/SitemapController.php src/Repository/CategoryRepository.php --level=8` → 0 erreur
