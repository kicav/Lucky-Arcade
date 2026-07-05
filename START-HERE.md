# Start here — Lucky Arcade v0.6

## Upgrade from v0.5/v0.5.1

1. Stop the Laravel server with `Ctrl+C`.
2. Copy this update into the repository root.
3. Run:

```bash
chmod +x upgrade-v0.6.sh run-codespaces.sh
bash upgrade-v0.6.sh
bash run-codespaces.sh
```

4. Open port 8000 from the Codespaces **PORTS** tab.

The upgrade creates a timestamped SQLite backup before migration.
