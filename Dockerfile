FROM php:8.4-apache

# Installation des dépendances nécessaires
RUN apt-get update && apt-get install -y \
    libssl-dev \
    git \
    unzip \
    libzip-dev \
    zlib1g-dev

# Installation des extensions PHP requises
RUN docker-php-ext-install mysqli pdo pdo_mysql zip

# Installation de l'extension MongoDB
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Installation de Composer pour la gestion des dépendances
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuration d'Apache et copie des fichiers du projet
COPY . /var/www/html/
WORKDIR /var/www/html
RUN a2enmod rewrite

# Exposition du port 80 pour accéder à l'application
EXPOSE 80
