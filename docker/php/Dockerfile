FROM php:8.3-fpm-alpine

# Install system dependencies (Alpine uses apk)
RUN apk add --no-cache \
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

WORKDIR /var/www/html

# Marquer le répertoire de travail comme sûr pour git
RUN git config --global --add safe.directory /var/www/html

# Créer les répertoires d'upload avec les bonnes permissions
RUN mkdir -p /var/www/html/public/uploads/articles/content \
    && chmod -R 777 /var/www/html/public/uploads
