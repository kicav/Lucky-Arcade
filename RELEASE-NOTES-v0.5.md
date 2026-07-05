# Lucky Arcade v0.5 Release Notes

v0.5 mở rộng MVP theo hai hướng: tăng vòng lặp tương tác cho người chơi và bổ sung công cụ vận hành cho admin.

## Điểm nổi bật

- Lucky Slots với kết quả deterministic và xác minh lại bằng seed đã reveal.
- Daily missions có progress, claim reward một lần và ledger đầy đủ.
- Player Statistics giúp kiểm tra plays, wins, stake, payout, net và RTP quan sát.
- Announcements hiển thị toàn site, hỗ trợ khoảng thời gian và audit.
- SQLite backup command có retention.

## Migration mới

- `2026_07_05_000014_create_user_missions_table`
- `2026_07_05_000015_create_announcements_table`

## Sau nâng cấp

```bash
cd lucky-arcade-app
php artisan migrate:status
php artisan test
php artisan wallets:reconcile
php artisan arcade:backup --keep=10
```
