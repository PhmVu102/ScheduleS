<?php
// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kiểm tra biến
if (!isset($order)) {
    die("Lỗi: Chưa có biến \$order. Kiểm tra lại file Controller hoặc logic lấy dữ liệu.");
}
?>
<div class="ord-wrapper">
    <a href="index.php?page=order_history" class="ord-btn-back">
        <i class="fas fa-arrow-left"></i> Quay lại danh sách đơn hàng
    </a>

    <div class="ord-card">

        <div class="ord-header">
            <div>
                <div class="ord-title">Đơn hàng #<?= htmlspecialchars($order['code']) ?></div>
                <div class="ord-date">Đặt ngày: <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></div>
            </div>

            <?php
            $stt = $order['status'];
            $sttText = 'Chờ xử lý';
            $color = '#f39c12';

            switch ($stt) {
                case 'pending':
                    $sttText = 'Chờ xử lý';
                    $color = '#f39c12';
                    break;
                case 'confirmed':
                    $sttText = 'Đã xác nhận';
                    $color = '#3498db';
                    break;
                case 'shipping':
                    $sttText = 'Đang giao hàng';
                    $color = '#17a2b8';
                    break;
                case 'completed':
                    $sttText = 'Giao thành công';
                    $color = '#28a745';
                    break;
                case 'cancelled':
                    $sttText = 'Đã hủy';
                    $color = '#dc3545';
                    break;
                case 'returned':
                    $sttText = 'Trả hàng';
                    $color = '#6c757d';
                    break;
            }
            ?>
            <span class="ord-badge" style="background-color: <?= $color ?>;">
                <?= $sttText ?>
            </span>
        </div>

        <div class="ord-info-grid">
            <div>
                <div class="ord-label">Địa chỉ nhận hàng</div>
                <div class="ord-info-content">
                    <b><?= htmlspecialchars($order['fullname']) ?></b><br>
                    <?= htmlspecialchars($order['phone']) ?><br>
                    <?= htmlspecialchars($order['address']) ?>
                </div>
            </div>
            <div>
                <div class="ord-label">Thanh toán & Ghi chú</div>
                <div class="ord-info-content">
                    Thanh toán: <b><?= strtoupper($order['payment_method']) ?></b><br>
                    Ghi chú: <?= !empty($order['note']) ? htmlspecialchars($order['note']) : 'Không có' ?>
                </div>
            </div>
        </div>

        <div class="ord-label">Chi tiết sản phẩm</div>
        <?php foreach ($order_details as $item): ?>
            <div class="ord-item">
                <?php
                $thumb = $item['thumbnail'] ?? '';
                $img_src = 'assets/img/product/default.png'; // Ảnh mặc định

                if (!empty($thumb)) {
                    // Trường hợp 1: Ảnh lấy từ link online (http/https)
                    if (strpos($thumb, 'http') === 0) {
                        $img_src = $thumb;
                    }
                    // Trường hợp 2: Trong DB đã lưu sẵn đường dẫn "assets/..."
                    elseif (strpos($thumb, 'assets/') === 0) {
                        $img_src = $thumb;
                    }
                    // Trường hợp 3: Trong DB chỉ lưu mỗi tên file (ví dụ: "iphone.jpg")
                    else {
                        $img_src = 'assets/img/product/' . $thumb;
                    }
                }

                // Xử lý biến thể (giữ nguyên)
                $variant_text = [];
                if (!empty($item['ram'])) $variant_text[] = $item['ram'];
                if (!empty($item['rom'])) $variant_text[] = $item['rom'];
                if (!empty($item['color'])) $variant_text[] = $item['color'];
                $variant_display = implode(" - ", $variant_text);
                ?>

                <img src="<?= htmlspecialchars($img_src) ?>"
                    class="ord-item-img"
                    onerror="this.onerror=null; this.src='assets/img/product/default.png'">
                <div class="ord-item-details">
                    <span class="ord-item-name"><?= htmlspecialchars($item['name']) ?></span>

                    <?php if (!empty($variant_display)): ?>
                        <div class="ord-item-variant">
                            Phân loại: <?= htmlspecialchars($variant_display) ?>
                        </div>
                    <?php endif; ?>

                    <div class="ord-item-qty">Số lượng: x<?= $item['quantity'] ?></div>
                </div>

                <div class="ord-item-price">
                    <?= number_format($item['total_price'], 0, ',', '.') ?>₫
                </div>
            </div>
        <?php endforeach; ?>

        <div class="ord-summary">
            <div class="ord-sum-row">Tổng tiền hàng: <?= number_format($order['total_money'], 0, ',', '.') ?>₫</div>
            <div class="ord-sum-row">Phí vận chuyển: <?= number_format($order['shipping_fee'], 0, ',', '.') ?>₫</div>
            <?php if ($order['discount_money'] > 0): ?>
                <div class="ord-sum-row" style="color: #27ae60;">Giảm giá: -<?= number_format($order['discount_money'], 0, ',', '.') ?>₫</div>
            <?php endif; ?>

            <div class="ord-sum-total">
                Tổng thanh toán: <?= number_format($order['final_money'], 0, ',', '.') ?>₫
            </div>
        </div>

        <div class="ord-label" style="margin-top: 30px;">Lịch sử giao dịch</div>
        <?php if (!empty($payment_histories)): ?>
            <table class="ord-payment-table">
                <thead>
                    <tr>
                        <th>Thời gian</th>
                        <th>Phương thức</th>
                        <th>Mã GD</th>
                        <th>Số tiền</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payment_histories as $pay): ?>
                        <tr>
                            <td><?= date('H:i d/m/Y', strtotime($pay['created_at'])) ?></td>
                            <td><?= strtoupper($pay['payment_method']) ?></td>
                            <td><?= htmlspecialchars($pay['transaction_code'] ?: '---') ?></td>
                            <td><b><?= number_format($pay['amount'], 0, ',', '.') ?>₫</b></td>
                            <td>
                                <?php
                                $pstt = $pay['status'];
                                $pcolor = '#666';
                                $ptext = $pstt;
                                if (in_array($pstt, ['success', 'paid'])) {
                                    $pcolor = '#28a745';
                                    $ptext = 'Đã thanh toán';
                                } elseif ($pstt == 'pending') {
                                    $pcolor = '#f39c12';
                                    $ptext = 'Đang chờ';
                                } elseif ($pstt == 'failed') {
                                    $pcolor = '#dc3545';
                                    $ptext = 'Thất bại';
                                }
                                ?>
                                <span style="color: <?= $pcolor ?>; font-weight: 600;"><?= $ptext ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color: #999; font-style: italic;">Chưa có giao dịch nào.</p>
        <?php endif; ?>

        <?php
        // --- 1. LOGIC KIỂM TRA THANH TOÁN (QR CODE) ---
        $showQR = false;
        $isOnlinePayment = in_array($order['payment_method'], ['bank', 'momo']);

        // Kiểm tra xem đã trả tiền thành công chưa
        $isPaid = false;
        if (!empty($payment_histories)) {
            foreach ($payment_histories as $ph) {
                if (in_array($ph['status'], ['paid', 'success'])) {
                    $isPaid = true;
                    break;
                }
            }
        }

        // Điều kiện hiện QR:
        // 1. Là thanh toán Online (Bank/Momo)
        // 2. Chưa trả tiền ($isPaid == false)
        // 3. Đơn hàng chưa bị hủy hoặc hoàn thành (Vẫn hiện khi đang 'shipping' để khách trả tiền muộn)
        if ($isOnlinePayment && !$isPaid && in_array($order['status'], ['pending', 'confirmed', 'shipping'])) {
            $showQR = true;
        }


        // --- 2. LOGIC KIỂM TRA VẬN CHUYỂN ---
        $showShipping = false;
        // Điều kiện hiện Vận chuyển:
        // 1. Có dữ liệu vận chuyển
        // 2. Trạng thái đơn là Đang giao hoặc Hoàn thành
        if (!empty($shipping_info) && in_array($order['status'], ['shipping', 'completed'])) {
            $showShipping = true;
        }
        ?>

        <?php if ($showQR): ?>
            <div class="ord-qr-section">
                <div class="ord-qr-title"><i class="fas fa-qrcode"></i> Quét mã để thanh toán ngay</div>

                <div class="ord-qr-layout">
                    <div class="ord-qr-left">
                        <?php if ($order['payment_method'] == 'bank'):
                            $bank_id = "MB";
                            $acc_no = "0862385393";
                            $acc_name = "NGUYEN HAI DANG";
                            $amt = $order['final_money'];
                            $content = $order['code'];
                            $qr_url = "https://img.vietqr.io/image/{$bank_id}-{$acc_no}-compact2.jpg?amount={$amt}&addInfo=" . urlencode($content) . "&accountName=" . urlencode($acc_name);
                        ?>
                            <img src="<?= $qr_url ?>" class="ord-qr-img" alt="VietQR">
                        <?php elseif ($order['payment_method'] == 'momo'): ?>
                            <img src="assets/img/qr-momo-shop.jpg" class="ord-qr-img" alt="Momo">
                        <?php endif; ?>
                    </div>

                    <div class="ord-qr-info">
                        <p>Ngân hàng: <b>MB Bank (Quân Đội)</b></p>
                        <p>Số tài khoản: <b>0862385393</b></p>
                        <p>Chủ tài khoản: <b>NGUYEN HAI DANG</b></p>
                        <p>Số tiền: <span class="ord-highlight"><?= number_format($order['final_money'], 0, ',', '.') ?>₫</span></p>
                        <p>Nội dung CK: <span class="ord-note"><?= $order['code'] ?></span></p>
                        <small style="color: #666; display: block; margin-top: 10px;">
                            * Hệ thống sẽ tự động cập nhật trạng thái sau 1-3 phút.
                        </small>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php
        // Điều kiện hiển thị: Có thông tin VÀ Trạng thái đơn phải là 'shipping' hoặc 'completed'
        $showShipping = !empty($shipping_info) && in_array($order['status'], ['shipping', 'completed']);
        ?>

        <?php if ($showShipping): ?>
            <div class="ord-shipping-box">
                <h3 class="ord-ship-title">
                    <i class="fas fa-shipping-fast"></i> Theo dõi vận đơn
                </h3>

                <div class="ord-ship-content">
                    <div class="ord-ship-row">
                        <span class="ord-ship-label">Đơn vị vận chuyển:</span>
                        <strong><?= htmlspecialchars($shipping_info['provider']) ?></strong>
                    </div>

                    <div class="ord-ship-row">
                        <span class="ord-ship-label">Mã vận đơn:</span>
                        <span class="ord-ship-code"><?= htmlspecialchars($shipping_info['tracking_code']) ?></span>

                        <i class="fas fa-copy ord-btn-copy"
                            onclick="navigator.clipboard.writeText('<?= $shipping_info['tracking_code'] ?>'); alert('Đã sao chép mã vận đơn: <?= $shipping_info['tracking_code'] ?>');"
                            title="Sao chép"></i>
                    </div>

                    <div class="ord-ship-row">
                        <span class="ord-ship-label">Tình trạng:</span>
                        <span class="ord-ship-status">
                            <?= htmlspecialchars($shipping_info['status']) ?>
                        </span>
                    </div>

                    <div class="ord-ship-time">
                        <i class="far fa-clock"></i> Cập nhật lần cuối: <?= date('H:i - d/m/Y', strtotime($shipping_info['updated_at'])) ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>