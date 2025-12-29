# =====================
# Stage 1: Composer
# =====================
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-scripts

# =====================
# Stage 2: Runtime
# =====================
FROM dunglas/frankenphp:php8.3

ENV SERVER_NAME=":80"
WORKDIR /app

RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install intl gd zip pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy source code
COPY . /app

# Copy vendor from composer stage
COPY --from=vendor /app/vendor /app/vendor

# Copy Caddyfile
COPY Caddyfile /etc/caddy/Caddyfile

RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80
