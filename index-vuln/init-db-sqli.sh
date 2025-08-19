#!/bin/bash

DB_PATH="/var/www/html/data.sqlite"

if [ ! -f "$DB_PATH" ]; then
    echo "[+] Creating SQLite DB at $DB_PATH..."
    touch "$DB_PATH"
    chown www-data:www-data "$DB_PATH"
    chmod 666 "$DB_PATH"

    # Inisialisasi tabel di sini
    sudo -u www-data php -r '
        $db = new PDO("sqlite:/var/www/html/data.sqlite");
        $db->exec("CREATE TABLE IF NOT EXISTS products (id INTEGER PRIMARY KEY, name TEXT, price TEXT)");
        $db->exec("INSERT INTO products (id, name, price) VALUES (1, \"Laptop\", \"1500\"), (2, \"Phone\", \"800\")");
    '
fi

# Start Apache
apache2-foreground
