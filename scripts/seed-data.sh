#!/bin/bash

# Script para crear datos de prueba en UrbanSole

# Crear categorías
php bin/console doctrine:query:sql "INSERT INTO categoria (nombre, slug) VALUES 
('Running', 'running'),
('Basketball', 'basketball'),
('Casual', 'casual'),
('Lifestyle', 'lifestyle');"

echo "✅ Categorías creadas"

# Ver IDs de categorías
php bin/console doctrine:query:sql "SELECT * FROM categoria LIMIT 5;"
