# Start here — Lucky Arcade v0.9

## Upgrade from v0.8 or v0.8.1

1. Stop the Laravel server with `Ctrl+C`.
2. Copy the v0.9 update into the repository root.
3. Run:

```bash
chmod +x upgrade-v0.9.sh run-codespaces.sh
bash upgrade-v0.9.sh
bash run-codespaces.sh
```

4. Open port 8000 from the Codespaces **PORTS** tab.
5. Test Notifications, Support, Weekly League and **Admin → Live operations**.

The upgrade creates a timestamped SQLite safety copy, runs migrations, runs the full test suite, reconciles wallets, creates a backup and runs the production doctor.

## Live checks

```bash
cd lucky-arcade-app
php artisan arcade:prune-live
php artisan test --filter=Live
```

The live feed is ephemeral. Financial truth remains in `game_entries`, `wallets` and `ledger_entries`.
