---
description: Corriger le style de code avec PHP CS Fixer
allowed-tools: Bash
---

# Corriger le style de code

Depuis `/home/mikhawa/cv-mikhawa`, exécute :

```
docker compose exec php ./vendor/bin/php-cs-fixer fix
```

Affiche la liste des fichiers modifiés. S'il n'y a aucune modification, indique que le code est déjà conforme.
