BOOKNEST - Website ban sach (PHP thuan + MySQL)

CHAY BANG XAMPP
1. Chep thu muc nay vao htdocs (hoac doi ten thanh thu muc goc cua virtual host).
2. Mo phpMyAdmin -> Import file database.sql (tu tao CSDL "bookstore").
3. Neu MySQL co mat khau: sua config.php (db_user, db_pass).
4. Mo http://localhost/  (khong can port/thu muc con, URL duoc tao tu dong).

CHAY NHANH BANG PHP BUILT-IN SERVER
   php -S localhost:8080        (chay trong thu muc nay)

TAI KHOAN MAU
   Admin : admin@example.com / Admin@123   -> /admin/
   User  : user@example.com  / User@123

FRONTEND (11 trang): index, products, product, cart, checkout, orders,
                     login, register, profile, logout (+ 404 san pham)
BACKEND  (5 muc menu): Tong quan, Quan ly sach, Danh muc, Don hang, Nguoi dung
