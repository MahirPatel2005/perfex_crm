FROM php:8.2-apache

# Install required system libraries and PHP extensions for Perfex CRM
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    libcurl4-openssl-dev \
    libonig-dev \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mysqli pdo pdo_mysql zip intl curl mbstring

# Enable Apache mod_rewrite (required for CodeIgniter routes)
RUN a2enmod rewrite

# Set Apache root folder settings
RUN sed -ri -e 's!/var/www/html!/var/www/html!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!/var/www/!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copy project files into container
COPY . /var/www/html/

# Adjust directory permissions so Apache can write to uploads/temp
RUN chown -R www-data:www-data /var/www/html/ \
    && chmod -R 755 /var/www/html/

# Expose port 80
EXPOSE 80
