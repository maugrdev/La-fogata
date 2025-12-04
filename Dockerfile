# Usa una imagen base de PHP con Apache
FROM php:8.2-apache

# Instala las dependencias y las extensiones de PostgreSQL (libpq-dev y pdo_pgsql)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Habilita el módulo de reescritura de Apache, útil para URLs
RUN a2enmod rewrite

# Copia todos los archivos de tu proyecto (incluyendo .php y carpetas) al directorio web de Apache
COPY . /var/www/html/

# Establece los permisos correctos (es una buena práctica de seguridad)
RUN chown -R www-data:www-data /var/www/html

# La imagen ya tiene el comando de inicio (apache2-foreground) preconfigurado.
