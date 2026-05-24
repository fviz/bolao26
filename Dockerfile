# syntax=docker/dockerfile:1

FROM composer:2 AS vendor

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

COPY . .

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

FROM php:8.4-cli-alpine AS frontend

WORKDIR /var/www/html

RUN apk add --no-cache \
    nodejs \
    npm \
    && docker-php-ext-install -j"$(nproc)" mbstring xml zip \
    && rm -rf /var/cache/apk/*

COPY package.json package-lock.json ./

RUN npm ci

COPY . .

COPY --from=vendor /var/www/html/vendor ./vendor

ENV APP_KEY=base64:dGVtcG9yYXJ5a2V5Zm9yYnVpbGRvbmx5AAAAAAAAAAAAAAA=

RUN npm run build

FROM php:8.4-fpm-alpine AS app

WORKDIR /var/www/html

RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_mysql \
        zip \
    && rm -rf /var/cache/apk/*

COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh \
    && sed -i 's|listen = 127.0.0.1:9000|listen = 9000|g' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's|listen = /var/run/php-fpm.sock|listen = 9000|g' /usr/local/etc/php-fpm.d/www.conf \
    && mkdir -p /run/nginx

COPY . .

COPY --from=vendor /var/www/html/vendor ./vendor
COPY --from=frontend /var/www/html/public/build ./public/build

ENV APP_KEY=base64:dGVtcG9yYXJ5a2V5Zm9yYnVpbGRvbmx5AAAAAAAAAAAAAAA=

RUN php artisan storage:link \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
