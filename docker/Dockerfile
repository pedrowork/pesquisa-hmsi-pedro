# syntax=docker/dockerfile:1.7

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.* ./
RUN composer install \
    --prefer-dist \
    --no-progress \
    --no-interaction \
    --no-scripts \
    --ignore-platform-reqs \
    --optimize-autoloader

FROM php:8.4-cli-bookworm AS wayfinder
WORKDIR /app
COPY --from=vendor /app/vendor ./vendor
COPY . .
RUN php artisan wayfinder:generate

FROM node:20 AS assets
ARG VITE_REVERB_APP_KEY
ARG VITE_REVERB_HOST
ARG VITE_REVERB_PORT
ARG VITE_REVERB_SCHEME
ENV VITE_REVERB_APP_KEY=${VITE_REVERB_APP_KEY} \
    VITE_REVERB_HOST=${VITE_REVERB_HOST} \
    VITE_REVERB_PORT=${VITE_REVERB_PORT} \
    VITE_REVERB_SCHEME=${VITE_REVERB_SCHEME}
WORKDIR /app
COPY package.json pnpm-lock.yaml* pnpm-workspace.yaml ./
RUN corepack enable && corepack prepare pnpm@10.22.0 --activate
RUN (pnpm install --frozen-lockfile) || pnpm install
COPY resources ./resources
COPY vite.config.ts tsconfig.json postcss.config.js tailwind.config.js components.json ./
COPY public ./public
COPY --from=vendor /app/vendor ./vendor
COPY --from=wayfinder /app/resources/js/actions ./resources/js/actions
COPY --from=wayfinder /app/resources/js/routes ./resources/js/routes
COPY --from=wayfinder /app/resources/js/wayfinder ./resources/js/wayfinder
ENV SKIP_WAYFINDER=true
RUN pnpm build

FROM php:8.4-fpm-bookworm AS app
RUN apt-get update && apt-get install -y --no-install-recommends \
    libzip-dev libicu-dev libpq-dev libgmp-dev unzip git build-essential \
    libpng-dev libjpeg-dev libwebp-dev libfreetype6-dev \
    libmagickwand-dev libmagickwand-6.q16-6 jpegoptim optipng pngquant gifsicle \
    && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && docker-php-ext-install pdo_pgsql intl bcmath gmp zip exif pcntl sockets opcache gd \
    && pecl install redis imagick && docker-php-ext-enable redis imagick \
    && apt-get purge -y --auto-remove build-essential libmagickwand-dev \
    && rm -rf /var/lib/apt/lists/*
WORKDIR /var/www/html
COPY --from=vendor /app/vendor ./vendor
COPY . .
COPY --from=assets /app/public/build ./public/build
COPY --from=assets /app/bootstrap/ssr ./bootstrap/ssr
COPY docker/entrypoint.sh /entrypoint.sh
RUN php artisan package:discover --ansi \
    && chmod +x /entrypoint.sh \
    && chown -R www-data:www-data storage bootstrap/cache public
USER www-data
ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]

FROM node:20-alpine AS ssr
WORKDIR /app
COPY --from=assets /app/bootstrap/ssr ./bootstrap/ssr
COPY --from=assets /app/node_modules ./node_modules
USER node
EXPOSE 13714
CMD ["node", "bootstrap/ssr/ssr.js"]

FROM nginx:1.27-alpine AS nginx
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=app /var/www/html/public /var/www/html/public
