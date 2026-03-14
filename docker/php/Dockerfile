FROM php:8.3-fpm-alpine

# Install system dependencies (Alpine uses apk)
RUN apk add --no-cache \
    bash \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    libzip-dev \
    icu-dev \
    icu-data-full \
    zip \
    unzip \
    linux-headers \
    $PHPIZE_DEPS

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        intl \
        zip \
        opcache

# Install APCu and Xdebug via PECL
RUN pecl install apcu xdebug \
    && docker-php-ext-enable apcu xdebug

# Configurer la locale française pour ICU/intl
ENV LC_ALL=fr_FR.UTF-8
RUN echo "intl.default_locale = fr" > /usr/local/etc/php/conf.d/intl.ini

# Limites d'upload à 12 Mo
RUN printf "upload_max_filesize = 12M\npost_max_size = 12M\n" > /usr/local/etc/php/conf.d/uploads.ini

# Augmenter la limite mémoire PHP
RUN echo "memory_limit = 512M" > /usr/local/etc/php/conf.d/memory-limit.ini

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Aliases disponibles dans bash et sh (profile.d = chargé à l'ouverture du shell)
RUN printf '%s\n' \
    '# --- Symfony ---' \
    "alias pbc='php bin/console'" \
    "alias cc='php bin/console cache:clear'" \
    "alias lint='php bin/console lint:twig templates/ && php bin/console lint:yaml config/ && php bin/console lint:container'" \
    "alias asset='php bin/console asset-map:compile'" \
    "alias wind='php bin/console tailwind:build'" \
    '# --- Doctrine ---' \
    "alias ddc='php bin/console doctrine:database:create'" \
    "alias ddrop='php bin/console doctrine:database:drop --force'" \
    "alias dds='php bin/console doctrine:schema:validate'" \
    "alias mm='php bin/console make:migration'" \
    "alias migrate='php bin/console doctrine:migrations:migrate --no-interaction'" \
    "alias dfl='php bin/console doctrine:fixtures:load --no-interaction'" \
    '# --- Tests ---' \
    "alias test='php bin/phpunit --no-coverage'" \
    "alias testv='php bin/phpunit --no-coverage --testdox'" \
    '# --- Qualité ---' \
    "alias phpstan='vendor/bin/phpstan analyse src --level=8'" \
    "alias csfix='./vendor/bin/php-cs-fixer fix'" \
    "alias phpfix='./vendor/bin/php-cs-fixer fix'" \
    '# --- Composer ---' \
    "alias ci='composer install'" \
    "alias cu='composer update'" \
    "alias cval='composer validate --strict'" \
    "alias caudit='composer audit'" \
    '# --- Git ---' \
    "alias gs='git status'" \
    "alias ga='git add .'" \
    "alias gc='git commit'" \
    "alias gps='git push'" \
    "alias gpu='git pull'" \
    "alias gl='git log --oneline --graph --decorate -15'" \
    > /etc/profile.d/aliases.sh \
    && chmod +x /etc/profile.d/aliases.sh \
    && echo 'source /etc/profile.d/aliases.sh' >> /root/.bashrc

WORKDIR /var/www/html

# Marquer le répertoire de travail comme sûr pour git
RUN git config --global --add safe.directory /var/www/html

# Créer les répertoires d'upload avec les bonnes permissions
RUN mkdir -p /var/www/html/public/uploads/articles/content \
    && chmod -R 777 /var/www/html/public/uploads
