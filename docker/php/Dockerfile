FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    curl \
    unzip \
    libpq-dev \
    librabbitmq-dev \
    && docker-php-ext-install pdo pdo_pgsql sockets \
    && pecl install amqp redis \
    && docker-php-ext-enable amqp redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install sockets
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www