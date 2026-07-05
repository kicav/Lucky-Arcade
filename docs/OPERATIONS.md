# Operations handbook

## Codespaces

`bash run-codespaces.sh` starts the web server, queue worker and scheduler. Logs are written to:

```text
storage/logs/queue-worker.log
storage/logs/scheduler.log
storage/logs/laravel.log
```

## Health checks

```bash
php artisan arcade:doctor
php artisan arcade:doctor --json
php artisan arcade:doctor --strict
```

`--strict` fails on warnings as well as errors and is intended for deployment gates.

## Daily metrics

```bash
php artisan arcade:metrics --days=30
```

Metrics are rebuilt from source game entries. Re-running the command does not increment values twice.

## Wallet integrity

```bash
php artisan wallets:reconcile
```

Do not use `--fix` until the discrepancy and source transaction have been reviewed.

## Backups

```bash
php artisan arcade:backup --keep=14
```

- SQLite uses the SQLite backup API.
- PostgreSQL uses `pg_dump` custom format.
- Backup files are placed in `storage/app/backups`.
- Copy backups to external object storage in a real deployment.
- Test restore procedures regularly.

## Data retention

```bash
php artisan arcade:prune
```

This removes old read notifications, old security events and old operation-run history. Unread player notifications are retained.

## Scheduler

The scheduler currently runs:

- metrics every 15 minutes;
- wallet reconciliation daily;
- database backup daily;
- operational pruning daily;
- failed-job pruning daily.

Only one scheduler instance should invoke due tasks for a single deployment unless a shared cache and an appropriate single-server strategy are configured.

## Live Experience operations (v0.9)

The default live transport polls `/live/feed` with a monotonic event cursor. Configure `LIVE_POLL_MS`, `LIVE_EVENT_TTL_SECONDS` and `LIVE_PRESENCE_WINDOW_MINUTES` in production.

```bash
php artisan arcade:prune-live
```

The scheduler runs this command hourly. `live_events` and `user_presences` are transient operational tables and are not included in financial reconciliation.
