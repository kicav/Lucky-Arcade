# Lucky Arcade v0.9 — Live Experience

## What changed

v0.9 adds a transport-neutral live experience without introducing a mandatory WebSocket dependency.

- Per-user, public and administrator live events.
- Notification badge updates without page reloads.
- Live support threads and JSON message submission.
- Live Weekly League refresh after settled games.
- Online user presence and current-page tracking.
- Administrator Live Operations dashboard.
- Automatic cleanup of expired events and stale presence rows.

## Privacy and authorization

- Players only receive public events and events targeted to their own user ID.
- Administrator events are only included for roles allowed to access support, system or live operations.
- Support message endpoints enforce ticket ownership or administrator-area authorization.
- Live events are transient and are not a financial source of truth.

## Transport

The default transport is HTTPS polling every four seconds. Configure:

```dotenv
LIVE_POLL_MS=4000
LIVE_EVENT_TTL_SECONDS=21600
LIVE_PRESENCE_WINDOW_MINUTES=5
```

A future Reverb adapter can publish the same event payloads over private WebSocket channels.
