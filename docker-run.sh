#!/bin/sh

echo "-------------------------------------"
echo "  STARTING DEPLOYMENT SCRIPT"
echo "-------------------------------------"

echo "1. Running Migrations..."
php artisan migrate:fresh --force -vvv

echo "2. Running Seeding..."
php artisan db:seed --force -vvv

echo "-------------------------------------"
echo "  SETUP COMPLETE - STARTING APACHE"
echo "-------------------------------------"

exec apache2-foreground
