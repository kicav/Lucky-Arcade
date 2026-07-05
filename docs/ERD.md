# Entity relationship overview

- `users` 1—1 `wallets`
- `users` 1—N `game_entries`, `ledger_entries`, `user_notifications`
- `games` 1—N `game_entries`
- `promo_codes` 1—N `promo_code_redemptions`; each user may redeem a code once
- `support_tickets` 1—N `support_messages`; tickets belong to users
- `weekly_league_settlements` identifies a settled week
- `weekly_league_rewards` stores the top-three rewards for each settled week
- Existing referral, achievement, mission, announcement and fairness entities remain unchanged
