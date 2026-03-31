#!/bin/bash
# ============================================================================
# automateCRM - Docker Setup Script
# Initializes the Docker environment for first-time setup
# ============================================================================

set -euo pipefail

CYAN='\033[36m'
GREEN='\033[32m'
YELLOW='\033[33m'
RED='\033[31m'
RESET='\033[0m'

log()   { echo -e "${GREEN}[✓]${RESET} $1"; }
warn()  { echo -e "${YELLOW}[!]${RESET} $1"; }
error() { echo -e "${RED}[✗]${RESET} $1"; exit 1; }
info()  { echo -e "${CYAN}[i]${RESET} $1"; }

echo ""
echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${RESET}"
echo -e "${CYAN}  automateCRM - Docker Environment Setup${RESET}"
echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${RESET}"
echo ""

# ──────────────────────────────────────────────────────────────────────────────
# Prerequisites check
# ──────────────────────────────────────────────────────────────────────────────
info "Checking prerequisites..."

command -v docker >/dev/null 2>&1 || error "Docker is not installed. Please install Docker first."
command -v docker compose >/dev/null 2>&1 && COMPOSE="docker compose" || {
    command -v docker-compose >/dev/null 2>&1 && COMPOSE="docker-compose" || error "Docker Compose is not installed."
}

log "Docker and Docker Compose found"

# ──────────────────────────────────────────────────────────────────────────────
# Environment file
# ──────────────────────────────────────────────────────────────────────────────
if [ ! -f .env ]; then
    info "Creating .env from .env.example..."
    cp .env.example .env

    # Update .env for Docker
    sed -i 's/DB_HOST=127.0.0.1/DB_HOST=mysql/' .env
    sed -i 's/DB_DATABASE=laravel/DB_DATABASE=automatecrm/' .env
    sed -i 's/DB_USERNAME=root/DB_USERNAME=automatecrm/' .env
    sed -i 's/DB_PASSWORD=/DB_PASSWORD=secret/' .env
    sed -i 's/REDIS_HOST=127.0.0.1/REDIS_HOST=redis/' .env
    sed -i 's/CACHE_DRIVER=file/CACHE_DRIVER=redis/' .env
    sed -i 's/SESSION_DRIVER=file/SESSION_DRIVER=redis/' .env
    sed -i 's/QUEUE_CONNECTION=sync/QUEUE_CONNECTION=redis/' .env

    log ".env configured for Docker"
else
    warn ".env already exists — skipping"
fi

# ──────────────────────────────────────────────────────────────────────────────
# Build & start containers
# ──────────────────────────────────────────────────────────────────────────────
MODE="${1:-prod}"

if [ "$MODE" = "dev" ]; then
    info "Starting in DEVELOPMENT mode..."
    $COMPOSE -f docker-compose.yml -f docker-compose.dev.yml up -d --build
else
    info "Starting in PRODUCTION mode..."
    $COMPOSE up -d --build
fi

# ──────────────────────────────────────────────────────────────────────────────
# Wait for MySQL
# ──────────────────────────────────────────────────────────────────────────────
info "Waiting for MySQL to be ready..."
RETRIES=30
until $COMPOSE exec mysql mysqladmin ping -h localhost --silent 2>/dev/null; do
    RETRIES=$((RETRIES - 1))
    if [ $RETRIES -le 0 ]; then
        error "MySQL did not become ready in time"
    fi
    sleep 2
done
log "MySQL is ready"

# ──────────────────────────────────────────────────────────────────────────────
# Laravel setup
# ──────────────────────────────────────────────────────────────────────────────
info "Generating application key..."
$COMPOSE exec app php artisan key:generate --no-interaction
log "App key generated"

info "Running migrations..."
$COMPOSE exec app php artisan migrate --force
log "Migrations complete"

info "Seeding database..."
$COMPOSE exec app php artisan db:seed --force
log "Database seeded"

info "Caching configuration..."
$COMPOSE exec app php artisan config:cache
$COMPOSE exec app php artisan route:cache
$COMPOSE exec app php artisan view:cache
log "Caches warmed"

# ──────────────────────────────────────────────────────────────────────────────
# Done
# ──────────────────────────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${RESET}"
echo -e "${GREEN}  automateCRM is running!${RESET}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${RESET}"
echo ""
echo -e "  App:        ${CYAN}http://localhost:8000${RESET}"
if [ "$MODE" = "dev" ]; then
    echo -e "  phpMyAdmin: ${CYAN}http://localhost:8081${RESET}"
    echo -e "  Redis Cmd:  ${CYAN}http://localhost:8082${RESET}"
fi
echo -e "  Login:      ${CYAN}admin@admin.com / password${RESET}"
echo ""
