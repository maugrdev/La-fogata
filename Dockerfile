# Usa una imagen base de PHP con Apache
FROM php:8.2-apache

# 1. Instala el driver de PostgreSQL (si usas la DB de Render)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# 2. Habilita el módulo de reescritura de Apache
RUN a2enmod rewrite

# 3. Copia todos los archivos de tu proyecto al directorio web de Apache
COPY . /var/www/html/

# 4. CRÍTICO: Asegura que el usuario www-data sea el dueño
RUN chown -R www-data:www-data /var/www/html

# 5. CRÍTICO: Forzar permisos de lectura/escritura/ejecución para la carpeta.
#    Esto es a veces necesario para que Apache pueda acceder.
RUN chmod -R 755 /var/www/html

# Asegura que el puerto 80 esté expuesto (aunque la imagen base ya lo hace)
EXPOSE 80
