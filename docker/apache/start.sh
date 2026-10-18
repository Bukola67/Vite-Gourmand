#!/bin/bash
set -e

# Railway fournit PORT dynamiquement. Valeur par défaut pour le développement local.
PORT="${PORT:-80}"

echo ">> Application des migrations Doctrine..."

php bin/console doctrine:database:create --if-not-exists --no-interaction || true
php bin/console doctrine:migrations:migrate --no-interaction

echo ">> Nettoyage du cache Symfony..."
php bin/console cache:clear --no-warmup

echo ">> Configuration d'Apache sur le port ${PORT}..."

sed -ri "s/^Listen [0-9]+$/Listen ${PORT}/" /etc/apache2/ports.conf

echo ">> Démarrage d'Apache sur le port ${PORT}..."

exec apache2-foreground