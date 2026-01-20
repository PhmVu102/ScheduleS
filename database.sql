-- ============================================================
-- DATABASE WEBSITE BÁN ĐIỆN THOẠI & LAPTOP (OPTIMIZED)
-- Fix lỗi Foreign Key & Tối ưu Index
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+07:00";

-- Xóa DB cũ nếu muốn làm mới (Bỏ comment dòng dưới nếu cần)
-- DROP DATABASE IF EXISTS schedules;

CREATE DATABASE IF NOT EXISTS schedules CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE schedules;

-- 1. USERS (Người dùng & Admin)
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    avatar VARCHAR(255) DEFAULT NULL, -- Thêm ảnh đại diện cho đẹp
    role TINYINT DEFAULT 0 COMMENT '0: User, 1: Admin',
    status TINYINT DEFAULT 1 COMMENT '1: Active, 0: Block',
    reset_token VARCHAR(255) NULL, -- Dùng cho quên mật khẩu
    reset_expiry DATETIME NULL,    -- Dùng cho quên mật khẩu
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. USER ADDRESSES (Sổ địa chỉ)
CREATE TABLE user_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    recipient_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    is_default TINYINT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. CATEGORIES (Danh mục: Laptop, Điện thoại...)
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    logo VARCHAR(255),
    status TINYINT DEFAULT 1,
    -- parent_id: INT, mặc định là 0, dùng để lưu trữ ID của danh mục cha (0 nếu là danh mục gốc)
    parent_id INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. BRANDS (Thương hiệu: Apple, Samsung...)
CREATE TABLE brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    logo VARCHAR(255),
    status TINYINT DEFAULT 1,
    INDEX (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. PRODUCTS (Sản phẩm chung)
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    brand_id INT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    type ENUM('other','phone') NOT NULL,
    thumbnail VARCHAR(255),
    description LONGTEXT,
    view_count INT DEFAULT 0,
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL,
    INDEX (slug),
    INDEX (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. PHONE DETAILS (Thông số Điện thoại)
CREATE TABLE phone_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL UNIQUE,
    chipset VARCHAR(100),
    ram VARCHAR(50),
    rom VARCHAR(50),
    camera VARCHAR(100),
    battery VARCHAR(50),
    screen VARCHAR(50),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. PRODUCT VARIANTS (Biến thể: Màu sắc, Cấu hình giá)
CREATE TABLE product_variants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    sku VARCHAR(50) UNIQUE, -- Mã kho nên là duy nhất
    ram VARCHAR(50),
    rom VARCHAR(50),
    color VARCHAR(50),
    price DECIMAL(15,0) NOT NULL,
    price_sale DECIMAL(15,0) DEFAULT 0,
    stock INT DEFAULT 0,
    image VARCHAR(255),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. IMEI (Quản lý từng serial sản phẩm)
CREATE TABLE imei_numbers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_variant_id INT NOT NULL,
    imei VARCHAR(50) NOT NULL UNIQUE,
    status ENUM('available','sold','error') DEFAULT 'available',
    FOREIGN KEY (product_variant_id) REFERENCES product_variants(id) ON DELETE CASCADE,
    INDEX (imei)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. VOUCHERS (Mã giảm giá)
CREATE TABLE vouchers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount_type ENUM('percent', 'fixed') DEFAULT 'fixed',
    discount_value DECIMAL(15,0) NOT NULL,
    min_order_amount DECIMAL(15,0) DEFAULT 0,
    quantity INT DEFAULT 0,
    start_date DATETIME,
    end_date DATETIME,
    status TINYINT DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. NEWS (Tin tức)
CREATE TABLE news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255), -- Nên thêm slug cho bài viết
    thumbnail VARCHAR(255),
    content LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. ORDERS (Đơn hàng)
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    code VARCHAR(50) UNIQUE,
    fullname VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    note TEXT,
    total_money DECIMAL(15,0) NOT NULL, -- Tổng tiền hàng
    shipping_fee DECIMAL(15,0) DEFAULT 0, -- Phí ship
    discount_money DECIMAL(15,0) DEFAULT 0, -- Tiền giảm giá
    final_money DECIMAL(15,0) NOT NULL, -- Khách phải trả
    payment_method VARCHAR(50) DEFAULT 'COD',
    status ENUM('pending','confirmed','shipping','completed','cancelled','returned') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. USER VOUCHERS (Lịch sử dùng mã - PHẢI ĐẶT SAU ORDERS)
CREATE TABLE user_vouchers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    voucher_id INT NOT NULL,
    order_id INT NOT NULL,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (voucher_id) REFERENCES vouchers(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. ORDER DETAILS (Chi tiết đơn hàng)
CREATE TABLE order_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_variant_id INT,
    product_name VARCHAR(255),
    variant_info VARCHAR(255), -- Lưu cứng thông tin: Màu Đen, 8GB/256GB
    price DECIMAL(15,0) NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(15,0) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_variant_id) REFERENCES product_variants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. PAYMENT HISTORY (Lịch sử thanh toán online)
CREATE TABLE payment_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    payment_method VARCHAR(50),
    transaction_code VARCHAR(100),
    amount DECIMAL(15,0),
    status VARCHAR(50), -- success, failed
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. SHIPPING TRACKING (Vận chuyển)
CREATE TABLE shipping_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    provider VARCHAR(100), -- VD: GiaoHangNhanh
    tracking_code VARCHAR(100),
    status VARCHAR(100),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. REVIEWS (Đánh giá sản phẩm)
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    rating TINYINT DEFAULT 5,
    comment TEXT,
    parent_id INT DEFAULT NULL COMMENT 'ID của bình luận cha',
    status TINYINT DEFAULT 0 COMMENT '0=chờ duyệt, 1=đã duyệt, 2=từ chối',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. ORDER RETURNS (Yêu cầu trả hàng)
CREATE TABLE order_returns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    user_id INT NOT NULL,
    reason TEXT,
    images TEXT,
    status ENUM('pending','approved','rejected','refunded') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. WISHLISTS (Sản phẩm yêu thích)
CREATE TABLE wishlists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 20. CONTACT MESSAGES (Liên hệ)
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(255) NOT NULL,
    order_code VARCHAR(50),
    message TEXT NOT NULL,
    status TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 21. ADMIN LOGS (Nhật ký hoạt động Admin)
CREATE TABLE admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(255),
    detail TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 22. BANNERS (Quảng cáo)
CREATE TABLE banners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image_url VARCHAR(255),
    link VARCHAR(255),
    position VARCHAR(50),
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;

INSERT INTO categories (name, slug, logo, status) VALUES
('Điện thoại', 'phone', 'phone.png', 1),

-- Thêm Brands (Samsung, Acer, Sony, Apple, Xiaomi, MSI)
INSERT INTO brands (name, slug, logo, status) VALUES
('Apple', 'apple', 'apple.png', 1);

-- ============================================================
-- 4. iPhone 15 Pro Max
-- ============================================================
INSERT INTO products (category_id, brand_id, name, slug, type, thumbnail, description, is_featured, status)
VALUES (
    (SELECT id FROM categories WHERE slug='phone' LIMIT 1),
    (SELECT id FROM brands WHERE slug='apple' LIMIT 1),
    'iPhone 15 Pro Max', 'iphone-15-pro-max', 'phone', 'assets/img/product/ip15pm-titan.webp', 
    'Khung titan, chip A17 Pro mạnh mẽ nhất thế giới smartphone.', 1, 1
);
SET @p_id = LAST_INSERT_ID();

INSERT INTO product_variants (product_id, sku, ram, rom, color, price, price_sale, stock, image) VALUES 
(@p_id, 'IP15PM-TI-256', '8GB', '256GB', 'Titan Tự Nhiên', 29990000, 28590000, 50, 'assets/img/product/ip15pm-titan.webp'),
(@p_id, 'IP15PM-BL-512', '8GB', '512GB', 'Titan Xanh', 34990000, 32990000, 30, 'assets/img/product/ip15pm-blue.webp');

INSERT INTO phone_details (product_id, chipset, ram, rom, camera, battery, screen) VALUES 
(@p_id, 'Apple A17 Pro', '8GB', '256GB/512GB/1TB', '48MP + 12MP + 12MP', '4441 mAh', '6.7 inch Super Retina XDR');

-- ============================================================
-- SẢN PHẨM 1: iPhone 15 128GB
-- ============================================================
INSERT INTO products (category_id, brand_id, name, slug, type, thumbnail, description, status) VALUES (
    (SELECT id FROM categories WHERE slug='phone' LIMIT 1),
    (SELECT id FROM brands WHERE slug='apple' LIMIT 1),
    'iPhone 15 128GB', 'iphone-15-128gb', 'phone', 
    'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/i/p/iphone-15-plus_1_.png', 
    'Thiết kế Dynamic Island, camera 48MP, cổng sạc USB-C mới.', 1
);
SET @p_id = LAST_INSERT_ID();
INSERT INTO product_variants (product_id, sku, ram, rom, color, price, price_sale, stock) VALUES 
(@p_id, 'IP15-HONG', '6GB', '128GB', 'Hồng', 19990000, 18990000, 50);
INSERT INTO phone_details (product_id, chipset, ram, rom, camera, battery, screen) VALUES 
(@p_id, 'Apple A16 Bionic', '6GB', '128GB', '48MP + 12MP', '3349 mAh', '6.1 inch OLED');
INSERT INTO users (id, fullname, email, password, phone, role, status, created_at)
VALUES (
    1,
    'Đăng Nguyễn',
    'nguyenhaid50@gmail.com',
    '$2y$10$vI8aWBnW3fID.ZQ4/zo1G.q1lRps.9cGLcZEiGDMVr5yUP1KUOYTa', -- Mật khẩu là: 123456
    '0862385393',
    1, -- Role 1 (Admin)
    1, -- Status 1 (Active)
    '2025-12-06 15:20:18'
);  