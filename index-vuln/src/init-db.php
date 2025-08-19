<?php
$db = new PDO('sqlite:/var/www/html/data.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$db->exec("CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY,
    name TEXT,
    price TEXT
)");

$db->exec("INSERT INTO products (id, name, price) VALUES
(1, 'Laptop', '1500'),
(2, 'Phone', '800'),
(3, 'Tablet', '400')");

echo "Database initialized.";
?>
