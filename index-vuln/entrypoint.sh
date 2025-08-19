#!/bin/bash
cd /var/www/html

# Install Twig only if vendor folder doesn't exist
if [ ! -d "vendor" ]; then
    echo "[*] Installing Twig with Composer..."
    composer require twig/twig
fi

# Start Apache
exec apache2-foreground
