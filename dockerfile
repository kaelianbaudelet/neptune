# Utilise l'image officielle de PHP 8.2 avec Apache
FROM php:8.2-apache

# Définir le nom du serveur globalement pour éviter l'avertissement AH00558
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Définir le répertoire de travail
WORKDIR /var/www/html

# Mise à jour des packages système et installation des dépendances
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    cron \
    netcat-openbsd \
    && docker-php-ext-configure zip \
    && docker-php-ext-install pdo pdo_mysql zip gd mbstring opcache \
    && docker-php-ext-enable opcache

# Installation de Composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# Copier uniquement les fichiers composer pour installer les dépendances (optimisation du cache)
COPY composer.json composer.lock* ./

# Installer les dépendances (sans les scripts pour l'instant)
RUN composer install --no-dev --no-scripts --no-autoloader

# Copier le reste des fichiers du projet
COPY . .

# Terminer l'installation de composer (générer l'autoloader optimisé)
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

# Activer mod_rewrite et appliquer la configuration
RUN a2enmod rewrite

COPY neptune-vhost.conf /etc/apache2/sites-available/neptune-vhost.conf
RUN a2ensite neptune-vhost.conf && a2dissite 000-default

# Configuration d'OPcache pour de meilleures performances en production
RUN echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.interned_strings_buffer=8" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.max_accelerated_files=4000" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.revalidate_freq=2" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.enable_cli=1" >> /usr/local/etc/php/conf.d/opcache.ini

# Donner les permissions au dossier
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

RUN echo "date.timezone=Europe/Paris" > /usr/local/etc/php/conf.d/timezone.ini

# Exposer le port 80
EXPOSE 80

# Script d'entrée
RUN chmod +x docker-entrypoint.sh

# Commande par défaut
CMD ["./docker-entrypoint.sh"]
