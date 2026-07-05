#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP="$ROOT/lucky-arcade-app"

if [ ! -d "$APP" ]; then
    echo "Lucky Arcade has not been installed yet. Running setup-linux.sh..."
    "$ROOT/setup-linux.sh"
fi

cd "$APP"
php artisan serve --host=0.0.0.0 --port=8000
