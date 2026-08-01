#!/usr/bin/env bash
set -euo pipefail

APP_DIR=/var/www/lamari
BACKUP_ROOT=/var/backups/lamari/daily
BACKUP_STAMP=$(date -u +%Y%m%d-%H%M%S)
BACKUP_DIR="$BACKUP_ROOT/$BACKUP_STAMP"

install -d -m 700 "$BACKUP_ROOT" "$BACKUP_DIR"

php -r '$src=new PDO("sqlite:/var/www/lamari/database/database.sqlite"); $dst=$argv[1]; $src->exec("VACUUM INTO ".$src->quote($dst));' "$BACKUP_DIR/database.sqlite"
tar -C "$APP_DIR" -czf "$BACKUP_DIR/media.tar.gz" storage/app/public
sha256sum "$BACKUP_DIR/database.sqlite" "$BACKUP_DIR/media.tar.gz" > "$BACKUP_DIR/SHA256SUMS"
chmod 600 "$BACKUP_DIR"/*

find "$BACKUP_ROOT" -mindepth 1 -maxdepth 1 -type d -mtime +30 -exec rm -rf -- {} +
