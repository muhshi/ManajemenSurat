FROM dunglas/frankenphp:php8.3

ENV SERVER_NAME=":80"
WORKDIR /app

# Install system deps & PHP extensions (WAJIB sebelum composer)
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip unzip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install intl gd zip pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy source
COPY . /app

# Install composer binary
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# INSTALL DEPENDENCIES (KUNCI)
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-scripts

# Copy Caddyfile
COPY Caddyfile /etc/caddy/Caddyfile

RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80
