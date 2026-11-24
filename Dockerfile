FROM php:8.2-apache

# Enable Apache modules needed for routing, compression, and headers
RUN a2enmod rewrite headers deflate expires

# Install ca-certificates, Composer dependencies tools, and PHP extensions
RUN apt-get update && apt-get install -y ca-certificates unzip libpng-dev && rm -rf /var/lib/apt/lists/* && \
    docker-php-ext-install pdo pdo_mysql gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configure Apache: enable .htaccess overrides and bind to dynamic $PORT
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf && \
    sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Copy application files
COPY . /var/www/html/

# Install PHP dependencies (Dompdf)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Setup storage directory permissions
RUN mkdir -p /var/www/html/storage/logs /var/www/html/storage/requirements /var/www/html/storage/clearances && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 775 /var/www/html/storage

ENV PORT=80
EXPOSE 80

CMD ["sh", "-c", "php database/migrate_phase_a.php && php database/migrate_phase_b.php && apache2-foreground"]
