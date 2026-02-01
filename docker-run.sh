#!/bin/sh

# 1. Reset Database and Seed (Fixes your 500 error)
echo "Migrating database..."
php artisan migrate:fresh --seed --force

# 2. Start Apache Server
echo "Starting server..."
exec apache2-foreground
