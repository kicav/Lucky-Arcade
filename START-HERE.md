# Lucky Arcade v0.4 — Bắt đầu nhanh

## Cài mới trên GitHub Codespaces

```bash
bash setup-linux.sh
bash run-codespaces.sh
```

## Nâng cấp từ v0.3

1. Dừng server bằng `Ctrl+C`.
2. Chép nội dung gói update v0.4 vào thư mục gốc repository.
3. Chạy:

```bash
chmod +x upgrade-v0.4.sh run-codespaces.sh
bash upgrade-v0.4.sh
bash run-codespaces.sh
```

Script tự sao lưu SQLite trước migration.

## Kiểm tra nhanh

- Chơi High Low.
- Mở Achievements và kiểm tra First Steps.
- Mở Referrals, copy link và tạo tài khoản thử nghiệm.
- Cho tài khoản mới chơi một lượt để kích hoạt referral reward.
- Đăng nhập admin và mở `/admin/analytics`.

## Tài khoản mẫu

- Player: `demo@example.com` / `Demo123!`
- Admin: `admin@example.com` / `ChangeMe123!`

Đổi mật khẩu admin trước khi chuyển port sang Public.
