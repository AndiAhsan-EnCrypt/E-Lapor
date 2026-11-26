#!/bin/sh
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*
ls /etc/apache2/mods-enabled/mpm_prefork.load >/dev/null 2>&1 || a2enmod mpm_prefork >/dev/null
PORT="${PORT:-8080}"
sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf
sed -i "s/:80>/:$PORT>/" /etc/apache2/sites-available/000-default.conf
php /var/www/html/docker-init-db.php || true
exec apache2-foreground
