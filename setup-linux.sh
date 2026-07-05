#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
TARGET="$ROOT/lucky-arcade-app"

command -v composer >/dev/null || { echo "Composer is required"; exit 1; }
[ ! -e "$TARGET" ] || { echo "$TARGET already exists"; exit 1; }

composer create-project laravel/laravel:^13.0 "$TARGET"
cp -R "$ROOT/overlay/." "$TARGET/"
cp "$ROOT/LICENSE" "$TARGET/LICENSE"
cp -R "$ROOT/docs" "$TARGET/docs"
cd "$TARGET"
cp .env.example .env
sed -i 's/^APP_NAME=.*/APP_NAME="Lucky Arcade"/' .env
mkdir -p database
touch database/database.sqlite
php artisan key:generate
php artisan migrate --seed
php artisan test
printf '\nInstalled. Run: cd lucky-arcade-app && php artisan serve\n'
