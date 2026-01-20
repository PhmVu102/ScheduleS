<?php
class UserModel
{
    private $conn;

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
                $password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            // --- [THÊM DÒNG NÀY] ĐỂ ĐỒNG BỘ GIỜ VIỆT NAM (GMT+7) ---
            $this->conn->exec("SET time_zone = '+07:00';");
        } catch (PDOException $e) {
            die("Kết nối thất bại: " . $e->getMessage());
        }
    }
    public function getBanners($position = null)
    {
        if ($position) {
            $sql = "SELECT * FROM banners WHERE position = :pos AND status = 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':pos' => $position]);
        } else {
            // Nếu không truyền position, lấy TẤT CẢ banner đang active
            $sql = "SELECT * FROM banners WHERE status = 1";
            $stmt = $this->conn->query($sql);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function saveLocation($lat, $lon, $address, $userId = null)
    {
        try {
            $sql = "INSERT INTO user_locations (user_id, latitude, longitude, address) 
                    VALUES (:user_id, :lat, :lon, :address)";

            $stmt = $this->conn->prepare($sql);

            $stmt->bindParam(':user_id', $userId); // Có thể là null
            $stmt->bindParam(':lat', $lat);
            $stmt->bindParam(':lon', $lon);
            $stmt->bindParam(':address', $address);

            return $stmt->execute(); // Trả về true nếu thành công
        } catch (PDOException $e) {
            // Ghi log lỗi nếu cần
            return false;
        }
    }
    public function login($email, $password)
    {
        // SỬA: Xóa "AND status = 1" để tìm được cả tài khoản bị khóa
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");

        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return $user; // Trả về user để Controller tự kiểm tra status
        }
        return false;
    }
    public function increaseViewCount($id)
    {
        // Câu lệnh SQL cộng dồn view_count
        $sql = "UPDATE products SET view_count = view_count + 1 WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
    // Đăng ký (bắt buộc email + fullname)
    public function register($fullname, $email, $password, $phone = null)
    {
        if ($this->emailExists($email)) {
            return false; // Email đã tồn tại
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO users (fullname, email, password, phone) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$fullname, $email, $hash, $phone]);
    }

    public function emailExists($email)
    {
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->rowCount() > 0;
    }

    public function getUserById($id)
    {
        $stmt = $this->conn->prepare("
            SELECT id, fullname, email, password, phone, role, created_at 
            FROM users 
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Trong class UserModel

    // models/UserModel.php

    public function getDefaultAddress($id)
    {
        // 1. Ưu tiên tìm địa chỉ mặc định (is_default = 1)
        $sql = "SELECT * FROM user_addresses WHERE user_id = ? AND is_default = 1 LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return $result;
        }

        // 2. Nếu không có mặc định, lấy địa chỉ mới thêm gần nhất
        $sql2 = "SELECT * FROM user_addresses WHERE user_id = ? ORDER BY id DESC LIMIT 1";
        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->execute([$id]);

        // Trả về mảng dữ liệu hoặc false nếu chưa có địa chỉ nào
        return $stmt2->fetch(PDO::FETCH_ASSOC);
    }
    // Hàm cập nhật thông tin cá nhân
    public function updateProfile($id, $fullname, $phone)
    {
        $stmt = $this->conn->prepare("UPDATE users SET fullname = ?, phone = ? WHERE id = ?");
        return $stmt->execute([$fullname, $phone, $id]);
    }

    // Hàm đổi mật khẩu
    public function changePassword($id, $new_password)
    {
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$hash, $id]);
    }
    public function getProducts($filters = [], $limit = 12, $offset = 0)
    {
        // --- QUERY CHÍNH ---
        $sql = "SELECT p.*, 
                    b.name as brand_name, 
                    MIN(pv.price) as price, 
                    MIN(NULLIF(pv.price_sale, 0)) as price_sale,
                    pv.ram, pv.rom, pv.image as variant_image,
                    ld.cpu, ld.screen as lap_screen,
                    pd.chipset, pd.screen as phone_screen
            FROM products p
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN product_variants pv ON p.id = pv.product_id
            LEFT JOIN laptop_details ld ON p.id = ld.product_id
            LEFT JOIN phone_details pd ON p.id = pd.product_id
            WHERE p.status = 1";

        $params = [];

        // 1. Lọc theo Category
        if (!empty($filters['category'])) {
            $sql .= " AND p.type = :category";
            $params[':category'] = $filters['category'];
        }

        // 2. Lọc theo Brand
        if (!empty($filters['brand'])) {
            $sql .= " AND b.name = :brand";
            $params[':brand'] = $filters['brand'];
        }

        // Tìm kiếm theo tên sản phẩm
        if (!empty($filters['keyword'])) {
            $sql .= " AND p.name LIKE :keyword";
            $params[':keyword'] = '%' . $filters['keyword'] . '%';
        }

        // Group by
        $sql .= " GROUP BY p.id HAVING 1=1";

        // 3. Lọc theo Giá
        if (!empty($filters['price'])) {
            switch ($filters['price']) {
                case 'under15':
                    $sql .= " AND price < 15000000";
                    break;
                case '15-25':
                    $sql .= " AND price >= 15000000 AND price < 25000000";
                    break;
                case '25-35':
                    $sql .= " AND price >= 25000000 AND price < 35000000";
                    break;
                case 'over35':
                    $sql .= " AND price >= 35000000";
                    break;
            }
        }

        $sql .= " ORDER BY p.created_at DESC";

        // --- [MỚI] THÊM LIMIT OFFSET CHO PHÂN TRANG ---
        // Lưu ý: Chỉ áp dụng phân trang khi không phải là 'retry' (tìm kiếm lại)
        // Hoặc cứ áp dụng luôn cũng được, nhưng cần cẩn thận logic đệ quy
        $sql .= " LIMIT $limit OFFSET $offset";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // --- PHẦN 2: LOGIC "AI" TÌM KIẾM ---
        // (Giữ nguyên logic cũ, nhưng lưu ý khi đệ quy gọi lại hàm thì cũng phải truyền limit/offset)

        if (empty($results) && !empty($filters['keyword']) && !isset($filters['is_retry'])) {
            $suggestedKeyword = $this->findClosestKeyword($filters['keyword']);

            if ($suggestedKeyword) {
                $filters['keyword'] = $suggestedKeyword;
                $filters['is_retry'] = true;

                // Gọi đệ quy: Reset offset về 0 vì đây là kết quả tìm kiếm mới
                $results = $this->getProducts($filters, $limit, 0);

                if (!empty($results)) {
                    $results[0]['suggestion_original'] = $params[':keyword'];
                    $results[0]['suggestion_new'] = $suggestedKeyword;
                }
            }
        }

        return $results;
    }

    // --- HÀM PHỤ TRỢ (Thêm vào bên dưới hàm getProducts) ---
    private function findClosestKeyword($input)
    {
        // Lấy danh sách TÊN tất cả sản phẩm (Query nhẹ thôi)
        $sql = "SELECT name FROM products";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $allNames = $stmt->fetchAll(PDO::FETCH_COLUMN); // Chỉ lấy cột name thành mảng phẳng

        $bestMatch = null;
        $shortestDistance = -1;
        $input = strtolower(trim($input));

        foreach ($allNames as $name) {
            // So sánh chuỗi nhập vào với tên sản phẩm trong DB
            $dbName = strtolower($name);

            // Tính khoảng cách Levenshtein
            $distance = levenshtein($input, $dbName);

            // Logic kiểm tra độ khớp:
            // 1. Nếu distance = 0 (khớp 100% - trường hợp này ít xảy ra ở đây vì đã qua SQL like)
            // 2. Nếu distance thấp (sai 1-3 ký tự)
            // 3. Hoặc từ khoá nhập vào là một phần của tên sản phẩm (strpos)

            if ($distance == 0 || strpos($dbName, $input) !== false) {
                return $name; // Trả về ngay nếu khớp
            }

            // Tìm từ có khoảng cách nhỏ nhất (giống nhất)
            if ($shortestDistance < 0 || $distance < $shortestDistance) {
                $shortestDistance = $distance;
                $bestMatch = $name;
            }
        }

        // Ngưỡng chấp nhận: Chỉ gợi ý nếu sai khác <= 3 ký tự (hoặc tuỳ chỉnh theo độ dài từ)
        if ($shortestDistance <= 3) {
            return $bestMatch;
        }

        return null; // Không tìm thấy từ nào giống
    }
    public function getProductById($id)
    {
        // 1. Lấy thông tin chung + Brand
        $sql = "SELECT p.*, b.name as brand_name 
            FROM products p 
            LEFT JOIN brands b ON p.brand_id = b.id 
            WHERE p.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) return null;

        // 2. Lấy thông tin cấu hình chi tiết (tùy theo loại laptop hay phone)
        if ($product['type'] == 'laptop') {
            $sql_detail = "SELECT * FROM laptop_details WHERE product_id = ?";
        } else {
            $sql_detail = "SELECT * FROM phone_details WHERE product_id = ?";
        }

        $stmt_detail = $this->conn->prepare($sql_detail);
        $stmt_detail->execute([$id]);
        $details = $stmt_detail->fetch(PDO::FETCH_ASSOC);

        // Gộp cấu hình vào mảng product
        if ($details) {
            $product['specs'] = $details;
        }

        // 3. Lấy các phiên bản (Màu sắc, RAM, Giá...)
        // Sắp xếp để giá thấp nhất lên đầu
        $sql_variants = "SELECT * FROM product_variants WHERE product_id = ? ORDER BY price ASC";
        $stmt_variants = $this->conn->prepare($sql_variants);
        $stmt_variants->execute([$id]);
        $product['variants'] = $stmt_variants->fetchAll(PDO::FETCH_ASSOC);

        return $product;
    }
    // ... Bên trong class UserModel ...

    // 1. Lấy danh sách đánh giá của sản phẩm (Kèm tên người dùng)
    // 1. Lấy danh sách đánh giá (Sửa lại câu SQL cũ)
    public function addReview($user_id, $product_id, $rating, $comment, $parent_id = null, $status = 0)
    {
        $sql = "INSERT INTO reviews (user_id, product_id, rating, comment, parent_id, status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$user_id, $product_id, $rating, $comment, $parent_id, $status]);
    }

    // 2. Cập nhật hàm lấy bình luận (Chỉ lấy status = 1)
    public function getReviews($product_id)
    {
        $sql = "SELECT r.*, u.fullname, u.role 
                FROM reviews r 
                JOIN users u ON r.user_id = u.id 
                WHERE r.product_id = ? 
                AND r.status = 1  -- CHỈ LẤY BÌNH LUẬN ĐÃ DUYỆT
                ORDER BY r.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$product_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. Lấy sản phẩm liên quan (Cùng danh mục, trừ sản phẩm hiện tại)
    public function getRelatedProducts($category_id, $current_product_id)
    {
        // Lấy 4 sản phẩm cùng danh mục, kèm giá thấp nhất từ bảng variants
        $sql = "SELECT p.*, MIN(pv.price) as price, MIN(pv.price_sale) as price_sale
                FROM products p
                LEFT JOIN product_variants pv ON p.id = pv.product_id
                WHERE p.category_id = ? AND p.id != ? AND p.status = 1
                GROUP BY p.id
                LIMIT 4";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$category_id, $current_product_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // models/UserModel.php

    public function getBestsellers($limit = 5)
    {
        $sql = "SELECT p.*, 
                   MIN(pv.price) as price, 
                   MIN(NULLIF(pv.price_sale, 0)) as price_sale, -- THÊM DÒNG NÀY
                   IFNULL(AVG(r.rating), 5) as rating
            FROM products p
            LEFT JOIN product_variants pv ON p.id = pv.product_id
            LEFT JOIN reviews r ON p.id = r.product_id
            WHERE p.status = 1
            GROUP BY p.id
            ORDER BY p.view_count DESC
            LIMIT $limit";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Kiểm tra mã giảm giá
    public function getVoucherByCode($code)
    {
        $sql = "SELECT * FROM vouchers 
                WHERE code = ? 
                AND status = 1 
                AND quantity > 0 
                AND start_date <= NOW() 
                AND end_date >= NOW() 
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // (Tùy chọn) Hàm giảm số lượng voucher sau khi đặt hàng thành công
    // Hàm giảm số lượng voucher sau khi đặt hàng thành công
    public function decreaseVoucherQuantity($code)
    {
        // Chỉ trừ khi số lượng > 0
        $sql = "UPDATE vouchers SET quantity = quantity - 1 WHERE code = ? AND quantity > 0";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$code]);
    }
    // models/UserModel.php

    public function createOrder($orderData, $cartItems)
    {
        try {
            // 1. Bắt đầu transaction (để đảm bảo toàn vẹn dữ liệu)
            $this->conn->beginTransaction();

            // 2. Thêm vào bảng ORDERS
            $sql = "INSERT INTO orders (user_id, code, fullname, phone, address, note, total_money, shipping_fee, discount_money, final_money, payment_method, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $orderData['user_id'],
                $orderData['code'],
                $orderData['fullname'],
                $orderData['phone'],
                $orderData['address'],
                $orderData['note'],
                $orderData['total_money'],
                $orderData['shipping_fee'],
                $orderData['discount_money'],
                $orderData['final_money'],
                $orderData['payment_method']
            ]);

            // Lấy ID đơn hàng vừa tạo
            $order_id = $this->conn->lastInsertId();

            // 3. Thêm vào bảng ORDER_DETAILS
            $sql_detail = "INSERT INTO order_details (order_id, product_variant_id, product_name, price, quantity, total_price) 
                           VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_detail = $this->conn->prepare($sql_detail);

            foreach ($cartItems as $item) {
                // Giả sử giỏ hàng lưu variant_id, nếu không có thì để NULL
                $variant_id = isset($item['variant_id']) ? $item['variant_id'] : null;
                $total_item_price = $item['price'] * $item['quantity'];

                $stmt_detail->execute([
                    $order_id,
                    $variant_id,
                    $item['name'],
                    $item['price'],
                    $item['quantity'],
                    $total_item_price
                ]);
            }

            // 4. Nếu mọi thứ ok thì Commit (Lưu thật)
            $this->conn->commit();
            return $order_id;
        } catch (Exception $e) {
            // Nếu có lỗi thì Rollback (Hủy hết thao tác nãy giờ)
            $this->conn->rollBack();
            return false;
        }
    }
    // Thêm vào UserModel.php
    public function getOrderById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // File: models/UserModel.php

    public function addUserAddress($user_id, $fullname, $phone, $address)
    {
        // 1. Kiểm tra xem địa chỉ này (của user này) đã tồn tại chưa để tránh trùng
        $sqlCheck = "SELECT id FROM user_addresses WHERE user_id = ? AND address = ? LIMIT 1";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->execute([$user_id, $address]);

        // Nếu đã có rồi thì dừng lại, không lưu nữa
        if ($stmtCheck->rowCount() > 0) {
            return false;
        }

        // 2. Kiểm tra user đã có địa chỉ nào chưa (để set mặc định)
        $sqlCount = "SELECT count(*) as total FROM user_addresses WHERE user_id = ?";
        $stmtCount = $this->conn->prepare($sqlCount);
        $stmtCount->execute([$user_id]);
        $row = $stmtCount->fetch(PDO::FETCH_ASSOC);

        // Nếu total = 0 (chưa có gì) -> is_default = 1, ngược lại là 0
        $is_default = ($row['total'] == 0) ? 1 : 0;

        // 3. Thêm địa chỉ mới
        $sqlInsert = "INSERT INTO user_addresses (user_id, recipient_name, phone, address, is_default) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmtInsert = $this->conn->prepare($sqlInsert);

        return $stmtInsert->execute([$user_id, $fullname, $phone, $address, $is_default]);
    }
    // Trong class UserModel

    // 1. Lấy tất cả địa chỉ của user
    public function getUserAddresses($user_id)
    {
        $sql = "SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. Lấy 1 địa chỉ cụ thể (để sửa)
    public function getAddressById($id, $user_id)
    {
        $sql = "SELECT * FROM user_addresses WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id, $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 3. Xóa địa chỉ (Chỉ xóa nếu đúng là của user đó)
    public function deleteUserAddress($id, $user_id)
    {
        // 1. Kiểm tra xem địa chỉ sắp xóa có phải là mặc định không
        $sqlCheck = "SELECT is_default FROM user_addresses WHERE id = ? AND user_id = ?";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->execute([$id, $user_id]);
        $addr = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        // 2. Xóa địa chỉ
        $sqlDelete = "DELETE FROM user_addresses WHERE id = ? AND user_id = ?";
        $stmtDelete = $this->conn->prepare($sqlDelete);
        $deleted = $stmtDelete->execute([$id, $user_id]);

        // 3. Nếu xóa thành công VÀ địa chỉ vừa xóa là mặc định
        if ($deleted && $addr && $addr['is_default'] == 1) {
            // Tìm một địa chỉ còn lại bất kỳ của user đó để set làm mặc định mới
            // Lấy cái mới nhất (ID lớn nhất)
            $sqlGetNew = "SELECT id FROM user_addresses WHERE user_id = ? ORDER BY id DESC LIMIT 1";
            $stmtGetNew = $this->conn->prepare($sqlGetNew);
            $stmtGetNew->execute([$user_id]);
            $newDefault = $stmtGetNew->fetch(PDO::FETCH_ASSOC);

            if ($newDefault) {
                // Set thành mặc định
                $sqlUpdate = "UPDATE user_addresses SET is_default = 1 WHERE id = ?";
                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                $stmtUpdate->execute([$newDefault['id']]);
            }
        }

        return $deleted;
    }

    // 4. Cập nhật địa chỉ
    public function updateUserAddress($id, $user_id, $name, $phone, $address)
    {
        $sql = "UPDATE user_addresses SET recipient_name = ?, phone = ?, address = ? WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$name, $phone, $address, $id, $user_id]);
    }
    // Trong class UserModel

    // 1. Lấy danh sách mã đơn hàng của user (để hiển thị vào select box)
    public function getUserOrders($user_id)
    {
        // SỬA: Dùng SELECT * để lấy đầy đủ fullname, phone, total_money, status...
        $sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. Lưu tin nhắn liên hệ
    public function saveContact($data)
    {
        $sql = "INSERT INTO contact_messages (user_id, fullname, email, phone, subject, order_code, message) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['user_id'],
            $data['fullname'],
            $data['email'],
            $data['phone'],
            $data['subject'],
            $data['order_code'],
            $data['message']
        ]);
    }
    // 2. Lấy chi tiết sản phẩm trong đơn hàng (kèm thông tin sản phẩm)
    // models/UserModel.php

    // File: models/UserModel.php

    public function getOrderDetail($order_id)
    {
        // Chúng ta sẽ thực hiện 2 lần JOIN:
        // 1. Join p1: Dành cho trường hợp có variant_id (đi đường chính tắc)
        // 2. Join p2: Dành cho trường hợp không có variant_id (đi đường tắt qua Tên sản phẩm)

        $sql = "SELECT od.*, 
                   COALESCE(p1.name, p2.name, od.product_name) as name, 
                   COALESCE(p1.thumbnail, p2.thumbnail) as thumbnail,pv.ram, pv.rom, pv.color 
            FROM order_details od 
            
            -- Cách 1: Tìm theo ID biến thể (Code cũ)
            LEFT JOIN product_variants pv ON od.product_variant_id = pv.id
            LEFT JOIN products p1 ON pv.product_id = p1.id 
            
            -- Cách 2: Tìm theo Tên sản phẩm (Fallback dự phòng)
            LEFT JOIN products p2 ON od.product_name = p2.name
            
            WHERE od.order_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$order_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // 4. Hủy đơn hàng (Chỉ hủy được khi đơn mới đặt - status = 0)
    // Hủy đơn hàng
    public function cancelOrder($order_id, $user_id)
    {
        // Chỉ cho phép hủy khi trạng thái là 'pending' (Chờ xử lý)
        // Và đơn hàng phải thuộc về user đó (WHERE user_id = ?)
        $sql = "UPDATE orders 
                SET status = 'cancelled' 
                WHERE id = ? AND user_id = ? AND status = 'pending'";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$order_id, $user_id]);

        // Trả về true nếu có dòng nào được update (rowCount > 0)
        return $stmt->rowCount() > 0;
    }
    // models/UserModel.php

    // Hàm đăng ký tự động trả về ID người dùng mới
    public function autoRegisterUser($fullname, $email, $phone, $plainPassword)
    {
        // Kiểm tra email đã tồn tại chưa
        if ($this->emailExists($email)) {
            return false; // Email đã có người dùng, không tạo mới
        }

        $hash = password_hash($plainPassword, PASSWORD_DEFAULT);

        // Role 0 = Khách hàng
        $sql = "INSERT INTO users (fullname, email, password, phone, role) VALUES (?, ?, ?, ?, 0)";
        $stmt = $this->conn->prepare($sql);

        if ($stmt->execute([$fullname, $email, $hash, $phone])) {
            // Trả về ID của user vừa tạo
            return $this->conn->lastInsertId();
        }
        return false;
    }
    // models/UserModel.ph

    // 2. Lấy lịch sử thanh toán theo ID đơn hàng
    public function getPaymentHistory($order_id)
    {
        $sql = "SELECT * FROM payment_history WHERE order_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$order_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // models/UserModel.php

    // Lấy thông tin vận chuyển của đơn hàng
    public function getShippingInfo($order_id)
    {
        $sql = "SELECT * FROM shipping_tracking WHERE order_id = ? ORDER BY updated_at DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$order_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function initShippingTracking($order_id)
    {
        $sql = "INSERT INTO shipping_tracking (order_id, provider, tracking_code, status, updated_at) 
                VALUES (?, 'Đang cập nhật', 'Đang cập nhật', 'Chờ lấy hàng', NOW())";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$order_id]);
    }
    // 1. Kiểm tra xem user đã dùng voucher này chưa
    public function checkUserUsedVoucher($user_id, $voucher_id)
    {
        $sql = "SELECT id FROM user_vouchers WHERE user_id = ? AND voucher_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id, $voucher_id]);
        return $stmt->rowCount() > 0; // Trả về true nếu đã dùng
    }

    // 2. Lưu lịch sử dùng voucher (Gọi khi đặt hàng thành công)
    public function saveUserVoucher($user_id, $voucher_id, $order_id)
    {
        $sql = "INSERT INTO user_vouchers (user_id, voucher_id, order_id) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$user_id, $voucher_id, $order_id]);
    }
    public function countProducts($filters = [])
    {
        // Câu SQL đếm số lượng (Lưu ý: Không join quá nhiều bảng con để tối ưu tốc độ)
        // Tuy nhiên để lọc giá chính xác thì vẫn phải join variants

        $sql = "SELECT COUNT(DISTINCT p.id) as total 
                FROM products p
                LEFT JOIN brands b ON p.brand_id = b.id
                LEFT JOIN product_variants pv ON p.id = pv.product_id
                WHERE p.status = 1";

        $params = [];

        // Copy y nguyên các điều kiện lọc từ hàm getProducts
        if (!empty($filters['category'])) {
            $sql .= " AND p.type = :category";
            $params[':category'] = $filters['category'];
        }
        if (!empty($filters['brand'])) {
            $sql .= " AND b.name = :brand";
            $params[':brand'] = $filters['brand'];
        }
        if (!empty($filters['keyword'])) {
            $sql .= " AND p.name LIKE :keyword";
            $params[':keyword'] = '%' . $filters['keyword'] . '%';
        }

        // Lưu ý: Việc đếm khi có lọc giá (HAVING) khá phức tạp trong SQL đơn giản.
        // Cách đơn giản nhất là lấy hết ID rồi đếm PHP (nhưng nặng), hoặc dùng Subquery.
        // Ở đây mình tạm bỏ qua lọc giá trong count để code đơn giản và nhanh.
        // Nếu bắt buộc đếm chuẩn theo giá, bạn cần dùng subquery: SELECT COUNT(*) FROM (SELECT ... HAVING ...) as t

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['total'];
    }
    // Lấy danh sách thương hiệu
    public function getAllBrands()
    {
        $sql = "SELECT * FROM brands WHERE status = 1 ORDER BY name ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // --- WISHLIST ---

    // 1. Lấy danh sách ID sản phẩm đã like của User (để tô đỏ trái tim khi load trang)
    public function getUserWishlistIds($user_id)
    {
        $sql = "SELECT product_id FROM wishlists WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        // Trả về mảng dạng [1, 5, 8] (chỉ lấy cột product_id)
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    // Lấy danh sách sản phẩm trong wishlist của user
    public function getWishlistItems($user_id)
    {
        // Join bảng wishlists với products và variants để lấy giá/ảnh
        $sql = "SELECT p.*, 
                       b.name as brand_name, 
                       MIN(pv.price) as price, 
                       MIN(NULLIF(pv.price_sale, 0)) as price_sale,
                       pv.image as variant_image,
                       w.created_at as liked_at
                FROM wishlists w
                JOIN products p ON w.product_id = p.id
                LEFT JOIN brands b ON p.brand_id = b.id
                LEFT JOIN product_variants pv ON p.id = pv.product_id
                WHERE w.user_id = ? AND p.status = 1
                GROUP BY p.id
                ORDER BY w.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // 2. Toggle Wishlist (Nếu có rồi thì xóa, chưa có thì thêm)
    public function toggleWishlist($user_id, $product_id)
    {
        // Kiểm tra xem đã like chưa
        $sqlCheck = "SELECT id FROM wishlists WHERE user_id = ? AND product_id = ?";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->execute([$user_id, $product_id]);

        if ($stmtCheck->rowCount() > 0) {
            // Đã like -> Xóa (Unlike)
            $sql = "DELETE FROM wishlists WHERE user_id = ? AND product_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$user_id, $product_id]);
            return 'removed';
        } else {
            // Chưa like -> Thêm (Like)
            $sql = "INSERT INTO wishlists (user_id, product_id) VALUES (?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$user_id, $product_id]);
            return 'added';
        }
    }

    // === QUÊN MẬT KHẨU ===
    // Lưu token reset
    public function saveResetToken($email, $token, $expiry)
    {
        $sql = "UPDATE users SET reset_token = ?, reset_expiry = ? WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$token, $expiry, $email]);
    }
    // Lưu token reset password vào DB
    public function updateResetToken($email, $token)
    {
        // Thiết lập thời gian hết hạn là 30 phút kể từ lúc gửi yêu cầu
        // Bạn có thể đổi '+30 minutes' thành '+1 hour' nếu muốn lâu hơn
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $expiry = date('Y-m-d H:i:s', strtotime('+30 minutes'));

        // Câu lệnh SQL update
        $sql = "UPDATE users SET reset_token = ?, reset_expiry = ? WHERE email = ?";

        $stmt = $this->conn->prepare($sql);

        // Thực thi và trả về kết quả (true/false)
        return $stmt->execute([$token, $expiry, $email]);
    }
    // Kiểm tra token hợp lệ + còn hạn + lấy user
    public function getUserByResetToken($token)
    {
        // Lấy giờ hiện tại theo múi giờ Việt Nam
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $now = date('Y-m-d H:i:s');

        $sql = "SELECT * FROM users WHERE reset_token = ? AND reset_expiry > ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$token, $now]); // So sánh expiry > $now

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Lấy user theo email (dùng cho forgot password)
    public function getUserByEmail($email)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ? AND status = 1 LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // models/UserModel.php

    public function updatePasswordAndClearToken($userId, $newPasswordHash)
    {
        // Cập nhật mật khẩu mới và xóa token/thời gian hết hạn
        $sql = "UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$newPasswordHash, $userId]);
    }
    // Lưu lịch sử thanh toán
    public function addPaymentHistory($order_id, $payment_method, $trans_code, $amount, $status)
    {
        $sql = "INSERT INTO payment_history (order_id, payment_method, transaction_code, amount, status, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$order_id, $payment_method, $trans_code, $amount, $status]);
    }
    public function updatePaymentHistory($order_id, $trans_code, $amount, $status)
    {
        // Cập nhật lại mã giao dịch, số tiền thực nhận và trạng thái thành công
        $sql = "UPDATE payment_history 
                SET transaction_code = ?, amount = ?, status = ? 
                WHERE order_id = ?";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$trans_code, $amount, $status, $order_id]);
    }
    // Lấy đơn hàng theo Mã Code (VD: ORD123456) - Dùng cho thanh toán Online
    public function getOrderByCode($code)
    {
        $sql = "SELECT * FROM orders WHERE code = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Cập nhật trạng thái đơn hàng (Sau khi thanh toán thành công)
    public function updateOrderStatus($orderId, $status)
    {
        // status: 1=Chờ xử lý, 2=Đã thanh toán/Đang giao, 3=Hoàn thành...
        $sql = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$status, $orderId]);
    }
    // --- [BỔ SUNG] TRỪ KHO KHI THANH TOÁN THÀNH CÔNG ---

    // 1. Lấy danh sách sản phẩm trong đơn hàng (chỉ cần variant_id và số lượng)
    public function getOrderItemsForStockUpdate($order_id)
    {
        $sql = "SELECT product_variant_id, quantity FROM order_details WHERE order_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$order_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. Trừ số lượng tồn kho trong bảng product_variants
    public function decreaseProductStock($variant_id, $quantity)
    {
        // Logic: Chỉ trừ khi số lượng tồn kho (quantity) lớn hơn hoặc bằng số lượng mua
        $sql = "UPDATE product_variants 
                SET quantity = quantity - ? 
                WHERE id = ? AND quantity >= ?";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$quantity, $variant_id, $quantity]);
    }
}
