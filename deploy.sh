#!/usr/bin/env bash
set -e

echo "=== INICIANDO DESPLIEGUE EN PRODUCCIÓN sys-POS ==="

# 1. Poner aplicación en modo mantenimiento con mensaje amigable
php artisan down --message="Actualizando el sistema a una nueva versión. Volvemos en 1 minuto." --retry=60 || true

# 2. Descargar últimos cambios de Git
git pull origin main

# 3. Instalar dependencias optimizadas de composer sin paquetes dev
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# 4. Ejecutar migraciones de base de datos seguras
php artisan migrate --force

# 5. Optimización y Cache de Configuración, Rutas y Vistas
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 6. Reiniciar workers de cola supervisor
php artisan queue:restart

# 7. Salir de modo mantenimiento
php artisan up

echo "=== DESPLIEGUE COMPLETADO CON ÉXITO ==="
