# Multi-stage build: Composer + Vite build + final runtime with Nginx + PHP-FPM.
FROM composer:2 AS composer_deps
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts --no-progress
COPY . .
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress

FROM node:22-alpine AS assets
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

FROM php:8.2-fpm-alpine
# System deps and PHP extensions
RUN apk add --no-cache nginx curl bash git zip unzip icu-libs libzip libpng libjpeg-turbo freetype gettext postgresql-libs \
 && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS icu-dev libzip-dev libpng-dev libjpeg-turbo-dev freetype-dev oniguruma-dev postgresql-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo_mysql pdo_pgsql gd zip intl bcmath opcache \
 && apk del .build-deps \
 && mkdir -p /run/nginx

WORKDIR /app

# App code, vendor, and built assets
COPY --from=composer_deps /app /app
COPY --from=assets /app/public/build /app/public/build

# Nginx config template + start script
COPY docker/nginx/default.conf.template /etc/nginx/templates/default.conf.template
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh \
 && chown -R www-data:www-data /app/storage /app/bootstrap/cache \
 && chmod -R 775 /app/storage /app/bootstrap/cache

ENV PORT=8080
EXPOSE 8080

CMD ["/usr/local/bin/start.sh"]