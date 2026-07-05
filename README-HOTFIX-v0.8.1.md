# Lucky Arcade v0.8.1 hotfix

Sửa test `ProductionOperationsTest::system_page_exposes_queue_and_readiness_information`.

Test cũ phụ thuộc vào chuỗi HTML được render bằng `assertSee()`. Bản sửa kiểm tra:

- response HTTP thành công;
- đúng Blade view;
- đủ dữ liệu queue/readiness;
- tiêu đề hiển thị bằng `assertSeeText()`;
- collection `operationRuns` thực sự chứa task `metrics.refresh`.

Bản vá không thay đổi database, wallet, game engine hoặc dữ liệu người dùng.
