FROM php:8.4-apache

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    curl \
    ca-certificates \
    gnupg \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installer Node.js 20.x
RUN mkdir -p /etc/apt/keyrings \
    && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_20.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list \
    && apt-get update \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installer les extensions PHP nécessaires pour Symfony
RUN docker-php-ext-install -j$(nproc) \
        intl \
        zip \
        opcache

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Activer les modules Apache nécessaires
RUN a2enmod rewrite headers

# Copier la configuration Apache personnalisée
COPY docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf

# Configuration PHP pour le développement
RUN echo "memory_limit = 512M" >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini

# Définir le répertoire de travail
WORKDIR /var/www/html

# Permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80