FROM php:8.4-apache

# Installation des dépendances nécessaires
RUN apt-get update && apt-get install -y \
    libssl-dev \
    git \
    unzip \
    libzip-dev \
    zlib1g-dev \
    curl \
    libicu-dev \
    libonig-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Installation des extensions PHP requises
RUN docker-php-ext-install \
    mysqli \
    pdo \
    pdo_mysql \
    zip \
    intl \
    mbstring \
    opcache

# Installation de l'extension MongoDB
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Optimisation de PHP pour la production
RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini
RUN sed -i 's/memory_limit = 128M/memory_limit = 256M/g' /usr/local/etc/php/php.ini

# Installation de Composer pour la gestion des dépendances
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuration d'Apache
RUN echo '<VirtualHost *:80>\n\
    ServerAdmin webmaster@localhost\n\
    DocumentRoot /var/www/html/public\n\
    \n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    \n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

RUN a2enmod rewrite headers

# Copie des fichiers du projet
COPY . /var/www/html/
WORKDIR /var/www/html

# Ajout pour résoudre les problèmes de Git et MongoDB
RUN git config --global --add safe.directory /var/www/html
RUN composer install --no-dev --optimize-autoloader --ignore-platform-req=ext-mongodb


# Fixer les permissions
RUN chown -R www-data:www-data /var/www/html

# Exposition du port 80 pour accéder à l'application
EXPOSE 80

# Commande de démarrage
CMD ["apache2-foreground"]
