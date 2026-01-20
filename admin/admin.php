<?php
session_start();

// --- 1. Kiểm tra quyền Admin (Giữ nguyên logic cũ) ---
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 1) {
    echo "<script>
            alert('Vui lòng đăng nhập tài khoản Admin!');
            window.location.href = '../index.php';
          </script>";
    exit;
}

// --- 2. Khởi tạo Router ---
// Lưu ý: Nếu Router.php nằm ở thư mục gốc, dùng '../Router.php'.
// Nếu bạn copy Router.php vào trong thư mục admin, hãy sửa thành 'Router.php'
require_once '../Router.php';

// Đảm bảo controller được load (Router có thể tự load, nhưng require ở đây cho chắc chắn với cấu trúc cũ)
$router = new Router();

// --- 3. Đăng ký các Route (Mapping từ switch-case cũ) ---

// Dashboard & Orders
$router->add('dashboard', 'Admincontroller', 'dashboard');
$router->add('banner', 'Admincontroller', 'banner');
$router->add('orders', 'Admincontroller', 'orders');

// Voucher
$router->add('voucher_history', 'Admincontroller', 'voucher_history');
$router->add('voucher', 'Admincontroller', 'voucher_management');
$router->add('edit_voucher', 'Admincontroller', 'edit_voucher');
$router->add('add_voucher', 'Admincontroller', 'add_voucher');
$router->add('delete_voucher', 'Admincontroller', 'delete_voucher');

// Tracking & Order Updates
$router->add('shipping_tracking', 'Admincontroller', 'shipping_tracking');
$router->add('update_tracking', 'Admincontroller', 'update_tracking');
$router->add('update_order', 'Admincontroller', 'update_order');

// User Management
$router->add('user_management', 'Admincontroller', 'user_management');
$router->add('formthem_user', 'Admincontroller', 'formthem_user');
$router->add('add_user', 'Admincontroller', 'addUser'); // Note: case 'add_user' -> function addUser
$router->add('xoa', 'Admincontroller', 'xoa_user');
$router->add('sua', 'Admincontroller', 'sua_user');
$router->add('sua_user', 'Admincontroller', 'sua_user');
$router->add('khoa_user', 'Admincontroller', 'khoa_user');
$router->add('mo_khoa_user', 'Admincontroller', 'mo_khoa_user');

// Categories
$router->add('categorys', 'Admincontroller', 'categorys');
$router->add('edit_category', 'Admincontroller', 'edit_category');
$router->add('add_category', 'Admincontroller', 'add_category');
$router->add('delete_category', 'Admincontroller', 'delete_category');

// Comments
$router->add('comments', 'Admincontroller', 'comments');
$router->add('sua_coments', 'Admincontroller', 'comments'); // Map về cùng hàm comments

// Products & IMEI
$router->add('admin_product', 'Admincontroller', 'admin_product');
$router->add('formthem_product', 'Admincontroller', 'formthem_product');
$router->add('imei', 'Admincontroller', 'imei');

// Wishlist & Contact
$router->add('wishlist', 'Admincontroller', 'wishlist');
$router->add('delete_wishlist', 'Admincontroller', 'deleteWishlist'); // Case sensitive
$router->add('contact', 'Admincontroller', 'contact');


// --- 4. Thực thi Router ---
// Router sẽ tự động lấy $_GET['page'] để xử lý
$router->dispatch('dashboard');
