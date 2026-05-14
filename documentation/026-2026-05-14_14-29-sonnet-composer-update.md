# 026 — Mise à jour des dépendances Composer

**Date :** 2026-05-14  
**Modèle :** Claude Sonnet 4.6  
**Branche :** `dev/main`

---

## Contexte

Mise à jour de routine des dépendances Composer. Aucune dépendance n'a changé de version majeure — uniquement des correctifs de patch et de sécurité.

---

## Paquets mis à jour

| Paquet | Avant | Après |
|--------|-------|-------|
| `doctrine/doctrine-fixtures-bundle` | `^4.3` | `^4.3.1` |
| `doctrine/orm` | `^3.6.3` | `^3.6.5` |
| `easycorp/easyadmin-bundle` | `^4.29.6` | `^4.29.8` |
| `phpstan/phpstan` | `^2.1.51` | `^2.1.54` |
| `phpunit/phpunit` | `^12.5.23` | `^12.5.25` |

---

## Commande exécutée

```bash
docker compose exec php composer update
```

---

## Vérification

```bash
docker compose exec php composer validate
docker compose exec php php bin/console lint:container
docker compose exec php vendor/bin/phpstan analyse src --level=8
docker compose exec php php bin/phpunit
```

---

## Notes

Aucune rupture de compatibilité constatée. Le fichier `config/reference.php` a été régénéré automatiquement par Symfony lors de la mise à jour.