# Chạy Lucky Arcade hoàn toàn trên trình duyệt bằng GitHub Codespaces

## 1. Đưa mã nguồn lên GitHub

1. Tạo một repository mới trên GitHub.
2. Giải nén gói này trên máy.
3. Dùng **Add file > Upload files** để tải toàn bộ nội dung bên trong thư mục lên repository.
4. Bảo đảm `.devcontainer/devcontainer.json` và `.devcontainer/Dockerfile` tồn tại ở thư mục gốc repository.

## 2. Tạo môi trường Codespaces

1. Mở repository.
2. Chọn **Code > Codespaces > Create codespace on main**.
3. GitHub sẽ tự tạo máy Linux có PHP 8.3, Composer và SQLite.

## 3. Cài và chạy ứng dụng

Trong terminal của Codespaces chạy:

```bash
./run-codespaces.sh
```

Lần đầu, script sẽ tự:

- tạo Laravel 13 trong `lucky-arcade-app`;
- chép mã nguồn Lucky Arcade;
- tạo SQLite;
- chạy migration và seed;
- chạy kiểm thử;
- mở web server tại cổng 8000.

Khi cổng 8000 xuất hiện, chọn **Open in Browser**.

## 4. Tài khoản mẫu

- Admin: `admin@example.com` / `ChangeMe123!`
- Player: `demo@example.com` / `Demo123!`

Đổi mật khẩu admin trước khi cho người khác truy cập.

## 5. Những lần chạy sau

Mở lại Codespace rồi chạy:

```bash
cd lucky-arcade-app
php artisan serve --host=0.0.0.0 --port=8000
```

## Lưu ý

Codespaces phù hợp để lập trình, kiểm thử và trình diễn ngắn hạn. Nó sẽ dừng khi không hoạt động và không thay thế dịch vụ hosting production chạy liên tục.
