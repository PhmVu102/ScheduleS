<div class="table-card">
    <h2><i class="fa-solid fa-clock-rotate-left"></i> Lịch sử sử dụng Voucher</h2>

    <div style="overflow-x: auto;">
        <table class="modern-table">
            <thead>
                <tr>
                    <th width="5%" style="text-align: center;">ID</th>
                    <th width="25%">Khách hàng</th>
                    <th width="20%">Mã Voucher</th>
                    <th width="15%" style="text-align: center;">Đơn hàng</th>
                    <th width="20%">Thời gian dùng</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($vouchers)): ?>
                    <?php foreach ($vouchers as $item): ?>
                        <tr>
                            <td style="text-align: center;">
                                #<?php echo $item['id']; ?>
                            </td>

                            <td>
                                <span class="user-name">
                                    <?php echo htmlspecialchars($item['fullname'] ?? 'Khách vãng lai'); ?>
                                </span>
                                </td>

                            <td>
                                <span class="badge-code">
                                    <i class="fa-solid fa-ticket"></i> 
                                    <?php echo htmlspecialchars($item['code'] ?? 'N/A'); ?>
                                </span>
                            </td>

                            <td style="text-align: center;">
                                <a href="?page=orders&action=detail&id=<?php echo $item['order_id']; ?>" class="order-link">
                                    #<?php echo $item['order_id']; ?>
                                </a>
                            </td>

                            <td class="date-cell">
                                <?php 
                                    // Format lại ngày giờ cho đẹp (Ngày/Tháng/Năm Giờ:Phút)
                                    echo date("d/m/Y H:i", strtotime($item['used_at'])); 
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: #999;">
                            <i class="fa-regular fa-folder-open" style="font-size: 40px; margin-bottom: 10px;"></i><br>
                            Chưa có lịch sử sử dụng voucher nào!
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>