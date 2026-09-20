# syntax=docker/dockerfile:1

# ── Сборка: PHP нужен и здесь, потому что Wayfinder во время vite build
# ── вызывает `php artisan wayfinder:generate`.
FROM php:8.4-cli-alpine AS build

RUN apk add --no-cache git unzip nodejs npm

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-interaction \
        --no-progress

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY . .

# Артефакты сборки не лежат в репозитории, поэтому собираются здесь.
RUN cp -n .env.example .env \
    && mkdir -p storage/framework/cache/data storage/framework/sessions \
        storage/framework/views storage/logs bootstrap/cache \
    && composer dump-autoload --no-dev --optimize --classmap-authoritative \
    && php artisan wayfinder:generate --with-form \
    && npm run build \
    && rm -rf node_modules .env

# ── Среда выполнения: nginx + php-fpm под одним supervisor.
FROM php:8.4-fpm-alpine AS runtime

RUN apk add --no-cache nginx supervisor \
    && docker-php-ext-install -j"$(nproc)" opcache pcntl bcmath \
    && rm -rf /var/cache/apk/*

COPY docker/production/php.ini /usr/local/etc/php/conf.d/99-fari.ini
COPY docker/production/nginx.conf /etc/nginx/nginx.conf
COPY docker/production/supervisord.conf /etc/supervisord.conf
COPY docker/production/entrypoint.sh /usr/local/bin/entrypoint

WORKDIR /var/www/html

COPY --from=build --chown=www-data:www-data /app /var/www/html

RUN chmod +x /usr/local/bin/entrypoint \
    && mkdir -p /var/log/supervisor /run/nginx \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=40s --retries=3 \
    CMD php -r 'exit(@file_get_contents("http://127.0.0.1/up") === false ? 1 : 0);'

ENTRYPOINT ["entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
