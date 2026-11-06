# Test cases thủ công (phần tài khoản và quản trị người dùng)

Môi trường: XAMPP, Chrome. Tài khoản mẫu: admin@example.com / Admin@123, user@example.com / User@123.

| # | Chức năng | Các bước | Kết quả mong đợi | Kết quả thực tế |
|---|-----------|----------|------------------|-----------------|
| 1 | Đăng ký, mật khẩu ngắn | Nhập mật khẩu `abc123` | Báo tối thiểu 8 ký tự | Đạt |
| 2 | Đăng ký, mật khẩu thiếu chữ hoa/số | Nhập `abcdefgh` | Báo phải có chữ hoa, chữ thường, chữ số | Đạt |
| 3 | Đăng ký hợp lệ | Email mới, mật khẩu `Abcdefg1` | Tạo được tài khoản và tự đăng nhập | Đạt |
| 4 | Đăng ký, email trùng | Dùng lại email đã có | Báo email đã đăng ký | Đạt |
| 5 | Đăng nhập sai mật khẩu | Nhập sai mật khẩu | Báo lỗi, không vào được | Đạt |
| 6 | Đăng nhập đúng | Dùng tài khoản mẫu | Vào được hệ thống | Đạt |
| 7 | Đăng xuất bằng nút | Bấm nút Đăng xuất | Mất phiên, mở profile.php bị chuyển về đăng nhập | Đạt |
| 8 | Đăng xuất bằng đường link | Đang đăng nhập, mở thẳng /logout.php | Vẫn còn đăng nhập | Đạt |
| 9 | Admin tìm người dùng | Ô tìm kiếm gõ `an` | Chỉ hiện Nguyễn Văn An | Đạt |
| 10 | Admin tìm không có kết quả | Gõ `zzz` | Hiện "Không tìm thấy người dùng." | Đạt |
| 11 | Phân trang người dùng | Số người dùng ≤ 5 | Không hiện nút số trang | Đạt |
| 12 | Phân quyền | Đăng nhập user thường, mở admin/users.php | Bị chuyển về đăng nhập, báo không có quyền | Đạt |
| 13 | Cookie phiên | F12 → Application → Cookies | PHPSESSID có HttpOnly và SameSite=Lax | Đạt |