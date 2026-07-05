# Changelog

## v0.9.0

- Added a database-backed live event stream with user, public and administrator audiences.
- Added adaptive browser polling, live notification counts and toast messages.
- Added live support message polling and AJAX replies for players and administrators.
- Added live Weekly League refresh after game settlement.
- Added online-presence tracking with throttled database writes.
- Added an Admin Live Operations dashboard.
- Added event and presence pruning with a scheduled command.
- Added feature tests for feed privacy, live support, presence and administrator live operations.

## v0.8.0

- Added PostgreSQL and Redis production configuration with Docker Compose.
- Added queued, after-commit daily game metrics and an analytics aggregate table.
- Added production doctor, operational pruning and metrics rebuild commands.
- Added queue and scheduler processes to the Codespaces runner.
- Added queue, backup, readiness and operation-run information to System Operations.
- Added consistent PostgreSQL backups through `pg_dump`.
- Added a PHP/database CI matrix, Composer audit and Dependabot configuration.
- Added scheduled reconciliation, backups, metrics, pruning and failed-job cleanup.

## v0.7.0

- Added optional TOTP two-factor authentication and single-use recovery codes.
- Added a Security Center and persistent authentication event history.
- Added Super Admin, Operations, Support and Analyst roles.
- Added server-side per-area administrator authorization.
- Added administrator role assignment and System Health dashboards.
- Added secure response headers and new authentication/security tests.

## v0.6.0

- Added promo codes and per-player redemption records.
- Added player and admin support tickets with threaded messages.
- Added Weekly League standings and previous-week settlement.
- Added ledger entries, notifications and audit logging for new reward paths.
- Added tests for promo redemption, support replies and league settlement.
- Integrated the v0.5.1 mission date/idempotency hotfix.

## v0.5.1

- Fixed duplicate daily missions on SQLite date-cast comparisons.

## v0.5.0

- Added Lucky Slots, daily missions, player statistics, announcements and SQLite backup command.
