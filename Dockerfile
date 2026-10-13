# syntax=docker/dockerfile:1.7
# StockFlow — multi-stage image build.
#   target "app" (default): php-fpm runtime (API, queue worker, migrations)
#   target "web":           nginx serving /public and proxying PHP to php-fpm

ARG PHP_VERSION=8.4
ARG NODE_VERSION=22

############################
# 1) Frontend assets (Vite)
############################
FROM node:${NODE_VERSION}-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json .npmrc ./
RUN npm ci --no-audit --no-fund
COPY vite.config.ts tsconfig.json ./
COPY resources ./resources
COPY public ./public
RUN npm run build

############################
# 2) Composer (prod deps)
############################
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist \
        --no-scripts --no-autoloader --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative --no-scripts

############################
# 3) PHP-FPM runtime
############################
FROM php:${PHP_VERSION}-fpm-alpine AS app

RUN apk add --no-cache icu-libs libzip oniguruma sqlite-libs fcgi \
 && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS icu-dev libzip-dev oniguruma-dev sqlite-dev \
 && docker-php-ext-install -j"$(nproc)" pdo_mysql pdo_sqlite mbstring intl zip bcmath opcache pcntl \
 && apk del .build-deps \
 && rm -rf /tmp/* /usr/src/php*

COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-stockflow.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/zz-www.conf
COPY --chmod=0755 docker/php/entrypoint.sh /usr/local/bin/entrypoint
COPY --chmod=0755 docker/php/healthcheck.sh /usr/local/bin/php-fpm-healthcheck

WORKDIR /var/www/html
COPY --from=vendor --chown=www-data:www-data /app /var/www/html
COPY --from=assets --chown=www-data:www-data /app/public/build /var/www/html/public/build

# Twelve-factor defaults; everything else (APP_KEY, DB_*, ...) comes from the environment.
ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    LOG_STDERR_FORMATTER=Monolog\\Formatter\\JsonFormatter

RUN rm -f .env database/*.sqlite public/hot \
 && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
 && php artisan package:discover --ansi \
 && chown -R www-data:www-data storage bootstrap/cache database

USER 82:82
EXPOSE 9000
HEALTHCHECK --interval=10s --timeout=3s --start-period=10s --retries=5 CMD ["php-fpm-healthcheck"]
ENTRYPOINT ["entrypoint"]
CMD ["php-fpm", "-F"]

############################
# 4) Nginx (static + proxy)
############################
FROM nginx:1.29-alpine AS web
COPY docker/nginx/default.conf.template /etc/nginx/templates/default.conf.template
COPY --from=assets /app/public /var/www/html/public
# Upstream php-fpm address: "app:9000" in compose, "127.0.0.1:9000" in a shared K8s pod.
ENV PHP_FPM_HOST=app:9000 \
    NGINX_ENVSUBST_FILTER=^PHP_FPM_
EXPOSE 80
HEALTHCHECK --interval=10s --timeout=3s --start-period=10s --retries=5 \
    CMD wget -qO- http://127.0.0.1/nginx-health >/dev/null || exit 1
