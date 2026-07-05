#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP="$ROOT/lucky-arcade-app"
if [ ! -f "$APP/artisan" ]; then
    echo "Không tìm thấy lucky-arcade-app. Hãy chạy setup-linux.sh trước."
    exit 1
fi
cd "$APP"
XDEBUG_MODE=off composer require laravel/horizon --no-interaction
XDEBUG_MODE=off php artisan horizon:install
XDEBUG_MODE=off php artisan optimize:clear
printf '\nHorizon đã được cài. Đặt QUEUE_CONNECTION=redis và chạy: php artisan horizon\n'
