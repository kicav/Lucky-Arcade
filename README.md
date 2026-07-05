# Lucky Arcade v0.4

Lucky Arcade là nền tảng **social gaming dùng virtual credits**, viết bằng Laravel. Credits không có giá trị tiền mặt; dự án không có nạp/rút, crypto hoặc payment gateway.

## Chức năng người chơi

- Đăng ký, đăng nhập, wallet ledger bất biến và lịch sử chơi.
- Dice, European Roulette, Coin Flip và High Low.
- Provably-fair HMAC-SHA256, seed rotation và xác minh lượt chơi lịch sử.
- Daily reward, leaderboard và xuất ledger CSV.
- Account/profile, đổi mật khẩu, daily stake limit và self-exclusion.
- Notification center.
- Achievement có progress và reward credit ảo.
- Referral link/code; hai tài khoản chỉ được thưởng sau lượt chơi đầu tiên của người được giới thiệu.

## Chức năng quản trị

- Dashboard, game settings, play history và audit log.
- Tìm kiếm, xem chi tiết, suspend/reactivate người chơi.
- Cấp promotional credits thông qua ledger transaction có lý do.
- Analytics 14 ngày, hiệu suất theo game, RTP quan sát và system net.
- Không có chức năng chọn người thắng hoặc sửa kết quả game.

## Cài mới trong GitHub Codespaces

```bash
bash setup-linux.sh
bash run-codespaces.sh
```

Mở tab **PORTS**, chọn cổng `8000`, rồi **Open in Browser**.

## Nâng cấp từ v0.3

```bash
bash upgrade-v0.4.sh
bash run-codespaces.sh
```

## Kiểm thử

```bash
cd lucky-arcade-app
XDEBUG_MODE=off php artisan test
XDEBUG_MODE=off php artisan wallets:reconcile
```

## Tài khoản mẫu

- Player: `demo@example.com` / `Demo123!`
- Admin: `admin@example.com` / `ChangeMe123!`

## Giới hạn

Đây là MVP học tập và demo. Trước production cần PostgreSQL, Redis queue/cache/lock, backup tự động, email verification, 2FA cho admin, permission chi tiết, observability và security review độc lập.
