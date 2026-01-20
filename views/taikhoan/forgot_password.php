<div class="auth-wrapper">
    <div class="user-auth-panel">
        <h3>Quên mật khẩu</h3>
        
        <?php if (isset($success)): ?>
            <div class="alert-box success"><?= $success ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert-box error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php?page=send_reset_link">
            <div class="form-group">
                <label>Email của bạn</label>
                <input type="email" name="email" class="custom-input" required>
            </div>
            <button type="submit" class="auth-btn primary">Gửi link đặt lại</button>
        </form>

        <a href="index.php?page=login" class="back-link">
            &larr; Quay lại đăng nhập
        </a>
    </div>
</div>