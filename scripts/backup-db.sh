#!/bin/bash
# ============================================================================
# automateCRM - Database Backup Script
# Creates timestamped database backups with optional S3 upload
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
BACKUP_DIR="${BACKUP_DIR:-./backups}"
TIMESTAMP=$(date +%Y%m%d-%H%M%S)
DB_NAME="${DB_DATABASE:-automatecrm}"
S3_BUCKET="${BACKUP_S3_BUCKET:-}"
RETENTION_DAYS="${BACKUP_RETENTION_DAYS:-30}"

mkdir -p "$BACKUP_DIR"

BACKUP_FILE="${BACKUP_DIR}/${DB_NAME}-${TIMESTAMP}.sql.gz"

echo ""
info "Starting database backup..."
info "Database: ${DB_NAME}"
info "Output:   ${BACKUP_FILE}"

# ──────────────────────────────────────────────────────────────────────────────
# Dump
# ──────────────────────────────────────────────────────────────────────────────
if command -v docker compose >/dev/null 2>&1 && docker compose ps mysql >/dev/null 2>&1; then
    info "Dumping via Docker..."
    docker compose exec -T mysql mysqldump \
        -u root \
        -p"${DB_PASSWORD:-password}" \
        --single-transaction \
        --routines \
        --triggers \
        "$DB_NAME" | gzip > "$BACKUP_FILE"
elif command -v mysqldump >/dev/null 2>&1; then
    info "Dumping via local mysqldump..."
    mysqldump \
        -h "${DB_HOST:-127.0.0.1}" \
        -u "${DB_USERNAME:-root}" \
        -p"${DB_PASSWORD:-}" \
        --single-transaction \
        --routines \
        --triggers \
        "$DB_NAME" | gzip > "$BACKUP_FILE"
else
    error "Neither Docker MySQL nor local mysqldump available"
fi

FILESIZE=$(du -h "$BACKUP_FILE" | cut -f1)
log "Backup created: ${BACKUP_FILE} (${FILESIZE})"

# ──────────────────────────────────────────────────────────────────────────────
# Upload to S3 (optional)
# ──────────────────────────────────────────────────────────────────────────────
if [ -n "$S3_BUCKET" ]; then
    info "Uploading to S3: s3://${S3_BUCKET}/backups/"
    aws s3 cp "$BACKUP_FILE" "s3://${S3_BUCKET}/backups/$(basename "$BACKUP_FILE")"
    log "Uploaded to S3"
fi

# ──────────────────────────────────────────────────────────────────────────────
# Cleanup old backups
# ──────────────────────────────────────────────────────────────────────────────
info "Cleaning backups older than ${RETENTION_DAYS} days..."
DELETED=$(find "$BACKUP_DIR" -name "*.sql.gz" -mtime +"$RETENTION_DAYS" -delete -print | wc -l)
log "Removed ${DELETED} old backup(s)"

echo ""
log "Backup complete: ${BACKUP_FILE}"
