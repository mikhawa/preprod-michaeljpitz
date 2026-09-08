# 037 - Fix tri instable EasyAdmin - articles manquants dans la liste

**Date** : 2026-08-03
**Modèle** : Claude Sonnet 5

## Problème

Sur https://preprod.michaeljpitz.com/admin/article, l'article id 401
("Les Array en PHP") n'apparaissait sur aucune page de la liste EasyAdmin,
alors qu'il existe bien en base et s'affiche correctement sur le site public
(`/article/les-array-en-php`). La liste affichait un total de 20 articles au
lieu de 21.

## Cause

`ArticleCrudController::configureCrud()` définissait un tri par défaut sur
un seul critère : `->setDefaultSort(['createdAt' => 'DESC'])`.

Dans `ProdArticlesFixtures.php`, les 20 articles "brouillons" et l'article
"Les Array en PHP" sont tous `persist()`-és puis flush()-és dans le même
appel (`$manager->flush()` ligne ~204). Le champ `createdAt` étant renseigné
via le callback `#[ORM\PrePersist]` (résolution à la seconde), ces 21
articles se retrouvent avec une valeur `createdAt` strictement identique
(`2026-07-27 09:15:32`).

Un `ORDER BY createdAt DESC` sur des lignes strictement égales ne garantit
aucun ordre stable entre deux requêtes paginées distinctes (page 1 puis
page 2 = deux `SELECT ... LIMIT` séparés). MariaDB peut alors retourner la
même ligne sur plusieurs pages tout en omettant une autre ligne, sans que le
total ne soit techniquement faux (les lignes existent, mais l'ensemble
récupéré sur l'ensemble des pages n'est pas complet).

## Correction

Remplacement du tri par défaut par un tri sur `id` (colonne unique, donc
intrinsèquement stable) dans `src/Controller/Admin/ArticleCrudController.php` :

```php
->setDefaultSort(['id' => 'DESC']);
```

Cela garantit un ordre total et stable, même en présence de valeurs
`createdAt` identiques, et évite les doublons/omissions entre pages. C'est
aussi le classement souhaité (articles les plus récents en premier, par id
décroissant).

## Vérification

- Recharger https://preprod.michaeljpitz.com/admin/article (toutes pages)
  et confirmer que les 22 articles (20 brouillons + 2 publiés) apparaissent
  chacun une seule fois, y compris l'article id 401.