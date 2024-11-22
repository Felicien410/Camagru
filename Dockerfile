# Utiliser l'image officielle PHP avec Apache
FROM php:8.2-apache

# Activer les modules Apache nécessaires
RUN a2enmod rewrite

# Installer les dépendances nécessaires
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    msmtp \
    msmtp-mta \
    iputils-ping \
    && rm -rf /var/lib/apt/lists/*

# Configurer msmtp
COPY ./backend/msmtprc /etc/msmtprc
RUN chmod 0644 /etc/msmtprc

# Configurer les extensions PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install pdo pdo_mysql gd

# Configurer sendmail
RUN echo "sendmail_path = /usr/bin/msmtp -t" >> /usr/local/etc/php/conf.d/php-sendmail.ini

# Copier le fichier php.ini personnalisé
COPY ./backend/php.ini /usr/local/etc/php/

# Définir le répertoire de travail
WORKDIR /var/www/html

# Configurer Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Copier les fichiers de l'application
COPY ./backend /var/www/html

# Définir les permissions appropriées
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/public/uploads

# S'assurer que le dossier uploads existe et a les bonnes permissions
RUN mkdir -p /var/www/html/public/uploads \
    && chown -R www-data:www-data /var/www/html/public/uploads \
    && chmod -R 777 /var/www/html/public/uploads

# Exposer le port 80
EXPOSE 80

# Lancer Apache en mode premier plan
CMD ["apache2-foreground"]