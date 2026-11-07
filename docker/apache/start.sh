#!/bin/sh
set -eu

cd /var/www/html

: "${PORT:=8080}"
: "${APP_ENV:=prod}"
: "${APP_DEBUG:=0}"

export APP_ENV APP_DEBUG

echo "=== Configuration du port Apache : ${PORT} ==="

sed -i -E \
    "s/^[[:space:]]*Listen[[:space:]]+.*/Listen ${PORT}/" \
    /etc/apache2/ports.conf

sed -i -E \
    "s/<VirtualHost[[:space:]]+[^>]+>/<VirtualHost *:${PORT}>/" \
    /etc/apache2/sites-available/000-default.conf

echo "=== Configuration du nom du serveur ==="

printf 'ServerName localhost\n' \
    > /etc/apache2/conf-available/servername.conf

a2enconf servername

echo "=== Configuration du MPM Apache ==="

rm -f \
    /etc/apache2/mods-enabled/mpm_event.load \
    /etc/apache2/mods-enabled/mpm_event.conf \
    /etc/apache2/mods-enabled/mpm_worker.load \
    /etc/apache2/mods-enabled/mpm_worker.conf

ln -sf /etc/apache2/mods-available/mpm_prefork.load \
    /etc/apache2/mods-enabled/mpm_prefork.load

ln -sf /etc/apache2/mods-available/mpm_prefork.conf \
    /etc/apache2/mods-enabled/mpm_prefork.conf

echo "=== Validation de la configuration Apache ==="

apache2ctl -t

echo "=== Préparation des dossiers Symfony ==="

mkdir -p var/cache var/log var/share

chown -R www-data:www-data var
chmod -R u+rwX var

echo "=== Application des migrations Doctrine ==="

runuser -u www-data -- \
    php bin/console doctrine:migrations:migrate \
    --env=prod \
    --no-debug \
    --no-interaction \
    --allow-no-migration

echo "=== Reconstruction du cache avec les variables de production ==="

runuser -u www-data -- \
    php bin/console cache:clear \
    --env=prod \
    --no-debug \
    --no-warmup

runuser -u www-data -- \
    php bin/console cache:warmup \
    --env=prod \
    --no-debug

echo "=== Vérification des permissions Symfony ==="

runuser -u www-data -- sh -eu -c '
    test -w var/cache
    test -w var/cache/prod
    test -w var/log
'

echo "=== Permissions Symfony : OK ==="
echo "=== Démarrage Apache ==="

exec /usr/local/bin/docker-php-entrypoint apache2-foreground