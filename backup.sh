#!/bin/sh
set -e

TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR=/opt/ashadi/backups
BACKUP_FILE="${BACKUP_DIR}/backup_${TIMESTAMP}.sql.gz"

mkdir -p "$BACKUP_DIR"


echo "[$(date)] Starting backup..."

docker compose -f /opt/ashadi/docker-compose.yml exec -T mysql \
  mysqldump -u root -p"${DB_ROOT_PASSWORD}" \
  --single-transaction \
  --routines \
  --triggers \
  "${DB_NAME}" | gzip > "$BACKUP_FILE"

echo "[$(date)] Backup complete: ${BACKUP_FILE}"

# Keep only last 7 days
find "$BACKUP_DIR" -name "*.sql.gz" -mtime +7 -delete

echo "[$(date)] Old backups cleaned up."