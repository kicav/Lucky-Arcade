#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP="$ROOT/lucky-arcade-app"
SOURCE="$ROOT/overlay/tests/Feature/ProductionOperationsTest.php"
TARGET="$APP/tests/Feature/ProductionOperationsTest.php"

if [ ! -f "$APP/artisan" ]; then
    echo "Không tìm thấy $APP/artisan"
    echo "Hãy chạy script này tại thư mục gốc /workspaces/Lucky-Arcade."
    exit 1
fi

if [ ! -f "$SOURCE" ]; then
    echo "Không tìm thấy file bản vá: $SOURCE"
    exit 1
fi

mkdir -p "$(dirname "$TARGET")"
cp "$SOURCE" "$TARGET"

cd "$APP"
XDEBUG_MODE=off php artisan optimize:clear
XDEBUG_MODE=off php artisan test --filter=ProductionOperationsTest
XDEBUG_MODE=off php artisan test

printf '\nHotfix v0.8.1 đã áp dụng thành công.\n'
