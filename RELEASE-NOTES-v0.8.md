# Lucky Arcade v0.8 — Production Foundation

v0.8 moves the project from a feature-focused demo toward a deployable service architecture.

## Added

- PostgreSQL and Redis production stack.
- Nginx and PHP-FPM container configuration.
- Separate web, queue and scheduler services.
- Queued daily game metrics dispatched after a settled bet commits.
- Idempotent aggregate rebuilding for SQLite and PostgreSQL.
- `arcade:doctor`, `arcade:metrics` and `arcade:prune` commands.
- SQLite and PostgreSQL backup support.
- Scheduled maintenance tasks.
- Admin System Operations controls and readiness checks.
- SQLite/PostgreSQL CI on PHP 8.3 and 8.4.
- Composer security audit and Dependabot configuration.

## Compatibility

- Existing Codespaces installations continue to use SQLite and the database queue.
- Production examples use PostgreSQL and Redis.
- No real-money, deposit, withdrawal or credit-transfer capability was added.

## Upgrade

```bash
bash upgrade-v0.8.sh
bash run-codespaces.sh
```
