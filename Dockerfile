# Dockerfile for Laravel Application
FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    supervisor \
    cron \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd \
    && pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Create healthcheck script for PHP-FPM
RUN echo '#!/bin/bash' > /usr/local/bin/php-fpm-healthcheck \
    && echo 'SCRIPT_NAME=/ping' >> /usr/local/bin/php-fpm-healthcheck \
    && echo 'SCRIPT_FILENAME=/ping' >> /usr/local/bin/php-fpm-healthcheck \
    && echo 'REQUEST_METHOD=GET' >> /usr/local/bin/php-fpm-healthcheck \
    && echo 'cgi-fcgi -s /var/run/php-fpm.sock -b /dev/null 2>/dev/null && exit 0 || exit 1' >> /usr/local/bin/php-fpm-healthcheck \
    && chmod +x /usr/local/bin/php-fpm-healthcheck

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --optimize-autoloader --no-dev

# Install Node.js and build assets
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install \
    && npm run build

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Setup cron for scheduler
RUN echo "* * * * * cd /var/www && php artisan schedule:run >> /dev/null 2>&1" > /etc/cron.d/esppd-scheduler \
    && chmod 0644 /etc/cron.d/esppd-scheduler \
    && crontab /etc/cron.d/esppd-scheduler

# Expose port
EXPOSE 9000

# Healthcheck
HEALTHCHECK --interval=30s --timeout=10s --retries=3 \
    CMD php-fpm-healthcheck || exit 1

CMD ["php-fpm"]
