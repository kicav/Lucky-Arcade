#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP="$ROOT/lucky-arcade-app"

if [ ! -f "$APP/artisan" ]; then
    echo "Lucky Arcade chưa được cài đặt. Bắt đầu thiết lập..."
    bash "$ROOT/setup-linux.sh"
fi

cd "$APP"

if command -v ss >/dev/null 2>&1 && ss -ltn | grep -q ':8000 '; then
    echo "Cổng 8000 đang được sử dụng. Hãy dừng server cũ bằng Ctrl+C hoặc chạy: pkill -f 'artisan serve'"
    exit 1
fi

echo "Mở Lucky Arcade tại cổng 8000..."
XDEBUG_MODE=off php artisan serve --host=0.0.0.0 --port=8000
