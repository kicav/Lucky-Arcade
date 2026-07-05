# Lucky Arcade v0.9

Lucky Arcade is a Laravel social-gaming demo that uses virtual credits only. It has no deposits, withdrawals or cash value.

## v0.9 highlights

- Database-backed live event stream with adaptive browser polling.
- Live notification badge and transient toast messages.
- Live support threads with JSON replies and automatic message refresh.
- Live Weekly League standings refresh after settled games.
- Online-presence tracking with write throttling.
- Admin Live Operations dashboard for active users, support queue and recent events.
- Ephemeral live-event retention and scheduled pruning.
- PostgreSQL, Redis, queue, scheduler and Docker production foundation from v0.8.

The default transport intentionally uses ordinary HTTPS polling, so v0.9 works in Codespaces and behind standard PHP hosting without a WebSocket service. The domain layer is isolated so a future Reverb/WebSocket transport can replace polling without changing wallet or game settlement logic.

## Upgrade an existing v0.8/v0.8.1 repository

```bash
bash upgrade-v0.9.sh
bash run-codespaces.sh
```

## Fresh Codespaces install

```bash
bash setup-linux.sh
bash run-codespaces.sh
```

## Important commands

```bash
cd lucky-arcade-app
php artisan test
php artisan wallets:reconcile
php artisan arcade:doctor
php artisan arcade:prune-live
```

Demo player: `demo@example.com` / `Demo123!`

Demo admin: `admin@example.com` / `ChangeMe123!`

Change demo credentials, create a unique `APP_KEY`, enable administrator 2FA and keep the service private until production-readiness checks pass.
