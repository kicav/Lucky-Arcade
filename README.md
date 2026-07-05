# Lucky Arcade v0.2

Lucky Arcade là nền tảng **social gaming chỉ dùng credit ảo**, được xây bằng Laravel 13. Credit không thể nạp, rút hoặc quy đổi thành tiền.

## Chức năng hiện có

### Nền tảng v0.1

- Đăng ký, đăng nhập, đăng xuất và session.
- Ví credit với sổ cái bất biến (`ledger_entries`).
- Dice và European Roulette.
- Kết quả HMAC-SHA256 với server seed, client seed và nonce.
- Dashboard người chơi và quản trị.
- Bật/tắt game, giới hạn cược và audit log.

### Bổ sung trong v0.2

- Daily reward 250 credit, chỉ nhận một lần mỗi ngày.
- Leaderboard theo số dư và tổng net thắng/thua.
- Trình xác minh lượt chơi sau khi server seed được công khai.
- Xuất sổ cái cá nhân thành CSV.
- Lịch sử lượt chơi cho admin, có bộ lọc.
- Trang audit log cho admin.
- Lệnh kiểm tra tính nhất quán ví: `php artisan wallets:reconcile`.
- Rate limit cho đăng nhập, đăng ký, đặt cược và nhận thưởng.
- Animation nhẹ cho Dice và Roulette, không cần Node.js.
- Sửa URL/proxy cho GitHub Codespaces.
- GitHub Actions kiểm thử overlay tự động.

## Chạy mới trên GitHub Codespaces

1. Đưa toàn bộ nội dung repository lên GitHub, bao gồm `.devcontainer`.
2. Chọn **Code → Codespaces → Create codespace on main**.
3. Trong Terminal:

```bash
bash setup-linux.sh
bash run-codespaces.sh
```

4. Mở **PORTS → 8000 → Open in Browser**.

Tài khoản mẫu:

- Player: `demo@example.com` / `Demo123!`
- Admin: `admin@example.com` / `ChangeMe123!`

Đổi mật khẩu admin trước khi công khai cổng.

## Nâng cấp từ v0.1 đang chạy

Sao lưu hoặc commit repository trước. Sau khi chép nội dung v0.2 vào repository:

```bash
cd /workspaces/Lucky-Arcade
git pull
bash upgrade-v0.2.sh
bash run-codespaces.sh
```

Script nâng cấp sẽ:

1. Sao lưu `database/database.sqlite`.
2. Chép overlay v0.2 vào ứng dụng hiện tại.
3. Giữ nguyên `.env`, người dùng, ví và lịch sử chơi.
4. Chạy migration mới.
5. Xóa cache và chạy test.

## Kiểm tra sau nâng cấp

```bash
cd /workspaces/Lucky-Arcade/lucky-arcade-app
php artisan route:list
php artisan wallets:reconcile
php artisan test
```

Các đường dẫn mới:

- `/leaderboard`
- `/ledger/export`
- `/admin/entries`
- `/admin/audit-logs`

## Luồng fairness

1. Người chơi thấy hash của server seed trước khi chơi.
2. Lượt chơi lưu client seed, nonce và hash.
3. Khi rotate seed, server seed cũ được công khai.
4. Trang Fairness chạy lại đúng game engine với dữ liệu lịch sử.
5. Hash, kết quả thắng/thua và payout đều phải khớp.

## Cấu trúc repository

```text
.devcontainer/          Môi trường GitHub Codespaces
overlay/                Mã nguồn chép lên Laravel sạch
docs/                   Kiến trúc và roadmap
setup-linux.sh          Cài mới
upgrade-v0.2.sh         Nâng cấp dữ liệu hiện tại
run-codespaces.sh       Chạy server cổng 8000
```

## Nguyên tắc an toàn

- Chỉ dùng virtual credits, không có giá trị tiền mặt.
- Admin không được sửa kết quả hoặc chọn người thắng.
- Mọi thay đổi số dư phải tạo ledger entry.
- Không commit `.env`, SQLite, `vendor` hoặc mật khẩu thật.
- Codespaces chỉ phù hợp phát triển và demo, không phải hosting 24/7.

## License

MIT. Xem `LICENSE`.
