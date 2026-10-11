#!/bin/sh
set -eu

: "${PORT:=8080}"

echo "=== Railway entrypoint : nettoyage des MPM Apache ==="
echo "=== MPM actifs avant correction ==="
ls -la /etc/apache2/mods-enabled/ | grep mpm || echo "(aucun MPM listé)"

rm -f /etc/apache2/mods-enabled/mpm_event.load \
      /etc/apache2/mods-enabled/mpm_event.conf \
      /etc/apache2/mods-enabled/mpm_worker.load \
      /etc/apache2/mods-enabled/mpm_worker.conf \
      /etc/apache2/mods-enabled/mpm_prefork.load \
      /etc/apache2/mods-enabled/mpm_prefork.conf

ln -sf /etc/apache2/mods-available/mpm_prefork.load \
       /etc/apache2/mods-enabled/mpm_prefork.load

ln -sf /etc/apache2/mods-available/mpm_prefork.conf \
       /etc/apache2/mods-enabled/mpm_prefork.conf

echo "=== MPM actifs après correction ==="
ls -la /etc/apache2/mods-enabled/ | grep mpm

echo "=== Configuration du port Apache : ${PORT} ==="
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf

echo "=== Validation de la configuration Apache ==="
apache2ctl -t

echo "=== Application des migrations Doctrine ==="
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

echo "=== Démarrage Apache ==="
exec /usr/local/bin/docker-php-entrypoint apache2-foreground