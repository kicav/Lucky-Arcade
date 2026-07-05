#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP="$ROOT/lucky-arcade-app"

if [ ! -f "$APP/artisan" ]; then
    echo "Lucky Arcade chưa được cài đặt. Bắt đầu thiết lập..."
    bash "$ROOT/setup-linux.sh"
fi

cd "$APP"
mkdir -p storage/logs

if command -v ss >/dev/null 2>&1 && ss -ltn | grep -q ':8000 '; then
    echo "Cổng 8000 đang được sử dụng. Hãy dừng server cũ bằng Ctrl+C hoặc chạy: pkill -f 'artisan serve'"
    exit 1
fi

cleanup() {
    for pid in "${QUEUE_PID:-}" "${SCHEDULER_PID:-}"; do
        if [ -n "$pid" ]; then
            kill "$pid" 2>/dev/null || true
            wait "$pid" 2>/dev/null || true
        fi
    done
}
trap cleanup EXIT INT TERM

echo "Đang chạy queue worker cho analytics và tác vụ nền..."
XDEBUG_MODE=off php artisan queue:work \
    --queue=critical,default,analytics,notifications \
    --sleep=1 --tries=3 --timeout=90 \
    > storage/logs/queue-worker.log 2>&1 &
QUEUE_PID=$!

echo "Đang chạy Laravel scheduler..."
XDEBUG_MODE=off php artisan schedule:work \
    > storage/logs/scheduler.log 2>&1 &
SCHEDULER_PID=$!

echo "Mở Lucky Arcade tại cổng 8000..."
echo "Queue log: storage/logs/queue-worker.log"
echo "Scheduler log: storage/logs/scheduler.log"
XDEBUG_MODE=off php artisan serve --host=0.0.0.0 --port=8000
