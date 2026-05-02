FROM composer:2 AS composer

FROM php:8.4-fpm-alpine AS app

RUN apk add --no-cache \
    bash \
    icu-dev \
    libzip-dev \
    postgresql-dev \
    git \
    unzip \
    && docker-php-ext-install -j"$(nproc)" intl pdo_pgsql zip opcache

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
ENV APP_ENV=prod
ENV APP_DEBUG=0
ENV APP_SECRET=build-secret

COPY composer.json composer.lock symfony.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --no-scripts

COPY . .

RUN composer dump-autoload --classmap-authoritative --no-dev --no-interaction

RUN mkdir -p var public assets/vendor \
    && php bin/console importmap:install --env=prod \
    && php bin/console tailwind:build --env=prod --minify \
    && php bin/console asset-map:compile --env=prod \
    && mkdir -p /opt/public \
    && cp -a public/. /opt/public/ \
    && chown -R www-data:www-data var public assets

USER www-data

CMD ["php-fpm"]
