FROM php:8.2-apache

# Install ekstensi yang dibutuhkan Laravel & Filament
RUN apt-get update && apt-get install -y \
    libpng-dev libonig-dev libxml2-dev zip curl unzip libpq-dev \
    && docker-php-ext-install pdo_pgsql pgsql gd mbstring intl bcmath

# Aktifkan mod_rewrite Apache
RUN a2enmod rewrite

# Set Working Directory
WORKDIR /var/www/html
COPY . .

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Pengaturan folder public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Optimasi Laravel
RUN php artisan optimize