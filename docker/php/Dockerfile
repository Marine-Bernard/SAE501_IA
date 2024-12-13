FROM php:8.3.12-fpm

# Installation de l'extension pecl pcov
RUN pecl install pcov \
    && docker-php-ext-enable pcov

RUN [ -f /etc/apt/sources.list ] || echo "deb http://deb.debian.org/debian bullseye main" > /etc/apt/sources.list
RUN sed -i 's|http://deb.debian.org|https://deb.debian.org|' /etc/apt/sources.list    
RUN sed -i 's|http://deb.debian.org|https://deb.debian.org|' /etc/apt/sources.list
# Mise à jour et installation des dépendances nécessaires pour GD
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd

# Nettoyage pour garder l'image légère
RUN apt-get clean && rm -rf /var/lib/apt/lists/

EXPOSE 9000
