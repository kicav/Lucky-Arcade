<?php

return [
    'poll_ms' => max(1500, (int) env('LIVE_POLL_MS', 4000)),
    'event_ttl_seconds' => max(300, (int) env('LIVE_EVENT_TTL_SECONDS', 21600)),
    'presence_window_minutes' => max(1, (int) env('LIVE_PRESENCE_WINDOW_MINUTES', 5)),
];
