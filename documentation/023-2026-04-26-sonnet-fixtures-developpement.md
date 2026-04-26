# 023 — Fixtures de développement

**Date :** 2026-04-26  
**Modèle :** Claude Sonnet 4.6  
**Branche :** `dev/main`

---

## Contexte

Aucun jeu de données de développement n'existait. Il fallait créer des données réalistes pour tester l'application localement sans saisie manuelle.

---

## Installation de DoctrineFixturesBundle

Le bundle n'était pas dans les dépendances. Il a été installé puis déplacé dans `require` (et non `require-dev`) pour être disponible en déploiement preprod.

```bash
docker compose exec php composer require doctrine/doctrine-fixtures-bundle
```

La recette Symfony a automatiquement :
- Enregistré `Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle` dans `config/bundles.php` (env `dev` et `test`)
- Créé `src/DataFixtures/AppFixtures.php`

---

## Migration de schéma complémentaire

La table `page_category` était absente de la base (la table `category_page` existait à la place, nom inversé). Une migration a été générée et appliquée :

```bash
docker compose exec php php bin/console doctrine:migrations:diff
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
```

---

## Contenu des fixtures (`src/DataFixtures/AppFixtures.php`)

### Utilisateurs — 12 au total

| Email | Rôle | Statut | Mot de passe |
|-------|------|--------|--------------|
| `michaeljpitz@gmail.com` | `ROLE_ADMIN` | actif (1) | `erapacha25` |
| `michael.pitz@cf2m.be` | `ROLE_USER` | actif (1) | `erapacha25` |
| `alice.martin@example.com` | `ROLE_USER` | actif (1) | `password123` |
| `bob.dupont@example.com` | `ROLE_USER` | actif (1) | `password123` |
| `claire.lefebvre@example.com` | `ROLE_USER` | actif (1) | `password123` |
| `david.moreau@example.com` | `ROLE_USER` | actif (1) | `password123` |
| `emma.petit@example.com` | `ROLE_USER` | actif (1) | `password123` |
| `francois.bernard@example.com` | `ROLE_USER` | actif (1) | `password123` |
| `gaelle.richard@example.com` | `ROLE_USER` | actif (1) | `password123` |
| `hugo.thomas@example.com` | `ROLE_USER` | actif (1) | `password123` |
| `isabelle.durand@example.com` | `ROLE_USER` | inactif (0) | `password123` |
| `julien.leroy@example.com` | `ROLE_USER` | inactif (0) | `password123` |

### Catégories — 8 (hiérarchie sur 3 niveaux)

Le champ `level` stocke `0` pour une racine, ou l'`id` de la catégorie parente.

| Titre | Slug | Niveau | Parent |
|-------|------|--------|--------|
| PHP | `php` | 0 | racine |
| Symfony | `symfony` | 0 | racine |
| JavaScript | `javascript` | 0 | racine |
| Doctrine ORM | `doctrine-orm` | 2 | PHP |
| Twig | `twig` | 2 | Symfony |
| Stimulus.js | `stimulus-js` | 2 | JavaScript |
| Tailwind CSS | `tailwind-css` | 2 | JavaScript |
| Doctrine Migrations | `doctrine-migrations` | 3 | Doctrine ORM |

Les catégories sont persistées par groupes avec flush intermédiaire pour récupérer les IDs avant de les utiliser comme valeur de `level`.

### Articles — 21

20 articles génériques (contenu généré par `genererContenu()`) + 1 article spécifique :

**"Les Array en PHP"** (`les-array-en-php`) :
- Contenu HTML fourni manuellement
- Image téléchargée depuis `dev.michaeljpitz.com` → `public/uploads/articles/903ecff8abe47fd4f6e0d8e222a5ee965297926d.jpg`
- Extrait : *Un tableau en PHP est en fait une carte ordonnée qui associe des valeurs à des clés.*
- Catégorie : PHP, publié

Parmi les 21 articles : 16 publiés, 5 non publiés.

### Commentaires — 12

8 approuvés (`isApproved = true`), 4 en attente de modération (`isApproved = false`).

### Pages — 3

| Titre | Slug | Contenu initial |
|-------|------|-----------------|
| Politique de confidentialité (RGPD) | `rgpd` | Contenu statique préexistant dans le template |
| Contact | `contact` | Phrase d'introduction |
| Curriculum Vitae | `cv` | Compétences, expériences, formations |

---

## Alias Docker ajouté

Dans `docker/php/Dockerfile`, section `# --- Doctrine ---` :

```bash
alias fl='php bin/console doctrine:fixtures:load --append --no-interaction'
```

L'alias existant `dfl` purge la base avant chargement. Le nouveau `fl` ajoute les données sans purger.

---

## Commandes

```bash
# Purge + rechargement complet (IDs remis à 1 avec TRUNCATE)
docker compose exec php php bin/console doctrine:fixtures:load --purge-with-truncate --no-interaction

# Purge + rechargement (DELETE, IDs continuent)
docker compose exec php php bin/console doctrine:fixtures:load --no-interaction
# ou via alias : dfl

# Ajout sans purge
docker compose exec php php bin/console doctrine:fixtures:load --append --no-interaction
# ou via alias : fl
```
