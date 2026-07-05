# Changelog

## v0.3.0 — Player controls and operations

- Thêm game Coin Flip dùng HMAC-SHA256 và cơ chế seed hiện có.
- Thêm trang Account để đổi tên, đổi mật khẩu, đặt giới hạn cược theo ngày và tự loại trừ 1/7/30 ngày.
- Chặn đặt cược khi tài khoản bị đình chỉ, đang tự loại trừ hoặc vượt giới hạn ngày.
- Thêm trung tâm thông báo và badge chưa đọc.
- Tạo thông báo cho daily reward, big win và promotional credits.
- Admin có trang chi tiết người chơi, đình chỉ/kích hoạt lại và cấp promotional credits qua immutable ledger.
- Mọi thao tác admin nhạy cảm đều ghi audit log.
- Thêm tìm kiếm người chơi trong admin.
- Thêm test cho Coin Flip, play controls và admin promotional grant.

## v0.2.0

- Daily reward, leaderboard, fairness verifier, CSV ledger export, admin entries, audit log và wallet reconciliation.

## v0.1.0

- Laravel social-gaming MVP với Dice, European Roulette, virtual-credit wallet và admin dashboard.
