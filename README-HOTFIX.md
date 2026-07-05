# Lucky Arcade v0.5.1 mission hotfix

This fixes repeated `user_missions` inserts on SQLite caused by direct equality comparisons against a date-cast attribute.

## Apply

From the Lucky-Arcade repository root:

```bash
unzip -o lucky-arcade-v0.5.1-hotfix.zip
cp -a lucky-arcade-v0.5.1-hotfix/. .
rm -rf lucky-arcade-v0.5.1-hotfix
chmod +x apply-hotfix-v0.5.1.sh
bash apply-hotfix-v0.5.1.sh
```

The script updates both the persistent overlay source and the currently generated Laravel application, clears caches, and runs the full test suite.
