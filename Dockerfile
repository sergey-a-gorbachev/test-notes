FROM php:8.4-cli-alpine AS api-base

RUN apk add --no-cache sqlite-libs \
    && apk add --no-cache --virtual .sqlite-build-deps sqlite-dev \
    && docker-php-ext-install pdo_sqlite \
    && apk del .sqlite-build-deps
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts --no-autoloader
COPY backend/ ./
RUN composer dump-autoload --classmap-authoritative --no-interaction \
    && mkdir -p var/data var/cache var/log \
    && chown -R www-data:www-data var

FROM api-base AS api
USER www-data
EXPOSE 8000
CMD ["sh", "-c", "php bin/console doctrine:migrations:migrate --no-interaction && exec php -S 0.0.0.0:8000 -t public public/index.php"]

FROM api-base AS openapi
RUN php bin/console nelmio:apidoc:dump --format=yaml > /openapi.yaml

FROM node:22-alpine AS frontend
WORKDIR /app
COPY frontend/package.json frontend/package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY frontend/ ./
RUN npm run build

FROM node:24-alpine AS docs
WORKDIR /docs
COPY docs/package.json docs/package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY docs/ ./
COPY --from=openapi /openapi.yaml ./openapi.yaml
RUN npm run build

FROM nginx:stable-alpine AS web
COPY frontend/nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=frontend /app/dist /usr/share/nginx/html
COPY --from=docs /docs/build /usr/share/nginx/html/docs
COPY --from=openapi /openapi.yaml /usr/share/nginx/html/openapi.yaml
EXPOSE 80
