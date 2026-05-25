#!/bin/sh
set -e

cd /var/www/html

if [ -z "${APP_KEY:-}" ]; then
    echo "APP_KEY is not set. Add it to your Portainer stack environment variables."
    echo "Generate one with: php artisan key:generate --show"
    exit 1
fi

ensure_storage_directories() {
    mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache

    chown -R www-data:www-data storage bootstrap/cache
    chmod -R ug+rwx storage bootstrap/cache
}

ensure_storage_directories

php artisan config:clear --no-interaction
php artisan route:clear --no-interaction

wait_for_database() {
    echo "Waiting for database connection..."
    attempts=0
    max_attempts=60

    until php artisan db:show --no-interaction >/dev/null 2>&1; do
        attempts=$((attempts + 1))

        if [ "$attempts" -ge "$max_attempts" ]; then
            echo "Database connection failed after ${max_attempts} attempts."
            exit 1
        fi

        echo "Database not ready (attempt ${attempts}/${max_attempts}). Retrying in 2s..."
        sleep 2
    done

    echo "Database connection established."
}

if [ -n "${DB_HOST:-}" ]; then
    wait_for_database
fi

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force --no-interaction
fi

if [ "${APP_ENV:-local}" = "production" ]; then
    php artisan config:cache --no-interaction
    php artisan route:cache --no-interaction
    php artisan view:cache --no-interaction
fi

exec "$@"
