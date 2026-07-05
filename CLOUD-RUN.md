# Chạy trên GitHub Codespaces

Repository sử dụng image PHP 8.3 chính thức và không cần cài PHP/Composer trên Windows.

## Tạo Codespace

1. Đảm bảo repository có `.devcontainer/devcontainer.json`.
2. Chọn **Code → Codespaces → Create codespace on main**.
3. Kiểm tra:

```bash
php -v
composer --version
php -m | grep -Ei 'pdo_sqlite|sqlite3'
```

4. Cài mới:

```bash
bash setup-linux.sh
bash run-codespaces.sh
```

Hoặc nâng cấp:

```bash
bash upgrade-v0.4.sh
bash run-codespaces.sh
```

## Mở website

Không truy cập `localhost:8000` trên Windows. Mở **PORTS → 8000 → Open in Browser**. Laravel đã được cấu hình tin cậy proxy của Codespaces và tự đặt `APP_URL` theo tên Codespace.

## Lỗi cổng 8000 đang dùng

```bash
pkill -f "artisan serve" || true
bash run-codespaces.sh
```

## Dừng server

Nhấn `Ctrl+C` trong Terminal đang chạy server.
