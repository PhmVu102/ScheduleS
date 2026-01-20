<div class="order-container">
    <h1>Quản Lý Vận Chuyển</h1>

    <div class="order-filter-box">
        <input type="text" id="searchShip" onkeyup="filterShipping()" placeholder="Tìm theo mã đơn, mã vận đơn...">
    </div>

    <table>
        <thead>
            <tr>
                <th>Mã đơn</th>
                <th>Khách hàng</th>
                <th>Đơn vị VC</th>
                <th>Mã vận đơn</th>
                <th>Trạng thái</th>
                <th>Cập nhật cuối</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($shipments as $ship): ?>
                <tr class="ship-row" data-search="<?= strtolower($ship['order_code'] . ' ' . $ship['tracking_code']) ?>">
                    <td><strong>#<?= $ship['order_code'] ?></strong></td>
                    <td>
                        <?= htmlspecialchars($ship['fullname']) ?><br>
                        <small><?= htmlspecialchars($ship['phone']) ?></small>
                    </td>
                    <td><?= htmlspecialchars($ship['provider']) ?></td>
                    <td>
                        <span style="background:#eee; padding:2px 6px; border-radius:4px; font-family:monospace;">
                            <?= htmlspecialchars($ship['tracking_code']) ?>
                        </span>
                    </td>
                    <td>
                        <span style="color:#0066ff; font-weight:600;">
                            <?= htmlspecialchars($ship['status']) ?>
                        </span>
                    </td>
                    <td><?= date('H:i d/m', strtotime($ship['updated_at'])) ?></td>
                    <td>
                        <button class="order-btn-action btn-edit" 
                            onclick="openShipPopup(
                                '<?= $ship['id'] ?>', 
                                '<?= $ship['order_code'] ?>', 
                                '<?= htmlspecialchars($ship['provider'], ENT_QUOTES) ?>', 
                                '<?= htmlspecialchars($ship['tracking_code'], ENT_QUOTES) ?>', 
                                '<?= htmlspecialchars($ship['status'], ENT_QUOTES) ?>'
                            )">
                            <i class="fas fa-truck"></i> Cập nhật
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="order-edit-popup" id="shipPopup" style="display: none;">
        <div class="popup-content">
            <h2>Cập nhật Vận Đơn <span id="shipOrderCode" style="color:#0066ff"></span></h2>
            <input type="hidden" id="shipId">
            
            <label>Đơn vị vận chuyển</label>
            <select id="shipProvider">
                <option value="Giao Hàng Nhanh (GHN)">Giao Hàng Nhanh (GHN)</option>
                <option value="Giao Hàng Tiết Kiệm">Giao Hàng Tiết Kiệm (GHTK)</option>
                <option value="Viettel Post">Viettel Post</option>
                <option value="J&T Express">J&T Express</option>
                <option value="Shopee Express">Shopee Express</option>
                <option value="Khác">Khác</option>
            </select>

            <label>Mã vận đơn</label>
            <input type="text" id="shipCode" placeholder="Ví dụ: GHN123456...">

            <label>Trạng thái vận chuyển</label>
            <select id="shipStatus">
                <option value="Chờ lấy hàng">Chờ lấy hàng</option>
                <option value="Đang luân chuyển">Đang luân chuyển</option>
                <option value="Đang giao hàng">Shipper đang giao</option>
                <option value="Giao thành công">Giao thành công</option>
                <option value="Giao thất bại">Giao thất bại / Hoàn hàng</option>
            </select>

            <div class="popup-buttons">
                <button class="btn-save" onclick="saveShipping()">Lưu thông tin</button>
                <button class="btn-close" onclick="document.getElementById('shipPopup').style.display='none'">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Mở popup
    function openShipPopup(id, code, provider, tracking, status) {
        document.getElementById('shipPopup').style.display = 'flex';
        document.getElementById('shipId').value = id;
        document.getElementById('shipOrderCode').innerText = '#' + code;
        
        // Set giá trị cũ
        document.getElementById('shipCode').value = (tracking === 'Đang cập nhật') ? '' : tracking;
        
        // Chọn đúng select option (nếu có), nếu không thì để mặc định
        setSelectValue('shipProvider', provider, 'Khác');
        setSelectValue('shipStatus', status, 'Chờ lấy hàng');
    }

    function setSelectValue(id, value, defaultVal) {
        const select = document.getElementById(id);
        let found = false;
        for(let i=0; i<select.options.length; i++){
            if(select.options[i].value === value){
                select.selectedIndex = i;
                found = true;
                break;
            }
        }
        if(!found) select.value = defaultVal;
    }

    // Lưu dữ liệu
    function saveShipping() {
        const data = {
            id: document.getElementById('shipId').value,
            provider: document.getElementById('shipProvider').value,
            tracking_code: document.getElementById('shipCode').value || 'Đang cập nhật',
            status: document.getElementById('shipStatus').value
        };

        fetch('admin.php?page=update_tracking', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(res => {
            if(res.status === 'success') {
                alert('✅ ' + res.message);
                location.reload();
            } else {
                alert('❌ ' + res.message);
            }
        })
        .catch(err => alert('Lỗi kết nối!'));
    }

    // Lọc tìm kiếm
    function filterShipping() {
        const val = document.getElementById('searchShip').value.toLowerCase();
        document.querySelectorAll('.ship-row').forEach(row => {
            row.style.display = row.getAttribute('data-search').includes(val) ? '' : 'none';
        });
    }
</script>