# Usa una imagen base de PHP con Apache
FROM php:8.2-apache

# 1. Instala el driver de PostgreSQL (si usas la DB de Render)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# 2. Habilita el módulo de reescritura de Apache
RUN a2enmod rewrite

# 3. CRÍTICO: Copia el archivo de configuración de Apache
#    Este archivo debe existir en tu repositorio.
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# 4. Copia todos los archivos de tu proyecto al directorio web de Apache
COPY . /var/www/html/

# 5. Establece los permisos correctos
RUN chown -R www-data:www-data /var/www/html
