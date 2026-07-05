# Architecture v0.5

## Runtime

- Laravel 13 / PHP 8.3+
- Blade, CSS và JavaScript thuần
- SQLite cho Codespaces demo; PostgreSQL là mục tiêu production
- Session, cache và queue dùng database ở môi trường demo

## Các domain chính

- `GameEngines`: Dice, Roulette, Coin Flip, High Low, Slots.
- `PlaceBetAction`: idempotency, row lock, stake debit, payout credit, referral, achievements và mission progress.
- `FairnessSeedService`: server seed, client seed, nonce và reveal/rotate.
- `MissionService`: tạo daily mission theo ngày và tính progress từ game entries.
- `ClaimMissionRewardAction`: claim một lần qua wallet ledger.
- `AnnouncementService`: lọc announcement đang active trong khung thời gian.
- `ReconcileWallets`: đối chiếu balance hiện tại với ledger.
- `BackupArcade`: sao lưu SQLite vào `storage/app/backups`.

## Nguyên tắc dữ liệu

- Mọi thay đổi balance đều phải có ledger entry.
- Kết quả game không do frontend hoặc admin quyết định.
- Request chơi có UUID idempotency.
- Wallet, user, game và fairness seed được lock trong transaction.
- Reward daily/referral/achievement/mission có idempotency key riêng.
