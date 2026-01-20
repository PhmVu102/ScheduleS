<?php
require_once './models/ProductModel.php';
$productModel = new ProductModel();

$categories = $productModel->getCategories();
$brands     = $productModel->getBrands();

$editId = $_GET['id'] ?? 0;

$product      = null;
$phoneDetails = null;
$variants     = [];

if ($editId) {
    $product      = $productModel->getProductById($editId);
    $phoneDetails = $productModel->getPhoneDetailsByProductId($editId);
    $variants     = $productModel->getVariantsByProductId($editId);
}

// XỬ LÝ LƯU
if ($_POST) {
    $name        = trim($_POST['name'] ?? '');
    $slug        = trim($_POST['slug'] ?? '');
    $category_id = $_POST['category_id'] ?? null;
    $brand_id    = $_POST['brand_id'] ?? null;
    $description = $_POST['description'] ?? '';

    // Thông số từ bảng phone_details
    $chipset = $_POST['chipset'] ?? '';
    $ram     = $_POST['ram_device'] ?? '';
    $rom     = $_POST['storage'] ?? '';
    $screen  = $_POST['screen'] ?? '';
    $camera  = $_POST['camera'] ?? '';
    $battery = $_POST['battery'] ?? '';

    // === UPLOAD THUMBNAIL ===
    $thumbnailPath = $product['thumbnail'] ?? null;
    if (!empty($_FILES['thumbnail']['name'])) {
        $ext = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
        $fileName = time() . '_thumb_' . uniqid() . '.' . $ext;

        $targetDir = __DIR__ . '/../../assets/img/product/';
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $targetDir . $fileName)) {
            $thumbnailPath = 'assets/img/product/' . $fileName;
            // Xóa ảnh cũ
            if ($product['thumbnail'] && file_exists(__DIR__ . '/../../' . $product['thumbnail'])) {
                @unlink(__DIR__ . '/../../' . $product['thumbnail']);
            }
        }
    }

    $commonData = [
        'name'        => $name,
        'slug'        => $slug,
        'category_id' => $category_id,
        'brand_id'    => $brand_id,
        'thumbnail'   => $thumbnailPath,
        'description' => $description
    ];

    if ($editId) {
        $productModel->updateProduct($editId, $commonData);
        $productModel->updatePhoneDetails($editId, compact('chipset','ram','rom','screen','camera','battery'));
        $productModel->deleteVariantsByProductId($editId);
        $productId = $editId;
    } else {
        $productId = $productModel->addProduct($commonData);
        $productModel->addPhoneDetails($productId, compact('chipset','ram','rom','screen','camera','battery'));
    }

    // === XỬ LÝ BIẾN THỂ + ẢNH ===
    if (!empty($_POST['variants']) && is_array($_POST['variants'])) {
        $uploadDir = __DIR__ . '/../../assets/img/variants/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        foreach ($_POST['variants'] as $index => $v) {
            $imagePath = null;

            // ĐÚNG CÁCH xử lý file upload biến thể
            if (!empty($_FILES['variant_images']['error'][$index] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                $tmpName = $_FILES['variant_images']['tmp_name'][$index];
                $fileName = $_FILES['variant_images']['name'][$index];

                if (is_uploaded_file($tmpName)) {
                    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                    $newName = time() . '_var_' . $index . '_' . uniqid() . '.' . $ext;
                    $dest = $uploadDir . $newName;

                    if (move_uploaded_file($tmpName, $dest)) {
                        $imagePath = 'assets/img/variants/' . $newName;
                    }
                }
            }

            $productModel->addVariant([
                'product_id'  => $productId,
                'ram'         => $v['ram'] ?? '',
                'rom'         => $v['rom'] ?? '',
                'color'       => $v['color'] ?? '',
                'price'       => $v['price'] ?? 0,
                'price_sale'  => $v['price_sale'] ?? 0,
                'stock'       => $v['stock'] ?? 0,
                'image'       => $imagePath
            ]);
        }
    }

    header("Location: ?page=admin_product");
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $editId ? 'Sửa' : 'Thêm' ?> Sản Phẩm</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root { --primary: #1e3c72; --secondary: #2a5298; }
        .header { background: linear-gradient(135deg,var(--primary),var(--secondary)); color: white; padding: 20px; text-align: center; font-size: 24px; font-weight: 600; }
        .form-body { padding: 30px; max-width: 1200px; margin: 0 auto; }
        .row { display: flex; gap: 20px; margin-bottom: 20px; }
        .col-6 { flex: 1; }
        label { display: block; margin-bottom: 8px; font-weight: 600; }
        input, select, textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; }
        .variants-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .variants-table th, .variants-table td { border: 1px solid #eee; padding: 10px; text-align: center; }
        .variants-table th { background: #f8f9ff; }
        .btn { padding: 12px 30px; border: none; border-radius: 8px; color: white; cursor: pointer; margin-right: 10px; }
        .btn-save { background: #28a745; }
        .btn-add-variant { background: #17a2b8; }
        .btn-back { background: #6c757d; text-decoration: none; display: inline-block; text-align: center; }
        .variant-preview img { width: 70px; height: 70px; object-fit: cover; border-radius: 6px; margin-top: 8px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <i class="fas fa-<?= $editId ? 'edit' : 'plus-circle' ?>"></i>
            <?= $editId ? "SỬA SẢN PHẨM #$editId" : 'THÊM SẢN PHẨM MỚI' ?>
        </div>

        <div class="form-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-6">
                        <label>Tên sản phẩm *</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($product['name'] ?? '') ?>" required>
                    </div>
                    <div class="col-6">
                        <label>Slug *</label>
                        <input type="text" name="slug" value="<?= htmlspecialchars($product['slug'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <label>Danh mục *</label>
                        <select name="category_id" required>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= ($product['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6">
                        <label>Thương hiệu</label>
                        <select name="brand_id">
                            <option value="">-- Chọn thương hiệu --</option>
                            <?php foreach ($brands as $b): ?>
                                <option value="<?= $b['id'] ?>" <?= ($product['brand_id'] ?? '') == $b['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($b['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <label>Ảnh thumbnail</label>
                        <input type="file" name="thumbnail" accept="image/*">
                        <?php if (!empty($product['thumbnail'])): ?>
                            <div class="variant-preview">
                                <img src="../<?= htmlspecialchars($product['thumbnail']) ?>" alt="thumb">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <h3>Thông số kỹ thuật</h3>
                <div class="row">
                    <div class="col-6"><input type="text" name="chipset" value="<?= htmlspecialchars($phoneDetails['chipset'] ?? '') ?>" placeholder="Chipset"></div>
                    <div class="col-6"><input type="text" name="ram_device" value="<?= htmlspecialchars($phoneDetails['ram'] ?? '') ?>" placeholder="RAM"></div>
                </div>
                <div class="row">
                    <div class="col-6"><input type="text" name="storage" value="<?= htmlspecialchars($phoneDetails['rom'] ?? '') ?>" placeholder="ROM"></div>
                    <div class="col-6"><input type="text" name="screen" value="<?= htmlspecialchars($phoneDetails['screen'] ?? '') ?>" placeholder="Màn hình"></div>
                </div>
                <div class="row">
                    <div class="col-6"><input type="text" name="camera" value="<?= htmlspecialchars($phoneDetails['camera'] ?? '') ?>" placeholder="Camera"></div>
                    <div class="col-6"><input type="text" name="battery" value="<?= htmlspecialchars($phoneDetails['battery'] ?? '') ?>" placeholder="Pin"></div>
                </div>

                <div style="margin: 20px 0;">
                    <label>Mô tả sản phẩm</label>
                    <textarea name="description" rows="5"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                </div>

                <h3>Phiên bản sản phẩm</h3>
                <table class="variants-table" id="variantsTable">
                    <thead>
                        <tr>
                            <th>RAM</th><th>ROM</th><th>Màu</th><th>Giá gốc</th><th>Giá sale</th><th>Kho</th><th>Ảnh riêng</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($variants): foreach($variants as $i => $v): ?>
                        <tr>
                            <td><input type="text" name="variants[<?= $i ?>][ram]" value="<?= htmlspecialchars($v['ram']) ?>" style="width:80px"></td>
                            <td><input type="text" name="variants[<?= $i ?>][rom]" value="<?= htmlspecialchars($v['rom']) ?>" style="width:80px"></td>
                            <td><input type="text" name="variants[<?= $i ?>][color]" value="<?= htmlspecialchars($v['color']) ?>"></td>
                            <td><input type="number" name="variants[<?= $i ?>][price]" value="<?= $v['price'] ?>" required></td>
                            <td><input type="number" name="variants[<?= $i ?>][price_sale]" value="<?= $v['price_sale'] ?>"></td>
                            <td><input type="number" name="variants[<?= $i ?>][stock]" value="<?= $v['stock'] ?>"></td>
                            <td>
                                <input type="file" name="variant_images[<?= $i ?>]" accept="image/*">
                                <?php if ($v['image']): ?>
                                    <div class="variant-preview"><img src="../<?= htmlspecialchars($v['image']) ?>"></div>
                                <?php endif; ?>
                            </td>
                            <td><button type="button" onclick="this.closest('tr').remove()">Xóa</button></td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr>
                            <td><input type="text" name="variants[0][ram]" style="width:80px"></td>
                            <td><input type="text" name="variants[0][rom]" style="width:80px"></td>
                            <td>
                                <select name="variants[0][color]" style="width: 90px;">
                                    <option value="Đỏ">Đỏ</option>
                                    <option value="Xanh">Xanh</option>
                                    <option value="Đen">Đen</option>
                                    <option value="Trắng">Trắng</option>
                                </select>
                            </td>
                            <td><input type="number" name="variants[0][price]" required></td>
                            <td><input type="number" name="variants[0][price_sale]" value="0"></td>
                            <td><input type="number" name="variants[0][stock]" value="0"></td>
                            <td><input type="file" name="variant_images[0]" accept="image/*"></td>
                            <td><button type="button" onclick="this.closest('tr').remove()">Xóa</button></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <button type="button" class="btn btn-add-variant" onclick="addVariant()">+ Thêm phiên bản</button>

                <hr style="margin:40px 0">
                <button type="submit" class="btn btn-save">LƯU SẢN PHẨM</button>
                <a href="?page=admin_product" class="btn btn-back">Quay lại</a>
            </form>
        </div>
    </div>

    <script>
        function addVariant() {
            const tbody = document.querySelector('#variantsTable tbody');
            const index = tbody.rows.length;
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input type="text" name="variants[${index}][ram]" style="width:80px"></td>
                <td><input type="text" name="variants[${index}][rom]" style="width:80px"></td>
                <td>
                    <select name="variants[0][color]">
                        <option value=""></option>
                        <option value="Đỏ">Đỏ</option>
                        <option value="Xanh">Xanh</option>
                        <option value="Đen">Đen</option>
                        <option value="Trắng">Trắng</option>
                    </select>
                </td>
                <td><input type="number" name="variants[${index}][price]" required></td>
                <td><input type="number" name="variants[${index}][price_sale]" value="0"></td>
                <td><input type="number" name="variants[${index}][stock]" value="0"></td>
                <td><input type="file" name="variant_images[${index}]" accept="image/*"></td>
                <td><button type="button" onclick="this.closest('tr').remove()">Xóa</button></td>
            `;
            tbody.appendChild(tr);
        }
    </script>
</body>
</html>