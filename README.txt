BOOKNEST - Website bán sách (PHP thuần + MySQL)
Môn 503073 - Web Programming and Applications

1. YÊU CẦU
- XAMPP (Apache + MySQL/MariaDB), PHP 8.0 trở lên.
- Trình duyệt hiện đại (Chrome, Edge).

2. CÀI ĐẶT VÀ CHẠY BẰNG XAMPP
 1. Chép thư mục dự án vào C:\xampp\htdocs\
    (ví dụ C:\xampp\htdocs\bookstore\BookNest).
 2. Mở XAMPP Control Panel, bấm Start cho Apache và MySQL.
 3. Mở http://localhost/phpmyadmin -> tab Import -> chọn file database.sql -> Go.
    File tự tạo database "bookstore". Lưu ý: import lại sẽ XÓA dữ liệu cũ.
 4. Nếu MySQL có mật khẩu: sửa db_user, db_pass trong config.php.
 5. Mở http://localhost/bookstore/BookNest/
    (đường dẫn thay đổi theo nơi bạn đặt thư mục).

Chạy nhanh bằng PHP built-in server (trong thư mục dự án):
    php -S localhost:8080
rồi mở http://localhost:8080/

3. TÀI KHOẢN MẪU
- Quản trị: admin@example.com / Admin@123  (vào /admin/)
- Khách:    user@example.com  / User@123

4. CHỨC NĂNG
Khách hàng:
- Xem trang chủ, danh sách sách (lọc theo danh mục, tìm kiếm), chi tiết sách.
- Giỏ hàng (thêm, đổi số lượng, xóa), thanh toán, xem và hủy đơn hàng.
- Đăng ký (mật khẩu tối thiểu 8 ký tự, có chữ hoa, chữ thường, chữ số),
  đăng nhập, đăng xuất, cập nhật hồ sơ.
Quản trị:
- Tổng quan, quản lý sách, danh mục, đơn hàng (đổi trạng thái),
  người dùng (tìm kiếm, phân trang, đổi quyền).

5. BẢO MẬT
- Truy vấn dùng PDO prepared statements.
- Mật khẩu băm bcrypt (password_hash).
- Form POST có CSRF token; đăng xuất dùng POST.
- Cookie phiên đặt HttpOnly và SameSite=Lax.
- Dữ liệu xuất ra HTML được escape (htmlspecialchars).

6. CẤU TRÚC THƯ MỤC
- admin/     các trang quản trị
- assets/    CSS, JavaScript, ảnh mặc định
- includes/  header, footer, hàm dùng chung, kết nối CSDL
- uploads/   ảnh bìa sách tải lên
- docs/      tài liệu (test-cases.md)
- database.sql  cấu trúc và dữ liệu mẫu

7. THÀNH VIÊN
Xem README.md.