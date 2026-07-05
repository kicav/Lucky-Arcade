# Lucky Arcade v0.4 release notes

## New modules

- High Low provably-fair game.
- Referral codes, referral links and first-play qualification rewards.
- Achievement progression with ledger-backed virtual-credit rewards.
- Admin analytics for 14-day activity and all-time game performance.

## Upgrade

```bash
bash upgrade-v0.4.sh
bash run-codespaces.sh
```

## Required verification after upgrade

```bash
cd lucky-arcade-app
XDEBUG_MODE=off php artisan migrate:status
XDEBUG_MODE=off php artisan test
XDEBUG_MODE=off php artisan wallets:reconcile
```

The package was statically checked with PHP syntax validation for all PHP files, Bash syntax validation, JavaScript syntax validation and JSON parsing. Full Laravel tests run during `upgrade-v0.4.sh` in Codespaces.
