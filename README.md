# Lucky Arcade v0.3

Lucky Arcade là nền tảng **social gaming dùng virtual credits**, viết bằng Laravel. Credits không có giá trị tiền mặt, không có nạp/rút hoặc payment gateway.

## Chức năng

### Người chơi

- Đăng ký, đăng nhập và wallet ledger bất biến.
- Dice, European Roulette và Coin Flip.
- Provably-fair HMAC-SHA256, seed rotation và historical verification.
- Daily reward, leaderboard và CSV ledger export.
- Account/profile, đổi mật khẩu.
- Daily stake limit và self-exclusion 1/7/30 ngày.
- Trung tâm thông báo cho reward, big win và credit promotion.

### Quản trị

- Dashboard, game settings, play history và audit log.
- Tìm kiếm và xem chi tiết người chơi.
- Suspend/reactivate tài khoản.
- Cấp promotional credits thông qua ledger transaction có lý do và audit record.
- Không có chức năng chọn người thắng hoặc sửa kết quả game.

## Chạy trong Codespaces

```bash
bash setup-linux.sh
bash run-codespaces.sh
```

Mở tab **PORTS**, chọn cổng `8000`, rồi **Open in Browser**.

## Nâng cấp từ v0.2

```bash
bash upgrade-v0.3.sh
bash run-codespaces.sh
```

## Kiểm thử

```bash
cd lucky-arcade-app
XDEBUG_MODE=off php artisan test
XDEBUG_MODE=off php artisan wallets:reconcile
```

## Giới hạn

Đây là MVP học tập và demo. Trước production cần PostgreSQL, queue worker, centralized logging, backup, CSRF/session hardening, email verification, 2FA, permission granular và security review độc lập.
