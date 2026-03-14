# Créer des alias sur Mingw64 sous Windows

- [accueil](README.md)

### Console ubuntu wsl

    nano ~/.bashrc

```bash
alias uphp='docker compose exec php sh'
alias dphp='docker compose exec -it php bash'
alias dup='docker compose up -d --build'
alias ddo='docker compose down'
alias phpfix='./vendor/bin/php-cs-fixer fix'
alias asset='php bin/console asset-map:compile'
```


### ----------------------
### Symfony Commands
### ----------------------
```bash
alias pbc='php bin/console'
alias ddc='php bin/console doctrine:database:create'
alias sssd='symfony serve -d'
alias sss='symfony server:stop'
alias dfl='php bin/console doctrine:fixture:load'
alias test='vendor/bin/phpunit --testdox'
alias csfix='./vendor/bin/php-cs-fixer fix'
alias wind='php bin/console tailwind:build'
```

### ----------------------
### Git Commands
### ----------------------
```bash
alias gs='git status'
alias ga='git add .'
alias gc='git commit'
alias gps='git push'
alias gpu='git pull'
```

URL de WSL maison
\\wsl.localhost\Ubuntu\home\

URL de WSL bureau CF2m
\\wsl.localhost\Ubuntu2\home\mikhawa


### ----------------------
### Claude Code Skills (slash commands)
### ----------------------

| Commande | Arguments | Description |
|----------|-----------|-------------|
| `/validate` | aucun | Validation complète du projet : composer validate, lint twig/yaml/container, phpstan, cs-fixer (dry-run), audit. S'arrête à la première erreur. |
| `/test` | `[fichier\|--filter nom]` | Lance les tests PHPUnit dans Docker. Sans argument = tous les tests. |
| `/csfix` | aucun | Corrige automatiquement le style de code avec PHP CS Fixer. |
| `/pre-commit` | aucun | Vérification pré-commit complète (validation + analyse + tests + audit). Verdict OK/KO. |
| `/new-test` | `<chemin/source.php>` | Crée le test unitaire ou fonctionnel correspondant à un fichier source. |

Exemples :
```
/validate
/test tests/Unit/Entity/UserTest.php
/test --filter testLogin
/csfix
/pre-commit
/new-test src/Controller/HomeController.php
```

Les commandes sont définies dans `.claude/commands/*.md`.

---

Commandes courantes :

// création de DB

    php bin/console d:d:c


// make:migrations

    php bin/console ma:mi

// doctrine:migrations:migrate

    php bin/console d:m:m





Créer des alias sur Powershell

if (!(Test-Path -Path $PROFILE)) { New-Item -ItemType File -Path $PROFILE -Force }

code $PROFILE # Ou 'notepad $PROFILE' si vous n'avez pas VS Code

# "serve" lance un serveur PHP rapide sur le port 8000
function serve { Write-Host "Lancement du serveur PHP sur localhost:8000..." -ForegroundColor Cyan php -S localhost:8000 }
# "composer-update" abrégé
function cup { composer update }
function ci { composer install }
