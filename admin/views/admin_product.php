<?php
require_once './models/ProductModel.php';
$productModel = new ProductModel();

$search    = $_GET['search'] ?? '';
$category  = $_GET['category'] ?? '';
$status    = $_GET['status'] ?? '';
$p         = max(1, (int)($_GET['p'] ?? 1));
$limit     = 15;

$products  = $productModel->getProducts($search, $category, $status, $p, $limit);
$total     = $productModel->countProducts($search, $category, $status);
$pages     = ceil($total / $limit);

$query = http_build_query([
    'page'     => 'admin_product',
    'search'   => $search,
    'category' => $category,
    'status'   => $status
]);

$categories = $productModel->getCategories();
?>


<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Sản Phẩm - Schedules Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #1e3c72;
            --secondary: #2a5298;
        }

        .main {
            padding: 30px;
            max-width: 1500px;
            margin-left: auto;
            margin-right: auto;
        }

        .page-title {
            text-align: center;
            font-size: 28px;
            color: var(--primary);
            margin-bottom: 30px;
            font-weight: 600;
        }

        .filters {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 25px;
        }

        .filters input,
        .filters select {
            padding: 12px 18px;
            border: 1px solid #ddd;
            border-radius: 50px;
            font-size: 15px;
            min-width: 220px;
        }

        .filters button {
            padding: 12px 30px;
            background: var(--secondary);
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
        }

        .filters button:hover {
            background: #1e3c72;
        }

        table {
            width: 100%;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border-collapse: collapse;
        }

        thead {
            background: var(--primary);
            color: white;
        }

        th,
        td {
            padding: 18px 15px;
            text-align: center;
            font-size: 15px;
        }

        th {
            font-weight: 600;
        }

        tbody tr:hover {
            background: #f8f9ff;
        }

        .status {
            padding: 8px 18px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
            color: white;
        }

        .status.dangban {
            background: #28a745;
        }

        .status.ngunghoatdong {
            background: #dc3545;
        }

        .actions a {
            padding: 9px 16px;
            margin: 0 6px;
            border-radius: 50px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .actions .view {
            background: #17a2b8;
            color: white;
        }

        .actions .edit {
            background: #ffc107;
            color: #333;
        }

        .actions .delete {
            background: #dc3545;
            color: white;
        }

        .add-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 65px;
            height: 65px;
            background: var(--secondary);
            color: white;
            border-radius: 50%;
            font-size: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(42, 82, 152, 0.5);
            cursor: pointer;
            transition: 0.3s;
        }

        .add-btn:hover {
            transform: scale(1.15);
            background: #1e3c72;
        }

        .pagination {
            text-align: center;
            margin: 30px 0;
        }

        .pagination a {
            padding: 10px 18px;
            margin: 0 5px;
            background: white;
            color: var(--primary);
            border: 1px solid #ddd;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
        }

        .pagination a.active,
        .pagination a:hover {
            background: var(--secondary);
            color: white;
            border-color: var(--secondary);
        }
    </style>
</head>

<body>
    <div class="main">
        <h2 class="page-title">Quản Lý Sản Phẩm</h2>

        <form method="GET" class="filters">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm tên, slug...">
            <select name="category">
                <option value="">Tất cả danh mục</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="status">
                <option value="">Tất cả trạng thái</option>
                <option value="1" <?= $status === '1' ? 'selected' : '' ?>>Đang bán</option>
                <option value="0" <?= $status === '0' ? 'selected' : '' ?>>Ngừng bán</option>
            </select>
            <button type="submit">Tìm Kiếm</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ảnh</th>
                    <th>Tên sản phẩm</th>
                    <th>Danh mục</th>
                    <th>Thương hiệu</th>
                    <th>Giá thấp nhất</th>
                    <th>Tồn kho</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="9" style="text-align:center; padding:40px;">Không tìm thấy sản phẩm nào!</td>
                    </tr>
                    <?php else: foreach ($products as $p): ?>
                        <tr>
                            <td>#<?= $p['id'] ?></td>
                            <td>
                                <?php if (!empty($p['thumbnail'])): ?>
                                    <?php
                                    // Xử lý logic đường dẫn ảnh
                                    $thumb = $p['thumbnail'];
                                    $imgSrc = '';
                                    // 1. Nếu là link online bắt đầu bằng http
                                    if (strpos($thumb, 'http') === 0) {
                                        $imgSrc = $thumb;
                                    }
                                    // 2. Nếu là ảnh nội bộ -> thêm ../ để thoát khỏi thư mục admin
                                    else {
                                        $imgSrc = '../' . $thumb;
                                    }
                                    ?>

                                    <img src="<?= htmlspecialchars($imgSrc) ?>"
                                        width="50" height="50"
                                        style="border-radius: 8px; object-fit: cover; border: 1px solid #eee;"
                                        onerror="this.onerror=null; this.src='../assets/img/product/default.png'; this.style.objectFit='contain';" />

                                <?php else: ?>
                                    <div style="width: 50px; height: 50px; background: #f8f9fa; border-radius: 8px; display: flex; align-items: center; justify-content: center; border: 1px solid #eee;">
                                        <i class="fas fa-image" style="font-size: 20px; color: #ccc;"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:left; max-width:250px;">
                                <strong><?= htmlspecialchars($p['name']) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($p['category_name'] ?? 'Chưa có') ?></td>
                            <td><?= htmlspecialchars($p['brand_name'] ?? 'Chưa có') ?></td>
                            <td><?= $p['min_price'] ? number_format($p['min_price'], 0, ',', '.') . 'đ' : 'Chưa có giá' ?></td>
                            <td><?= $p['total_stock'] ?? 0 ?></td>
                            <td>
                                <span class="status <?= $p['status'] == 1 ? 'dangban' : 'ngunghoatdong' ?>">
                                    <?= $p['status'] == 1 ? 'Đang bán' : 'Ngừng bán' ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a href="admin.php?page=formthem_product&id=<?= $p['id'] ?>" class="edit">Sửa</a>
                                <a href="admin.php?page=admin_product&delete=<?= $p['id'] ?>" class="delete"
                                    onclick="return confirm('Xóa sản phẩm này?')">Xóa
                                </a>
                            </td>
                        </tr>
                <?php endforeach;
                endif; ?>
            </tbody>
        </table>

        <!-- Phân trang -->
        <?php
        $p = max(1, (int)($_GET['p'] ?? 1));
        $limit = 15;

        $products  = $productModel->getProducts($search, $category, $status, $p, $limit);
        $total     = $productModel->countProducts($search, $category, $status);
        $pages     = ceil($total / $limit);

        $query = http_build_query([
            'page'     => 'admin_product',
            'search'   => $search,
            'category' => $category,
            'status'   => $status
        ]);
        ?>
        <div class="pagination">
            <?php if ($p > 1): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['p' => $p - 1])) ?>">«</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $pages; $i++): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['p' => $i])) ?>"
                    class="<?= $p == $i ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($p < $pages): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['p' => $p + 1])) ?>">»</a>
            <?php endif; ?>

            <?php if ($pages <= 1): ?>
                <!-- Không hiển thị gì nếu chỉ có 1 trang hoặc ít hơn -->
            <?php endif; ?>
        </div>

    </div>
    <!-- Nút thêm sản phẩm -->
    <div class="add-btn" onclick="location.href='admin.php?page=formthem_product'">
        <i class="fas fa-plus"></i>
    </div>

    <!-- Xử lý xóa nhanh -->
    <?php
    if (isset($_GET['delete'])) {
        $id = $_GET['delete'];
        $product = $productModel->getProductById($id);

        // 1. Kiểm tra nếu có tên ảnh trong cột 'thumbnail'
        if (!empty($product['thumbnail'])) {
            // Đường dẫn trong DB: assets/img/product/xiaomi14.webp

            // Tạo đường dẫn tuyệt đối từ ổ cứng
            // Kết quả sẽ là: E:/xampp/htdocs/ScheduleS/assets/img/product/xiaomi14.webp
            $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/ScheduleS/' . $product['thumbnail'];

            // Kiểm tra file có thực sự tồn tại không rồi xóa
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        // 2. Xóa dữ liệu trong Database
        $productModel->delete($id);

        // 3. Thông báo và chuyển trang
        echo "<script>alert('Đã xóa sản phẩm và ảnh thành công!'); location.href='?page=admin_product';</script>";
    }
    ?>
</body>