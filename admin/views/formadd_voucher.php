<?php
// Đảm bảo biến không bị null
$is_edit = $is_edit ?? false;
$voucher_data = $voucher ?? [];

// Các giá trị động (Khớp với tên cột trong Database)
$current_code       = $voucher_data['code'] ?? '';
$current_type       = $voucher_data['discount_type'] ?? 'fixed'; // Mặc định là fixed
$current_value      = $voucher_data['discount_value'] ?? '';
$current_min_order  = $voucher_data['min_order_amount'] ?? 0;
$current_quantity   = $voucher_data['quantity'] ?? 100;
$current_start      = $voucher_data['start_date'] ?? '';
$current_end        = $voucher_data['end_date'] ?? '';
$current_status     = $voucher_data['status'] ?? 1;

// Action + nút (Sửa lại page=edit_voucher cho khớp controller)
$form_action = $is_edit 
    ? "admin.php?page=edit_voucher&id={$voucher_data['id']}" 
    : "admin.php?page=add_voucher";

$title_text = $is_edit ? "Cập Nhật Voucher" : "Thêm Voucher Mới";
$btn_text = $is_edit ? "Lưu Thay Đổi" : "Tạo Voucher Ngay";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<style>
    /* 1. Thiết lập chung & Font chữ xịn */
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

    :root {
        --primary-color: #6a11cb;
        --secondary-color: #2575fc;
        --text-color: #333;
        --bg-input: #f4f6f8;
    }

    body {
        font-family: 'Poppins', sans-serif;
        /* Nền Gradient thời thượng */
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 0;
        padding: 20px;
    }

    /* 2. Cái thẻ Card chứa Form */
    .form-card {
        background: #ffffff;
        width: 100%;
        max-width: 650px; /* Tăng chiều rộng xíu cho thoáng */
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2); 
        position: relative;
        overflow: hidden;
    }

    .form-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 100%; height: 8px;
        background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
    }

    .form-title {
        text-align: center;
        color: var(--text-color);
        font-weight: 700;
        font-size: 24px;
        margin-bottom: 30px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* 3. Style cho các ô Input */
    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        font-size: 14px;
        color: #555;
    }

    .custom-input, .custom-select {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid transparent;
        background-color: var(--bg-input);
        border-radius: 10px;
        font-size: 15px;
        transition: all 0.3s ease;
        box-sizing: border-box; 
        font-family: inherit;
    }

    .custom-input:focus, .custom-select:focus {
        outline: none;
        background-color: #fff;
        border-color: var(--secondary-color);
        box-shadow: 0 4px 10px rgba(37, 117, 252, 0.1);
    }

    /* Chia cột cho đẹp (Grid đơn giản) */
    .row-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    /* 4. Nút bấm Gradient */
    .btn-submit {
        width: 100%;
        padding: 15px;
        border: none;
        border-radius: 10px;
        background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        color: white;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
        margin-top: 10px;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(37, 117, 252, 0.4);
    }

    .btn-back {
        display: block;
        text-align: center;
        margin-top: 20px;
        color: #888;
        text-decoration: none;
        font-size: 14px;
    }
    .btn-back:hover { color: var(--text-color); }

    /* Thông báo lỗi/thành công */
    .alert {
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
        text-align: center;
    }
    .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

</style>
</head>
<body>

    <div class="form-card">
        <h2 class="form-title"><?= $title_text ?></h2>

        <!-- Hiển thị thông báo nếu có trên URL -->
        <?php if (isset($_GET['success']) && $_GET['success'] == 1) : ?>
            <div class="alert alert-success">✅ Thao tác thành công!</div>
        <?php endif; ?>

        <form action="<?= $form_action ?>" method="POST">
            <?php if ($is_edit) : ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($voucher_data['id']) ?>">
            <?php endif; ?>

            <!-- Hàng 1: Mã Code + Trạng Thái -->
            <div class="row-grid">
                <div class="form-group">
                    <label class="form-label">Mã Voucher (*)</label>
                    <input type="text" name="code" class="custom-input" required 
                           placeholder="VD: SALE2024" value="<?= htmlspecialchars($current_code) ?>" 
                           style="text-transform: uppercase; font-weight: bold; letter-spacing: 1px;">
                </div>
                <div class="form-group">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="custom-select">
                        <option value="1" <?= $current_status == 1 ? 'selected' : '' ?>>Hoạt động</option>
                        <option value="0" <?= $current_status == 0 ? 'selected' : '' ?>>Đã khóa</option>
                    </select>
                </div>
            </div>

            <!-- Hàng 2: Loại giảm giá + Giá trị giảm -->
            <div class="row-grid">
                <div class="form-group">
                    <label class="form-label">Loại giảm giá (*)</label>
                    <select name="discount_type" class="custom-select">
                        <option value="fixed" <?= $current_type == 'fixed' ? 'selected' : '' ?>>Tiền mặt (VNĐ)</option>
                        <option value="percent" <?= $current_type == 'percent' ? 'selected' : '' ?>>Phần trăm (%)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Giá trị giảm (*)</label>
                    <input type="number" name="discount_value" class="custom-input" required min="0"
                           placeholder="VD: 50000 hoặc 10" value="<?= htmlspecialchars($current_value) ?>">
                </div>
            </div>

            <!-- Hàng 3: Đơn tối thiểu + Số lượng -->
            <div class="row-grid">
                <div class="form-group">
                    <label class="form-label">Đơn tối thiểu</label>
                    <input type="number" name="min_order_amount" class="custom-input" value="<?= htmlspecialchars($current_min_order) ?>" placeholder="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Số lượng (*)</label>
                    <input type="number" name="quantity" class="custom-input" required min="1"
                           placeholder="100" value="<?= htmlspecialchars($current_quantity) ?>">
                </div>
            </div>

            <!-- Hàng 4: Ngày bắt đầu + Kết thúc -->
            <div class="row-grid">
                <div class="form-group">
                    <label class="form-label">Ngày bắt đầu</label>
                    <!-- Dùng datetime-local để chọn cả giờ -->
                    <input type="datetime-local" name="start_date" class="custom-input"
                           value="<?= $current_start ? date('Y-m-d\TH:i', strtotime($current_start)) : '' ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Ngày kết thúc</label>
                    <input type="datetime-local" name="end_date" class="custom-input"
                           value="<?= $current_end ? date('Y-m-d\TH:i', strtotime($current_end)) : '' ?>">
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <?= $btn_text ?>
            </button>

            <a href="admin.php?page=voucher" class="btn-back">← Quay lại danh sách</a>
        </form>
    </div>

</body>
</html>