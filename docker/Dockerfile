FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Create log directory for PHP
RUN mkdir -p /var/log/php

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

# Install dependencies
RUN composer install
RUN npm install
RUN chown -R $(whoami):$(whoami) .

# Run migrations and seed data
#RUN php database/migrate.php
#RUN php database/seed.php

# Expose port 9000 for PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]