# Utilise l'image officielle de PHP 8.2 avec Apache
FROM php:8.2-apache

# Définir le répertoire de travail
WORKDIR /var/www/html
# Copier les fichiers du projet dans le conteneur
COPY . .
# Activer mod_rewrite et appliquer la configuration
RUN a2enmod rewrite && service apache2 restart


COPY neptune-vhost.conf /etc/apache2/sites-available/neptune-vhost.conf
RUN a2ensite neptune-vhost.conf && a2dissite 000-default && service apache2 restart

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
    && docker-php-ext-configure zip \
    && docker-php-ext-install pdo pdo_mysql zip gd mbstring opcache \
    && docker-php-ext-enable opcache

# Installation de Composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

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

# Commande par défaut
CMD ["apache2-foreground"]
