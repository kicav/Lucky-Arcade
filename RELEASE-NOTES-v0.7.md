# Lucky Arcade v0.7 release notes

## Security Center and TOTP

Players and administrators can enable standards-based six-digit TOTP authentication without an external package. Secrets are encrypted using Laravel's application key. Recovery codes are hashed, encrypted at rest and consumed once.

The login flow now separates password verification from the second-factor challenge. Failed logins, successful logins, logout, password changes and two-factor events are stored in a dedicated security-event log.

## Administrator governance

v0.7 replaces unrestricted `is_admin` access with four roles:

- **Super Admin:** all areas, including role assignment.
- **Operations:** game, player operations, promotions, reports, support, league and backups.
- **Support:** player lookup and support tickets.
- **Analyst:** read-only analytics, play history and audit log.

Server-side middleware enforces every area independently; hiding links is not the security boundary.

## Operations

The System Health page reports database connectivity, storage writability, database size, cache/queue configuration, recent backups and failed-login activity. Operations and Super Admin accounts can trigger the existing consistent SQLite backup command.

## Compatibility

- Laravel 13
- PHP 8.3+
- SQLite development setup
- Existing v0.6 data retained
