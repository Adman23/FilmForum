FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install mysqli \
    && a2enmod rewrite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY backend/ /var/www/html/
COPY frontend/ /var/www/html/assets/

WORKDIR /var/www/html
RUN composer install --no-dev --no-interaction --prefer-dist \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["sh", "-c", "php /var/www/html/setup-admin.php && apache2-foreground"]
