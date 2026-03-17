FROM richarvey/php-apache-heroku:latest

# Salin file project
COPY . /var/www/html

# Pengaturan folder kerja
WORKDIR /var/www/html

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Izinkan akses storage
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port
EXPOSE 80