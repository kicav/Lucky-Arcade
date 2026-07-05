# PostgreSQL migration guide

## New production database

Use the values from `.env.production.example`:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=lucky_arcade
DB_USERNAME=lucky_arcade
DB_PASSWORD=replace-me
```

Then run:

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan arcade:metrics --days=30
php artisan arcade:doctor --strict
```

## Existing SQLite data

Do not overwrite the working SQLite file.

1. Stop writes or place the application in maintenance mode.
2. Run `php artisan arcade:backup`.
3. Restore a copy into staging.
4. Migrate the schema to an empty PostgreSQL database.
5. Transfer data with a database migration tool such as `pgloader`, preserving primary keys and foreign-key order.
6. Reset PostgreSQL sequences after importing explicit IDs.
7. Verify row counts for users, wallets, ledger entries and game entries.
8. Run `php artisan wallets:reconcile`.
9. Run `php artisan arcade:metrics --days=365`.
10. Run the full test suite and application smoke tests before changing production traffic.

Because the schema contains ledgers, fairness seeds and idempotency keys, a direct unverified data copy is not considered complete. Wallet reconciliation and duplicate-key checks are mandatory.

## Concurrency

The bet settlement action locks the player, game, wallet and fairness seed rows inside one database transaction. Keep all wallet mutations behind row locks and retain unique idempotency keys when adding future reward paths.
