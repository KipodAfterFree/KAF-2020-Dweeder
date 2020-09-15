# Select the base image
FROM php:7.4.1-apache
# Enable modifications
RUN a2enmod headers
# Change ownership of /opt
RUN chown www-data:www-data -R /opt && chmod 775 -R /opt
# Copy frontend & backend to /var/www/html
COPY frontend /var/www/html
COPY backend /var/www/html/apis