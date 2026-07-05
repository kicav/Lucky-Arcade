#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
TARGET="$ROOT/lucky-arcade-app"

command -v php >/dev/null 2>&1 || { echo "Không tìm thấy PHP."; exit 1; }
command -v composer >/dev/null 2>&1 || { echo "Không tìm thấy Composer."; exit 1; }

PHP_VERSION_ID="$(php -r 'echo PHP_VERSION_ID;')"
if [ "$PHP_VERSION_ID" -lt 80300 ]; then
    echo "Laravel 13 cần PHP 8.3 trở lên. Phiên bản hiện tại: $(php -r 'echo PHP_VERSION;')"
    echo "Hãy kiểm tra .devcontainer rồi tạo lại hoặc rebuild Codespace."
    exit 1
fi

if ! php -m | grep -qi '^pdo_sqlite$'; then
    echo "Thiếu extension pdo_sqlite. Hãy rebuild Codespace bằng cấu hình .devcontainer đi kèm."
    exit 1
fi

if [ -d "$TARGET" ] && [ ! -f "$TARGET/artisan" ]; then
    echo "Phát hiện thư mục cài đặt dở. Đang xóa để cài lại sạch..."
    rm -rf "$TARGET"
fi

if [ -e "$TARGET" ]; then
    echo "$TARGET đã tồn tại và có vẻ đã cài đặt."
    echo "Để cập nhật v0.7, chạy: bash upgrade-v0.7.sh"
    exit 0
fi

echo "Đang tạo Laravel 13..."
XDEBUG_MODE=off composer create-project --no-interaction --prefer-dist laravel/laravel "$TARGET" "^13.0"

echo "Đang chép mã nguồn Lucky Arcade..."
cp -R "$ROOT/overlay/." "$TARGET/"
cp "$ROOT/LICENSE" "$TARGET/LICENSE"
rm -rf "$TARGET/docs"
cp -R "$ROOT/docs" "$TARGET/docs"

cd "$TARGET"

if [ ! -f .env ]; then
    cp .env.example .env
fi

sed -i 's/^APP_NAME=.*/APP_NAME="Lucky Arcade"/' .env
sed -i 's/^APP_ENV=.*/APP_ENV=local/' .env
sed -i 's/^APP_DEBUG=.*/APP_DEBUG=true/' .env
sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
sed -i 's/^SESSION_DRIVER=.*/SESSION_DRIVER=database/' .env
sed -i 's/^CACHE_STORE=.*/CACHE_STORE=database/' .env
sed -i 's/^QUEUE_CONNECTION=.*/QUEUE_CONNECTION=database/' .env

if [ -n "${CODESPACE_NAME:-}" ]; then
    FORWARD_DOMAIN="${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN:-app.github.dev}"
    PUBLIC_URL="https://${CODESPACE_NAME}-8000.${FORWARD_DOMAIN}"
    sed -i "s#^APP_URL=.*#APP_URL=${PUBLIC_URL}#" .env
fi

mkdir -p database
touch database/database.sqlite

XDEBUG_MODE=off php artisan key:generate --force
XDEBUG_MODE=off php artisan optimize:clear
XDEBUG_MODE=off php artisan migrate --seed --force
XDEBUG_MODE=off php artisan test

printf '\nCài đặt hoàn tất.\nChạy ứng dụng bằng:\n  bash run-codespaces.sh\n\n'
