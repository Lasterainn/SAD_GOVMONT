FROM php:8.3-fpm
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*
COPY docker/php.ini /usr/local/etc/php/conf.d/security.ini
WORKDIR /var/www/html
COPY . .
RUN chown -R www-data:www-data /var/www/html
USER www-data