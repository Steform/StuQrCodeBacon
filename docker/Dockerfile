# Use Apache and php8.2
FROM php:8.2-apache

# Install Imagick
RUN apt-get update && \
    apt-get install -y libmagickwand-dev && \
    pecl install imagick && \
    docker-php-ext-enable imagick && \
    docker-php-ext-install gd

# Define the working directory
WORKDIR /var/www/html

# Start needed Apache modules
RUN a2enmod rewrite

# Restart Apache to apply change
RUN service apache2 restart

# Expose the 80 port of container
EXPOSE 80

# Commande par défaut pour exécuter le conteneur
CMD ["apache2-foreground"]

# Set permissions for the /var/www/html directory
RUN chown -R www-data:www-data /var/www/html && chmod -R 777 /var/www/html