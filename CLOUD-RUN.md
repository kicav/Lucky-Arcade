# Chạy Lucky Arcade v0.7 trên cloud

## Phát triển

Dùng GitHub Codespaces và SQLite:

```bash
bash setup-linux.sh
bash run-codespaces.sh
```

## Hosting liên tục

Không dùng Codespaces như production host. Khi triển khai lâu dài:

1. Chuyển database sang PostgreSQL.
2. Đặt `APP_ENV=production`, `APP_DEBUG=false`.
3. Dùng Redis cho cache, queue và distributed lock.
4. Chạy queue worker và scheduler riêng.
5. Lưu backup ở object storage ngoài máy chủ.
6. Bắt buộc HTTPS, email verification và 2FA admin.
7. Chạy test, migration và wallet reconciliation trong CI/CD.

Command `arcade:backup` của v0.7 chỉ hỗ trợ SQLite và phù hợp demo/Codespaces.
