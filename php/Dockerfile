FROM php:8.3-fpm-bookworm

RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo_mysql zip opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html