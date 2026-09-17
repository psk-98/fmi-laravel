#!/bin/bash

set -e


echo "🚀 Starting deployment"

echo "⬇️ Go to project folder"

cd "$BACKEND_DIR"

echo "⬇️ Pull latest code"

git pull


echo "🐳 Building containers"

sudo docker compose up -d


echo "📦 Installing dependencies"

docker compose exec -T "app" composer install \
--no-dev \
--optimize-autoloader \
&& bun install --frozen-lockfile


echo "🛠 Running migrations"

sudo docker compose exec -T "app" php artisan migrate --force


echo "Build JS"

sudo docker compose  exec -T "app" bun run build


echo "🧹 Clearing Laravel cache"

sudo docker compose  exec -T "app" php artisan optimize:clear


echo "⚡ Rebuilding Laravel cache"

sudo docker compose exec -T "app" php artisan optimize


echo "👷 Restarting queues"

docker compose exec -T "queue" php artisan queue:restart
