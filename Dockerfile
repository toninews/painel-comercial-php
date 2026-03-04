FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --ignore-platform-req=php

FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libzip-dev libpq-dev libsqlite3-dev pkg-config unzip \
    && docker-php-ext-install pdo pdo_sqlite pdo_pgsql \
    && a2enmod rewrite headers expires \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY . /var/www/html
COPY --from=vendor /app/vendor /var/www/html/vendor
COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh \
    && mkdir -p /var/www/html/tmp/sessions /var/www/html/tmp/rate_limits /var/www/html/App/Database \
    && chown -R www-data:www-data /var/www/html/tmp /var/www/html/App/Database \
    && chmod -R 775 /var/www/html/tmp /var/www/html/App/Database

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]
