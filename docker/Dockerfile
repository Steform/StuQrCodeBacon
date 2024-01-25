# Use Apache and php8.2
FROM php:8.2-apache

# Install Imagick
RUN apt-get update && \
    apt-get install -y libmagickwand-dev && \
    pecl install imagick && \
    docker-php-ext-enable imagick && \
    docker-php-ext-install gd
    
RUN docker-php-ext-install exif

# Define the working directory
WORKDIR /var/www/html

# Start needed Apache modules
RUN a2enmod rewrite

# Restart Apache to apply change
RUN service apache2 restart

# Expose the 80 port of container
EXPOSE 80

# Starting apache
CMD ["apache2-foreground"]