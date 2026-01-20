<style>
    /* CSS cho giao diện Order Success */
    .order-success-wrapper {
        display: flex;
        justify-content: center;
        align-items: flex-start;
        /* Căn thẳng hàng trên cùng */
        gap: 40px;
        max-width: 1000px;
        margin: 50px auto;
        padding: 40px;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        font-family: Arial, sans-serif;
    }

    /* Cột bên trái: Thông tin đơn hàng */
    .os-left {
        flex: 1;
        text-align: left;
        padding-right: 20px;
        border-right: 1px solid #eee;
        /* Đường gạch ngăn cách */
    }

    /* Cột bên phải: Thanh toán / QR */
    .os-right {
        flex: 1;
        text-align: center;
        background: #f9f9f9;
        padding: 30px;
        border-radius: 12px;
    }

    /* Các thành phần con */
    .success-icon {
        font-size: 60px;
        color: #27ae60;
        margin-bottom: 20px;
    }

    .order-title {
        color: #2c3e50;
        margin-bottom: 10px;
        font-size: 28px;
        font-weight: bold;
    }

    .order-detail p {
        font-size: 16px;
        color: #555;
        margin: 10px 0;
    }

    .btn-home {
        display: inline-block;
        margin-top: 30px;
        padding: 12px 30px;
        background: #2c3e50;
        color: white;
        text-decoration: none;
        border-radius: 50px;
        font-weight: bold;
        transition: 0.3s;
    }

    .btn-home:hover {
        background: #34495e;
        transform: translateY(-2px);
    }

    /* Bank info box */
    .bank-info-box {
        text-align: left;
        background: #fff;
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #eee;
        display: inline-block;
        width: 100%;
        box-sizing: border-box;
    }

    .bank-info-box p {
        margin: 5px 0;
        font-size: 14px;
    }

    /* Responsive: Trên điện thoại sẽ quay về xếp dọc */
    @media (max-width: 768px) {
        .order-success-wrapper {
            flex-direction: column;
            padding: 20px;
        }

        .os-left {
            text-align: center;
            border-right: none;
            padding-right: 0;
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 30px;
        }
    }
</style>

<div class="container">
    <div class="order-success-wrapper">

        <div class="os-left" style="text-align: center; padding: 40px;">

            <?php if ($order['payment_method'] == 'cod'): ?>
                <i class="fas fa-check-circle success-icon"></i>
                <h1 class="order-title">Đặt hàng thành công!</h1>
                <p>Cảm ơn bạn đã mua sắm. Đơn hàng sẽ sớm được giao đến bạn.</p>

            <?php else: ?>
                <i class="fas fa-clock" style="font-size: 60px; color: #f39c12; margin-bottom: 20px;"></i>
                <h1 class="order-title">Đơn hàng đã được tạo!</h1>
                <p style="color: #d35400; font-weight: bold; font-size: 18px;">Vui lòng thanh toán để hoàn tất.</p>
                <p>Hệ thống sẽ tự động xác nhận sau khi nhận được tiền.</p>
            <?php endif; ?>

            <div style="margin: 20px 0; border-top: 1px dashed #ddd; border-bottom: 1px dashed #ddd; padding: 15px 0;">
                <p>Mã đơn hàng: <strong style="font-size: 20px; color: #333;">#<?= htmlspecialchars($order['code']) ?></strong></p>
                <p>Tổng tiền: <strong style="color: #d70018; font-size: 20px;"><?= number_format($order['final_money']) ?>₫</strong></p>
            </div>

            <?php if (isset($_GET['new_acc'])): ?>
                <div style="background: #e8f8f5; border: 1px solid #2ecc71; padding: 15px; border-radius: 8px; text-align: left; margin-top: 15px;">
                    <h4 style="color: #27ae60; margin: 0 0 10px 0;"><i class="fas fa-user-plus"></i> Tài khoản đã được tạo tự động</h4>
                    <p style="margin: 5px 0;">Email: <strong>(Email bạn vừa nhập)</strong></p>
                    <p style="margin: 5px 0;">Mật khẩu: <span style="font-size: 18px; font-weight: bold; color: #d35400; background: #fff; padding: 2px 8px; border-radius: 4px;"><?= htmlspecialchars($_GET['new_acc']) ?></span></p>
                    <small style="color: #666;">* Vui lòng đổi mật khẩu sau khi đăng nhập.</small>
                </div>
            <?php endif; ?>

            <a href="index.php?page=products" class="btn-home">
                <i class="fas fa-arrow-left"></i> Tiếp tục mua sắm
            </a>
        </div>

        <div class="os-right">
            <?php if ($order['payment_method'] == 'cod'): ?>
                <h3 style="color: #27ae60; margin-top: 0;">Thanh toán khi nhận hàng</h3>
                <img src="assets/img/shipper.png" alt="COD" style="width: 120px; margin: 20px auto; display: block;">
                <p style="color: #666;">Bạn sẽ thanh toán cho Shipper khi nhận được hàng.</p>

            <?php elseif ($order['payment_method'] == 'bank'): ?>
                <h3 style="color: #2980b9; margin-top: 0;">Quét mã VietQR</h3>
                <p style="font-size: 14px; color: #666;">Mở App Ngân hàng để quét</p>

                <?php
                $bank_id = "MB";
                $account_no = "0862385393";
                $account_name = "NGUYEN HAI DANG";
                $amount = $order['final_money'];
                $content = $order['code'];
                $qr_url = "https://img.vietqr.io/image/{$bank_id}-{$account_no}-compact2.jpg?amount={$amount}&addInfo={$content}&accountName={$account_name}";
                ?>

                <div style="background: white; padding: 10px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: inline-block;">
                    <img src="<?= $qr_url ?>" alt="QR Code" style="width: 100%; max-width: 250px;">
                </div>

                <div class="bank-info-box" style="margin-top: 20px;">
                    <p>Ngân hàng: <strong>MB Bank</strong></p>
                    <p>Số tài khoản: <strong><?= $account_no ?></strong> <button onclick="navigator.clipboard.writeText('<?= $account_no ?>');alert('Đã copy!')" style="border:none; background:none; cursor:pointer; color:#3498db;"><i class="far fa-copy"></i></button></p>
                    <p>Chủ tài khoản: <strong><?= $account_name ?></strong></p>
                    <p>Nội dung CK: <strong style="background: #f1c40f; padding: 2px 6px; border-radius: 4px; color: #000;"><?= $content ?></strong></p>
                </div>

            <?php elseif ($order['payment_method'] == 'momo'): ?>
                <h3 style="color: #d82d8b; margin-top: 0;">Thanh toán MoMo</h3>
                <img src="assets/img/qr-momo-shop.jpg" alt="Momo QR" style="width: 200px; border-radius: 10px; border: 1px solid #ddd;">
                <p style="margin-top: 15px;">Nội dung chuyển tiền:</p>
                <div style="background: #fff0f6; border: 1px dashed #d82d8b; padding: 10px; border-radius: 8px; color: #d82d8b; font-weight: bold; font-size: 20px;">
                    <?= $order['code'] ?>
                </div>
            <?php endif; ?>

        </div>

    </div>
</div>