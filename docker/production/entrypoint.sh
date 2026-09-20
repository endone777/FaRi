#!/bin/sh
# Готовит рабочие каталоги, базу и кэши, затем передаёт управление supervisor.
set -e

cd /var/www/html

DB_PATH="${DB_DATABASE:-/var/www/html/storage/app/database/database.sqlite}"

mkdir -p \
    storage/app/public/products \
    storage/app/private \
    storage/app/database \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ] && [ ! -f "$DB_PATH" ]; then
    echo "→ создаю базу $DB_PATH"
    touch "$DB_PATH"
fi

echo "→ миграции"
php artisan migrate --force --no-interaction

if [ "${DEPLOY_SEED:-false}" = "true" ]; then
    echo "→ наполняю справочники и каталог"
    php artisan db:seed --force --no-interaction
fi

echo "→ ссылка на хранилище"
php artisan storage:link --force --no-interaction || true

echo "→ кэширую конфигурацию, маршруты и представления"
php artisan optimize

# php-fpm работает под www-data, поэтому права выставляем после artisan.
chown -R www-data:www-data storage bootstrap/cache
chmod -R u+rwX,g+rwX storage bootstrap/cache

echo "→ приложение готово"

exec "$@"
