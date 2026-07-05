# Start here — Lucky Arcade v0.7

## Upgrade from v0.6

1. Stop the Laravel server with `Ctrl+C`.
2. Copy this update into the repository root.
3. Run:

```bash
chmod +x upgrade-v0.7.sh run-codespaces.sh
bash upgrade-v0.7.sh
bash run-codespaces.sh
```

4. Open port 8000 from the Codespaces **PORTS** tab.

The upgrade creates a timestamped SQLite backup before migration, runs all tests, reconciles wallets and creates an operational backup.
