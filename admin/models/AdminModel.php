<?php
class AdminModel
{
    protected $conn;

    public function __construct()
    {
        $host = "localhost";
        $db_name = "schedules";
        $username = "root";
        $password = "";

        try {
            $this->conn = new PDO(
                "mysql:host=$host;dbname=$db_name;charset=utf8mb4",
                $username,
                $password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            die("Kết nối thất bại: " . $exception->getMessage());
        }
    }
    // --- QUẢN LÝ BANNER ---

    // 1. Lấy danh sách Banner
    public function getAllBanners()
    {
        $sql = "SELECT * FROM banners ORDER BY position ASC, created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. Thêm Banner mới
    public function addBanner($image_url, $link, $position, $status)
    {
        $sql = "INSERT INTO banners (image_url, link, position, status) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$image_url, $link, $position, $status]);
    }

    // 3. Xóa Banner
    public function deleteBanner($id)
    {
        $sql = "DELETE FROM banners WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    // 4. Cập nhật trạng thái Banner (Ẩn/Hiện)
    public function updateBannerStatus($id, $status)
    {
        $sql = "UPDATE banners SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$status, $id]);
    }

    // 5. Cập nhật thông tin Banner (nếu cần sửa link/vị trí)
    public function updateBanner($id, $link, $position)
    {
        $sql = "UPDATE banners SET link = ?, position = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$link, $position, $id]);
    }
    // Trong Admin_model.php
    public function getBannerById($id)
    {
        $sql = "SELECT * FROM banners WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getVoucherHistory()
    {
        $sql = "SELECT 
            uv.id,
            uv.order_id,
            uv.used_at,
            u.fullname,
            u.email,
            v.code,
            v.discount_value
        FROM user_vouchers uv
        LEFT JOIN users u ON uv.user_id = u.id
        LEFT JOIN vouchers v ON uv.voucher_id = v.id
        ORDER BY uv.used_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    //==============================USER====================================
    public function getAllusers()
    {
        // Giữ lại câu truy vấn sử dụng CASE để hiển thị 'admin' hoặc 'user'
        $stmt = $this->conn->prepare("
            SELECT 
                *, 
                CASE role 
                    WHEN 1 THEN 'admin' 
                    WHEN 0 THEN 'user' 
                    ELSE 'unknown' 
                END AS role_name 
            FROM users
        ");
        $stmt->execute();

        // Hàm thoát và trả về kết quả đã được xử lý role_name
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function addUser($fullname, $email, $phone, $password, $role) // CHỈ CẦN 4 THAM SỐ
    {
        $stmt = $this->conn->prepare("
            INSERT INTO users (fullname, email,phone, password,role, status, created_at) 
            VALUES (?, ?, ?, ?, ?,'1', NOW())
        ");
        // CHỈ CẦN 4 GIÁ TRỊ TƯƠNG ỨNG VỚI 4 PLACEHOLDER ?
        return $stmt->execute([$fullname, $email, $phone, $password, $role]);
    }
    public function checkEmailExists($email)
    {
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC); // Trả về TRUE nếu tồn tại
    }

    public function sua_user($id, $fullname, $email, $phone, $password, $role)
    {
        // Chuyển đổi role từ chuỗi sang int (vì form gửi "user"/"admin")

        $set_clauses = [
            'fullname = :fullname',
            'email = :email',
            'phone = :phone',
            'role = :role'
        ];
        $params = [
            ':fullname' => $fullname,
            ':email'    => $email,
            ':phone'    => $phone,
            ':role'     => $role,
            ':id'       => $id
        ];

        // Chỉ cập nhật mật khẩu nếu người dùng nhập (khác rỗng)
        if (!empty($password)) {
            $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
            $set_clauses[] = 'password = :password';
        }

        $sql = "UPDATE users SET " . implode(', ', $set_clauses) . " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute($params);
    }

    public function xoa_user($id)
    {
        // Xóa user theo ID
        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
        $result = $stmt->execute([$id]);

        // Nếu xóa thành công → reset AUTO_INCREMENT
        if ($result) {
            $this->conn->query("ALTER TABLE users AUTO_INCREMENT = 1");
        }

        return $result;
    }
    public function updateVoucher($id, $data)
    {
        try {
            $sql = "UPDATE vouchers SET 
                    code = :code,
                    discount_type = :discount_type,
                    discount_value = :discount_value,
                    min_order_amount = :min_order_amount,
                    quantity = :quantity,
                    start_date = :start_date,
                    end_date = :end_date,
                    status = :status
                    WHERE id = :id";

            $stmt = $this->conn->prepare($sql);

            // Gộp ID vào mảng data để bind param
            $data['id'] = $id;

            return $stmt->execute($data);
        } catch (PDOException $e) {
            return false;
        }
    }
    public function getUserById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC); // Lấy về một hàng duy nhất
    }
    public function capNhatStatus($id, $status)
    {
        $sql = "UPDATE users SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':status' => $status,
            ':id'     => $id
        ]);
    }
    //=============================USER=====================================

    public function getOrder()
    {
        // 1. Lấy danh sách đơn hàng, JOIN với users và payment_history
        $stmt = $this->conn->prepare("
            SELECT 
                o.*, 
                ph.status AS payment_status, 
                ph.payment_method,
                u.fullname AS user_name,     -- <<< Tên người đăng nhập đặt hàng
                u.email AS user_email
                FROM orders o
                LEFT JOIN payment_history ph ON o.id = ph.order_id
                LEFT JOIN users u ON o.user_id = u.id  -- <<< JOIN với bảng users
                ORDER BY o.created_at DESC
            ");
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Lặp qua từng đơn hàng để lấy chi tiết sản phẩm + biến thể (Giữ nguyên)
        foreach ($orders as &$order) {
            $stmt_details = $this->conn->prepare("
            SELECT 
                od.product_name, 
                od.quantity, 
                od.price, 
                od.total_price,
                pv.ram, 
                pv.rom, 
                pv.color, 
                pv.image as variant_image
            FROM order_details od
            LEFT JOIN product_variants pv ON od.product_variant_id = pv.id
            WHERE od.order_id = :order_id
        ");
            $stmt_details->execute(['order_id' => $order['id']]);

            $order['details'] = $stmt_details->fetchAll(PDO::FETCH_ASSOC);
        }

        return $orders;
    }
    // Thêm vào AdminModel.php
    public function getOrderById($id)
    {
        $stmt = $this->conn->prepare("SELECT id, fullname, phone, address, status, total_money, payment_method FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Thêm vào trong class AdminModel
    // File: models/AdminModel.php

    public function updateOrder($id, $fullname, $phone, $address, $status, $note)
    {
        try {
            $sql = "UPDATE orders 
                    SET fullname = :fullname, 
                        phone = :phone, 
                        address = :address, 
                        status = :status, 
                        note = :note 
                    WHERE id = :id";

            $stmt = $this->conn->prepare($sql);

            // Gán dữ liệu vào các tham số
            $stmt->execute([
                ':fullname' => $fullname,
                ':phone'    => $phone,
                ':address'  => $address,
                ':status'   => $status,
                ':note'     => $note,
                ':id'       => $id
            ]);

            return true; // Trả về true nếu chạy thành công
        } catch (PDOException $e) {
            // Bạn có thể ghi log lỗi ở đây nếu cần
            return false;
        }
    }

    public function deleteOrder($id)
    {
        try {
            // 1. Bắt đầu giao dịch (Transaction)
            // Giúp đảm bảo xóa sạch hết hoặc không xóa gì cả (nếu lỗi)
            $this->conn->beginTransaction();

            // 2. Xóa dữ liệu ở TẤT CẢ các bảng con liên quan

            // Bảng: Lịch sử thanh toán
            $this->conn->prepare("DELETE FROM payment_history WHERE order_id = ?")->execute([$id]);

            // Bảng: Theo dõi vận đơn (Mới thêm)
            $this->conn->prepare("DELETE FROM shipping_tracking WHERE order_id = ?")->execute([$id]);

            // Bảng: Lịch sử dùng Voucher (Mới thêm)
            $this->conn->prepare("DELETE FROM user_vouchers WHERE order_id = ?")->execute([$id]);

            // Bảng: Chi tiết đơn hàng (Sản phẩm)
            $this->conn->prepare("DELETE FROM order_details WHERE order_id = ?")->execute([$id]);

            // 3. Cuối cùng mới xóa Đơn hàng chính (Bảng cha)
            $stmt = $this->conn->prepare("DELETE FROM orders WHERE id = ?");
            $result = $stmt->execute([$id]);

            // 4. Xác nhận (Commit) để lưu thay đổi
            $this->conn->commit();

            return $result;
        } catch (PDOException $e) {
            // Nếu có bất kỳ lỗi nào, hoàn tác lại (Rollback)
            $this->conn->rollBack();
            return false;
        }
    }
    // --- QUẢN LÝ VẬN CHUYỂN ---

    // 1. Lấy danh sách vận đơn (Kết hợp thông tin đơn hàng)
    public function getShippingList()
    {
        $sql = "SELECT st.*, o.code as order_code, o.fullname, o.phone, o.address 
                    FROM shipping_tracking st
                    JOIN orders o ON st.order_id = o.id
                    ORDER BY st.updated_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // 2. Cập nhật thông tin vận đơn
    public function updateTracking($id, $provider, $tracking_code, $status)
    {
        $sql = "UPDATE shipping_tracking 
                    SET provider = ?, tracking_code = ?, status = ?, updated_at = NOW() 
                    WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$provider, $tracking_code, $status, $id]);
    }
    public function getAllVouchers()
    {
        $stmt = $this->conn->prepare("SELECT * FROM vouchers ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function deleteVoucher($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM vouchers WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Lấy voucher theo id để sửa
    public function getVoucherById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM vouchers WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addVoucher($data)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO vouchers 
                (code, discount_type, discount_value, min_order_amount, quantity, start_date, end_date, status) 
            VALUES 
                (:code, :discount_type, :discount_value, :min_order_amount, :quantity, :start_date, :end_date, :status)
        ");
        return $stmt->execute($data);
    }
    // --- QUẢN LÝ KHO (STOCK) ---

    // 1. Trừ tồn kho khi đơn hàng hoàn thành
    public function updateStock($order_id)
    {
        // Lấy danh sách sản phẩm trong đơn hàng đó
        $sql = "SELECT product_variant_id, quantity FROM order_details WHERE order_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$order_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Duyệt qua từng sản phẩm để trừ kho
        foreach ($items as $item) {
            if (!empty($item['product_variant_id'])) {
                $this->decreaseVariantStock($item['product_variant_id'], $item['quantity']);
            }
        }
    }
    public function restoreStock($order_id)
    {
        // 1. Lấy danh sách sản phẩm trong đơn hàng
        $sql = "SELECT product_variant_id, quantity FROM order_details WHERE order_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$order_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Duyệt qua từng sản phẩm để CỘNG lại kho
        foreach ($items as $item) {
            if (!empty($item['product_variant_id']) && $item['product_variant_id'] > 0) {
                $sql_update = "UPDATE product_variants SET stock = stock + ? WHERE id = ?";
                $this->conn->prepare($sql_update)->execute([$item['quantity'], $item['product_variant_id']]);
            }
        }
    }
    // 2. Hàm trừ số lượng tồn kho của 1 biến thể
    private function decreaseVariantStock($variant_id, $quantity)
    {
        // Trừ stock, nhưng không để âm (GREATEST(0, ...))
        $sql = "UPDATE product_variants 
                    SET stock = GREATEST(0, stock - ?) 
                    WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$quantity, $variant_id]);
    }
    // ==================== QUẢN LÝ ĐÁNH GIÁ / BÌNH LUẬN (bảng reviews) ====================
    public function getAllComments($search = '')
    {
        $sql = "SELECT 
                        r.id,
                        r.product_id,
                        r.user_id,
                        r.comment AS content,
                        r.status,
                        r.rating,
                        r.created_at,
                        p.name AS product_name,
                        COALESCE(u.fullname, 'Khách vãng lai') AS user_name
                    FROM reviews r
                    LEFT JOIN products p ON r.product_id = p.id
                    LEFT JOIN users u ON r.user_id = u.id
                    WHERE 1=1";

        $params = [];
        if ($search !== '') {
            $like = "%$search%";
            $sql .= " AND (r.comment LIKE ? OR u.fullname LIKE ? OR p.name LIKE ?)";
            $params = [$like, $like, $like];
        }

        $sql .= " ORDER BY r.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 5. Toggle trạng thái duyệt (0 ↔ 1)
    public function toggleCommentStatus($id)
    {
        $sql = "UPDATE reviews SET status = IF(status = 1, 0, 1) WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    // 7. Xóa comment
    public function deleteComment($id)
    {
        $sql = "DELETE FROM reviews WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
    //==================COMMENTS=====================================


    // ==================== THỐNG KÊ DASHBOARD ====================
    public function getDashboardStats()
    {
        $today = date('Y-m-d');

        // 1. Đơn hàng hôm nay
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = ?");
        $stmt->execute([$today]);
        $todayOrders = (int)$stmt->fetchColumn();

        // 2. Tổng doanh thu toàn bộ thời gian (từ order_details của đơn completed)
        $stmt = $this->conn->prepare("
                SELECT COALESCE(SUM(od.total_price), 0) AS revenue
                FROM order_details od
                JOIN orders o ON od.order_id = o.id
                WHERE o.status = 'completed'
            ");
        $stmt->execute();
        $totalRevenue = (float)$stmt->fetchColumn();

        // 3. Khách hàng mới hôm nay
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM users WHERE DATE(created_at) = ?");
        $stmt->execute([$today]);
        $newCustomers = (int)$stmt->fetchColumn();

        // 4. Đơn bị hủy hôm nay
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM orders WHERE status = 'cancelled' AND DATE(created_at) = ?");
        $stmt->execute([$today]);
        $cancelledToday = (int)$stmt->fetchColumn();

        // 5. Doanh thu 7 ngày gần nhất (từ order_details)
        $revenue7days = [];
        $dates7days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $dates7days[] = date('d/m', strtotime($date));

            $stmt = $this->conn->prepare("
                    SELECT COALESCE(SUM(od.total_price), 0)
                    FROM order_details od
                    JOIN orders o ON od.order_id = o.id
                    WHERE o.status = 'completed' 
                    AND DATE(o.created_at) = ?
                ");
            $stmt->execute([$date]);
            $revenue7days[] = (float)$stmt->fetchColumn();
        }

        // 6. Thống kê số lượng đơn theo trạng thái
        $statusCount = [
            'pending'     => 0,
            'processing'  => 0,
            'shipping'    => 0,
            'completed'   => 0,
            'cancelled'   => 0
        ];
        $stmt = $this->conn->query("SELECT status, COUNT(*) as cnt FROM orders GROUP BY status");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (isset($statusCount[$row['status']])) {
                $statusCount[$row['status']] = (int)$row['cnt'];
            }
        }

        return [
            'todayOrders'    => $todayOrders,
            'totalRevenue'   => $totalRevenue,
            'newCustomers'   => $newCustomers,
            'cancelledToday' => $cancelledToday,
            'revenue7days'   => $revenue7days,
            'dates7days'     => $dates7days,
            'statusCount'    => $statusCount
        ];
    }
    // Trong class AdminModel
    public function getAllCategories()
    {
        // Dùng PDO ($this->conn) đã được cấu hình chuẩn trong __construct
        $stmt = $this->conn->prepare("SELECT * FROM categories ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getCategoryById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function updateCategory($id, $name, $slug, $status)
    {
        try {
            $sql = "UPDATE categories SET name = ?, slug = ?, status = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$name, $slug, $status, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
    public function deleteCategory($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM categories WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    // --- (Thêm vào AdminModel) ---

    // 3. Thêm mới danh mục
    public function addCategory($name, $slug, $status)
    {
        try {
            $created_at = date('Y-m-d H:i:s');
            $sql = "INSERT INTO categories (name, slug, status, created_at) VALUES (?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$name, $slug, $status, $created_at]);
        } catch (PDOException $e) {
            return false;
        }
    }
    public function deleteContacts($ids)
    {
        // $ids là mảng [1, 2, 3...]
        if (empty($ids)) return false;

        // Tạo chuỗi placeholder (?,?,?)
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "DELETE FROM contact_messages WHERE id IN ($placeholders)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($ids);
    }
    // 1. Lấy danh sách liên hệ (có phân trang)
    public function getContactsList($search = '', $status = '', $limit = 20, $offset = 0)
    {
        $sql = "SELECT * FROM contact_messages WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (fullname LIKE ? OR email LIKE ? OR phone LIKE ? OR subject LIKE ?)";
            $searchParam = "%$search%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
        }

        if ($status !== '') { // Kiểm tra khác rỗng vì status có thể là 0
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // 2. Đếm tổng số tin nhắn
    public function countContacts($search = '', $status = '')
    {
        $sql = "SELECT COUNT(*) as total FROM contact_messages WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (fullname LIKE ? OR email LIKE ? OR phone LIKE ? OR subject LIKE ?)";
            $searchParam = "%$search%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
        }

        if ($status !== '') {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    public function getAllWishlists()
    {
        // 1. Lấy thông tin Wishlist
        // 2. Lấy tên và thumbnail từ products
        // 3. Lấy tên người dùng từ users
        // 4. Lấy GIÁ THẤP NHẤT (MIN) từ bảng product_variants làm giá hiển thị
        $sql = "SELECT w.id, w.created_at, 
                   p.name AS product_name, 
                   p.thumbnail, 
                   u.fullname AS user_name,
                   MIN(pv.price) AS price
            FROM wishlists w
            JOIN products p ON w.product_id = p.id
            JOIN users u ON w.user_id = u.id
            LEFT JOIN product_variants pv ON p.id = pv.product_id
            GROUP BY w.id
            ORDER BY w.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function deleteWishlist($id)
    {
        $sql = "DELETE FROM wishlists WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
    public function updatePaymentStatus($order_id, $payment_status)
    {
        // Cập nhật trạng thái thanh toán trong payment_history
        $sql = "UPDATE payment_history SET status = ? WHERE order_id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$payment_status, $order_id]);
    }
    // ==================== QUẢN LÝ IMEI / SERIAL ====================

    // 1. Thêm mới 1 IMEI
    public function addImei($variant_id, $imei)
    {
        try {
            $stmt = $this->conn->prepare("INSERT INTO imei_numbers (product_variant_id, imei, status) VALUES (?, ?, 'available')");
            return $stmt->execute([$variant_id, $imei]);
        } catch (PDOException $e) {
            // Trả về false nếu lỗi (ví dụ trùng IMEI)
            return false;
        }
    }

    // 2. Xóa IMEI
    public function deleteImei($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM imei_numbers WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // 3. Cập nhật trạng thái IMEI
    public function updateImeiStatus($id, $status)
    {
        $stmt = $this->conn->prepare("UPDATE imei_numbers SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    // 4. Lấy danh sách biến thể (kèm tên sản phẩm) để hiển thị trong Dropdown
    public function getAllVariantsForImei()
    {
        $sql = "SELECT v.id, p.name, v.ram, v.rom, v.color 
                FROM product_variants v 
                JOIN products p ON v.product_id = p.id 
                ORDER BY p.name ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 5. Lấy danh sách IMEI có phân trang và tìm kiếm
    public function getImeisList($search = '', $status_filter = '', $limit = 20, $offset = 0)
    {
        $sql = "SELECT i.*, p.name as product_name, v.ram, v.rom, v.color 
                FROM imei_numbers i 
                JOIN product_variants v ON i.product_variant_id = v.id 
                JOIN products p ON v.product_id = p.id 
                WHERE 1=1";

        $params = [];

        if (!empty($search)) {
            $sql .= " AND (i.imei LIKE ? OR p.name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if (!empty($status_filter)) {
            $sql .= " AND i.status = ?";
            $params[] = $status_filter;
        }

        $sql .= " ORDER BY i.id DESC LIMIT $limit OFFSET $offset";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 6. Đếm tổng số lượng IMEI (để tính phân trang)
    public function countImeis($search = '', $status_filter = '')
    {
        $sql = "SELECT COUNT(*) as total 
                FROM imei_numbers i 
                JOIN product_variants v ON i.product_variant_id = v.id 
                JOIN products p ON v.product_id = p.id 
                WHERE 1=1";

        $params = [];

        if (!empty($search)) {
            $sql .= " AND (i.imei LIKE ? OR p.name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if (!empty($status_filter)) {
            $sql .= " AND i.status = ?";
            $params[] = $status_filter;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
}
