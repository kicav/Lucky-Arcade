# Lucky Arcade v0.3 — Bắt đầu nhanh

## Cài mới trên GitHub Codespaces

```bash
bash setup-linux.sh
bash run-codespaces.sh
```

## Nâng cấp từ v0.2

1. Dừng server bằng `Ctrl+C`.
2. Chép nội dung gói update v0.3 vào thư mục gốc repository.
3. Chạy:

```bash
chmod +x upgrade-v0.3.sh run-codespaces.sh
bash upgrade-v0.3.sh
bash run-codespaces.sh
```

Script tự sao lưu SQLite trước migration.

## Tài khoản mẫu

- Player: `demo@example.com` / `Demo123!`
- Admin: `admin@example.com` / `ChangeMe123!`

Đổi mật khẩu admin trước khi chuyển port sang Public.
