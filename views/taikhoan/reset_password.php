<div class="auth-wrapper">
    <div class="user-auth-panel">
        <h3>Đặt lại mật khẩu</h3>

        <?php if (isset($success)): ?>
            <div class="alert-box success"><?= $success ?></div>
        <?php elseif (isset($error)): ?>
            <div class="alert-box error"><?= $error ?></div>
        <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label>Mật khẩu mới</label>
                    <input type="password" name="password" class="custom-input" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Nhập lại mật khẩu</label>
                    <input type="password" name="confirm" class="custom-input" required>
                </div>
                <button type="submit" class="auth-btn success">Cập nhật mật khẩu</button>
            </form>
        <?php endif; ?>

        <a href="index.php?page=login" class="back-link">Quay lại đăng nhập</a>
    </div>
</div>