<?php
$is_edit = $is_edit ?? false; 
$user_data = $user ?? [];

// Chuẩn bị các giá trị động cho Form
$current_fullname = $user_data['fullname'] ?? '';
$current_email = $user_data['email'] ?? '';
$current_phone = $user_data['phone'] ?? '';
$current_role = $user_data['role'] ?? 'user';

// Xác định hành động và chữ trên nút
$form_action = $is_edit ? "admin.php?page=sua_user&id={$user_data['id']}" : "admin.php?page=add_user";
$button_text = $is_edit ? "💾 CẬP NHẬT NGƯỜI DÙNG" : "➕ THÊM NGƯỜI DÙNG";
$button_class = $is_edit ? "btn-update" : "btn-submit";
?>

<div class="container">
    <h1>👥 Quản Lý Người Dùng</h1>

    <?php if (!empty($success)) : ?>
        <div class="alert success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)) : ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="form-container">
        <h2>
            <?php if ($is_edit) : ?>
                ✏️ Chỉnh Sửa Người Dùng ID <?= htmlspecialchars($user_data['id']) ?>
            <?php else : ?>
                ➕ Thêm Người Dùng Mới
            <?php endif; ?>
        </h2>
        
        <form action="<?= $form_action ?>" method="POST" class="user-form">
            <?php if ($is_edit) : ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($user_data['id'] ?? '') ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="fullname">Họ tên</label>
                <input type="text" id="fullname" name="fullname" required placeholder="Nhập họ tên đầy đủ" 
                       value="<?= htmlspecialchars($current_fullname) ?>"> 
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="Nhập địa chỉ email"
                       value="<?= htmlspecialchars($current_email) ?>"> 
            </div>

            <div class="form-group">
                <label for="phone">Số điện thoại</label>
                <input type="text" id="phone" name="phone" required placeholder="Nhập số điện thoại"
                       value="<?= htmlspecialchars($current_phone) ?>"> 
            </div>

            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="password" 
                       placeholder="<?= $is_edit ? 'Để trống nếu không thay đổi mật khẩu' : 'Tạo mật khẩu an toàn (BẮT BUỘC)' ?>"
                       <?= $is_edit ? '' : 'required' ?>>
            </div>

            <div class="form-group">
                <label for="role">Quyền (Vai trò)</label>
                <select id="role" name="role" required>
                    <option value="user" <?= ($current_role ?? 0) == 0 ? 'selected' : '' ?>>Người dùng</option>
                    <option value="admin" <?= ($current_role ?? 0) == 1 ? 'selected' : '' ?>>Quản trị viên</option>
                </select>
            </div>
            
            <button type="submit" class="btn <?= $button_class ?>">
                <?= $button_text ?>
            </button>
            
        </form>
    </div>
    
    <hr style="margin: 40px 0; border-top: 2px solid #ddd;"> 
</div>