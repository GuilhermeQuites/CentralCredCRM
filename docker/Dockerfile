FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    ca-certificates \
    curl \
    git \
    gnupg \
    libonig-dev \
    libpng-dev \
    libxml2-dev \
    unzip \
    zip \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && docker-php-ext-install bcmath exif gd mbstring pcntl pdo_mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy dependency manifests first for better layer caching
COPY package.json package-lock.json ./

# Install Node dependencies and build frontend assets
RUN npm ci

COPY . .

# Build Vite assets (outputs to public/build/)
RUN npm run build

# Install PHP dependencies (no dev, optimised autoloader)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Fix storage and cache permissions
RUN chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache
