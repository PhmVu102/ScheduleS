<div class="oh-wrapper">
    <h2 class="oh-title">Lịch Sử Đơn Hàng</h2>
    <div class="oh-card">
        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <i class="fas fa-clipboard-list"></i>
                <p>Bạn chưa có đơn hàng nào.</p>
                <a href="index.php?page=products" class="btn-shop-now">Mua sắm ngay</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="oh-table">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Ngày đặt</th>
                            <th>Người nhận</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>
                                    <b style="color: #333;">#<?= htmlspecialchars($order['code']) ?></b>
                                </td>

                                <td>
                                    <?= date('d/m/Y', strtotime($order['created_at'])) ?>
                                    <br>
                                    <small style="color:#999;"><?= date('H:i', strtotime($order['created_at'])) ?></small>
                                </td>

                                <td>
                                    <div style="font-weight: 600;"><?= htmlspecialchars($order['fullname']) ?></div>
                                    <div style="font-size: 13px; color: #777; margin-top: 2px;">
                                        <i class="fas fa-phone-alt" style="font-size: 11px;"></i> <?= htmlspecialchars($order['phone']) ?>
                                    </div>
                                </td>

                                <td>
                                    <span style="color:#d70018; font-weight:700; font-size: 16px;">
                                        <?= number_format($order['final_money'], 0, ',', '.') ?>₫
                                    </span>
                                </td>

                                <td>
                                    <?php
                                    $stt = $order['status'];
                                    $sttText = 'Chờ xử lý';
                                    $sttClass = 'badge-pending';

                                    if ($stt == 'confirmed' || $stt == 1) {
                                        $sttText = 'Đã xác nhận';
                                        $sttClass = 'badge-info';
                                    } elseif ($stt == 'shipping' || $stt == 2) {
                                        $sttText = 'Đang giao hàng';
                                        $sttClass = 'badge-shipping';
                                    } elseif ($stt == 'completed' || $stt == 3) {
                                        $sttText = 'Giao thành công';
                                        $sttClass = 'badge-success';
                                    } elseif ($stt == 'cancelled' || $stt == 4) {
                                        $sttText = 'Đã hủy';
                                        $sttClass = 'badge-danger';
                                    } elseif ($stt == 'returned') {
                                        $sttText = 'Trả hàng';
                                        $sttClass = 'badge-danger';
                                    }
                                    ?>
                                    <span class="badge <?= $sttClass ?>"><?= $sttText ?></span>
                                </td>

                                <td>
                                    <a href="index.php?page=order_history&action=view&id=<?= $order['id'] ?>" class="action-btn btn-view">
                                        <i class="fas fa-eye"></i> Chi tiết
                                    </a>

                                    <?php if ($order['status'] == 'pending' || $order['status'] == 0): ?>
                                        <a href="index.php?page=order_history&action=cancel&id=<?= $order['id'] ?>"
                                            class="action-btn btn-cancel"
                                            onclick="return confirm('Bạn chắc chắn muốn hủy đơn hàng này?');">
                                            <i class="fas fa-times"></i> Hủy
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>