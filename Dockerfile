# 1. Use the official PHP with Apache image
FROM php:8.2-apache

# 2. Install dependencies for Laravel and PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql

# 3. Install Composer (PHP Package Manager)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Set the working directory
WORKDIR /var/www/html

# 5. Copy your application code
COPY . .

# 6. Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# 7. Fix permissions for Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 8. Configure Apache to point to /public (Critical for Laravel)
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 9. Enable Apache Mod Rewrite (For Laravel Routes)
RUN a2enmod rewrite

# 10. Expose Port 80
EXPOSE 80
