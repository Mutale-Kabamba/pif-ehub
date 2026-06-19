#!/bin/bash
# PIF E-Hub Laravel Application Setup Script
# Run this after cloning the repository

set -e

echo "========================================="
echo "  PIF E-Hub Laravel Setup"
echo "========================================="
echo ""

# Step 1: Ensure bootstrap/cache directory exists
echo "[1/7] Creating bootstrap/cache directory..."
mkdir -p bootstrap/cache
echo "*" > bootstrap/cache/.gitignore
echo "!.gitignore" >> bootstrap/cache/.gitignore

# Step 2: Ensure storage directories exist
echo "[2/7] Creating storage directories..."
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/testing
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p storage/app/public

# Step 3: Copy .env if it doesn't exist
echo "[3/7] Setting up environment file..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo "      Created .env from .env.example"
fi

# Step 4: Ensure SQLite database file exists
echo "[4/7] Creating SQLite database..."
if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
    echo "      Created database/database.sqlite"
fi

# Step 5: Install dependencies WITHOUT running scripts first
echo "[5/7] Installing composer dependencies (no scripts)..."
composer install --no-dev --optimize-autoloader --no-scripts

# Step 6: Generate application key
echo "[6/7] Generating application key..."
php artisan key:generate --ansi

# Step 7: Run post-autoload scripts now that everything is in place
echo "[7/7] Running post-install scripts..."
composer dump-autoload --optimize

echo ""
echo "========================================="
echo "  Setup Complete!"
echo "========================================="
echo ""
echo "Next steps:"
echo "  1. Run migrations and seeders:"
echo "     php artisan migrate --force"
echo "     php artisan db:seed --force"
echo ""
echo "  2. Start the development server:"
echo "     php artisan serve"
echo ""
echo "  3. Visit http://localhost:8000 for the survey portal"
echo "  4. Visit http://localhost:8000/admin/login for the admin dashboard"
echo ""
