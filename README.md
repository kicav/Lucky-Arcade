# Lucky Arcade — Social Casino dùng coin ảo

Lucky Arcade là bộ mã nguồn khởi đầu cho một nền tảng trò chơi may rủi **chỉ sử dụng credit ảo, không nạp/rút và không quy đổi thành tiền**. Dự án được thiết kế theo kiến trúc Laravel monolith, có giao diện người chơi, dashboard quản trị, ví credit theo sổ cái và cơ chế kết quả có thể kiểm chứng.

## Phạm vi phiên bản MVP

- Đăng ký, đăng nhập và đăng xuất.
- Mỗi tài khoản mới nhận 10.000 credit dùng thử.
- Ví credit có `ledger_entries`; không sửa số dư mà không ghi sổ cái.
- Dice: chọn `under/over`, ngưỡng 2–98, house edge 1%.
- European Roulette: straight, red/black, odd/even, low/high và dozen.
- Provably fair bằng HMAC-SHA256, server-seed hash, client seed và nonce.
- Trang kiểm tra/đổi seed; seed cũ được công khai sau khi rotate.
- Dashboard người chơi, lịch sử lượt chơi và lịch sử ví.
- Dashboard admin chỉ xem thống kê, bật/tắt game và sửa giới hạn cược.
- Audit log cho thay đổi cấu hình game.
- Không có payment gateway, withdrawal, sportsbook hoặc chức năng chọn người thắng.

## Công nghệ

- PHP 8.3+
- Laravel 13
- MySQL/PostgreSQL/SQLite
- Blade + CSS thuần, không bắt buộc Node.js
- PHPUnit

## Cài đặt nhanh trên Windows

Yêu cầu: PHP 8.3+, Composer và extension SQLite.

Mở PowerShell trong thư mục này:

```powershell
Set-ExecutionPolicy -Scope Process Bypass
.\setup-windows.ps1
```

Script sẽ tạo thư mục `lucky-arcade-app`, cài Laravel 13, chép phần triển khai, tạo SQLite, migrate và seed dữ liệu.

Sau đó:

```powershell
cd lucky-arcade-app
php artisan serve
```

Truy cập `http://127.0.0.1:8000`.

Tài khoản mẫu:

- Admin: `admin@example.com` / `ChangeMe123!`
- Người chơi: `demo@example.com` / `Demo123!`

**Phải đổi mật khẩu admin trước khi public.**

## Cài đặt thủ công

```bash
composer create-project laravel/laravel:^13.0 lucky-arcade-app
cp -R overlay/. lucky-arcade-app/
cd lucky-arcade-app
cp .env.example .env
php artisan key:generate
```

Với SQLite:

```bash
touch database/database.sqlite
```

Cập nhật `.env`:

```env
APP_NAME="Lucky Arcade"
DB_CONNECTION=sqlite
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

Khởi tạo:

```bash
php artisan migrate --seed
php artisan test
php artisan serve
```

## Luồng một lượt chơi

1. Backend kiểm tra game, giới hạn cược và request id.
2. Transaction database được mở.
3. Wallet và fairness seed bị khóa bằng `lockForUpdate()`.
4. Engine tạo kết quả từ server seed, client seed và nonce.
5. Game entry được tạo.
6. Credit cược bị trừ và ghi ledger.
7. Nếu thắng, payout được cộng và ghi ledger riêng.
8. Nonce tăng đúng một đơn vị.
9. Transaction commit; lỗi ở bất kỳ bước nào sẽ rollback.

## Cấu trúc chính

```text
app/
├── Actions/Games/PlaceBetAction.php
├── Contracts/GameEngine.php
├── DTO/GameOutcome.php
├── GameEngines/
│   ├── DiceEngine.php
│   └── RouletteEngine.php
├── Services/
│   ├── FairnessSeedService.php
│   ├── GameEngineRegistry.php
│   └── ProvablyFairService.php
├── Models/
└── Http/Controllers/
```

## Nguyên tắc an toàn

- Chỉ dùng credit ảo, không có giá trị quy đổi.
- Không thêm nạp/rút trước khi có đánh giá pháp lý độc lập.
- Không cho admin sửa kết quả hoặc số dư trực tiếp.
- Mọi điều chỉnh credit phải là ledger entry bù trừ.
- Giữ `APP_DEBUG=false` trên production.
- Bắt buộc HTTPS, backup, rate limiting, 2FA admin và monitoring khi triển khai thật.

## Nguồn học hỏi

Dự án này được viết mới, không sao chép mã nguồn của các repo tham khảo. Chi tiết nằm trong `docs/OPEN_SOURCE_REFERENCES.md`.

## License

MIT. Xem `LICENSE`.

## Chạy không cần cài đặt trên Windows

Repository có sẵn cấu hình `.devcontainer` cho GitHub Codespaces. Xem hướng dẫn trong `CLOUD-RUN.md`. Sau khi Codespace mở, chạy:

```bash
./run-codespaces.sh
```
