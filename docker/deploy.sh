#!/bin/bash
set -e

echo "=== HR Robot - Docker Deploy ==="

# Check .env
if [ ! -f .env ]; then
    echo "Creating .env from .env.docker..."
    cp .env.docker .env
    echo "!! IMPORTANT: Edit .env and set APP_KEY, DB_PASSWORD, etc."
    echo "   Run: php artisan key:generate --show"
    exit 1
fi

# Build & start
echo "Building containers..."
docker compose build

echo "Starting services..."
docker compose up -d

# Wait for DB
echo "Waiting for database..."
sleep 5

# Run migrations
echo "Running migrations..."
docker compose exec app php artisan migrate --force

# Cache config
echo "Caching config..."
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

# Storage link
docker compose exec app php artisan storage:link 2>/dev/null || true

echo ""
echo "=== Deploy complete ==="
echo "App:    http://localhost:${APP_PORT:-80}"
echo "AI:     http://localhost:${AI_PORT:-8095}/health"
echo ""
echo "Create admin: docker compose exec app php artisan tinker"
echo "Check logs:   docker compose logs -f"
