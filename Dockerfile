# Use a standard, official PHP image with Apache pre-installed
FROM php:8.2-apache

# Install PostgreSQL client libraries (required to talk to your database)
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Enable Apache mod_rewrite (great for PHP routing and clean URLs)
RUN a2ennd rewrite || true

# Copy all your local files into the web server directory
COPY . /var/www/html/

# Expose port 80 so Render can direct traffic to your app
EXPOSE 80