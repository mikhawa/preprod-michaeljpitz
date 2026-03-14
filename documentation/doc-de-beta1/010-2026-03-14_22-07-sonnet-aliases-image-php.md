# 010 — Ajout des aliases dans l'image PHP Docker

**Date** : 2026-03-14 à 22h07
**Moteur IA** : Claude Sonnet 4.6
**Branche** : `dev/mailpit-local`

---

## Contexte

Les aliases du `RACCOURCIS.md` étaient définis pour la console WSL de l'hôte. Ils ont été intégrés directement dans l'image PHP Docker pour être disponibles à chaque `docker compose exec php bash`.

## Modifications du Dockerfile

### Installation de `bash`

Alpine Linux embarque uniquement `ash` (BusyBox). `bash` a été ajouté aux dépendances système pour bénéficier de l'auto-complétion et de la compatibilité des aliases.

### Fichier `/etc/profile.d/aliases.sh`

Chargé automatiquement à l'ouverture d'un shell interactif. Sourcé également dans `/root/.bashrc`.

## Liste complète des aliases

| Alias | Commande | Catégorie |
|-------|----------|-----------|
| `pbc` | `php bin/console` | Symfony |
| `cc` | `php bin/console cache:clear` | Symfony |
| `lint` | lint Twig + YAML + container | Symfony |
| `asset` | `php bin/console asset-map:compile` | Symfony |
| `wind` | `php bin/console tailwind:build` | Symfony |
| `ddc` | `php bin/console doctrine:database:create` | Doctrine |
| `ddrop` | `php bin/console doctrine:database:drop --force` | Doctrine |
| `dds` | `php bin/console doctrine:schema:validate` | Doctrine |
| `mm` | `php bin/console make:migration` | Doctrine |
| `migrate` | `php bin/console doctrine:migrations:migrate --no-interaction` | Doctrine |
| `dfl` | `php bin/console doctrine:fixtures:load --no-interaction` | Doctrine |
| `test` | `php bin/phpunit --no-coverage` | Tests |
| `testv` | `php bin/phpunit --no-coverage --testdox` | Tests |
| `phpstan` | `vendor/bin/phpstan analyse src --level=8` | Qualité |
| `csfix` | `./vendor/bin/php-cs-fixer fix` | Qualité |
| `phpfix` | `./vendor/bin/php-cs-fixer fix` | Qualité |
| `ci` | `composer install` | Composer |
| `cu` | `composer update` | Composer |
| `cval` | `composer validate --strict` | Composer |
| `caudit` | `composer audit` | Composer |
| `gs` | `git status` | Git |
| `ga` | `git add .` | Git |
| `gc` | `git commit` | Git |
| `gps` | `git push` | Git |
| `gpu` | `git pull` | Git |
| `gl` | `git log --oneline --graph --decorate -15` | Git |

## Utilisation

```bash
# Entrer dans le conteneur
docker compose exec php bash

# Exemples d'aliases disponibles immédiatement
migrate     # migrations Doctrine
test        # PHPUnit
wind        # build Tailwind
lint        # lint Twig + YAML + container
gl          # log git compact
```

## Rebuild nécessaire

Après modification du Dockerfile, rebuild requis :

```bash
docker compose build php
docker compose up -d php
```
