<?php
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once "Router.php";

// Đảm bảo bạn đã include hoặc autoload ApiController nếu chưa có
// require_once "Controllers/ApiController.php"; 

$router = new Router();

// --- User Routes ---
$router->add('home', 'Usercontroller', 'home');
$router->add('wishlists', 'Usercontroller', 'wishlists');
$router->add('toggle_wishlist', 'Usercontroller', 'toggle_wishlist');
$router->add('payment', 'Usercontroller', 'payment');
$router->add('process_order', 'Usercontroller', 'process_order');
$router->add('order_success', 'Usercontroller', 'order_success');
$router->add('products', 'Usercontroller', 'products');
$router->add('cart', 'Usercontroller', 'cart');
$router->add('saveLocation', 'Usercontroller', 'saveLocation');
$router->add('product_details', 'Usercontroller', 'product_details');
$router->add('news', 'Usercontroller', 'news');
$router->add('about', 'Usercontroller', 'about');
$router->add('contact', 'Usercontroller', 'contact');
$router->add('check_coupon', 'Usercontroller', 'check_coupon');
$router->add('post_contact', 'Usercontroller', 'post_contact');
$router->add('order_history', 'Usercontroller', 'order_history');
$router->add('login', 'Usercontroller', 'login');
$router->add('logout', 'Usercontroller', 'logout');
$router->add('register', 'Usercontroller', 'register');
$router->add('profile', 'Usercontroller', 'profile');
$router->add('forgot_password', 'Usercontroller', 'forgot_password');
$router->add('reset_password', 'Usercontroller', 'reset_password');
$router->add('send_reset_link', 'Usercontroller', 'send_reset_link');
$router->add('post_review', 'Usercontroller', 'post_review');

// --- Payment Routes ---
$router->add('webhook_payment', 'PaymentController', 'webhook');

// --- API Location Routes (Thay thế cho switch case cũ) ---
// Logic: Route Name -> Tên Controller -> Tên Method
$router->add('api_province', 'ApiController', 'getProvinces');
$router->add('api_district', 'ApiController', 'getDistricts');
$router->add('api_ward', 'ApiController', 'getWards');
$router->add('api_fee', 'ApiController', 'calculateFee');

// Thực thi router
$router->dispatch();
