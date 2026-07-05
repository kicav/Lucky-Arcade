# Lucky Arcade v0.6

Lucky Arcade is a Laravel social-gaming demo that uses virtual credits only. It has no deposits, withdrawals or cash value.

## v0.6 highlights

- Promo-code redemption with ledger, notifications, limits and admin audit logs.
- Player/admin support-ticket workflow with threaded replies and statuses.
- Weekly League with transparent scoring and idempotent top-three virtual-credit settlement.
- Includes the v0.5.1 SQLite daily-mission synchronization fix.
- Existing Dice, Roulette, Coin Flip, High Low, Lucky Slots, missions, achievements, referrals, analytics and backups remain available.

## Existing project upgrade

```bash
bash upgrade-v0.6.sh
bash run-codespaces.sh
```

## Fresh Codespaces install

```bash
bash setup-linux.sh
bash run-codespaces.sh
```

Demo player: `demo@example.com` / `Demo123!`

Demo admin: `admin@example.com` / `ChangeMe123!`

Change demo passwords before making a forwarded port public.
