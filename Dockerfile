# Stage 1: Build Assets
FROM node:20-alpine AS assets-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: Production
FROM dunglas/frankenphp:php8.4

ENV SERVER_NAME=":80"
WORKDIR /app

# Install system deps & PHP extensions
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip unzip git \
    python3 python3-venv python3-pip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install intl gd zip pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy source
COPY . /app
# Copy compiled assets from Stage 1
COPY --from=assets-builder /app/public/build /app/public/build

# Install composer binary
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# INSTALL DEPENDENCIES
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-scripts

# SETUP PYTHON VENV FOR SEP-BP (PDF Extraction)
RUN mkdir -p /app/app/Scripts \
    && python3 -m venv /app/app/Scripts/venv \
    && /app/app/Scripts/venv/bin/pip install pdfplumber

# Copy Caddyfile
COPY Caddyfile /etc/caddy/Caddyfile

# Post-install: generate Laravel caches & Filament assets
RUN mkdir -p storage/framework/{sessions,views,cache} bootstrap/cache \
    && php artisan package:discover --ansi \
    && php artisan filament:upgrade \
    && php artisan storage:link || true \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

