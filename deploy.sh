#!/bin/bash

set -e


echo "🚀 Starting deployment"


echo "⬇️ Pull latest code"

git pull

echo "🐳 Building containers"

sudo docker compose up -d

echo "📦 Installing dependencies"

docker compose exec -T "app" composer install \
--no-dev \
--optimize-autoloader

echo "🛠 Running migrations"

sudo docker compose exec -T "app" php artisan migrate --force

echo "👷 Restarting queues"

docker compose exec -T "queue" php artisan queue:restart
