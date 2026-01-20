<?php
require_once __DIR__ . '/../models/AdminModel.php';
require_once __DIR__ . '/../models/ProductModel.php';
class Admincontroller
{
    private $admin_model;
    public function __construct()
    {
        $this->admin_model = new AdminModel();
    }
    public function index()
    {
        include 'views/header.php';
    }
    // Trong file controllers/Admincontroller.php
    public function banner()
    {
        // --- XỬ LÝ POST: THÊM BANNER ---
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
            $link = $_POST['link'];
            $position = $_POST['position'];
            $status = isset($_POST['status']) ? 1 : 0;
            $image_url = '';

            // Xử lý Upload ảnh
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $target_dir = "../assets/img/"; // Đảm bảo thư mục này tồn tại
                $file_name = basename($_FILES["image"]["name"]);
                $target_file = $target_dir . $file_name;

                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image_url = "assets/img/" . $file_name; // Lưu đường dẫn tương đối vào DB
                }
            }

            if ($image_url) {
                $this->admin_model->addBanner($image_url, $link, $position, $status);
                echo "<script>alert('✅ Thêm banner thành công!'); location.href='?page=banner';</script>";
            } else {
                echo "<script>alert('❌ Lỗi: Vui lòng chọn ảnh hợp lệ!'); history.back();</script>";
            }
            exit;
        }

        // --- XỬ LÝ GET: XÓA ---
        if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
            $id = $_GET['id'];

            // BƯỚC 1: Lấy thông tin banner hiện tại để lấy đường dẫn ảnh
            // Giả sử bạn có hàm getBannerById trong model. Nếu chưa có, bạn cần tạo nó.
            $banner = $this->admin_model->getBannerById($id);
            // BƯỚC 2: Xóa ảnh vật lý (Logic bạn đã viết)
            // Kiểm tra xem trong DB có lưu tên ảnh không
            if (!empty($banner['image_url'])) {
                // Đường dẫn tuyệt đối: E:/xampp/.../ScheduleS/assets/img/product/abc.webp
                $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/ScheduleS/' . $banner['image_url'];

                // Kiểm tra file tồn tại và xóa
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            // BƯỚC 3: Xóa dữ liệu trong Database
            $this->admin_model->deleteBanner($id);

            // BƯỚC 4: Chuyển hướng
            echo "<script>location.href='?page=banner';</script>";
            exit;
        }

        // --- XỬ LÝ POST: CẬP NHẬT TRẠNG THÁI (AJAX Toggle) ---
        if (isset($_GET['action']) && $_GET['action'] == 'toggle_status' && isset($_GET['id'])) {
            $id = $_GET['id'];
            $current_status = $_GET['status']; // Status hiện tại (0 hoặc 1)
            $new_status = ($current_status == 1) ? 0 : 1;
            $this->admin_model->updateBannerStatus($id, $new_status);
            echo "<script>location.href='?page=banner';</script>";
            exit;
        }

        // --- HIỂN THỊ DANH SÁCH ---
        $this->index(); // Load header/sidebar
        $banners = $this->admin_model->getAllBanners();
        include 'views/banner/banner.php';
    }
    public function voucher_history()
    {
        // 1. Lấy từ khóa tìm kiếm từ URL (nếu có)
        $search = $_GET['search'] ?? '';

        // 2. Gọi Model để lấy danh sách
        $vouchers = $this->admin_model->getVoucherHistory();

        // 3. Include View để hiển thị
        // Lưu ý: Đường dẫn include phải đúng với cấu trúc thư mục của bạn
        // Giả sử bạn có thư mục views/admin/
        $this->index();
        include 'views/admin_user_vouchers.php'; // File view nội dung (tạo ở Bước 3)
        // include 'views/admin/footer.php'; // Footer nếu có
    }
    public function orders()
    {
        // 1. XỬ LÝ XÓA ĐƠN HÀNG (Nếu có tham số action=delete trên URL)
        if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
            $id = $_GET['id'];

            // Gọi Model để xóa
            $result = $this->admin_model->deleteOrder($id);

            if ($result) {
                // Xóa xong thì thông báo và load lại trang để mất tham số trên URL
                echo "<script>alert('✅ Xóa đơn hàng thành công!'); window.location.href='admin.php?page=orders';</script>";
            } else {
                echo "<script>alert('❌ Lỗi: Không thể xóa đơn hàng này!'); window.location.href='admin.php?page=orders';</script>";
            }
            exit; // Dừng code để không chạy phần hiển thị bên dưới ngay lập tức
        }

        // 2. HIỂN THỊ DANH SÁCH (Code cũ)
        $this->index();
        $orders = $this->admin_model->getOrder();
        include 'views/orders/orders.php';
    }
    // 2. API Cập nhật trạng thái (Dành cho Javascript gọi)
    public function update_order()
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!empty($data['id'])) {
            $id = $data['id'];
            $fullname = $data['fullname'];
            $phone = $data['phone'];
            $address = $data['address'];
            $new_status = $data['status'];
            $note = $data['note'];
            $new_payment_status = $data['payment_status'] ?? null;

            // Lấy thông tin đơn hàng hiện tại từ DB
            $current_order = $this->admin_model->getOrderById($id);

            // Lấy trạng thái cũ
            $current_status = $current_order['status'] ?? '';

            // Lấy phương thức thanh toán (chuyển về chữ thường để so sánh cho chuẩn)
            $payment_method = strtolower($current_order['payment_method'] ?? 'cod');

            // --- BƯỚC 1: KIỂM TRA LOGIC NGĂN CHẶN QUAY LẠI (Giữ nguyên của bạn) ---
            if (!$this->isStatusTransitionAllowed($current_status, $new_status)) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => "❌ Không thể chuyển từ trạng thái '{$current_status}' sang '{$new_status}'!"]);
                exit();
            }

            // 1. Cập nhật thông tin chung đơn hàng
            $result = $this->admin_model->updateOrder($id, $fullname, $phone, $address, $new_status, $note);

            // 2. Cập nhật trạng thái thanh toán
            $payment_result = true;
            if ($new_payment_status !== null) {
                $payment_result = $this->admin_model->updatePaymentStatus($id, $new_payment_status);
            }

            // 3. --- LOGIC TRỪ KHO (SỬA LẠI THEO YÊU CẦU) ---
            if ($result) {
                // --- A. LOGIC TRỪ TỒN KHO (Deduct Stock) ---
                $should_deduct = false;

                // Kiểm tra xem đơn hàng đã được thanh toán thành công chưa?
                // (Kiểm tra cả trạng thái mới gửi lên HOẶC trạng thái cũ trong DB)
                $is_paid_success = ($new_payment_status == 'success') || ($current_order['payment_status'] == 'success');

                if ($payment_method === 'cod') {
                    // 1. COD: Trừ khi Admin bấm Xác nhận (Confirmed)
                    // (Từ trạng thái Pending -> Confirmed)
                    if ($new_status == 'confirmed' && $current_status == 'pending') {
                        $should_deduct = true;
                    }
                } else {
                    // 2. ONLINE (Bank/Momo...): Trừ khi (Đã Thanh Toán + Đã Xác Nhận)
                    // Trường hợp này bao gồm cả việc hệ thống tự động nhảy trạng thái

                    if ($new_status == 'confirmed' && $is_paid_success) {
                        // Chỉ trừ nếu trước đó chưa bị trừ (tức là trạng thái cũ là Pending)
                        if ($current_status == 'pending') {
                            $should_deduct = true;
                        }
                    }

                    // Bổ sung: Nếu hệ thống nhảy thẳng sang Shipping (Đang giao) thì cũng phải trừ
                    if ($new_status == 'shipping' && $current_status == 'pending') {
                        $should_deduct = true;
                    }
                }

                // Thực hiện lệnh trừ kho
                if ($should_deduct) {
                    $this->admin_model->updateStock($id);
                }


                // --- B. LOGIC HOÀN TỒN KHO (Restore Stock) ---
                $should_restore = false;

                if ($new_status == 'cancelled' || $new_status == 'returned') {

                    // Logic hoàn kho: Chỉ hoàn nếu trước đó ĐÃ BỊ TRỪ.
                    // Dựa vào logic trừ ở trên, ta suy ra các trạng thái đã bị trừ:

                    if ($payment_method === 'cod') {
                        // COD: Đã trừ khi ở Confirmed, Shipping, Completed
                        if (in_array($current_status, ['confirmed', 'shipping', 'completed'])) {
                            $should_restore = true;
                        }
                    } else {
                        // Online: Cũng đã trừ khi ở Confirmed (kèm Success), Shipping, Completed
                        // Lưu ý: Nếu đơn Online ở Confirmed nhưng CHƯA thanh toán (pending) thì chưa bị trừ -> Không hoàn.
                        // Nhưng ở shop bạn, "tự động đổi sang xác nhận và đã thanh toán" => Tức là Confirmed luôn đi kèm Success.

                        if (in_array($current_status, ['confirmed', 'shipping', 'completed'])) {
                            // Kiểm tra kỹ hơn: Nếu đơn cũ là Confirmed nhưng Payment là Pending/Failed thì chưa bị trừ.
                            // Tuy nhiên để đơn giản và an toàn cho trường hợp "tự động" của bạn:
                            $should_restore = true;
                        }
                    }
                }

                // Thực hiện lệnh hoàn kho
                if ($should_restore) {
                    $this->admin_model->restoreStock($id);
                }
            }

            header('Content-Type: application/json');
            if ($result && $payment_result) {
                echo json_encode(['status' => 'success', 'message' => 'Cập nhật đơn hàng thành công!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => '❌ Lỗi: Không thể cập nhật CSDL.']);
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Dữ liệu gửi lên không hợp lệ.']);
        }
        exit();
    }
    /**
     * Kiểm tra xem việc chuyển trạng thái có được cho phép hay không.
     * @param string $current_status Trạng thái cũ
     * @param string $new_status Trạng thái mới
     * @return bool
     */
    private function isStatusTransitionAllowed($current_status, $new_status)
    {
        // Định nghĩa quy trình chuyển trạng thái hợp lệ
        // Chỉ cho phép tiến lên hoặc chuyển sang Cancelled/Returned
        $allowed_transitions = [
            'pending'   => ['confirmed', 'cancelled'],
            'confirmed' => ['shipping', 'cancelled', 'returned'],
            'shipping'  => ['completed', 'returned', 'cancelled'],
            'completed' => ['completed', 'returned'], // Hoàn thành chỉ có thể tự hoàn thành lại, hoặc trả hàng
            'cancelled' => ['cancelled'], // Đã hủy là trạng thái cuối cùng, không thể quay lại
            'returned'  => ['returned']   // Trả hàng là trạng thái cuối cùng, không thể quay lại
        ];

        // Nếu trạng thái mới không nằm trong danh sách cho phép từ trạng thái hiện tại, trả về false.
        return in_array($new_status, $allowed_transitions[$current_status] ?? []);
    }
    public function dashboard()
    {
        // Lấy dữ liệu thống kê từ Model
        $stats = $this->admin_model->getDashboardStats();

        // Truyền dữ liệu vào view
        extract($stats);

        $this->index(); // load header + sidebar
        include 'views/dashboard.php';
    }

    //====================================XUÂN HƯỜNG USER===============================================================
    public function user_management()
    {
        $this->index();
        $users = $this->admin_model->getAllusers();
        include 'views/user_management.php';
    }
    public function addUser()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: admin.php?page=formthem_user");
            exit;
        }

        // LẤY DỮ LIỆU (an toàn, không báo undefined)
        $fullname = trim($_POST['fullname'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $pass_raw = $_POST['password'] ?? '';
        $role     = $_POST['role'] ?? 'user';

        // KIỂM TRA RỖNG
        if ($fullname === '' || $email === '' || $pass_raw === '') {
            echo "<script>alert('Vui lòng nhập đầy đủ thông tin');</script>";
            echo "<script>window.location.href='?page=formthem_user';</script>";
            return;
        }

        // KIỂM TRA EMAIL TRÙNG
        if ($this->admin_model->checkEmailExists($email)) {
            echo "<script>alert('Email đã tồn tại!');</script>";
            echo "<script>window.location.href='?page=formthem_user';</script>";
            return;
        }

        // HASH MẬT KHẨU
        $password = password_hash($pass_raw, PASSWORD_BCRYPT);

        // GỌI MODEL THÊM USER
        $this->admin_model->addUser($fullname, $email, $phone, $password, $role);
        echo "<script>window.location.href='?page=user_management';</script>";
    }

    public function sua_user()
    {
        // XỬ LÝ CẬP NHẬT (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id       = $_POST['id'] ?? null;
            $fullname = trim($_POST['fullname'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $phone    = trim($_POST['phone'] ?? '');
            $password = trim($_POST['password'] ?? ''); // có thể rỗng
            $role     = $_POST['role'] ?? 'user';       // "user" hoặc "admin"

            if (!$id) {
                header("Location: admin.php?page=user_management&error=Thiếu ID");
                exit;
            }

            // Chuyển role string → int
            $role = ($role === 'admin') ? 1 : 0;

            // Chỉ hash mật khẩu nếu có nhập mới
            $hashed_password = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;
            $result = $this->admin_model->sua_user($id, $fullname, $email, $phone, $hashed_password, $role);

            if ($result) {
                header("Location: admin.php?page=user_management&success=Cập nhật thành công!");
            } else {
                header("Location: admin.php?page=sua&id=$id&error=Cập nhật thất bại!");
            }
            exit;
        }

        // HIỂN THỊ FORM SỬA (GET)
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header("Location: admin.php?page=user_management&error=Thiếu ID");
            exit;
        }

        $user_data = $this->admin_model->getUserById($id);
        if (!$user_data) {
            header("Location: admin.php?page=user_management&error=Không tìm thấy người dùng");
            exit;
        }

        $is_edit = true;
        extract(['is_edit' => $is_edit, 'user' => $user_data]);
        $this->index(); // load header + sidebar
        include 'views/formthem_user.php';
    }
    public function xoa_user()
    {
        $id = $_GET['id'] ?? null;

        // 1. Kiểm tra ID hợp lệ (rất quan trọng)
        if (empty($id) || !is_numeric($id)) {
            $error = urlencode("ID không hợp lệ.");
            header("Location: admin.php?page=user_management&error=" . $error);
            exit;
        }
        $result = $this->admin_model->xoa_user($id);

        // 3. Xử lý kết quả trả về
        if ($result) {
            $success = urlencode("Người dùng ID {$id} đã được xóa thành công.");
            header("Location: admin.php?page=user_management&success=" . $success);
        } else {
            $error = urlencode("Lỗi CSDL: Không thể xóa người dùng ID {$id}.");
            // ✅ ĐÃ SỬA: Dùng tham số &error= để chuyển hướng lỗi
            header("Location: admin.php?page=user_management&error=" . $error);
        }
        exit;
    }
    public function khoa_user()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            header("Location: admin.php?page=user_management&error=Thiếu ID");
            exit;
        }

        $result = $this->admin_model->capNhatStatus($id, 0); // 0 = khóa

        if ($result) {
            header("Location: admin.php?page=user_management&success=Đã khóa tài khoản!");
        } else {
            header("Location: admin.php?page=user_management&error=Không thể khóa!");
        }
        exit;
    }

    public function mo_khoa_user()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            header("Location: admin.php?page=user_management&error=Thiếu ID");
            exit;
        }

        $result = $this->admin_model->capNhatStatus($id, 1); // 1 = hoạt động

        if ($result) {
            header("Location: admin.php?page=user_management&success=Đã mở khóa tài khoản!");
        } else {
            header("Location: admin.php?page=user_management&error=Không thể mở khóa!");
        }
        exit;
    }

    public function formthem_user()
    {
        $this->index();
        include 'views/formthem_user.php';
    }
    //======================XUÂN HƯỜNG==============================================//
    public function categorys()
    {
        $this->index();
        // 1. Lấy dữ liệu từ Model
        // (Đảm bảo bạn đã khởi tạo $this->admin_model trong __construct của Controller)
        $categories = $this->admin_model->getAllCategories();

        // 2. Xử lý Phân trang (Logic PHP thuần)
        $limit = 10; // Số lượng hiển thị trên 1 trang
        $current_page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;

        $total_records = count($categories);
        $total_pages = ceil($total_records / $limit);
        $offset = ($current_page - 1) * $limit;

        // Cắt mảng dữ liệu cho trang hiện tại
        $list_current = array_slice($categories, $offset, $limit);

        // 3. Gọi View (Biến $list_current, $total_pages... sẽ tự động sang View)
        // Lưu ý: Không gọi $this->index() nếu nó load lại giao diện Dashboard gây trùng lặp
        include 'views/categorys.php';
    }
    //================================================COMMENTS===========================//
    public function comments()
    {
        // Nếu là AJAX POST (toggle, delete)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            header('Content-Type: application/json');

            $action = $_POST['action'] ?? '';
            $id     = (int)($_POST['id'] ?? 0);

            if ($id <= 0) {
                echo json_encode(['success' => false, 'msg' => 'ID không hợp lệ']);
                exit;
            }

            switch ($action) {
                case 'toggle':
                    $success = $this->admin_model->toggleCommentStatus($id);
                    echo json_encode(['success' => $success]);
                    exit;

                case 'delete':
                    $success = $this->admin_model->deleteComment($id);
                    echo json_encode(['success' => $success]);
                    exit;
            }

            echo json_encode(['success' => false, 'msg' => 'Hành động không hợp lệ']);
            exit;
        }

        // Nếu là GET: Hiển thị danh sách
        $search = $_GET['search'] ?? '';
        $comments = $this->admin_model->getAllComments($search);

        $this->index();
        include 'views/comments.php';
    }
    //=====================comment===========================
    public function admin_product()
    {
        $this->index();  // load header, sidebar…

        $productModel = new ProductModel();

        // Lấy dữ liệu lọc từ URL
        $search   = $_GET['search'] ?? '';
        $category = $_GET['category'] ?? '';
        $status   = $_GET['status'] ?? '';
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit    = 10;

        // Lấy danh sách sản phẩm theo trang
        $products = $productModel->getProducts($search, $category, $status, $page, $limit);

        // Tổng số sản phẩm
        $total = $productModel->countProducts($search, $category, $status);
        $pages = ceil($total / $limit);

        // Truyền dữ liệu sang view
        include 'views/admin_product.php';
    }

    public function formthem_product()
    {
        $this->index();
        include 'views/formthem_product.php';
    }
    public function shipping_tracking()
    {
        $this->index(); // Load layout admin
        $shipments = $this->admin_model->getShippingList();
        include 'views/orders/shipping_tracking.php';
    }
    public function voucher_management()
    {
        $this->index();
        $vouchers = $this->admin_model->getAllVouchers();
        include 'views/voucher_management.php';
    }
    public function update_tracking()
    {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);

        if (!empty($input['id'])) {
            $result = $this->admin_model->updateTracking(
                $input['id'],
                $input['provider'],
                $input['tracking_code'],
                $input['status']
            );

            if ($result) {
                echo json_encode(['status' => 'success', 'message' => 'Cập nhật vận đơn thành công!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Lỗi CSDL']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Thiếu dữ liệu']);
        }
        exit;
    }
    public function delete_voucher()
    {
        if (isset($_GET['id'])) {
            $this->admin_model->deleteVoucher($_GET['id']);
        }
        header("Location: ?page=voucher");
    }
    public function edit_voucher()
    {
        // 1. Lấy ID từ URL
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        // 2. Lấy thông tin voucher cũ
        // (Hàm getVoucherById đã có trong Model từ trước)
        $voucher = $this->admin_model->getVoucherById($id);

        if (!$voucher) {
            echo "<script>alert('❌ Không tìm thấy mã giảm giá!'); location.href='?page=voucher';</script>";
            exit;
        }

        $msg = '';

        // 3. Xử lý POST (Cập nhật)
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Lấy dữ liệu từ form
            $data = [
                'code' => trim($_POST['code']),
                'discount_type' => $_POST['discount_type'],
                'discount_value' => (float)$_POST['discount_value'],
                'min_order_amount' => (float)$_POST['min_order_amount'],
                'quantity' => (int)$_POST['quantity'],
                'start_date' => $_POST['start_date'],
                'end_date' => $_POST['end_date'],
                'status' => (int)$_POST['status']
            ];

            // Validate ngày tháng
            if ($data['end_date'] <= $data['start_date']) {
                $msg = "<div class='alert alert-danger'>Ngày kết thúc phải lớn hơn ngày bắt đầu!</div>";
            } else {
                // Gọi Model để update
                if ($this->admin_model->updateVoucher($id, $data)) {
                    echo "<script>alert('✅ Cập nhật voucher thành công!'); location.href='?page=voucher';</script>";
                    exit;
                } else {
                    $msg = "<div class='alert alert-danger'>Lỗi hệ thống: Không thể cập nhật!</div>";
                }
            }
        }

        // 4. Gọi View
        include 'views/edit_voucher.php';
    }
    public function add_voucher()
    {
        // Xử lý khi bấm nút Lưu (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // 1. Chuẩn bị mảng dữ liệu (Lưu ý dấu hai chấm : trước key)
            $data = [
                ':code'             => $_POST['code'],
                ':discount_type'    => $_POST['discount_type'],
                ':discount_value'   => $_POST['discount_value'],
                ':min_order_amount' => $_POST['min_order_amount'] ?? 0,
                ':quantity'         => $_POST['quantity'],
                ':start_date'       => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
                ':end_date'         => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
                ':status'           => isset($_POST['status']) ? 1 : 0
            ];

            // 2. Gọi Model và truyền ĐÚNG 1 BIẾN LÀ MẢNG $data
            // (Đừng truyền lẻ tẻ $code, $type... vào đây sẽ bị lỗi)
            if ($this->admin_model->addVoucher($data)) {
                echo "<script>alert('✅ Thêm mã giảm giá thành công!'); window.location.href='?page=voucher';</script>";
            } else {
                echo "<script>alert('❌ Lỗi: Mã code này đã tồn tại!'); window.history.back();</script>";
            }
            exit;
        }

        // Hiển thị Form thêm mới (GET)
        include 'views/formadd_voucher.php';
    }
    public function edit_category()
    {
        // 1. Lấy ID từ URL
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        // 2. Gọi Model lấy dữ liệu cũ
        $cat = $this->admin_model->getCategoryById($id);

        // Nếu không tìm thấy ID thì đuổi về trang danh sách
        if (!$cat) {
            echo "<script>alert('❌ Không tìm thấy danh mục!'); location.href='?page=categorys';</script>";
            exit;
        }

        $msg = ''; // Biến lưu thông báo lỗi

        // 3. Xử lý khi người dùng bấm nút Lưu (POST)
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = trim($_POST['name']);
            $slug = trim($_POST['slug']);
            $status = (int)$_POST['status'];

            if (empty($name)) {
                $msg = "<div class='alert-error'>Tên danh mục không được để trống!</div>";
            } else {
                // Gọi Model để Update
                $result = $this->admin_model->updateCategory($id, $name, $slug, $status);

                if ($result) {
                    echo "<script>alert('✅ Cập nhật thành công!'); location.href='?page=categorys';</script>";
                    exit;
                } else {
                    $msg = "<div class='alert-error'>Lỗi hệ thống: Không thể cập nhật!</div>";
                }
            }
        }

        // 4. Gọi View hiển thị
        include 'views/edit_category.php';
    }
    public function delete_category()
    {
        if (isset($_GET['id'])) {
            $this->admin_model->deleteCategory($_GET['id']);
        }
        header("Location: ?page=categorys");
    }
    public function add_category()
    {
        $msg = ''; // Biến lưu thông báo lỗi để hiển thị ở View

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = trim($_POST['name']);
            $slug = trim($_POST['slug']);
            $status = (int)$_POST['status'];

            if (empty($name)) {
                $msg = "<div class='alert-error'>Tên danh mục không được để trống!</div>";
            } else {
                // Gọi Model để thêm mới
                $result = $this->admin_model->addCategory($name, $slug, $status);

                if ($result) {
                    echo "<script>alert('✅ Thêm danh mục thành công!'); location.href='?page=categorys';</script>";
                    exit;
                } else {
                    $msg = "<div class='alert-error'>Lỗi hệ thống: Không thể thêm mới! (Có thể tên bị trùng)</div>";
                }
            }
        }

        // Gọi View hiển thị form
        include 'views/add_category.php';
    }
    public function contact()
    {
        $this->index();
        // --- XỬ LÝ POST: XÓA NHIỀU (AJAX gọi vào đây hoặc Form POST) ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'bulk_delete') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!empty($input['ids'])) {
                $this->admin_model->deleteContacts($input['ids']);
                echo json_encode(['status' => 'success', 'message' => 'Đã xóa các mục đã chọn!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Chưa chọn mục nào.']);
            }
            exit;
        }

        // --- XỬ LÝ GET: XÓA ĐƠN LẺ ---
        if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
            $this->admin_model->deleteContacts([$_GET['id']]); // Tận dụng hàm xóa nhiều
            echo "<script>location.href='?page=contact';</script>";
            exit;
        }

        // --- LẤY THAM SỐ TÌM KIẾM ---
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $status_filter = isset($_GET['status']) ? $_GET['status'] : '';

        // --- PHÂN TRANG ---
        $limit = 20;
        $current_page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
        $offset = ($current_page - 1) * $limit;

        // --- GỌI MODEL ---
        $total_records = $this->admin_model->countContacts($search, $status_filter);
        $total_pages = ceil($total_records / $limit);
        $contacts = $this->admin_model->getContactsList($search, $status_filter, $limit, $offset);

        // Gọi View
        include 'views/contact.php';
    }
    public function wishlist()
    {
        $this->index();
        $list_wishlist = $this->admin_model->getAllWishlists();
        include 'views/wishlist.php';
    }
    public function deleteWishlist()
    {
        if (isset($_GET['id']) && $_GET['id'] > 0) {
            $id = $_GET['id'];
            $this->admin_model->deleteWishlist($id);

            // Xóa xong quay lại trang danh sách và thông báo
            echo "<script>alert('Đã xóa thành công!'); window.location.href='index.php?page=wishlist';</script>";
        }
    }

    public function imei()
    {
        $this->index();
        // --- XỬ LÝ POST: THÊM MỚI IMEI ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_imei') {
            $variant_id = $_POST['product_variant_id'];
            $imei_list = explode("\n", $_POST['imei_list']); // Tách từng dòng
            $success = 0;

            foreach ($imei_list as $imei) {
                $imei = trim($imei);
                if (!empty($imei)) {
                    // Gọi Model để thêm
                    if ($this->admin_model->addImei($variant_id, $imei)) {
                        $success++;
                    }
                }
            }
            echo "<script>alert('Đã thêm $success IMEI thành công!'); location.href='?page=imei';</script>";
            exit;
        }

        // --- XỬ LÝ GET: XÓA IMEI ---
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
            $id = $_GET['id'];
            $this->admin_model->deleteImei($id);
            echo "<script>location.href='?page=imei';</script>";
            exit;
        }

        // --- XỬ LÝ POST: CẬP NHẬT TRẠNG THÁI ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
            $id = $_POST['id'];
            $status = $_POST['status'];
            $this->admin_model->updateImeiStatus($id, $status);
            echo "<script>location.href='?page=imei';</script>";
            exit;
        }

        // --- CHUẨN BỊ DỮ LIỆU HIỂN THỊ ---

        // 1. Lấy danh sách biến thể (cho dropdown)
        $variants = $this->admin_model->getAllVariantsForImei();

        // 2. Lấy tham số tìm kiếm
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $status_filter = isset($_GET['status']) ? $_GET['status'] : '';

        // 3. Phân trang
        $limit = 20;
        $current_page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
        $offset = ($current_page - 1) * $limit;

        // 4. Lấy dữ liệu từ Model
        $total_records = $this->admin_model->countImeis($search, $status_filter);
        $imeis = $this->admin_model->getImeisList($search, $status_filter, $limit, $offset);

        $total_pages = ceil($total_records / $limit);

        // 5. Gọi View
        include "views/imei.php";
    }
}
