# Các dự án mã nguồn mở dùng để học hỏi

## 1. Laravel Social Gaming

Repository: https://github.com/promexdotme/laravel-social-gaming

Điểm đáng học:

- Cách tổ chức một ứng dụng social gaming bằng Laravel.
- Phân tách giao diện người chơi và back-office.
- Quản lý trạng thái game, game lobby và tài nguyên game.

Điểm không đưa vào Lucky Arcade:

- Payment gateway, crypto/fiat và sportsbook.
- Phụ thuộc CDN/socket/tài nguyên bên ngoài.
- Chức năng operator khai báo kết quả.

Lưu ý giấy phép: tại thời điểm rà soát, repository công khai không hiển thị rõ file `LICENSE` ở thư mục gốc. Vì vậy Lucky Arcade chỉ học ý tưởng kiến trúc, không sao chép code hoặc asset.

## 2. Cassanova

Repository: https://github.com/GizzZmo/Cassanova

Điểm đáng học:

- Game lobby, dashboard, transaction history và bố cục responsive.
- Phân tách frontend/backend và tài liệu dự án.
- Tư duy CI, security policy và roadmap.

Repo dùng MIT nhưng Lucky Arcade vẫn tự viết giao diện và backend để tránh biến dự án thành bản clone.

## 3. ts-roulette-engine

Repository: https://github.com/NivBuskila/ts-roulette-engine

Điểm đáng học:

- Clean Architecture cho game logic.
- Tách engine khỏi frontend.
- HMAC-SHA256, kiểm thử luật cược và payout.
- European roulette với các loại cược chuẩn.

Lucky Arcade triển khai lại thuật toán bằng PHP và API nội bộ Laravel.

## 4. Offline Casino

Repository: https://github.com/Piotrunius/Offline-Casino

Điểm đáng học:

- Danh mục game sử dụng virtual currency.
- Giao diện đơn giản, responsive và chạy không cần thanh toán.
- Cách mô tả rõ đây là sản phẩm giải trí, không dùng tiền thật.

## Quy tắc sử dụng nguồn mở

- Chỉ copy code khi license cho phép và phải giữ copyright/license tương ứng.
- Không lấy hình ảnh, âm thanh hoặc asset chỉ vì repository có thể xem công khai.
- Repository không có license được xem là “all rights reserved” về mặt thực hành.
- Ghi attribution trong `NOTICE` khi đưa mã nguồn bên thứ ba vào dự án.
