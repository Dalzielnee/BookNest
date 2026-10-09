-- Website bán sách - database MySQL / MariaDB
SET NAMES utf8mb4;
CREATE DATABASE IF NOT EXISTS bookstore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bookstore;

DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  address VARCHAR(255) DEFAULT NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  description VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NOT NULL,
  title VARCHAR(200) NOT NULL,
  author VARCHAR(120) NOT NULL,
  price DECIMAL(12,0) NOT NULL,
  stock INT NOT NULL DEFAULT 0,
  image VARCHAR(255) DEFAULT NULL,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  receiver VARCHAR(100) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  address VARCHAR(255) NOT NULL,
  note VARCHAR(255) DEFAULT NULL,
  total DECIMAL(14,0) NOT NULL,
  status ENUM('pending','confirmed','shipping','done','cancelled') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  title VARCHAR(200) NOT NULL,
  price DECIMAL(12,0) NOT NULL,
  quantity INT NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Tài khoản mẫu: admin@example.com / Admin@123  |  user@example.com / User@123
INSERT INTO users (full_name,email,password,phone,address,role) VALUES
('Quản trị viên','admin@example.com','$2y$10$hFVCvBzRKx/P5bFzg9DSz.pN6MS4nGhgXb0l3vxzjVyF6sPP7i8Ju','0900000001','1 Nguyễn Hữu Thọ, Q7, TP.HCM','admin'),
('Nguyễn Văn An','user@example.com','$2y$10$1KTJ.MoTunD8dXHNaexbtOvWptf1k149t3fWa8NoiDUWH7nYHfruq','0900000002','19 Nguyễn Hữu Thọ, Q7, TP.HCM','user');

INSERT INTO categories (name,description) VALUES
('Văn học','Tiểu thuyết, truyện ngắn, thơ'),
('Kinh tế','Kinh doanh, quản trị, tài chính'),
('Công nghệ thông tin','Lập trình, cơ sở dữ liệu, AI'),
('Kỹ năng sống','Phát triển bản thân'),
('Thiếu nhi','Truyện tranh, sách giáo dục trẻ em');

INSERT INTO products (category_id,title,author,price,stock,description) VALUES
(1,'Dế Mèn phiêu lưu ký','Tô Hoài',58000,40,'Tác phẩm văn học thiếu nhi kinh điển của Việt Nam, kể về cuộc phiêu lưu của chú dế mèn.'),
(1,'Số đỏ','Vũ Trọng Phụng',72000,25,'Tiểu thuyết trào phúng nổi tiếng phản ánh xã hội Việt Nam thập niên 1930.'),
(1,'Mắt biếc','Nguyễn Nhật Ánh',95000,60,'Câu chuyện tình yêu đơn phương của Ngạn dành cho Hà Lan.'),
(1,'Nhà giả kim','Paulo Coelho',79000,50,'Hành trình đi tìm kho báu của chàng chăn cừu Santiago.'),
(2,'Cha giàu cha nghèo','Robert Kiyosaki',110000,35,'Bài học về tiền bạc và tư duy tài chính.'),
(2,'Nghĩ giàu làm giàu','Napoleon Hill',99000,30,'Những nguyên tắc thành công từ nhiều doanh nhân nổi tiếng.'),
(2,'Khởi nghiệp tinh gọn','Eric Ries',130000,20,'Phương pháp xây dựng doanh nghiệp khởi nghiệp hiệu quả.'),
(3,'Clean Code','Robert C. Martin',320000,15,'Nghệ thuật viết mã sạch và dễ bảo trì.'),
(3,'Lập trình PHP & MySQL','Nhiều tác giả',185000,22,'Giáo trình xây dựng ứng dụng web với PHP và MySQL.'),
(3,'Cơ sở dữ liệu quan hệ','Nguyễn Văn Ba',150000,18,'Nền tảng thiết kế và truy vấn cơ sở dữ liệu quan hệ.'),
(4,'Đắc nhân tâm','Dale Carnegie',88000,70,'Nghệ thuật giao tiếp và ứng xử.'),
(4,'Atomic Habits','James Clear',165000,45,'Xây dựng thói quen tốt, loại bỏ thói quen xấu.'),
(5,'Doraemon tập 1','Fujiko F. Fujio',22000,100,'Truyện tranh thiếu nhi nổi tiếng về chú mèo máy.'),
(5,'Hoàng tử bé','Antoine de Saint-Exupéry',68000,55,'Câu chuyện triết lý nhẹ nhàng dành cho mọi lứa tuổi.');
