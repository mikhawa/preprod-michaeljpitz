# Créer des alias sur Mingw64 sous Windows

- [accueil](README.md)

### Console ubuntu wsl

    nano ~/.bashrc

```bash
alias uphp='docker compose exec php sh'
alias dphp='docker compose exec -it php bash'
alias dup='docker compose up -d --build'
alias ddo='docker compose down'
```


### ----------------------
### Symfony Commands
### ----------------------
```bash
# --- Symfony ---
alias pbc='php bin/console'
alias cc='php bin/console cache:clear'
alias lint='php bin/console lint:twig templates/ && php bin/console lint:yaml config/ && php bin/console lint:container'
alias asset='php bin/console asset-map:compile'
alias wind='php bin/console tailwind:build'
# --- Doctrine ---
alias ddc='php bin/console doctrine:database:create'
alias ddrop='php bin/console doctrine:database:drop --force'
alias dds='php bin/console doctrine:schema:validate'
alias mm='php bin/console make:migration'
alias migrate='php bin/console doctrine:migrations:migrate --no-interaction'
alias dfl='php bin/console doctrine:fixtures:load --no-interaction'
# --- Tests ---
alias test='php bin/phpunit --no-coverage'
alias testv='php bin/phpunit --no-coverage --testdox'
# --- Qualité ---
alias phpstan='vendor/bin/phpstan analyse src --level=8'
alias csfix='./vendor/bin/php-cs-fixer fix'
alias phpfix='./vendor/bin/php-cs-fixer fix'
# --- Composer ---
alias ci='composer install'
alias cu='composer update'
alias cval='composer validate --strict'
alias caudit='composer audit'
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
