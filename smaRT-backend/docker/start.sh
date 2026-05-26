#!/bin/sh
set -e

echo "🚀 Starting smaRT Backend..."

# Default to port 8000 if PORT not set (Koyeb sets PORT automatically)
export PORT="${PORT:-8000}"

# Generate Nginx config from template with the correct port
envsubst '${PORT}' < /etc/nginx/http.d/default.conf.template > /etc/nginx/http.d/default.conf

echo "📡 Listening on port $PORT"

# Cache config, routes, and views for production performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations (--force required in production)
php artisan migrate --force

echo "✅ Laravel ready. Starting services..."

# Start Supervisor (manages PHP-FPM + Nginx)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
