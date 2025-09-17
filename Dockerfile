# Use PHP with FPM
FROM php:8.2-fpm

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git \
    && docker-php-ext-install zip pdo pdo_mysql

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install PHP dependencies without running scripts
RUN composer install --optimize-autoloader --no-scripts --no-interaction

# Expose port 8000 for Laravel
EXPOSE 8000

# Start Laravel using artisan serve
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
