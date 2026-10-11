FROM php:8.4-apache

# ------------------------------------------------------------
# 1. Dépendances système et extensions PHP nécessaires au projet
# ------------------------------------------------------------
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    pkg-config \
    libssl-dev \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        intl \
        zip \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# ------------------------------------------------------------
# 2. Composer
# ------------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Autorise Symfony Flex et Symfony Runtime à fonctionner pendant
# les commandes Composer lancées en root lors du build Docker.
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV APP_ENV=prod
ENV APP_DEBUG=0

# ------------------------------------------------------------
# 3. Dépendances PHP de production
# ------------------------------------------------------------
# Cette copie séparée conserve le cache Docker tant que composer.lock
# n'a pas changé.
COPY composer.json composer.lock ./

RUN COMPOSER_ALLOW_SUPERUSER=1 composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --no-interaction

# ------------------------------------------------------------
# 4. Code source
# ------------------------------------------------------------
COPY . .

# Génère l'autoloader Symfony Runtime, installe les dépendances
# importmap puis compile les assets versionnés pour la production.
# APP_SECRET est fictif et utilisé seulement au build : ne jamais
# placer ici le véritable secret Railway.
RUN COMPOSER_ALLOW_SUPERUSER=1 composer dump-autoload --optimize \
    && php bin/console importmap:install \
    && php bin/console asset-map:compile \
    && APP_SECRET=build_only php bin/console cache:clear --no-warmup

# ------------------------------------------------------------
# 5. Apache / Symfony
# ------------------------------------------------------------
RUN a2enmod rewrite headers

COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

COPY docker/apache/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 8080

CMD ["/usr/local/bin/start.sh"]