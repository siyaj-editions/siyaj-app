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

COPY composer.json composer.lock symfony.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --no-scripts

COPY . .

RUN composer dump-autoload --classmap-authoritative --no-dev --no-interaction \
    && APP_ENV=prod \
    APP_DEBUG=0 \
    APP_SECRET=build-secret \
    DATABASE_URL=postgresql://app:app@database:5432/app?serverVersion=16&charset=utf8 \
    STRIPE_PUBLIC_KEY=pk_test_build \
    STRIPE_SECRET_KEY=sk_test_build \
    STRIPE_WEBHOOK_SECRET=whsec_build \
    php bin/console tailwind:build

RUN chown -R www-data:www-data var public

USER www-data

CMD ["php-fpm"]
