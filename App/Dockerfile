FROM php:8.1-fpm

RUN apt-get update && apt-get install -y \
    git curl zip unzip libpq-dev libzip-dev libpng-dev libonig-dev nodejs npm ca-certificates \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql mbstring zip gd

WORKDIR /var/www/html

# Copy PHP and Node dependency manifests first for better caching
COPY composer.json composer.lock /var/www/html/
COPY package.json package-lock.json /var/www/html/

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install PHP dependencies (no dev for production)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist || true

# Install node deps and build assets (resources folder contains JS/CSS)
RUN npm ci --prefix resources || true
RUN npm run build --prefix resources || true

# Copy the rest of the application
COPY . /var/www/html

# Ensure storage and cache directories are writable
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true
RUN chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache || true

ENV PORT=8080
EXPOSE 8080

CMD ["php-fpm", "-F", "-R"]
