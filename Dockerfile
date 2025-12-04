<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html

    # CRÍTICO: Indica a Apache que use public_menu.php como página de inicio
    DirectoryIndex public_menu.php index.php

    <Directory /var/www/html>
        # CRÍTICO: Permite que los archivos .htaccess funcionen
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
