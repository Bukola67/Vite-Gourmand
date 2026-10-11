#!/bin/sh
set -e

: "${PORT:=8080}"

sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf

echo ">> Application des migrations Doctrine..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

echo ">> Démarrage Apache sur le port ${PORT}..."
exec apache2-foreground