#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="$ROOT_DIR/lucky-arcade-app"
SOURCE_FILE="$ROOT_DIR/overlay/app/Services/MissionService.php"
TARGET_FILE="$APP_DIR/app/Services/MissionService.php"

if [[ ! -f "$APP_DIR/artisan" ]]; then
  echo "Could not find $APP_DIR/artisan"
  echo "Run this script from the Lucky-Arcade repository root."
  exit 1
fi

mkdir -p "$(dirname "$TARGET_FILE")"
cp "$SOURCE_FILE" "$TARGET_FILE"

cd "$APP_DIR"
XDEBUG_MODE=off php artisan optimize:clear
XDEBUG_MODE=off php artisan test

echo
echo "Lucky Arcade v0.5.1 mission hotfix applied successfully."
