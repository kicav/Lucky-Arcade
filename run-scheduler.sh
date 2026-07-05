#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT/lucky-arcade-app"
exec env XDEBUG_MODE=off php artisan schedule:work
