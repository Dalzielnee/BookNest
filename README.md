# BookNest

Website bán sách trực tuyến xây dựng bằng PHP thuần và MySQL.
Bài tập lớn môn 503073 - Lập trình Web và Ứng dụng.

## Công nghệ

- PHP 8 (không dùng framework), PDO
- MySQL / MariaDB
- HTML, CSS, JavaScript thuần
- Chạy trên XAMPP

## Chức năng

**Khách hàng**
- Xem trang chủ, danh sách sách (lọc theo danh mục, tìm kiếm), chi tiết sách
- Giỏ hàng, thanh toán, xem và hủy đơn hàng
- Đăng ký (mật khẩu tối thiểu 8 ký tự, có chữ hoa, chữ thường, chữ số), đăng nhập, đăng xuất, cập nhật hồ sơ

**Quản trị**
- Tổng quan, quản lý sách, danh mục, đơn hàng, người dùng

## Chạy thử nhanh

1. Chép thư mục dự án vào `C:\xampp\htdocs\`, bật Apache và MySQL trong XAMPP.
2. Vào `http://localhost/phpmyadmin`, Import file `database.sql`.
3. Mở `http://localhost/<đường dẫn thư mục dự án>/`.

Hướng dẫn chi tiết xem file [README.txt](README.txt).

## Tài khoản mẫu

| Vai trò | Email | Mật khẩu |
|---------|-------|----------|
| Quản trị | admin@example.com | Admin@123 |
| Khách | user@example.com | User@123 |

## Cấu trúc thư mục

| Thư mục / file | Nội dung |
|----------------|----------|
| `admin/` | Các trang quản trị |
| `assets/` | CSS, JavaScript, ảnh mặc định |
| `includes/` | Header, footer, hàm dùng chung, kết nối CSDL |
| `uploads/` | Ảnh bìa sách tải lên |
| `docs/` | Tài liệu, gồm `test-cases.md` |
| `database.sql` | Cấu trúc và dữ liệu mẫu |

## Danh sách thành viên:
1. Hoàng Xuân Tuấn - 52400166
2. Chiêm Tân Quý -