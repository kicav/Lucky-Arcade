# Architecture

Laravel monolith with Blade UI, SQLite/PostgreSQL-compatible persistence, database transactions, immutable wallet ledger and HMAC-SHA256 game outcomes.

## v0.6 domains

- Authentication and player controls
- Wallet and ledger
- Provably-fair game engines
- Rewards: daily, mission, achievement, referral, promo and weekly league
- Community support tickets
- Admin operations, analytics, announcements and audit logs

All virtual-credit mutations use transactions, wallet row locks and idempotency keys.
