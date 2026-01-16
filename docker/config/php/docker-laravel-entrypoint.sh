#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'bin/console' ]; then
	# WORKING on MAC & LINUX Dockers
	# chown www-data:www-data bootstrap/cache/
	# chown -R www-data:www-data storage/

	# NOT WORKING on MAC - setfacl: Not supported
    setfacl -R -m u:www-data:rwX storage bootstrap/cache vendor
	setfacl -dR -m u:www-data:rwX storage bootstrap/cache vendor
fi

exec docker-php-entrypoint "$@"