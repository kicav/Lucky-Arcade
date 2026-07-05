# Architecture

Lucky Arcade remains a Laravel monolith with Blade UI, database transactions, immutable wallet ledger and HMAC-SHA256 game outcomes.

## Runtime profiles

### Codespaces / demo

```text
Laravel web server
SQLite
Database cache / queue
Queue worker
Scheduler
HTTPS polling live transport
```

### Production

```text
Nginx
PHP-FPM application
PostgreSQL
Redis cache / sessions / queue
Queue workers
Scheduler
External backup storage
HTTPS polling or future Reverb transport
```

## Main domains

- Authentication, TOTP and player controls.
- Wallet and immutable ledger.
- Provably-fair game engines.
- Rewards and Weekly League.
- Support tickets and notifications.
- Admin RBAC, analytics, audit and system operations.
- Live events, presence and transient UI updates.

## Settlement path

```text
HTTP request
  -> validate player and game
  -> transaction
      -> lock user, game, wallet and fairness seed
      -> create settled game entry
      -> write debit / payout ledger rows
      -> evaluate synchronous reward invariants
      -> increment nonce
  -> commit
  -> dispatch GameSettled
  -> queued daily aggregate refresh
  -> queued live game / league event publication
```

## Live experience path

```text
Domain model change
  -> LiveEventService writes a short-lived live_events row
  -> authenticated browser polls /live/feed with a cursor
  -> backend filters by public, personal and permitted admin audiences
  -> browser updates notification badge, support thread or league table
```

`live_events` and `user_presences` are operational data. They may be pruned and must never be used to reconstruct wallet balances.
