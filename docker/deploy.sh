#!/bin/bash
set -e

echo "=== HR Robot - Deploy ==="

cd /opt/hr-servis

# Check .env
if [ ! -f .env ]; then
    echo "ERROR: .env file not found!"
    echo "Copy .env.example or create .env with proper settings."
    exit 1
fi

# Build & start containers
echo "Building containers..."
docker compose build

echo "Starting services..."
docker compose up -d

# Wait for DB
echo "Waiting for database..."
sleep 10

# Install composer dependencies
echo "Installing dependencies..."
docker compose exec app composer install --no-dev --optimize-autoloader

# Run migrations
echo "Running migrations..."
docker compose exec app php artisan migrate --force

# Generate key if not set
echo "Checking APP_KEY..."
docker compose exec app php artisan key:generate --force

# Cache config
echo "Caching config..."
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

# Storage link
docker compose exec app php artisan storage:link 2>/dev/null || true

# Permissions
echo "Setting permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache

echo ""
echo "=== Deploy complete ==="
echo "App: http://204.168.217.54"
echo ""
echo "Check status:  docker compose ps"
echo "Check logs:    docker compose logs -f"
echo "Create admin:  docker compose exec app php artisan tinker"
