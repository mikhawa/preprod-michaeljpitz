# Phase 3 : Frontend public

> Date : 31 janvier 2026

## Actions réalisées

### 1. Installation de KnpPaginatorBundle

- `knplabs/knp-paginator-bundle` v6.10 : pagination des listes d'articles (9 par page)

### 2. Controllers publics

**ArticleController** (`src/Controller/ArticleController.php`) :
- `GET /articles` (app_article_index) : liste paginée d'articles publiés (9/page), filtre optionnel par catégorie via `?category={slug}`
- `GET /articles/{slug}` (app_article_show) : page détaillée d'un article publié avec articles similaires (même catégorie)

**HomeController** (`src/Controller/HomeController.php`) :
- Enrichi pour passer les 3 derniers articles publiés au template

**SitemapController** (`src/Controller/SitemapController.php`) :
- `GET /sitemap.xml` : sitemap XML dynamique listant la page d'accueil, la liste des articles et chaque article publié

### 3. Repository enrichi

**ArticleRepository** (`src/Repository/ArticleRepository.php`) :
- `findPublishedQueryBuilder(?Category $category)` : QueryBuilder pour les articles publiés, triés par date, filtrage optionnel par catégorie
- `findLatestPublished(int $limit = 3)` : N derniers articles publiés
- `findSimilarArticles(Article $article, int $limit = 3)` : articles de la même catégorie (hors article courant)

### 4. Navigation et layout

**base.html.twig** mis à jour :
- Liens de navigation : Accueil, CV (ancre #cv), Articles, Contact (ancre #contact)
- Blocs SEO : `meta_description`, `meta_canonical`, `meta_og`, `og_title`, `og_description`, `og_image`
- Balises OpenGraph (og:title, og:description, og:type, og:image)
- Layout flex-col pour footer collé en bas

### 5. Templates

**home/index.html.twig** :
- Section Hero avec titre et description
- Section CV avec 3 colonnes : Compétences, Expériences, Formations (contenu placeholder)
- Section Derniers articles (3 cartes via composant ArticleCard)
- Section Contact avec lien email

**article/index.html.twig** :
- Filtres par catégorie (boutons avec état actif)
- Grille responsive 3 colonnes de cartes articles
- Pagination Tailwind complète (précédent/suivant + numéros de page)
- Message "Aucun article" si liste vide

**article/show.html.twig** :
- Navigation retour vers la liste
- Badge catégorie, titre, dates (publication + mise à jour)
- Image mise en avant
- Contenu avec classes prose pour le rendu HTML (Trix)
- Balises SEO : canonical, OpenGraph avec image
- Articles similaires en bas de page

### 6. Composants Twig réutilisables

**components/ArticleCard.html.twig** :
- Carte d'article avec image (ou placeholder), badge catégorie, titre, extrait, date et lien "Lire"
- Utilise le composant CategoryBadge

**components/CategoryBadge.html.twig** :
- Badge catégorie avec couleur dynamique (fond semi-transparent, texte coloré, bordure)
- Prop `linked` : lien vers le filtre catégorie (par défaut true)

### 7. SEO

- **robots.txt** (`public/robots.txt`) : Allow /, Disallow /admin et /login, lien vers sitemap
- **Sitemap XML** : route `/sitemap.xml` générée dynamiquement avec tous les articles publiés
- **Meta tags** dans base.html.twig : description, canonical (surchargeable), OpenGraph complet

### 8. Headers de sécurité

**SecurityHeadersSubscriber** (`src/EventSubscriber/SecurityHeadersSubscriber.php`) :
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: SAMEORIGIN`
- `X-XSS-Protection: 1; mode=block`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: camera=(), microphone=(), geolocation=()`
- `Content-Security-Policy` : self + unsafe-inline pour scripts/styles, images self/data/https, frames https

## Vérifications Phase 3

| Point | Statut |
|-------|--------|
| `http://localhost:8080/` (accueil avec CV) | OK - HTTP 200 |
| `http://localhost:8080/articles` (liste paginée) | OK - HTTP 200 |
| `http://localhost:8080/sitemap.xml` (sitemap XML) | OK - HTTP 200 |
| `http://localhost:8080/robots.txt` | OK - fichier statique |
| Headers de sécurité présents | OK - 6 headers vérifiés |
| lint:twig | OK - 8 fichiers valides |
| lint:container | OK - conteneur valide |
| Tailwind rebuild | OK - build réussi |

## Fichiers créés/modifiés

```
# Nouveaux fichiers
src/Controller/ArticleController.php
src/Controller/SitemapController.php
src/EventSubscriber/SecurityHeadersSubscriber.php
templates/article/index.html.twig
templates/article/show.html.twig
templates/components/ArticleCard.html.twig
templates/components/CategoryBadge.html.twig
templates/sitemap.xml.twig
public/robots.txt

# Fichiers modifiés
src/Controller/HomeController.php (ajout des derniers articles)
src/Repository/ArticleRepository.php (3 méthodes de requête)
templates/base.html.twig (navigation, SEO meta, layout flex)
templates/home/index.html.twig (CV placeholder, articles récents, contact)
```
