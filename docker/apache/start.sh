#!/bin/bash
set -e

echo ">> Application des migrations Doctrine..."

php bin/console doctrine:database:create --if-not-exists --no-interaction || true
php bin/console doctrine:migrations:migrate --no-interaction

echo ">> Nettoyage du cache Symfony..."
php bin/console cache:clear --no-warmup

echo ">> Démarrage d'Apache sur le port ${PORT}..."

exec apache2-foreground