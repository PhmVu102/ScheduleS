<?php
require_once 'models/UserModel.php';

use PHPMailer\PHPMailer\OAuthTokenProvider;

class Usercontroller
{
    private $userModel;
    public $data = [];
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->userModel = new UserModel();

        // --- 1. XỬ LÝ BANNER ---
        // Lấy tất cả banner đang hoạt động
        $allBanners = $this->userModel->getBanners();

        // Tạo mảng gom nhóm theo position
        $bannerList = [];
        foreach ($allBanners as $banner) {
            $bannerList[$banner['position']][] = $banner;
        }

        // Lưu vào data chung để đẩy ra View
        $this->data['bannerList'] = $bannerList;
        // 4. Lưu kết quả vào thuộc tính của class ($this->data)
        // Thay vì $slidesJson, ta lưu vào mảng data để dùng chung
        $this->data['bestsellers'] = $this->userModel->getBestsellers(5);
    }
    public function saveLocation()
    {
        // Đặt header trả về là JSON
        header('Content-Type: application/json');

        // 1. Nhận dữ liệu JSON thô từ Javascript gửi lên
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        // 2. Kiểm tra dữ liệu đầu vào
        if (!isset($data['lat']) || !isset($data['lon'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Thiếu thông tin tọa độ'
            ]);
            return;
        }

        // 3. Lấy thông tin User ID (nếu đang đăng nhập)
        // Giả sử bạn lưu user_id trong Session khi login
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        // 4. Lấy các tham số
        $lat = $data['lat'];
        $lon = $data['lon'];
        $address = isset($data['address']) ? $data['address'] : 'Không xác định';

        // 5. Gọi Model để lưu vào Database
        $result = $this->userModel->saveLocation($lat, $lon, $address, $userId);

        // 6. Trả kết quả về cho Javascript
        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Đã lưu vị trí thành công!'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Lỗi Server: Không thể lưu vào CSDL.'
            ]);
        }
    }
    public function header($title = 'ScheduleS - Điện thoại và Laptop giá rẻ')
    {
        // Tạo biến $pageTitle để file view/header.php sử dụng
        $pageTitle = $title;
        include 'views/header.php';
    }
    public function footer()
    {
        include 'views/footer.php';
    }
    public function products()
    {
        // 1. Lấy tham số filter
        $category = $_GET['category'] ?? '';
        $brand    = $_GET['brand']    ?? '';
        $price    = $_GET['price']    ?? '';
        $keyword  = $_GET['keyword']  ?? '';

        // --- PHÂN TRANG ---
        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        if ($page < 1) $page = 1;
        $limit = 12; // Số sản phẩm trên 1 trang
        $offset = ($page - 1) * $limit;

        $filters = [
            'category' => $category,
            'brand'    => $brand,
            'price'    => $price,
            'keyword'  => $keyword
        ];

        // 2. Gọi Model lấy dữ liệu
        $products = $this->userModel->getProducts($filters, $limit, $offset);
        $total_products = $this->userModel->countProducts($filters);

        // Tính tổng số trang
        $total_pages = ceil($total_products / $limit);
        $wishlist_ids = [];
        if (isset($_SESSION['user'])) {
            $wishlist_ids = $this->userModel->getUserWishlistIds($_SESSION['user']['id']);
        }
        // 3. Helper URL (Thêm tham số p)
        $urlHelper = function ($params = []) use ($category, $brand, $price, $keyword) {
            $query = ['page' => 'products'];
            if ($category) $query['category'] = $category;
            if ($brand)    $query['brand']    = $brand;
            if ($price)    $query['price']    = $price;
            if ($keyword)  $query['keyword']  = $keyword;

            // Gộp tham số mới vào
            $query = array_merge($query, $params);
            return 'index.php?' . http_build_query($query);
        };

        $hasFilter = !empty($brand) || !empty($price);
        $brandList = $this->userModel->getAllBrands();
        $this->header($category ? ucfirst($category) : 'Sản phẩm');
        include 'views/products.php';
        $this->footer();
    }
    // 2. [MỚI] API Xử lý thả tim (AJAX)
    public function toggle_wishlist()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user'])) {
            echo json_encode(['status' => 'login_required', 'message' => 'Vui lòng đăng nhập để lưu yêu thích!']);
            exit;
        }

        $product_id = $_POST['product_id'] ?? 0;
        $user_id = $_SESSION['user']['id'];

        if ($product_id) {
            $action = $this->userModel->toggleWishlist($user_id, $product_id);
            echo json_encode(['status' => 'success', 'action' => $action]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi dữ liệu']);
        }
        exit;
    }
    // Trong class Usercontroller

    public function product_details()
    {
        // 1. Lấy ID từ URL
        $id = $_GET['id'] ?? 0;

        // 2. [MỚI] Xử lý tăng view (Có kiểm tra Session để chống spam F5)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Tạo key session unique cho từng sản phẩm, ví dụ: viewed_product_15
        $sessionKey = 'viewed_product_' . $id;

        // Nếu chưa xem trong phiên này thì mới tăng view
        if (!isset($_SESSION[$sessionKey])) {
            $this->userModel->increaseViewCount($id); // Gọi hàm vừa viết ở Model
            $_SESSION[$sessionKey] = true; // Đánh dấu là đã xem
        }

        // 3. Lấy thông tin sản phẩm (Giữ nguyên code cũ của bạn)
        $product = $this->userModel->getProductById($id);

        if (!$product) {
            echo "<script>alert('Sản phẩm không tồn tại!'); window.location.href='?page=products';</script>";
            exit;
        }

        // 4. Các logic còn lại giữ nguyên
        $reviews = $this->userModel->getReviews($id);
        $related_products = $this->userModel->getRelatedProducts($product['category_id'], $id);

        $this->header($product['name']);
        include 'views/product_details.php';
        $this->footer();
    }
    public function post_review()
    {
        if (!isset($_SESSION['user'])) {
            echo "<script>alert('Vui lòng đăng nhập để đánh giá!'); window.location.href='index.php?page=login';</script>";
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_SESSION['user']['id'];
            $user_role = $_SESSION['user']['role']; // Lấy role từ session
            $product_id = $_POST['product_id'];
            $parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
            $rating = $_POST['rating'];
            $comment = $_POST['comment'];
            $status = ($user_role == 1) ? 1 : 0;
            $this->userModel->addReview($user_id, $product_id, $rating, $comment,  $parent_id, $status);
            if ($user_role == 1) {
                echo "<script>alert('Đã trả lời bình luận!'); window.location.href='index.php?page=product_details&id=$product_id#reviews';</script>";
            } else {
                echo "<script>alert('Cảm ơn bạn! Đánh giá sẽ hiển thị sau khi được duyệt.'); window.location.href='index.php?page=product_details&id=$product_id#reviews';</script>";
            }
            exit;
        }
    }
    public function login()
    {
        // Biến chứa lỗi để hiển thị ra View
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $error = "Vui lòng nhập đầy đủ email và mật khẩu!";
            } else {
                // Gọi Model để kiểm tra
                $user = $this->userModel->login($email, $password);

                if ($user) {
                    // --- 1. KIỂM TRA TRẠNG THÁI (QUAN TRỌNG) ---
                    if ($user['status'] == 0) {
                        // Nếu bị khóa, báo lỗi JS và reload lại trang login
                        echo "<script>
                                alert('❌ Tài khoản của bạn đã bị khóa hoặc chưa kích hoạt. Vui lòng liên hệ Admin!');
                                window.location.href = '?page=login'; 
                              </script>";
                        exit;
                    }

                    // --- 2. LƯU SESSION NẾU TÀI KHOẢN HỢP LỆ ---
                    $_SESSION['user'] = $user; // Lưu nguyên mảng user (gồm id, fullname, role,...)

                    // Lưu lẻ từng cái nếu code cũ của bạn cần dùng
                    $_SESSION['user_id']  = $user['id'];
                    $_SESSION['fullname'] = $user['fullname'];
                    $_SESSION['email']    = $user['email'];
                    $_SESSION['role']     = $user['role'];

                    // --- 3. CHUYỂN HƯỚNG THEO QUYỀN ---
                    if ($user['role'] == 1) {
                        // Admin -> Vào trang quản trị
                        header('Location: admin/admin.php'); // Sửa đường dẫn admin của bạn nếu khác
                    } else {
                        // Khách hàng -> Vào trang chủ
                        header('Location: index.php');
                    }
                    exit;
                } else {
                    $error = "Email hoặc mật khẩu không đúng!";
                }
            }
        }

        // --- 4. HIỂN THỊ FORM ĐĂNG NHẬP ---
        $this->header("Đăng nhập");
        include 'views/taikhoan/login.php'; // Biến $error sẽ được dùng trong file này
        $this->footer();
    }
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullname = $_POST['fullname'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $phone = $_POST['phone'] ?? null;
            $result = $this->userModel->register($fullname, $email, $password, $phone);
            if ($result) {
                header('Location: index.php?page=login');
                exit;
            } else {
                $error = "Đăng ký thất bại hoặc Email đã tồn tại!";
            }
        }
        $this->header("Đăng ký");
        include 'views/taikhoan/register.php';
        $this->footer();
    }
    public function profile()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user'])) {
            header("Location: index.php?page=login");
            exit;
        }
        $user_id = $_SESSION['user']['id'];
        $user = $this->userModel->getUserById($user_id);
        if (!$user) {
            session_destroy();
            header("Location: index.php?page=login&error=expired");
            exit;
        }
        $success = '';
        $error = '';
        if (isset($_GET['msg'])) {
            $success = urldecode($_GET['msg']);
        }
        if (isset($_GET['action']) && $_GET['action'] == 'delete_address' && isset($_GET['id'])) {
            $addr_id = $_GET['id'];
            if ($this->userModel->deleteUserAddress($addr_id, $user_id)) {
                $msg = "Đã xóa địa chỉ thành công!";
            } else {
                $msg = "Lỗi: Không thể xóa địa chỉ này!";
            }
            header("Location: index.php?page=profile&msg=" . urlencode($msg));
            exit;
        }
        $edit_data = null;
        if (isset($_GET['action']) && $_GET['action'] == 'edit_address' && isset($_GET['id'])) {
            $edit_data = $this->userModel->getAddressById($_GET['id'], $user_id);
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['update_profile'])) {
                $fullname = trim($_POST['fullname'] ?? '');
                $phone    = trim($_POST['phone'] ?? '');

                if (empty($fullname)) {
                    $error = 'Họ và tên không được để trống!';
                } else {
                    if ($this->userModel->updateProfile($user_id, $fullname, $phone)) {
                        $success = 'Cập nhật thông tin thành công!';
                        $user['fullname'] = $fullname;
                        $user['phone']    = $phone;
                        $_SESSION['user']['fullname'] = $fullname;
                    } else {
                        $error = 'Cập nhật thất bại, vui lòng thử lại!';
                    }
                }
            }
            if (isset($_POST['change_password'])) {
                $old_pass = $_POST['old_password'] ?? '';
                $new_pass = $_POST['new_password'] ?? '';
                $confirm  = $_POST['confirm_password'] ?? '';

                if (!password_verify($old_pass, $user['password'])) {
                    $error = 'Mật khẩu cũ không đúng!';
                } elseif ($new_pass !== $confirm) {
                    $error = 'Mật khẩu mới không khớp!';
                } elseif (strlen($new_pass) < 6) {
                    $error = 'Mật khẩu mới phải ít nhất 6 ký tự!';
                } else {
                    if ($this->userModel->changePassword($user_id, $new_pass)) {
                        $success = 'Đổi mật khẩu thành công!';
                    } else {
                        $error = 'Đổi mật khẩu thất bại!';
                    }
                }
            }
            if (isset($_POST['save_address'])) {
                $name = $_POST['addr_name'];
                $phone = $_POST['addr_phone'];
                $address = $_POST['addr_detail'];
                $addr_id = $_POST['addr_id'] ?? '';
                if (!empty($addr_id)) {
                    $this->userModel->updateUserAddress($addr_id, $user_id, $name, $phone, $address);
                    $msg = "Cập nhật địa chỉ thành công!";
                } else {
                    $this->userModel->addUserAddress($user_id, $name, $phone, $address);
                    $msg = "Thêm địa chỉ mới thành công!";
                }
                header("Location: index.php?page=profile&msg=" . urlencode($msg));
                exit;
            }
        }
        $list_address = $this->userModel->getUserAddresses($user_id);
        $this->header("Tài Khoản");
        include 'views/taikhoan/profile.php';
        $this->footer();
    }
    public function home()
    {
        extract($this->data);
        $this->header('Trang chủ');
        include 'views/index.php';
        $this->footer();
    }
    public function cart()
    {
        $action = $_GET['action'] ?? '';
        if ($action == 'add' && $_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->addToCart();
        } elseif ($action == 'delete') {
            $this->deleteFromCart();
        } elseif ($action == 'update') {
            $this->updateCart();
        }
        $cart = $_SESSION['cart'] ?? [];
        $total_price = 0;
        $total_items = 0;
        foreach ($cart as $item) {
            $total_price += $item['price'] * $item['quantity'];
            $total_items += $item['quantity'];
        }
        $this->header('Giỏ hàng');
        include 'views/cart.php';
        $this->footer();
    }
    private function addToCart()
    {
        $id = $_POST['product_id'] ?? 0;
        $variant_id = $_POST['variant_id'] ?? 0;
        $product = $this->userModel->getProductById($id);
        if ($product) {
            $selected_variant = null;
            if (!empty($product['variants'])) {
                foreach ($product['variants'] as $v) {
                    if ($v['id'] == $variant_id) {
                        $selected_variant = $v;
                        break;
                    }
                }
            }
            if (!$selected_variant && !empty($product['variants'])) {
                $selected_variant = $product['variants'][0];
                $variant_id = $selected_variant['id'];
            }
            $price = $selected_variant['price_sale'] > 0 ? $selected_variant['price_sale'] : $selected_variant['price'];
            $img = !empty($selected_variant['image']) ? $selected_variant['image'] : $product['thumbnail'];
            $variant_info = [];
            if ($selected_variant['ram']) $variant_info[] = $selected_variant['ram'];
            if ($selected_variant['rom']) $variant_info[] = $selected_variant['rom'];
            if ($selected_variant['color']) $variant_info[] = $selected_variant['color'];
            $variant_str = implode(" - ", $variant_info);
            $cartKey = $id . '-' . $variant_id;

            $item = [
                'id' => $id,
                'variant_id' => $variant_id,
                'name' => $product['name'],
                'variant_text' => $variant_str,
                'image' => $img,
                'price' => $price,
                'quantity' => 1
            ];
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            if (isset($_SESSION['cart'][$cartKey])) {
                $_SESSION['cart'][$cartKey]['quantity']++;
            } else {
                $_SESSION['cart'][$cartKey] = $item;
            }
        }
        header("Location: index.php?page=cart");
        exit;
    }
    private function deleteFromCart()
    {
        $id = $_GET['id'] ?? 0;
        if (isset($_SESSION['cart'][$id])) {
            unset($_SESSION['cart'][$id]);
        }
        header("Location: index.php?page=cart");
        exit;
    }
    private function updateCart()
    {
        $id = $_GET['id'] ?? 0;
        $type = $_GET['type'] ?? 'inc';
        if (isset($_SESSION['cart'][$id])) {
            if ($type == 'inc') {
                $_SESSION['cart'][$id]['quantity']++;
            } else {
                $_SESSION['cart'][$id]['quantity']--;
                if ($_SESSION['cart'][$id]['quantity'] <= 0) {
                    unset($_SESSION['cart'][$id]);
                }
            }
        }
        header("Location: index.php?page=cart");
        exit;
    }
    public function check_coupon()
    {
        error_reporting(0);
        ini_set('display_errors', 0);
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code = $_POST['code'] ?? '';
            $code = strtoupper(trim($code));
            $total_amount = floatval($_POST['total_amount'] ?? 0);

            $voucher = $this->userModel->getVoucherByCode($code);

            if ($voucher) {
                // ============================================================
                // [PHẦN MỚI THÊM VÀO]: KIỂM TRA NGƯỜI DÙNG ĐÃ DÙNG MÃ CHƯA
                // ============================================================
                if (isset($_SESSION['user'])) {
                    $user_id = $_SESSION['user']['id'];

                    // Gọi hàm kiểm tra trong Model
                    $isUsed = $this->userModel->checkUserUsedVoucher($user_id, $voucher['id']);

                    if ($isUsed) {
                        echo json_encode([
                            'status' => false,
                            'message' => 'Bạn đã sử dụng mã giảm giá này rồi!'
                        ]);
                        exit; // Dừng ngay lập tức
                    }
                }
                // Tùy chọn: Nếu muốn bắt buộc đăng nhập mới được dùng mã thì mở đoạn này ra
                /*
                else {
                    echo json_encode([
                        'status' => false,
                        'message' => 'Vui lòng đăng nhập để sử dụng mã giảm giá!'
                    ]);
                    exit;
                }
                */
                // ============================================================

                // Kiểm tra đơn tối thiểu
                if ($total_amount < $voucher['min_order_amount']) {
                    echo json_encode([
                        'status' => false,
                        'message' => 'Mã này chỉ áp dụng cho đơn từ ' . number_format($voucher['min_order_amount']) . 'đ'
                    ]);
                    exit;
                }

                // Tính toán giảm giá
                $discount = 0;
                if ($voucher['discount_type'] == 'percent') {
                    $discount = ($total_amount * $voucher['discount_value']) / 100;
                } else {
                    $discount = $voucher['discount_value'];
                }

                if ($discount > $total_amount) {
                    $discount = $total_amount;
                }

                // Lưu vào session
                $_SESSION['coupon'] = [
                    'code' => $voucher['code'],
                    'discount_val' => $discount,
                    'discount_type' => $voucher['discount_type'],
                    'discount_raw' => $voucher['discount_value']
                ];

                echo json_encode([
                    'status' => true,
                    'message' => 'Áp dụng mã thành công!',
                    'discount' => $discount,
                    'code' => $voucher['code']
                ]);
            } else {
                if (isset($_SESSION['coupon'])) unset($_SESSION['coupon']);

                echo json_encode([
                    'status' => false,
                    'message' => 'Mã giảm giá không tồn tại hoặc đã hết hạn!'
                ]);
            }
        }
        exit;
    }
    public function payment()
    {
        if (empty($_SESSION['cart'])) {
            header("Location: index.php?page=cart");
            exit;
        }
        $cart = $_SESSION['cart'];
        $total_price = 0;
        foreach ($cart as $item) {
            $total_price += $item['price'] * $item['quantity'];
        }
        $discount = 0;
        $couponCode = '';
        if (isset($_SESSION['coupon'])) {
            $discount = $_SESSION['coupon']['discount_val'];
            $couponCode = $_SESSION['coupon']['code'];
        }
        $final_total = $total_price - $discount;
        if ($final_total < 0) $final_total = 0;
        $user = [];
        $default_addr = [];
        if (isset($_SESSION['user'])) {
            $id = $_SESSION['user']['id'];
            $user = $this->userModel->getUserById($id);
            $addr = $this->userModel->getDefaultAddress($id);
            if ($addr) {
                $default_addr = $addr;
            }
        }
        $saved_addresses = [];
        if (isset($_SESSION['user'])) {
            $saved_addresses = $this->userModel->getUserAddresses($_SESSION['user']['id']);
        }
        $this->header('Thanh Toán');
        include 'views/payment.php';
        $this->footer();
    }
    public function news()
    {
        $this->header('Tin tức');
        include 'views/news.php';
        $this->footer();
    }
    public function about()
    {
        $this->header('Giới thiệu');
        include 'views/about.php';
        $this->footer();
    }
    public function contact()
    {
        $user = [];
        $orders = [];
        if (isset($_SESSION['user'])) {
            $user_id = $_SESSION['user']['id'];
            $user = $this->userModel->getUserById($user_id);
            $orders = $this->userModel->getUserOrders($user_id);
        }
        $this->header('Liên hệ');
        include 'views/contact.php';
        $this->footer();
    }
    public function post_contact()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'user_id'    => isset($_SESSION['user']) ? $_SESSION['user']['id'] : null,
                'fullname'   => $_POST['fullname'] ?? '',
                'email'      => $_POST['email'] ?? '',
                'phone'      => $_POST['phone'] ?? '',
                'subject'    => $_POST['subject'] ?? '',
                'order_code' => $_POST['order_code'] ?? null,
                'message'    => $_POST['message'] ?? ''
            ];
            if ($data['order_code'] == 'other') {
                $data['order_code'] = null;
            }

            if ($this->userModel->saveContact($data)) {
                echo "<script>alert('Gửi tin nhắn thành công! Chúng tôi sẽ phản hồi sớm.'); window.location.href='index.php?page=contact';</script>";
            } else {
                echo "<script>alert('Lỗi khi gửi tin nhắn.'); window.history.back();</script>";
            }
        }
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        header('Location: index.php?page=login');
        exit;
    }
    public function process_order()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            // 1. Kiểm tra giỏ hàng
            $cart = $_SESSION['cart'] ?? [];
            if (empty($cart)) {
                header("Location: index.php?page=products");
                exit;
            }

            // 2. Lấy User ID
            $user_id = isset($_SESSION['user']) ? $_SESSION['user']['id'] : null;

            // --- KIỂM TRA VOUCHER ---
            if (isset($_SESSION['coupon']) && $user_id) {
                $voucher_info = $this->userModel->getVoucherByCode($_SESSION['coupon']['code']);
                if ($voucher_info) {
                    $isUsed = $this->userModel->checkUserUsedVoucher($user_id, $voucher_info['id']);
                    if ($isUsed) {
                        unset($_SESSION['coupon']);
                        echo "<script>alert('Voucher đã sử dụng!'); window.location.href='index.php?page=payment';</script>";
                        exit;
                    }
                }
            }

            // 3. Xử lý thông tin người nhận
            $selected_addr_id = $_POST['selected_address_id'] ?? 'new';
            $fullname = '';
            $phone = '';
            $address = '';
            $email    = trim($_POST['email'] ?? '');

            // Trường hợp 1: Chọn địa chỉ cũ
            if ($selected_addr_id !== 'new' && $user_id) {
                $addr = $this->userModel->getAddressById($selected_addr_id, $user_id);
                if ($addr) {
                    $fullname = $addr['recipient_name'];
                    $phone    = $addr['phone'];
                    $address  = $addr['address'];
                }
            }

            // Trường hợp 2: Nhập địa chỉ mới
            if (empty($fullname)) {
                $fullname = trim($_POST['fullname'] ?? '');
                $phone    = trim($_POST['phone'] ?? '');
                $street   = trim($_POST['address'] ?? '');
                $city     = trim($_POST['city'] ?? '');

                // Ghép địa chỉ chuẩn
                $address = $street;
                if ($city !== '') {
                    $address .= ' - ' . $city;
                }

                // Lưu địa chỉ nếu có user & hợp lệ & < 3 địa chỉ
                if ($user_id && $fullname && $phone && $street) {
                    $addresses = $this->userModel->getUserAddresses($user_id);
                    if (count($addresses) < 3) {
                        $this->userModel->addUserAddress($user_id, $fullname, $phone, $address);
                    }
                }
            }


            // 4. Xử lý User vãng lai (Chưa đăng nhập)
            $new_password = null;
            if (!$user_id && !empty($email)) {

                // Kiểm tra xem email này đã có tài khoản chưa
                $existingUser = $this->userModel->getUserByEmail($email);

                if ($existingUser) {
                    // --- TRƯỜNG HỢP A: ĐÃ CÓ TÀI KHOẢN ---
                    $user_id = $existingUser['id'];

                    // --- [SỬA ĐOẠN NÀY] KIỂM TRA SỐ LƯỢNG TRƯỚC KHI LƯU ---
                    if (!empty($fullname) && !empty($phone) && !empty($address)) {
                        $existingAddrs = $this->userModel->getUserAddresses($user_id);
                        if (count($existingAddrs) < 3) {
                            $this->userModel->addUserAddress($user_id, $fullname, $phone, $address);
                        }
                    }
                    // -----------------------------------------------------

                } else {
                    // --- TRƯỜNG HỢP B: CHƯA CÓ TÀI KHOẢN (Tạo mới) ---
                    $new_password = rand(100000, 999999);
                    $new_user_id = $this->userModel->autoRegisterUser($fullname, $email, $phone, $new_password);

                    if ($new_user_id) {
                        $user_id = $new_user_id;

                        // User mới tạo thì chưa có địa chỉ nào, nên lưu luôn không cần check < 3
                        $this->userModel->addUserAddress($user_id, $fullname, $phone, $address);

                        $_SESSION['new_account_info'] = [
                            'email' => $email,
                            'password' => $new_password
                        ];
                    }
                }
            }

            // 5. Tính toán tiền
            $total_money = 0;
            foreach ($cart as $item) {
                $total_money += $item['price'] * $item['quantity'];
            }
            $discount_money = isset($_SESSION['coupon']) ? $_SESSION['coupon']['discount_val'] : 0;
            $shipping_fee = 0;
            $final_money = ($total_money + $shipping_fee) - $discount_money;
            if ($final_money < 0) $final_money = 0;

            // 6. Tạo đơn hàng
            $note = $_POST['note'] ?? '';
            $payment_method = $_POST['payment'] ?? 'cod';
            $order_code = 'SSB' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

            $orderData = [
                'user_id' => $user_id,
                'code' => $order_code,
                'fullname' => $fullname,
                'phone' => $phone,
                'address' => $address,
                'note' => $note,
                'total_money' => $total_money,
                'shipping_fee' => $shipping_fee,
                'discount_money' => $discount_money,
                'final_money' => $final_money,
                'payment_method' => $payment_method
            ];

            $order_id = $this->userModel->createOrder($orderData, $cart);

            if ($order_id) {
                // 7. Tạo lịch sử thanh toán
                $trx_code = '';
                $pay_status = 'pending';

                if ($payment_method == 'cod') {
                    $pay_status = 'Thanh toán khi nhận hàng';
                } else {
                    $pay_status = 'pending';
                }

                $this->userModel->addPaymentHistory(
                    $order_id,
                    $payment_method,
                    $trx_code,
                    $final_money,
                    $pay_status
                );

                $this->userModel->initShippingTracking($order_id);

                if (isset($_SESSION['coupon']) && $user_id) {
                    $voucher_info = $this->userModel->getVoucherByCode($_SESSION['coupon']['code']);
                    if ($voucher_info) {
                        $this->userModel->saveUserVoucher($user_id, $voucher_info['id'], $order_id);
                        $this->userModel->decreaseVoucherQuantity($voucher_info['code']);
                    }
                }

                // 8. Dọn dẹp session và chuyển hướng
                unset($_SESSION['cart']);
                unset($_SESSION['coupon']);

                $new_pass_param = '';
                if (isset($_SESSION['new_account_info'])) {
                    $new_pass_param = '&new_acc=' . $_SESSION['new_account_info']['password'];
                    unset($_SESSION['new_account_info']);
                }

                header("Location: index.php?page=order_success&id=$order_id" . $new_pass_param);
                exit;
            } else {
                echo "<script>alert('Đặt hàng thất bại. Vui lòng thử lại!'); window.history.back();</script>";
            }
        }
    }
    public function order_success()
    {
        $order_id = $_GET['id'] ?? 0;
        $order = $this->userModel->getOrderById($order_id);

        $this->header('Đặt hàng thành công');
        include 'views/order_success.php';
        $this->footer();
    }
    public function order_history()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user'])) {
            header("Location: index.php?page=login");
            exit;
        }
        $user_id = $_SESSION['user']['id'];
        if (isset($_GET['action'])) {
            if ($_GET['action'] == 'view' && isset($_GET['id'])) {
                $id = $_GET['id'];
                $order = $this->userModel->getOrderById($id);
                $order_details = $this->userModel->getOrderDetail($id);
                $payment_histories = $this->userModel->getPaymentHistory($id);
                $shipping_info = $this->userModel->getShippingInfo($id);
                if (!$order || $order['user_id'] != $user_id) {
                    header("Location: index.php?page=order_history");
                    exit;
                }
                $this->header('Chi tiết đơn hàng #' . $order['code']);
                include 'views/taikhoan/order_detail.php';
                $this->footer();
                return;
            }
            if ($_GET['action'] == 'cancel' && isset($_GET['id'])) {
                $order_id = $_GET['id'];
                if ($this->userModel->cancelOrder($order_id, $user_id)) {
                    echo "<script>alert('Đã hủy đơn hàng thành công!'); window.location.href='index.php?page=order_history';</script>";
                } else {
                    echo "<script>alert('Không thể hủy đơn hàng này (Đã giao hoặc không tồn tại)!'); window.location.href='index.php?page=order_history';</script>";
                }
                return;
            }
        }
        $orders = $this->userModel->getUserOrders($user_id);
        $this->header('Lịch sử mua hàng');
        include 'views/taikhoan/order_history.php';
        $this->footer();
    }
    public function wishlists()
    {
        // 1. Kiểm tra đăng nhập (Bắt buộc)
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user'])) {
            echo "<script>alert('Vui lòng đăng nhập để xem danh sách yêu thích!'); window.location.href='index.php?page=login';</script>";
            exit;
        }

        $user_id = $_SESSION['user']['id'];

        // 2. Gọi Model lấy dữ liệu
        $products = $this->userModel->getWishlistItems($user_id);

        // 3. Chuẩn bị các biến cần thiết cho View (để tái sử dụng CSS products)
        // Vì view products.php dùng $wishlist_ids để tô đỏ tim, ta tạo giả nó
        $wishlist_ids = array_column($products, 'id');

        $this->header('Sản phẩm yêu thích');
        include 'views/wishlists.php'; // Tạo file view riêng cho dễ quản lý
        $this->footer();
    }

    // 1. Hiển thị form quên mật khẩu
    public function forgot_password()
    {
        // Xử lý khi người dùng bấm nút "Gửi link khôi phục"
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';

            // 1. Kiểm tra email có tồn tại không
            if ($this->userModel->emailExists($email)) {

                // 2. Tạo token và lưu vào DB
                $token = bin2hex(random_bytes(32));
                // Gọi hàm lưu token vào DB (bạn cần viết hàm này trong Model nếu chưa có)
                $this->userModel->updateResetToken($email, $token);

                // 3. Gửi email
                $link = "?page=reset_password&token=" . $token;
                // Gọi hàm gửi mail bạn đã viết ở bước trước
                if ($this->sendResetEmail($_POST['fullname'] ?? 'Khách hàng', $email, $link)) {
                    $success = "Đã gửi hướng dẫn đến email của bạn. Vui lòng kiểm tra hộp thư (kể cả mục Spam).";
                } else {
                    $error = "Lỗi gửi mail. Vui lòng thử lại sau.";
                }
            } else {
                $error = "Email này chưa được đăng ký trong hệ thống!";
            }
        }

        // Hiển thị View nhập email
        $this->header("Quên mật khẩu");
        include 'views/taikhoan/forgot_password.php'; // Bạn cần tạo file view này
        $this->footer();
    }

    // 2. Xử lý gửi mail reset
    public function send_reset_link()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=forgot_password');
            exit;
        }

        $email = trim($_POST['email'] ?? '');

        if (empty($email)) {
            $error = "Vui lòng nhập email!";
            $this->header("Quên mật khẩu");
            include 'views/taikhoan/forgot_password.php';
            $this->footer();
            return;
        }

        $user = $this->userModel->getUserByEmail($email); // bạn cần thêm hàm này (dưới đây)

        if (!$user) {
            // Không nói rõ để tránh leak email
            $success = "Nếu email tồn tại, chúng tôi đã gửi link đặt lại mật khẩu đến hộp thư của bạn.";
        } else {
            // Tạo token
            $token = bin2hex(random_bytes(50));
            $expiry = date("Y-m-d H:i:s", time() + 1800); // 30 phút

            $this->userModel->saveResetToken($email, $token, $expiry);

            $resetLink = "https://$_SERVER[HTTP_HOST]/ScheduleS/index.php?page=reset_password&token=" . $token;

            // Gửi mail (dùng PHPMailer - bạn thêm composer hoặc include thủ công)
            $this->sendResetEmail($user['fullname'], $email, $resetLink);

            $success = "Link đặt lại mật khẩu đã được gửi đến email của bạn!";

            if (isset($_SESSION['debug_reset_link'])) {
                $success .= "<br><br><div class='alert alert-info'>
                    <strong>Chế độ test (localhost):</strong><br>
                    <a href='{$_SESSION['debug_reset_link']}' target='_blank'>
                        Nhấn vào đây để đặt lại mật khẩu ngay
                    </a><br>
                    <small>Link: {$_SESSION['debug_reset_link']}</small>
                </div>";
                unset($_SESSION['debug_reset_link']); // Xóa sau khi dùng
            }
        }

        $this->header("Quên mật khẩu");
        include 'views/taikhoan/forgot_password.php';
        $this->footer();
    }

    // 3. Form + xử lý đổi mật khẩu mới
    public function reset_password()
    {
        $token = $_GET['token'] ?? '';
        echo "Token trên URL: " . $token . "<br>";
        if (empty($token)) {
            // Thay vì die(), hiển thị thông báo lỗi trên giao diện sẽ thân thiện hơn
            $error = "Link không hợp lệ hoặc thiếu token!";
            $this->header("Lỗi đặt lại mật khẩu");
            include 'views/taikhoan/reset_password.php';
            $this->footer();
            return;
        }

        // Kiểm tra token trong database
        $user = $this->userModel->getUserByResetToken($token);
        if (!$user) {
            echo "Không tìm thấy User khớp với token này trong DB!";
            // die(); // Bỏ comment dòng này nếu muốn dừng lại xem lỗi
        }
        if (!$user) {
            $error = "Link đặt lại mật khẩu đã hết hạn hoặc không tồn tại!";
            $this->header("Lỗi đặt lại mật khẩu");
            include 'views/taikhoan/reset_password.php';
            $this->footer();
            return;
        }

        // Xử lý khi người dùng nhấn nút "Đổi mật khẩu"
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirm  = $_POST['confirm'] ?? '';

            if (strlen($password) < 6) {
                $error = "Mật khẩu phải có ít nhất 6 ký tự!";
            } elseif ($password !== $confirm) {
                $error = "Mật khẩu nhập lại không khớp!";
            } else {
                // Mã hóa mật khẩu
                $hash = password_hash($password, PASSWORD_DEFAULT);

                // Gọi Model để cập nhật DB
                if ($this->userModel->updatePasswordAndClearToken($user['id'], $hash)) {
                    $success = "Đổi mật khẩu thành công! Bạn có thể <a href='index.php?page=login' style='color: #007bff; text-decoration: underline;'>đăng nhập ngay</a>.";
                } else {
                    $error = "Đã xảy ra lỗi hệ thống khi cập nhật mật khẩu. Vui lòng thử lại.";
                }
            }
        }

        $this->header("Đặt lại mật khẩu");
        include 'views/taikhoan/reset_password.php';
        $this->footer();
    }

    // === GỬI MAIL QUA SMTP BUNO.IO (SSL - Port 465) ===
    // === GỬI MAIL QUA SMTP BUNO.IO (SSL - Port 465) ===
    private function sendResetEmail($fullname, $email, $link)
    {
        // Nhúng thư viện (Sử dụng __DIR__ để đường dẫn luôn đúng)
        require_once __DIR__ . '/../vendor/PHPMailer/src/Exception.php';
        require_once __DIR__ . '/../vendor/PHPMailer/src/PHPMailer.php';
        require_once __DIR__ . '/../vendor/PHPMailer/src/SMTP.php';

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            // 1. Cấu hình Server (Theo ảnh bạn cung cấp)
            $mail->isSMTP();
            $mail->Host       = 'mail.buno.io.vn';      // Máy chủ gửi mail
            $mail->SMTPAuth   = true;
            $mail->Username   = 'support@buno.io.vn';   // Tài khoản email
            $mail->Password   = 'Lcs:81SR;0mYz6'; // <--- QUAN TRỌNG: Nhập mật khẩu thật của email support@buno.io.vn

            // Port 465 đi kèm với ENCRYPTION_SMTPS (SSL)
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->CharSet    = 'UTF-8';                // Hỗ trợ tiếng Việt

            // 2. Người gửi & Người nhận
            $mail->setFrom('support@buno.io.vn', 'ScheduleS Support');
            $mail->addAddress($email, $fullname);

            // 3. Nội dung email (Giao diện giống Discord)
            $mail->isHTML(true);
            $mail->Subject = 'Đặt lại mật khẩu ScheduleS';

            // CSS nội tuyến (Inline CSS) để tương thích với Gmail/Outlook
            $mail->Body    = "
                <div style='font-family: Helvetica, Arial, sans-serif; min-width: 1000px; overflow: auto; line-height: 2;'>
                    <div style='margin: 50px auto; width: 70%; padding: 20px 0'>
                        <div style='border-bottom: 1px solid #eee'>
                            <a href='' style='font-size: 1.4em; color: #00466a; text-decoration:none; font-weight:600'>ScheduleS</a>
                        </div>
                        
                        <p style='font-size: 1.1em'>Xin chào <strong>$fullname</strong>,</p>
                        
                        <p>Mật khẩu tài khoản ScheduleS của bạn có thể được đặt lại bằng cách nhấm vào nút bên dưới. Nếu bạn không yêu cầu thay đổi này, vui lòng bỏ qua email này.</p>
                        
                        <div style='text-align: center; margin: 40px 0;'>
                            <a href='$link' style='background: #5865F2; color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 4px; font-weight: bold; font-size: 16px; display: inline-block;'>Đặt Lại Mật Khẩu</a>
                        </div>
                        
                        <p style='font-size: 0.9em;'>Hoặc dán đường dẫn sau vào trình duyệt của bạn:</p>
                        <p style='background: #f2f2f2; padding: 10px; font-size: 0.8em; word-break: break-all; color: #555;'>$link</p>
                        
                        <hr style='border: none; border-top: 1px solid #eee' />
                        
                        <div style='float: right; padding: 8px 0; color: #aaa; font-size: 0.8em; line-height: 1; font-weight: 300'>
                            <p>ScheduleS Team</p>
                            <p>Hỗ trợ: support@buno.io.vn</p>
                        </div>
                    </div>
                </div>
            ";

            // Nội dung văn bản thuần (cho thiết bị không hỗ trợ HTML)
            $mail->AltBody = "Xin chào $fullname. Vui lòng truy cập link sau để đặt lại mật khẩu: $link";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mail Error: " . $mail->ErrorInfo);
            return false;
        }
    }
    /**
     * Chuẩn hóa tên địa chỉ để phù hợp với GHTK
     * @param string $city Tỉnh/Thành phố (VD: Thành phố Hà Nội)
     * @param string $ward Phường/Xã (VD: Phường Bến Nghé)
     * @param string $addressDetail Địa chỉ chi tiết
     * @return array Mảng địa chỉ chuẩn GHTK
     */
    private function normalizeForGHTK($city, $ward, $addressDetail)
    {
        // 1. MAP Tên Tỉnh/Thành đặc biệt
        // GHTK thường thích tên ngắn gọn hoặc có tiền tố cụ thể
        $provinceMap = [
            'Thành phố Hồ Chí Minh' => 'TP. Hồ Chí Minh', // Hoặc 'Hồ Chí Minh' tùy thời điểm API
            'TP Hồ Chí Minh'        => 'TP. Hồ Chí Minh',
            'Thành phố Hà Nội'      => 'Hà Nội',
            'TP Hà Nội'             => 'Hà Nội',
            'Thành phố Đà Nẵng'     => 'Đà Nẵng',
            'Thành phố Hải Phòng'   => 'Hải Phòng',
            'Thành phố Cần Thơ'     => 'Cần Thơ',
            'Thừa Thiên Huế'        => 'Thừa Thiên - Huế', // GHTK đôi khi cần dấu gạch
        ];

        // Nếu nằm trong map thì lấy, không thì dùng nguyên gốc
        $province = $provinceMap[$city] ?? $city;

        // Xử lý các tỉnh khác: Xóa chữ "Tỉnh" ở đầu nếu có (GHTK thường không cần chữ Tỉnh)
        // Ví dụ: "Tỉnh Đồng Nai" -> "Đồng Nai"
        $province = preg_replace('/^Tỉnh\s+/u', '', $province);


        // 2. Chuẩn hóa Quận/Huyện (Ở đây bạn dùng chung biến $city cho Tỉnh và Quận???)
        // LƯU Ý: Trong form của bạn, $_POST['city'] là Tỉnh. GHTK cần cả Province và District.
        // Form frontend của bạn ĐANG THIẾU input name="district".
        // Bạn cần bổ sung input hidden district vào form HTML trước khi tiếp tục.

        // Giả sử bạn đã bổ sung $_POST['district']
        $district = $_POST['district'] ?? '';

        // GHTK thường yêu cầu:
        // - "Quận 1" -> "Quận 1" (Ok)
        // - "Thành phố Thủ Đức" -> "TP. Thủ Đức"
        // - "Thị xã Bến Cát" -> "TX. Bến Cát"
        $district = str_replace(
            ['Thành phố ', 'Thị xã '],
            ['TP. ', 'TX. '],
            $district
        );


        // 3. Chuẩn hóa Phường/Xã
        // GHTK thường nhận diện tốt cả có hoặc không có tiền tố Phường/Xã
        // Nhưng an toàn nhất là giữ nguyên hoặc map theo DB của họ.
        // Để đơn giản, ta trim() khoảng trắng.
        $ward = trim($ward);

        return [
            'address'  => trim($addressDetail), // Chỉ chứa số nhà, tên đường
            'province' => trim($province),
            'district' => trim($district),
            'ward'     => trim($ward)
        ];
    }
    /**
     * Gọi API tạo đơn hàng sang Giao Hàng Tiết Kiệm
     */
    public function createOrderGHTK($orderId, $orderInfo, $cartItems)
    {
        // Cấu hình (Nên đưa vào file Config hoặc Env)
        $token = '3FKSjtO7e4qFpTmoHIP3tm3bPauYRqJLdN731sd'; // <--- THAY TOKEN CỦA BẠN VÀO ĐÂY
        $url = "https://services.giaohangtietkiem.vn/services/shipment/order"; // Môi trường thật
        // $url = "https://services.giaohangtietkiem.vn/services/shipment/order/?ver=1.5"; // Sandbox test

        // 1. Chuẩn bị dữ liệu Địa chỉ
        // Giả sử $orderInfo chứa dữ liệu raw từ form
        $addr = $this->normalizeForGHTK(
            $orderInfo['province_name'], // Tên Tỉnh từ form
            $orderInfo['ward_name'],     // Tên Xã từ form
            $orderInfo['address_detail'] // Số nhà, đường
        );
        // Lưu ý: Cần thêm tham số District vào hàm normalize và lấy từ $orderInfo['district_name']

        // 2. Chuẩn bị danh sách sản phẩm
        $products = [];
        $totalWeight = 0;
        foreach ($cartItems as $item) {
            $weight = 0.2; // Mặc định 200g mỗi món (Nên lấy từ DB sản phẩm)
            $totalWeight += $weight * $item['quantity'];

            $products[] = [
                "name"     => $item['name'],
                "weight"   => $weight,
                "quantity" => (int)$item['quantity'],
                "price"    => (int)$item['price']
            ];
        }

        // 3. Cấu trúc dữ liệu gửi GHTK
        $data = [
            "products" => $products,
            "order" => [
                "id"           => "SSB_" . $orderId, // Mã đơn hàng unique của shop
                "pick_name"    => "Shop ScheduleS", // Tên người gửi (Cấu hình trong GHTK dashboard cũng được)
                "pick_money"   => (int)$orderInfo['final_money'], // Số tiền thu hộ (COD)

                // Thông tin người nhận
                "name"         => $orderInfo['fullname'],
                "tel"          => $orderInfo['phone'],
                "email"        => $orderInfo['email'] ?? '',

                // Địa chỉ (Đã chuẩn hóa)
                "address"      => $addr['address'],
                "province"     => $addr['province'],
                "district"     => $_POST['district_name'] ?? '', // Cần lấy tên quận chuẩn
                "ward"         => $addr['ward'],
                "hamlet"       => "Khác", // Thôn/Ấp (Nếu không có thì để Khác)

                "value"        => (int)$orderInfo['total_money'], // Giá trị khai báo bảo hiểm
                "transport"    => "road", // Đường bộ (road) hoặc Bay (fly)
                "pick_option"  => "cod",  // Shipper đến lấy hàng
                "deliver_option" => "none", // xteam (giao tối), none (giao chuẩn)
                "is_freeship"  => 0 // 1: Shop trả ship, 0: Khách trả ship (GHTK thu thêm tiền ship của khách)
            ]
        ];

        // 4. Gửi Request cURL
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_UNICODE), // Quan trọng: Unicode cho tiếng Việt
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Token: {$token}",
                "Content-Length: " . strlen(json_encode($data, JSON_UNESCAPED_UNICODE))
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            return ['success' => false, 'message' => "CURL Error: " . $error_msg];
        }

        curl_close($ch);

        // 5. Xử lý kết quả
        $result = json_decode($response, true);

        // Log lại phản hồi để debug (Khuyên dùng)
        // error_log("GHTK Response for Order #$orderId: " . print_r($result, true));

        return $result;
    }
}
