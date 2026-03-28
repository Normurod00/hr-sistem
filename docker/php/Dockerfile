FROM php:8.4-fpm-alpine

# System dependencies
RUN apk add --no-cache \
    git curl zip unzip libzip-dev libpng-dev libjpeg-turbo-dev freetype-dev \
    icu-dev oniguruma-dev postgresql-dev supervisor

# Redis extension
RUN apk add --no-cache --virtual .redis-deps autoconf gcc g++ make \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .redis-deps

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo pdo_pgsql pdo_mysql \
        mbstring zip gd intl opcache bcmath pcntl

# OPcache config for production
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.interned_strings_buffer=16" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini

# PHP config
RUN echo "upload_max_filesize=20M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size=25M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit=256M" >> /usr/local/etc/php/conf.d/uploads.ini

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Supervisor config for queue worker
COPY docker/php/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

WORKDIR /opt/hr-servis

EXPOSE 9000
CMD ["php-fpm"]
