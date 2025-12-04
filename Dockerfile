# Usa una imagen base de PHP con Apache
FROM php:8.2-apache

# 1. Instala el driver de PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# 2. Habilita el módulo de reescritura
RUN a2enmod rewrite

# 3. Copia todos los archivos de tu proyecto al directorio web de Apache
COPY . /var/www/html/

# 4. CRÍTICO: Asegura que el usuario www-data sea el dueño
RUN chown -R www-data:www-data /var/www/html

# 5. CRÍTICO: Forzar permisos de lectura/escritura/ejecución (más agresivo)
RUN chmod -R 775 /var/www/html

# 6. Establece el directorio de trabajo para la ejecución
WORKDIR /var/www/html

# 7. Ejecuta el servidor Apache como el comando principal
CMD ["apache2-foreground"]
