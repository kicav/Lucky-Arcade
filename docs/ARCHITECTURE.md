# Kiến trúc Lucky Arcade

## Mô hình triển khai

```text
Browser
  │
  ▼
Nginx/Apache
  │
  ▼
Laravel 13 monolith
  ├── Auth + User dashboard
  ├── Admin dashboard
  ├── Wallet/Ledger
  ├── Game engines
  ├── Provably fair
  └── Audit logs
  │
  ├── SQL database
  ├── Redis (production, tùy chọn)
  └── Queue worker (production, tùy chọn)
```

## Ranh giới domain

### Identity

`User`, authentication và quyền admin.

### Wallet

`Wallet` là snapshot số dư hiện tại. `LedgerEntry` là nguồn truy vết cho mọi thay đổi. Hai bản ghi phải được cập nhật trong cùng transaction.

### Fairness

Mỗi người dùng có một seed đang hoạt động:

- `server_seed`: bí mật, lưu bằng encrypted cast.
- `server_seed_hash`: công khai trước khi chơi.
- `client_seed`: công khai và có thể thay đổi khi rotate.
- `nonce`: tăng sau mỗi lượt.

Khi rotate, server seed cũ được reveal để người dùng kiểm tra lại lịch sử.

### Games

Mỗi game triển khai `GameEngine` và chỉ nhận dữ liệu thuần. Engine không truy cập database, session hay HTTP.

### Administration

Admin chỉ được:

- Xem thống kê.
- Bật/tắt game.
- Sửa min/max bet.

Không có API chọn người thắng, sửa kết quả hoặc cập nhật wallet trực tiếp.

## Idempotency

Mỗi lượt chơi cần `request_id` UUID. Cặp `(user_id, request_id)` là duy nhất. Khi trình duyệt gửi lại cùng request, hệ thống trả về game entry cũ và không trừ credit lần hai.

## v0.4 growth services

- `ReferralCodeService`: creates unique player referral codes.
- `ReferralRewardService`: awards both parties after the referred player's first settled game.
- `AchievementService`: evaluates progress, unlocks achievements and writes reward ledger entries.
- `HighLowEngine`: stateless provably-fair game engine registered through `GameEngineRegistry`.
- `Admin\\AnalyticsController`: read-only operational aggregation for the admin dashboard.

All virtual-credit changes continue to produce immutable ledger entries. Referral and achievement rewards are idempotent through database unique constraints and ledger idempotency keys.
