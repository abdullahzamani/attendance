#!/bin/sh
# Prepares the data volume, then starts the web server (default) or another command such as the scheduler.
set -e
cd /var/www/html

as_app() {
    if [ "$(id -u)" = "0" ]; then
        setpriv --reuid=www-data --regid=www-data --init-groups "$@"
    else
        "$@"
    fi
}

if [ "$(id -u)" = "0" ]; then
    mkdir -p storage/app/backups storage/framework/cache/data storage/framework/sessions \
        storage/framework/views storage/logs

    # Secrets are generated once and kept in the data volume, so they survive rebuilds and updates.
    if [ -z "$APP_KEY" ] && [ ! -s storage/app/.app_key ]; then
        echo "base64:$(php -r 'echo base64_encode(random_bytes(32));')" > storage/app/.app_key
    fi
    if [ -z "$BOARD_TOKEN" ] && [ ! -s storage/app/.board_token ]; then
        php -r 'echo bin2hex(random_bytes(16));' > storage/app/.board_token
    fi

    chown -R www-data:www-data storage bootstrap/cache
fi

if [ -z "$APP_KEY" ]; then APP_KEY="$(cat storage/app/.app_key)"; export APP_KEY; fi
if [ -z "$BOARD_TOKEN" ]; then BOARD_TOKEN="$(cat storage/app/.board_token)"; export BOARD_TOKEN; fi

if [ "$1" = "apache2-foreground" ]; then
    if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ] && [ -n "$DB_DATABASE" ] && [ ! -f "$DB_DATABASE" ]; then
        as_app touch "$DB_DATABASE"
    fi

    as_app php artisan migrate --force
    as_app php artisan db:seed --force
    # Caches config (including the keys above), routes, views and Filament components.
    as_app php artisan optimize
    as_app php artisan filament:optimize

    exec "$@"
fi

# Anything else (the scheduler, one-off artisan commands) runs as the web user,
# so the files it writes stay readable by the web server.
if [ "$(id -u)" = "0" ]; then
    exec setpriv --reuid=www-data --regid=www-data --init-groups "$@"
fi
exec "$@"
