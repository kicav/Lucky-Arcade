#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP="$ROOT/lucky-arcade-app"

if [ ! -f "$APP/artisan" ]; then
    echo "Không tìm thấy lucky-arcade-app/artisan. Hãy cài lần đầu bằng: bash setup-linux.sh"
    exit 1
fi

printf 'Đang sao lưu SQLite trước khi nâng cấp v0.5...\n'
if [ -f "$APP/database/database.sqlite" ]; then
    cp "$APP/database/database.sqlite" "$APP/database/database.sqlite.backup-v0.5-$(date +%Y%m%d-%H%M%S)"
fi

printf 'Đang chép mã nguồn v0.5...\n'
cp -R "$ROOT/overlay/." "$APP/"

cd "$APP"

if [ -n "${CODESPACE_NAME:-}" ]; then
    FORWARD_DOMAIN="${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN:-app.github.dev}"
    PUBLIC_URL="https://${CODESPACE_NAME}-8000.${FORWARD_DOMAIN}"
    if grep -q '^APP_URL=' .env; then
        sed -i "s#^APP_URL=.*#APP_URL=${PUBLIC_URL}#" .env
    else
        echo "APP_URL=${PUBLIC_URL}" >> .env
    fi
fi

XDEBUG_MODE=off php artisan optimize:clear
XDEBUG_MODE=off php artisan migrate --force
XDEBUG_MODE=off php artisan db:seed --force
XDEBUG_MODE=off php artisan test
XDEBUG_MODE=off php artisan wallets:reconcile
XDEBUG_MODE=off php artisan arcade:backup --keep=10

printf '\nNâng cấp v0.5 hoàn tất. Chạy:\n  bash run-codespaces.sh\n\n'
