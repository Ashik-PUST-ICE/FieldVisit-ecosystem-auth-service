FROM php:8.2-fpm-alpine

RUN apk add --no-cache bash git unzip mariadb-client icu-dev libzip-dev oniguruma-dev \
    && docker-php-ext-install pdo pdo_mysql intl zip mbstring \
    && pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/auth-service
COPY . .

RUN composer install --no-dev --optimize-autoloader \
    && php artisan optimize

CMD ["php-fpm"]
