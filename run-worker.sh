#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT/lucky-arcade-app"
exec env XDEBUG_MODE=off php artisan queue:work --queue=critical,default,analytics,notifications --sleep=1 --tries=3 --timeout=90
