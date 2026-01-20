<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ Sơ Cá Nhân - Schedules Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        /* --- CSS GỐC CỦA BẠN --- */
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --accent: #f093fb;
        }
        .tt-canhan {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: 70px;
            display: grid;
            grid-template-columns: repeat(2,1fr); /* Chia 2 cột */
            gap: 50px;
            align-items: start; /* Căn lên trên cùng để không bị khoảng trống */
        }
        .card {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(16px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            color: #fff;
        }

        h2 { color:#fff; text-align:center; margin-bottom:25px; font-size:24px; }
        .avatar {
            width:120px; height:120px; border-radius:50%; background:#fff; margin:0 auto 20px;
            display:flex; align-items:center; justify-content:center; font-size:48px; color:var(--primary);
            font-weight:bold; text-transform:uppercase;
        }
        .info { color:rgba(255,255,255,0.9); margin:15px 0; font-size:16px; }
        .info strong { color:#fff; }

        .input-group { margin:15px 0; }
        .input-group label { color:#fff; display:block; margin-bottom:8px; font-weight:500; }
        .input-group input {
            width:100%; padding:14px 18px; border:none; border-radius:50px;
            background:rgba(255,255,255,0.2); color:#fff; font-size:16px;
            box-sizing: border-box; /* Fix lỗi tràn input */
        }
        .input-group input:focus {
            outline:none; background:rgba(255,255,255,0.35);
            box-shadow:0 0 20px rgba(255,255,255,0.4);
        }
        
        /* Placeholder màu trắng mờ */
        ::placeholder { color: rgba(255,255,255,0.6); }

        .update-profile button, .doimk-profile button, .addr-form button {
            width:100%; padding:14px; margin-top:20px;
            border:none; border-radius:50px; font-size:17px; font-weight:600;
            cursor:pointer; transition:all .4s;
        }
        .update-profile .btn-update { background:linear-gradient(45deg,#4facfe,#00f2fe); color:#fff; }
        .doimk-profile .btn-password { background:linear-gradient(45deg,#ff9a9e,#fad0c4); color:#fff; }
        .addr-form .btn-save { background:linear-gradient(45deg,#43e97b,#38f9d7); color:#fff; }
        
        button:hover { transform:translateY(-4px); box-shadow:0 15px 30px rgba(0,0,0,0.4); }

        .success { background:rgba(72,219,251,0.3); color:#fff; padding:15px; border-radius:12px; text-align:center; margin:15px 0; border: 1px solid rgba(255,255,255,0.3); }
        .error { background:rgba(255,107,107,0.3); color:#fff; padding:15px; border-radius:12px; text-align:center; margin:15px 0; border: 1px solid rgba(255,255,255,0.3); }

        /* --- CSS MỚI CHO PHẦN ĐỊA CHỈ --- */
        .addr-item {
            background: rgba(0,0,0,0.2);
            padding: 15px;
            border-radius: 15px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .addr-content { font-size: 14px; line-height: 1.6; }
        .addr-actions a {
            color: #fff;
            text-decoration: none;
            margin-left: 10px;
            font-size: 14px;
            padding: 5px 10px;
            border-radius: 8px;
            transition: 0.3s;
        }
        .btn-edit-icon { background: rgba(52, 152, 219, 0.6); }
        .btn-delete-icon { background: rgba(231, 76, 60, 0.6); }
        .btn-edit-icon:hover, .btn-delete-icon:hover { opacity: 0.8; }
        
        .badge-default {
            background: #2ecc71;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            margin-left: 5px;
            vertical-align: middle;
        }

        @media (max-width: 768px) {
            .tt-canhan { grid-template-columns: 1fr; padding:30px; }
        }
    </style>
</head>
<body>
    <div class="tt-canhan">
        
        <div style="display: flex; flex-direction: column; gap: 50px;">
            
            <div class="card">
                <h2>Thông Tin Cá Nhân</h2>
                <div class="avatar"><?= htmlspecialchars(substr($user['fullname'] ?? 'US', 0, 2)) ?></div>
                <div class="info"><strong>Họ tên:</strong> <?= htmlspecialchars($user['fullname'] ?? 'Chưa cập nhật') ?></div>
                <div class="info"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></div>
                <div class="info"><strong>Số điện thoại:</strong> <?= !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'Chưa cập nhật' ?></div>
                <div class="info"><strong>Vai trò:</strong> <?= isset($user['role']) && $user['role'] == 1 ? 'Quản Trị Viên' : 'Khách Hàng' ?></div>
                <div class="info"><strong>Ngày tạo:</strong> <?= date('d/m/Y', strtotime($user['created_at'] ?? 'now')) ?></div>
            </div>

            <div class="card">
                <h2><?= isset($edit_data) ? 'Cập Nhật Địa Chỉ' : 'Sổ Địa Chỉ' ?></h2>

                <form method="POST" class="addr-form" action="index.php?page=profile">
                    <input type="hidden" name="addr_id" value="<?= $edit_data['id'] ?? '' ?>">
                    
                    <div class="input-group">
                        <label>Tên người nhận</label>
                        <input type="text" name="addr_name" required value="<?= $edit_data['recipient_name'] ?? '' ?>" placeholder="Ví dụ: Nguyễn Văn A">
                    </div>
                    
                    <div class="input-group">
                        <label>Số điện thoại</label>
                        <input type="text" name="addr_phone" required value="<?= $edit_data['phone'] ?? '' ?>" placeholder="Ví dụ: 0987654321">
                    </div>

                    <div class="input-group">
                        <label>Địa chỉ chi tiết</label>
                        <input type="text" name="addr_detail" required value="<?= $edit_data['address'] ?? '' ?>" placeholder="Số nhà, Đường, Quận, Tỉnh...">
                    </div>

                    <button type="submit" name="save_address" class="btn-save">
                        <?= isset($edit_data) ? 'Lưu Thay Đổi' : 'Thêm Địa Chỉ Mới' ?>
                    </button>
                    
                    <?php if(isset($edit_data)): ?>
                        <div style="text-align:center; margin-top:10px;">
                            <a href="index.php?page=profile" style="color:rgba(255,255,255,0.7); text-decoration:none;">Hủy bỏ chỉnh sửa</a>
                        </div>
                    <?php endif; ?>
                </form>

                <hr style="margin:30px 0; border:none; border-top:1px solid rgba(255,255,255,0.2);">

                <h3 style="color:#fff; font-size:18px; margin-bottom:15px;">Danh sách đã lưu</h3>
                
                <?php if (!empty($list_address)): ?>
                    <?php foreach ($list_address as $addr): ?>
                        <div class="addr-item">
                            <div class="addr-content">
                                <strong style="color:#fff; font-size:16px;"><?= htmlspecialchars($addr['recipient_name']) ?></strong>
                                <?php if($addr['is_default'] == 1): ?>
                                    <span class="badge-default">Mặc định</span>
                                <?php endif; ?>
                                <div style="color:rgba(255,255,255,0.8); margin-top:4px;">
                                    <i class="fas fa-phone" style="font-size:12px; margin-right:5px;"></i> <?= htmlspecialchars($addr['phone']) ?>
                                </div>
                                <div style="color:rgba(255,255,255,0.8); margin-top:2px;">
                                    <i class="fas fa-map-marker-alt" style="font-size:12px; margin-right:5px;"></i> <?= htmlspecialchars($addr['address']) ?>
                                </div>
                            </div>
                            <div class="addr-actions">
                                <a href="index.php?page=profile&action=edit_address&id=<?= $addr['id'] ?>" class="btn-edit-icon" title="Sửa">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <a href="index.php?page=profile&action=delete_address&id=<?= $addr['id'] ?>" class="btn-delete-icon" title="Xóa" onclick="return confirm('Bạn chắc chắn muốn xóa địa chỉ này?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align:center; color:rgba(255,255,255,0.6); font-style:italic;">Chưa có địa chỉ nào được lưu.</p>
                <?php endif; ?>

            </div>
        </div>

        <div class="card">
            <h2>Cập Nhật Tài Khoản</h2>

            <?php if(!empty($success)): ?><div class="success"><?= $success ?></div><?php endif; ?>
            <?php if(!empty($error)): ?><div class="error"><?= $error ?></div><?php endif; ?>

            <form method="POST" class="update-profile">
                <div class="input-group">
                    <label>Họ và tên</label>
                    <input type="text" name="fullname" value="<?= htmlspecialchars($user['fullname'] ?? '') ?>" required>
                </div>
                <div class="input-group">
                    <label>Số điện thoại chính</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="Ví dụ: 0901234567">
                </div>
                <button type="submit" name="update_profile" class="btn-update">Cập Nhật Thông Tin</button>
            </form>

            <hr style="margin:30px 0; border:none; border-top:1px solid rgba(255,255,255,0.2);">

            <h2 style="margin-top:20px;">Đổi Mật Khẩu</h2>
            <form method="POST" class="doimk-profile">
                <div class="input-group">
                    <label>Mật khẩu cũ</label>
                    <input type="password" name="old_password" required>
                </div>
                <div class="input-group">
                    <label>Mật khẩu mới</label>
                    <input type="password" name="new_password" required>
                </div>
                <div class="input-group">
                    <label>Nhập lại mật khẩu mới</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <button type="submit" name="change_password" class="btn-password">Đổi Mật Khẩu</button>
            </form>
        </div>
        
    </div>
</body>
</html>