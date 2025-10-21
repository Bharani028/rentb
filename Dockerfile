# -------- vendor (Composer) stage --------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts
COPY . .
RUN composer dump-autoload --optimize

# -------- frontend (Vite) stage [optional – keep if you use Vite/Mix] --------
FROM node:18 AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# -------- final runtime image (PHP-FPM + Nginx) --------
FROM php:8.2-fpm-alpine

# PHP extensions you typically need for Laravel
RUN apk add --no-cache nginx supervisor icu-dev oniguruma-dev libzip-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip intl opcache

WORKDIR /var/www/html

# Bring in app code & built assets
COPY --from=vendor /app /var/www/html
COPY --from=frontend /app/public/build /var/www/html/public/build

# Permissions for storage and cache
RUN chown -R www-data:www-data storage bootstrap/cache

# Nginx & Supervisor configs
COPY .render/nginx.conf /etc/nginx/nginx.conf
COPY .render/supervisord.conf /etc/supervisord.conf

# Render expects your app to listen on port 10000
EXPOSE 10000

# Start both php-fpm and nginx
CMD ["/usr/bin/supervisord","-c","/etc/supervisord.conf"]
