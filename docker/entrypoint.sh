#!/bin/sh
set -e

echo "=========================================="
echo "  automateCRM — Container Entrypoint"
echo "=========================================="

cd /var/www/html

# ---- Step 1: Generate .env if missing ----
if [ ! -f .env ]; then
    echo "[entrypoint] No .env found — creating from .env.example..."
    cp .env.example .env
    chown www-data:www-data .env
fi

# ---- Step 2: Set APP_KEY ----
# If APP_KEY env var is provided (from docker-compose), inject it into .env
if [ -n "$APP_KEY" ]; then
    echo "[entrypoint] Using APP_KEY from environment variable..."
    sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" .env
else
    # Check if .env already has a key
    EXISTING_KEY=$(grep "^APP_KEY=" .env | cut -d '=' -f2-)
    if [ -z "$EXISTING_KEY" ]; then
        echo "[entrypoint] Generating new APP_KEY..."
        php artisan key:generate --force
    else
        echo "[entrypoint] APP_KEY already set in .env"
    fi
fi

# ---- Step 3: Apply docker-compose env vars to .env ----
# Override key settings from docker-compose environment
[ -n "$APP_ENV" ]          && sed -i "s|^APP_ENV=.*|APP_ENV=${APP_ENV}|" .env
[ -n "$APP_DEBUG" ]        && sed -i "s|^APP_DEBUG=.*|APP_DEBUG=${APP_DEBUG}|" .env
[ -n "$APP_URL" ]          && sed -i "s|^APP_URL=.*|APP_URL=${APP_URL}|" .env
[ -n "$DB_CONNECTION" ]    && sed -i "s|^DB_CONNECTION=.*|DB_CONNECTION=${DB_CONNECTION}|" .env
[ -n "$DB_HOST" ]          && sed -i "s|^DB_HOST=.*|DB_HOST=${DB_HOST}|" .env
[ -n "$DB_PORT" ]          && sed -i "s|^DB_PORT=.*|DB_PORT=${DB_PORT}|" .env
[ -n "$DB_DATABASE" ]      && sed -i "s|^DB_DATABASE=.*|DB_DATABASE=${DB_DATABASE}|" .env
[ -n "$DB_USERNAME" ]      && sed -i "s|^DB_USERNAME=.*|DB_USERNAME=${DB_USERNAME}|" .env
[ -n "$DB_PASSWORD" ]      && sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|" .env
[ -n "$CACHE_DRIVER" ]     && sed -i "s|^CACHE_DRIVER=.*|CACHE_DRIVER=${CACHE_DRIVER}|" .env
[ -n "$SESSION_DRIVER" ]   && sed -i "s|^SESSION_DRIVER=.*|SESSION_DRIVER=${SESSION_DRIVER}|" .env
[ -n "$QUEUE_CONNECTION" ] && sed -i "s|^QUEUE_CONNECTION=.*|QUEUE_CONNECTION=${QUEUE_CONNECTION}|" .env
[ -n "$REDIS_HOST" ]       && sed -i "s|^REDIS_HOST=.*|REDIS_HOST=${REDIS_HOST}|" .env
[ -n "$REDIS_PORT" ]       && sed -i "s|^REDIS_PORT=.*|REDIS_PORT=${REDIS_PORT}|" .env
[ -n "$MAIL_MAILER" ]      && sed -i "s|^MAIL_MAILER=.*|MAIL_MAILER=${MAIL_MAILER}|" .env
[ -n "$MAIL_HOST" ]        && sed -i "s|^MAIL_HOST=.*|MAIL_HOST=${MAIL_HOST}|" .env
[ -n "$MAIL_PORT" ]        && sed -i "s|^MAIL_PORT=.*|MAIL_PORT=${MAIL_PORT}|" .env

# ---- Step 4: Wait for MySQL ----
echo "[entrypoint] Waiting for MySQL at ${DB_HOST:-db}:${DB_PORT:-3306}..."
MAX_RETRIES=30
RETRY=0
until php -r "
    try {
        new PDO(
            'mysql:host=${DB_HOST:-db};port=${DB_PORT:-3306};dbname=${DB_DATABASE:-automatecrm}',
            '${DB_USERNAME:-automatecrm}',
            '${DB_PASSWORD:-secret}'
        );
        echo 'connected';
    } catch (Exception \$e) {
        exit(1);
    }
" 2>/dev/null; do
    RETRY=$((RETRY + 1))
    if [ $RETRY -ge $MAX_RETRIES ]; then
        echo "[entrypoint] WARNING: MySQL not ready after ${MAX_RETRIES} attempts, continuing anyway..."
        break
    fi
    echo "[entrypoint]   Attempt ${RETRY}/${MAX_RETRIES} — retrying in 2s..."
    sleep 2
done

# ---- Step 5: Run migrations ----
echo "[entrypoint] Running database migrations..."
php artisan migrate --force || echo "[entrypoint] WARNING: Migrations failed, app may still work if tables exist"

# ---- Step 6: Cache configuration ----
echo "[entrypoint] Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ---- Step 7: Fix permissions ----
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "=========================================="
echo "  Initialization complete — starting app"
echo "=========================================="

# ---- Start supervisord ----
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
