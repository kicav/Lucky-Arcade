# Lucky Arcade v0.5 — Bắt đầu nhanh

## Cài mới trên GitHub Codespaces

```bash
bash setup-linux.sh
bash run-codespaces.sh
```

## Nâng cấp từ v0.4

1. Dừng server bằng `Ctrl+C`.
2. Chép nội dung gói update v0.5 vào thư mục gốc repository.
3. Chạy:

```bash
chmod +x upgrade-v0.5.sh run-codespaces.sh
bash upgrade-v0.5.sh
bash run-codespaces.sh
```

Script tự sao lưu SQLite, migrate, seed, chạy test, reconcile wallet và tạo một backup vận hành.

## Kiểm tra nhanh

- Chơi Lucky Slots và xác minh lượt chơi trong Fairness.
- Chơi ba lượt để hoàn thành mission `Warm-up round`, sau đó claim reward.
- Mở `Stats` để kiểm tra summary và thống kê từng game.
- Đăng nhập admin, mở `Announcements`, tạo một thông báo active.
- Chạy `php artisan arcade:backup --keep=10` và kiểm tra `storage/app/backups`.

## Tài khoản mẫu

- Player: `demo@example.com` / `Demo123!`
- Admin: `admin@example.com` / `ChangeMe123!`

Đổi mật khẩu admin trước khi chuyển port sang Public.
