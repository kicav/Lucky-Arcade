# Lucky Arcade v0.7

Lucky Arcade is a Laravel social-gaming demo that uses virtual credits only. It has no deposits, withdrawals or cash value.

## v0.7 highlights

- Optional TOTP two-factor authentication for players and administrators.
- Single-use recovery codes and a two-factor login challenge.
- Security Center with login, password and two-factor event history.
- Administrator roles: Super Admin, Operations, Support and Analyst.
- Per-area admin authorization and an administrator-access management page.
- System Health dashboard with database, storage, failed-login and backup information.
- Security response headers for every web response.
- Existing games, missions, achievements, referrals, promo codes, support tickets, Weekly League, analytics and backups remain available.

## Existing project upgrade

```bash
bash upgrade-v0.7.sh
bash run-codespaces.sh
```

## Fresh Codespaces install

```bash
bash setup-linux.sh
bash run-codespaces.sh
```

Demo player: `demo@example.com` / `Demo123!`

Demo admin: `admin@example.com` / `ChangeMe123!`

Change demo passwords and enable two-factor authentication before making a forwarded port public.
