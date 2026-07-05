# ERD v0.5

```text
users
 ├─1 wallet
 ├─N game_entries
 ├─N fairness_seeds
 ├─N ledger_entries
 ├─N user_notifications
 ├─N user_achievements
 ├─N user_missions
 └─N referred users

games ──N game_entries
fairness_seeds ──N game_entries
game_entries ──N ledger_entries (polymorphic reference)
user_missions ──N ledger_entries (polymorphic reference)
users(admin) ──N announcements
users(admin) ──N audit_logs
```

## Bảng mới v0.5

### user_missions

- `user_id`, `mission_key`, `mission_date`
- `progress`, `target`, `reward`
- `completed_at`, `claimed_at`
- unique `(user_id, mission_date, mission_key)`

### announcements

- `created_by`, `title`, `body`
- `active`, `starts_at`, `ends_at`
