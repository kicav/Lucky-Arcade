# Entity relationship overview

- `users` 1—1 `wallets`.
- `users` 1—N `game_entries`, `ledger_entries`, `user_notifications`, `security_events`.
- `users` 1—1 `user_presences`.
- `users` 1—N targeted `live_events`; public and administrator events have a null user ID.
- `games` 1—N `game_entries` and `daily_game_metrics`.
- `support_tickets` 1—N ordered `support_messages`.
- `operation_runs` records scheduled and manual operational task results.
- Promo, referral, achievement, mission, announcement, league and fairness entities remain unchanged.

## Source of truth

- Wallet balance must reconcile against ordered `ledger_entries`.
- Daily analytics can be rebuilt from `game_entries`.
- Live events are transient delivery hints and may be safely pruned.
- Presence is an approximate operational signal, not an authentication record.
