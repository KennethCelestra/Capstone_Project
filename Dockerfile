FROM php:8.2-apache

# Enable Apache modules needed for routing, compression, and headers
RUN a2enmod rewrite headers deflate expires

# Install ca-certificates and PDO MySQL extension
RUN apt-get update && apt-get install -y ca-certificates && rm -rf /var/lib/apt/lists/* && \
    docker-php-ext-install pdo pdo_mysql

# Configure Apache to bind to the dynamic $PORT provided by Render (default 80)
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Copy application files
COPY . /var/www/html/

# Setup storage directory permissions
RUN mkdir -p /var/www/html/storage/logs && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 775 /var/www/html/storage

ENV PORT=80
EXPOSE 80

CMD ["apache2-foreground"]
