FROM php:8.3-apache

# Install dependencies sistem
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl \
    libsqlite3-dev \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite gd

# Aktifkan mod_rewrite Apache dan atasi konflik MPM Apache
RUN a2enmod rewrite \
    && a2dismod mpm_event mpm_worker \
    && a2enmod mpm_prefork

# Konfigurasi Document Root Apache ke folder public/
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Salin seluruh file project
COPY . .

# Install dependencies PHP (Production)
RUN composer install --no-dev --optimize-autoloader

# Install dependencies Node.js dan Build Asset (Vite/Tailwind) secara otomatis di server
RUN npm install && npm run build

# Beri hak akses (permissions) untuk storage dan cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80