FROM node:16-alpine as node
FROM php:7.4-fpm-alpine

RUN apk upgrade --update \
    && apk --no-cache add icu-dev autoconf make g++ gcc curl libzip-dev

RUN docker-php-ext-configure opcache --enable-opcache \
    && docker-php-ext-install pdo_mysql zip opcache sockets

# composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# conf
COPY ./docker/php-fpm/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY ./docker/php-fpm/conf.d/customize.ini /usr/local/etc/php/conf.d/customize.ini

# Composer install
COPY composer.* /var/www/html/
RUN composer install --working-dir=/var/www/html --no-scripts --no-autoloader

# app
COPY . /var/www/html
WORKDIR /var/www/html

RUN composer dump-autoload --working-dir=/var/www/html --no-scripts

RUN set -eux \
    && chmod -R a=rwX storage/ bootstrap/cache \
    && cp .env.example .env \
    && php artisan config:clear \
    && php artisan route:cache \
    && php artisan view:cache
EXPOSE 9000
# node command
COPY --from=node /usr/local/bin /usr/local/bin
# npm command
COPY --from=node /usr/local/lib /usr/local/lib
# yarn command
COPY --from=node /opt /opt
