# Lucky Arcade v0.5

Lucky Arcade là nền tảng **social gaming dùng virtual credits**, viết bằng Laravel. Credits không có giá trị tiền mặt; dự án không có nạp/rút, crypto hoặc payment gateway.

## Chức năng người chơi

- Đăng ký, đăng nhập, wallet ledger bất biến và lịch sử chơi.
- Dice, European Roulette, Coin Flip, High Low và Lucky Slots.
- Provably-fair HMAC-SHA256, seed rotation và xác minh lượt chơi lịch sử.
- Daily reward, leaderboard, daily missions và xuất ledger CSV.
- Trang Player Statistics theo ngày và theo từng game.
- Account/profile, đổi mật khẩu, daily stake limit và self-exclusion.
- Notification center, achievements và referral reward sau first play.

## Chức năng quản trị

- Dashboard, game settings, play history và audit log.
- Tìm kiếm, xem chi tiết, suspend/reactivate người chơi.
- Cấp promotional credits thông qua ledger transaction có lý do.
- Analytics 14 ngày, hiệu suất theo game, RTP quan sát và system net.
- Tạo, lên lịch, bật/tắt và xóa announcement hiển thị toàn site.
- Command sao lưu SQLite có cơ chế giữ N bản gần nhất.
- Không có chức năng chọn người thắng hoặc sửa kết quả game.

## Cài mới trong GitHub Codespaces

```bash
bash setup-linux.sh
bash run-codespaces.sh
```

Mở tab **PORTS**, chọn cổng `8000`, rồi **Open in Browser**.

## Nâng cấp từ v0.4

```bash
bash upgrade-v0.5.sh
bash run-codespaces.sh
```

## Kiểm thử và vận hành

```bash
cd lucky-arcade-app
XDEBUG_MODE=off php artisan test
XDEBUG_MODE=off php artisan wallets:reconcile
XDEBUG_MODE=off php artisan arcade:backup --keep=10
```

## Tài khoản mẫu

- Player: `demo@example.com` / `Demo123!`
- Admin: `admin@example.com` / `ChangeMe123!`

## Giới hạn

Đây là MVP học tập và demo. Trước production cần PostgreSQL, Redis queue/cache/lock, backup ngoài máy chủ, email verification, 2FA cho admin, permission chi tiết, observability và security review độc lập.
