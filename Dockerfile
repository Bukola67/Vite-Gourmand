FROM php:8.4-apache

# Outils et dépendances système
RUN apt-get update && apt-get install -y \
        git \
        unzip \
        libicu-dev \
        libzip-dev \
        zip \
        curl \
        libonig-dev \
        libxml2-dev \
        pkg-config \
        libssl-dev \
    && docker-php-ext-install pdo pdo_mysql intl zip \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && php -m | grep -q '^mongodb$' \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Dépendances PHP : couche cache efficace
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# Code applicatif
COPY . .

# Autoloader optimisé et assets Symfony
ENV APP_ENV=prod \
    APP_DEBUG=0

RUN composer dump-autoload --optimize \
    && php bin/console importmap:install \
    && php bin/console asset-map:compile \
    && php bin/console cache:clear --no-warmup

# Configuration Apache
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/apache2.conf \
        /etc/apache2/conf-available/*.conf \
    && a2enmod rewrite headers

# Railway fournit un port dynamique
RUN sed -ri 's/^Listen 80$/Listen 80/' /etc/apache2/ports.conf

COPY docker/apache/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 8080

CMD ["/usr/local/bin/start.sh"]