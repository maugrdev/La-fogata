# Usa una imagen base de PHP con Apache
FROM php:8.2-apache

# Instala las extensiones necesarias (PostgreSQL para Render DB)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Habilita el módulo de reescritura de Apache, útil para URLs amigables
RUN a2enmod rewrite

# Copia todos los archivos de tu proyecto (incluyendo .php y las imágenes) al directorio web de Apache
COPY . /var/www/html/

# Establece los permisos correctos
RUN chown -R www-data:www-data /var/www/html

# El servidor Apache ya está configurado para ejecutarse en primer plano, no necesitas otro Start Command.
