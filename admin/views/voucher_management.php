<div class="order-container">
    <h1>Quản Lý Mã Giảm Giá</h1>

    <div class="order-filter-box">
        <div style="position: relative;">
            <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #999;"></i>
            <input type="text" id="searchVoucher" onkeyup="filterVouchers()" placeholder="Tìm theo mã code..." style="padding-left: 35px;">
        </div>
        
        <a class="order-btn-action btn-add" href="?page=add_voucher">
            <i class="fas fa-plus"></i> Thêm Voucher
        </a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Mã Code</th>
                <th>Giảm giá</th>
                <th>Đơn tối thiểu</th>
                <th>SL</th>
                <th>Thời gian</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($vouchers)): ?>
                <tr><td colspan="8" style="text-align:center; padding:30px; color:#888;">Chưa có mã giảm giá nào</td></tr>
            <?php else: ?>
                <?php foreach ($vouchers as $v): 
                    // Logic xử lý trạng thái hiển thị
                    $now = date('Y-m-d H:i:s');
                    $sttClass = 'active';
                    $sttText = 'Đang chạy';

                    if ($v['status'] == 0) {
                        $sttClass = 'hidden'; $sttText = 'Đã ẩn';
                    } elseif ($v['quantity'] <= 0) {
                        $sttClass = 'expired'; $sttText = 'Hết mã';
                    } elseif ($v['end_date'] < $now) {
                        $sttClass = 'expired'; $sttText = 'Hết hạn';
                    } elseif ($v['start_date'] > $now) {
                        $sttClass = 'hidden'; $sttText = 'Chưa mở';
                    }
                ?>
                <tr class="voucher-row" data-search="<?= strtolower($v['code']) ?>">
                    <td>#<?= $v['id'] ?></td>
                    
                    <td>
                        <strong style="color: #2563eb; font-family: monospace; font-size: 15px; background: #eff6ff; padding: 2px 6px; border-radius: 4px;">
                            <?= htmlspecialchars($v['code']) ?>
                        </strong>
                    </td>
                    
                    <td style="color: #d70018; font-weight: bold;">
                        <?php if($v['discount_type'] == 'percent'): ?>
                            <?= floatval($v['discount_value']) ?>%
                        <?php else: ?>
                            <?= number_format($v['discount_value'], 0, ',', '.') ?>₫
                        <?php endif; ?>
                    </td>

                    <td><?= number_format($v['min_order_amount'], 0, ',', '.') ?>₫</td>
                    
                    <td>
                        <span style="font-weight: 600;"><?= $v['quantity'] ?></span>
                    </td>
                    
                    <td style="font-size: 13px; color: #666;">
                        <div>BĐ: <?= date('d/m/y H:i', strtotime($v['start_date'])) ?></div>
                        <div>KT: <?= date('d/m/y H:i', strtotime($v['end_date'])) ?></div>
                    </td>

                    <td><span class="voucher-status <?= $sttClass ?>"><?= $sttText ?></span></td>

                    <td>
                        <a href="admin.php?page=edit_voucher&id=<?= $v['id'] ?>" 
                        class="order-btn-action btn-edit">
                            <i class="fas fa-edit"></i>
                        </a>

                        <a href="admin.php?page=delete_voucher&id=<?= $v['id'] ?>" 
                        class="order-btn-action btn-delete" 
                        onclick="return confirm('Bạn có chắc chắn muốn xoá voucher không? ');"> 
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>