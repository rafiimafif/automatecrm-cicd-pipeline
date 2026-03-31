#!/bin/bash
# ============================================================================
# automateCRM - Deployment Script
# Deploys the application to a remote server via SSH
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

# ──────────────────────────────────────────────────────────────────────────────
# Configuration
# ──────────────────────────────────────────────────────────────────────────────
ENVIRONMENT="${1:-staging}"
IMAGE="ghcr.io/rafiimafif/automateCRM"
TAG="${2:-$(git rev-parse --short HEAD)}"

case "$ENVIRONMENT" in
    staging)
        DEPLOY_HOST="${STAGING_HOST:-}"
        DEPLOY_USER="${STAGING_USER:-deploy}"
        DEPLOY_PATH="${STAGING_PATH:-/opt/automatecrm}"
        ;;
    production)
        DEPLOY_HOST="${PRODUCTION_HOST:-}"
        DEPLOY_USER="${PRODUCTION_USER:-deploy}"
        DEPLOY_PATH="${PRODUCTION_PATH:-/opt/automatecrm}"
        ;;
    *)
        error "Unknown environment: $ENVIRONMENT (use 'staging' or 'production')"
        ;;
esac

if [ -z "$DEPLOY_HOST" ]; then
    error "Deploy host not set. Export ${ENVIRONMENT^^}_HOST environment variable."
fi

echo ""
echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${RESET}"
echo -e "${CYAN}  automateCRM - Deploy to ${ENVIRONMENT}${RESET}"
echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${RESET}"
echo ""
echo -e "  Image:       ${IMAGE}:${TAG}"
echo -e "  Host:        ${DEPLOY_HOST}"
echo -e "  Path:        ${DEPLOY_PATH}"
echo ""

# Production safety check
if [ "$ENVIRONMENT" = "production" ]; then
    warn "You are about to deploy to PRODUCTION"
    read -p "Continue? [y/N] " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Aborted."
        exit 0
    fi
fi

# ──────────────────────────────────────────────────────────────────────────────
# Deploy
# ──────────────────────────────────────────────────────────────────────────────
info "Pulling latest image on remote..."
ssh "${DEPLOY_USER}@${DEPLOY_HOST}" "docker pull ${IMAGE}:${TAG}"
log "Image pulled"

info "Updating docker-compose on remote..."
scp docker-compose.yml "${DEPLOY_USER}@${DEPLOY_HOST}:${DEPLOY_PATH}/docker-compose.yml"
log "Compose file synced"

info "Deploying new version..."
ssh "${DEPLOY_USER}@${DEPLOY_HOST}" bash <<EOF
    cd ${DEPLOY_PATH}
    export IMAGE_TAG=${TAG}

    # Update image tag in compose
    sed -i "s|image: ${IMAGE}:.*|image: ${IMAGE}:${TAG}|" docker-compose.yml

    # Pull & restart
    docker compose pull
    docker compose up -d --remove-orphans

    # Run migrations
    docker compose exec -T app php artisan migrate --force

    # Clear & rebuild caches
    docker compose exec -T app php artisan config:cache
    docker compose exec -T app php artisan route:cache
    docker compose exec -T app php artisan view:cache

    # Health check
    sleep 5
    HTTP_CODE=\$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/api/health || echo "000")
    if [ "\$HTTP_CODE" = "200" ]; then
        echo "Health check passed"
    else
        echo "WARNING: Health check returned \$HTTP_CODE"
    fi
EOF

log "Deployment to ${ENVIRONMENT} complete"

echo ""
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${RESET}"
echo -e "${GREEN}  Deployed ${IMAGE}:${TAG} to ${ENVIRONMENT}${RESET}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${RESET}"
echo ""
