<header class="header-contact">
    <h1>Liên hệ với chúng tôi</h1>
    <p>Chúng tôi luôn sẵn sàng hỗ trợ bạn</p>
</header>

<main class="main-contact">
    <section class="contact-details">
        <h2>Thông tin liên hệ</h2>
        <div class="detail-item">
            <span>Địa chỉ:</span>
            <p>Đường đê Mỏ Bạch,Tổ 10, Thành phố Thái Nguyên, Thái Nguyên, Việt Nam</p>
        </div>
        <div class="detail-item">
            <span>Điện thoại:</span>
            <p>+84 862 385 393</p>
        </div>
        <div class="detail-item">
            <span>Email:</span>
            <p>support@buno.io.vn</p>
        </div>
    </section>

    <section class="contact-message">
        <h2>Gửi tin nhắn / Góp ý</h2>

        <form class="form-contact" action="index.php?page=post_contact" method="POST">

            <input type="text" name="fullname" placeholder="Họ và tên" required
                value="<?= isset($user['fullname']) ? htmlspecialchars($user['fullname']) : '' ?>">

            <input type="email" name="email" placeholder="Email" required
                value="<?= isset($user['email']) ? htmlspecialchars($user['email']) : '' ?>">

            <input type="text" name="phone" placeholder="Số điện thoại"
                value="<?= isset($user['phone']) ? htmlspecialchars($user['phone']) : '' ?>">

            <?php if (!empty($orders)): ?>
                <div style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600; color:#555;">Bạn cần hỗ trợ về đơn hàng nào?</label>
                    <select name="order_code" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                        <option value="other">-- Vấn đề chung / Khác --</option>
                        <?php foreach ($orders as $ord): ?>
                            <option value="<?= $ord['code'] ?>">
                                Đơn hàng #<?= $ord['code'] ?> (Ngày: <?= date('d/m/Y', strtotime($ord['created_at'])) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <input type="text" name="subject" placeholder="Chủ đề (VD: Khiếu nại, Hỏi đáp...)" required>

            <textarea name="message" placeholder="Nội dung chi tiết..." required rows="5"></textarea>

            <button type="submit" name="submit">Gửi tin nhắn</button>
        </form>
    </section>
</main>