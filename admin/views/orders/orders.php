<?php
// --- 1. LOGIC PHÂN TRANG (Thêm đoạn này vào đầu file) ---
// Cấu hình số lượng đơn hàng trên 1 trang
$limit = 6; 

// Lấy trang hiện tại từ URL (mặc định là 1)
$current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($current_page < 1) $current_page = 1;

// Tính toán tổng số trang
$total_records = count($orders); // Tổng số đơn hàng
$total_pages = ceil($total_records / $limit);

// Cắt dữ liệu cho trang hiện tại
$offset = ($current_page - 1) * $limit;
$list_current = array_slice($orders, $offset, $limit);
?>
<div class="order-container">
    <h1>Quản Lý Đơn Hàng</h1>

    <div class="order-filter-box">
        <input type="text" id="searchInput" onkeyup="filterOrders()" placeholder="Tìm theo mã đơn hàng, tên khách...">

        <select id="statusFilter" onchange="filterOrders()">
            <option value="all">Tất cả trạng thái</option>
            <option value="pending">Chờ xử lý</option>
            <option value="confirmed">Đã xác nhận</option>
            <option value="shipping">Đang giao</option>
            <option value="completed">Hoàn thành</option>
            <option value="cancelled">Đã hủy</option>
            <option value="returned">Trả hàng</option>
        </select>

        <select id="timeFilter" onchange="filterOrders()">
            <option value="all">Thời gian: Tất cả</option>
            <option value="today">Hôm nay</option>
            <option value="week">Tuần này</option>
            <option value="month">Tháng này</option>
        </select>
        <button onclick="exportToExcel()" style="padding: 8px 15px; background: #10b981; color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">
            <i class="fas fa-file-excel"></i> Xuất Excel
        </button>
    </div>

    <table>
        <thead>
            <tr>
                <th>Mã đơn</th>
                <th>Khách hàng</th>
                <th>Ngày đặt</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Phương thức</th>
                <th>Thanh toán</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($list_current as $od):
                // Xử lý hiển thị trạng thái đơn hàng
                $statusClass = 'pending';
                $statusText = $od['status'];
                switch ($od['status']) {
                    case 'pending': $statusClass = 'pending'; $statusText = 'Chờ xử lý'; break;
                    case 'confirmed': $statusClass = 'confirmed'; $statusText = 'Đã xác nhận'; break;
                    case 'shipping': $statusClass = 'shipping'; $statusText = 'Đang giao'; break;
                    case 'completed': $statusClass = 'completed'; $statusText = 'Hoàn thành'; break;
                    case 'cancelled': $statusClass = 'cancel'; $statusText = 'Đã hủy'; break;
                    case 'returned': $statusClass = 'returned'; $statusText = 'Trả hàng'; break;
                }

                // XỬ LÝ TRẠNG THÁI THANH TOÁN
                $paymentStatus = $od['payment_status'] ?? 'pending';
                $paymentText = 'Chờ TT';
                $paymentClass = 'pending';

                $methodCode = $od['payment_method'] ?? 'cod';
                $methodText = '';
                switch (strtolower($methodCode)) {
                    case 'cod': $methodText = 'Thanh toán khi nhận hàng'; break;
                    case 'bank': $methodText = 'Chuyển khoản (VietQR)'; break;
                    case 'momo': $methodText = 'Ví MoMo'; break;
                    case 'zalopay': $methodText = 'Ví ZaloPay'; break;
                    default: $methodText = strtoupper($methodCode);
                }

                if ($methodCode === 'cod') {
                    if ($od['status'] === 'completed') {
                        $paymentText = 'Đã TT (COD)'; $paymentClass = 'completed';
                    } elseif ($od['status'] === 'cancelled' || $od['status'] === 'returned') {
                        $paymentText = 'Không thu tiền'; $paymentClass = 'cancel';
                    } else {
                        $paymentText = 'Chờ thu tiền'; $paymentClass = 'pending';
                    }
                } else {
                    if ($paymentStatus == 'success' || $od['status'] == 2) {
                        $paymentText = 'Đã thanh toán'; $paymentClass = 'completed';
                    } elseif ($paymentStatus == 'failed') {
                        $paymentText = 'Thất bại'; $paymentClass = 'cancel';
                    } else {
                        $paymentText = 'Chờ thanh toán'; $paymentClass = 'pending';
                    }
                }
            ?>
                <tr class="order-row"
                    data-search="<?= strtolower($od['code'] . ' ' . $od['fullname']); ?>"
                    data-status="<?= $od['status']; ?>"
                    data-date="<?= date('Y-m-d', strtotime($od['created_at'])); ?>">
                    <td><strong><?= htmlspecialchars($od['code']); ?></strong></td>
                    <td>
                        <?php
                        $customerName = htmlspecialchars($od['user_name'] ?? $od['fullname']);
                        $receiverInfo = htmlspecialchars($od['fullname']);
                        $phone = htmlspecialchars($od['phone']);

                        if (!empty($od['user_name']) && $od['user_name'] != $od['fullname']) {
                            echo "<strong>" . $customerName . "</strong> (Đăng nhập)<br>";
                            echo "<small style='color: #666;'>Người nhận: {$receiverInfo} - SĐT: {$phone}</small>";
                        } else {
                            echo "<strong>" . $customerName . "</strong><br>";
                            echo "<small style='color: #666;'>SĐT: {$phone}</small>";
                        }
                        ?>
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($od['created_at'])); ?></td>
                    <td style="font-weight: bold; color: #2563eb;">
                        <?= number_format($od['final_money'], 0, ',', '.'); ?>đ
                    </td>
                    <td><span class="order-status <?= $statusClass; ?>"><?= $statusText; ?></span></td>
                    <td>
                        <div style="font-weight: 600; color: #555;"><?= $methodText; ?></div>
                    </td>
                    <td><span class="order-status <?= $paymentClass; ?>"><?= $paymentText; ?></span></td>

                    <td>
                        <button class="order-btn-action btn-view"
                            onclick='openDetailPopup(
                                "<?= $od['code']; ?>",
                                "<?= htmlspecialchars($od['fullname'], ENT_QUOTES); ?>",
                                "<?= htmlspecialchars($od['phone'], ENT_QUOTES); ?>",
                                "<?= htmlspecialchars($od['address'], ENT_QUOTES); ?>",
                                "<?= number_format($od['final_money'], 0, ',', '.'); ?>đ",
                                <?= json_encode($od['details']); ?> 
                            )'>
                            <i class="fa-solid fa-eye"></i>
                        </button>

                        <button class="order-btn-action btn-edit"
                            onclick="openEditPopup(
                                '<?= $od['id']; ?>',
                                '<?= htmlspecialchars($od['fullname'], ENT_QUOTES); ?>',
                                '<?= htmlspecialchars($od['phone'], ENT_QUOTES); ?>',
                                '<?= htmlspecialchars($od['address'], ENT_QUOTES); ?>',
                                '<?= $od['status']; ?>',
                                '<?= htmlspecialchars($od['note'] ?? '', ENT_QUOTES); ?>',
                                '<?= $od['final_money']; ?>',
                                '<?= $paymentStatus; ?>', 
                                '<?= $methodCode; ?>' 
                            )">
                            <i class="fas fa-edit"></i>
                        </button>
                        <?php if ($od['status'] == 'cancelled' || $od['status'] == 'returned' || $od['status'] == 'completed'): ?>
                            <a href="admin.php?page=orders&action=delete&id=<?= $od['id'] ?>"
                                class="order-btn-action btn-delete"
                                style="background:#dc3545; color:#fff; text-decoration:none; display:inline-block; text-align:center;"
                                onclick="return confirm('CẢNH BÁO: Bạn có chắc chắn muốn XÓA VĨNH VIỄN đơn hàng này không?');">
                                <i class="fas fa-trash"></i>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($total_pages > 1): ?>
    <div class="pagination-container">
        <ul class="pagination">
            <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=orders&p=<?= $current_page - 1 ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=orders&p=<?= $i ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>

            <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=orders&p=<?= $current_page + 1 ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        </ul>
    </div>
    <?php endif; ?>

    <div class="order-edit-popup" id="orderEditPopup">
        <div class="popup-content">
            <h2>Sửa Trạng Thái Đơn</h2>
            <input type="hidden" id="editOrderId">
            <label>Khách hàng</label>
            <input type="text" id="editCustomerName" readonly style="background: #f3f4f6;">
            <label>Số điện thoại</label>
            <input type="text" id="editPhone" readonly style="background: #f3f4f6;">
            <label>Phương thức thanh toán</label>
            <input type="text" id="editMethod" readonly style="background: #eef2ff; color: #333; font-weight: bold;">
            <label>Địa chỉ giao hàng</label>
            <input type="text" id="editAddress">
            <label>Trạng thái đơn hàng</label>
            <select id="editStatus">
                <option value="pending">Chờ xử lý</option>
                <option value="confirmed">Đã xác nhận</option>
                <option value="shipping">Đang giao hàng</option>
                <option value="completed">Hoàn thành</option>
                <option value="cancelled">Hủy đơn</option>
                <option value="returned">Trả hàng</option>
            </select>
            <label>Trạng thái Thanh toán</label>
            <select id="editPaymentStatus">
                <option value="pending">Chờ thanh toán</option>
                <option value="success">Đã thanh toán (Thành công)</option>
                <option value="failed">Thanh toán thất bại</option>
            </select>
            <label>Ghi chú (Admin)</label>
            <textarea id="editNote" placeholder="Ghi chú nội bộ..."></textarea>
            <label>Tổng tiền</label>
            <input type="text" id="editTotal" readonly style="background: #f3f4f6; font-weight: bold;">
            <div class="popup-buttons">
                <button class="btn-save" onclick="saveOrderChanges()">Cập nhật</button>
                <button class="btn-close" onclick="closeEditPopup()">Hủy</button>
            </div>
        </div>
    </div>

    <div class="order-edit-popup" id="orderDetailPopup">
        <div class="popup-content" style="width: 700px; max-width: 95%;">
            <h2 style="border-bottom: 1px solid #eee; padding-bottom: 10px;">Chi Tiết Đơn <span id="detailOrderCode" style="color: #2563eb;"></span></h2>
            <div style="margin: 15px 0; font-size: 14px; color: #4b5563;">
                <p><strong>Người nhận:</strong> <span id="detailCustomer"></span> (<span id="detailPhone"></span>)</p>
                <p><strong>Địa chỉ:</strong> <span id="detailAddress"></span></p>
            </div>
            <table style="margin-bottom: 20px;">
                <thead>
                    <tr style="background: #f9fafb;">
                        <th>Sản phẩm</th>
                        <th style="text-align: center;">SL</th>
                        <th style="text-align: right;">Đơn giá</th>
                        <th style="text-align: right;">Thành tiền</th>
                    </tr>
                </thead>
                <tbody id="orderItemsList"></tbody>
            </table>
            <div style="text-align: right; font-size: 16px; font-weight: bold;">
                Tổng cộng: <span id="detailFinalMoney" style="color: #dc2626;"></span>
            </div>
            <div class="popup-buttons">
                <button class="btn-close" onclick="closeDetailPopup()">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
    /* --- GIỮ NGUYÊN SCRIPT JS CỦA BẠN (Đã ẩn bớt để gọn, bạn giữ nguyên nhé) --- */
    function openEditPopup(orderId, customer, phone, address, status, note, total, paymentStatus, paymentMethod) {
        document.getElementById("orderEditPopup").style.display = "flex";
        document.getElementById("editOrderId").value = orderId;
        document.getElementById("editCustomerName").value = customer;
        document.getElementById("editPhone").value = phone;
        document.getElementById("editAddress").value = address;
        document.getElementById("editStatus").value = status;
        document.getElementById("editNote").value = note;
        document.getElementById("editMethod").value = paymentMethod.toUpperCase();
        document.getElementById("editPaymentStatus").value = paymentStatus;
        document.getElementById("editTotal").value = new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(total);
    }
    function closeEditPopup() { document.getElementById("orderEditPopup").style.display = "none"; }
    function openDetailPopup(code, customer, phone, address, finalMoney, details) {
        document.getElementById("orderDetailPopup").style.display = "flex";
        document.getElementById("detailOrderCode").innerText = "#" + code;
        document.getElementById("detailCustomer").innerText = customer;
        document.getElementById("detailPhone").innerText = phone;
        document.getElementById("detailAddress").innerText = address;
        document.getElementById("detailFinalMoney").innerText = finalMoney;
        const tbody = document.getElementById("orderItemsList");
        tbody.innerHTML = "";
        if (details && details.length > 0) {
            details.forEach(item => {
                let price = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(item.price);
                let total = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(item.total_price);
                let specs = [];
                if (item.ram) specs.push(item.ram);
                if (item.rom) specs.push(item.rom);
                if (item.color) specs.push(item.color);
                let variantString = specs.length > 0 ? specs.join(' / ') : '';
                let imgHtml = item.variant_image ? `<img src="../${item.variant_image}" class="variant-thumb" alt="Product Image">` : `<div class="no-thumb">No Img</div>`;
                let row = `<tr>
                    <td style="display: flex; align-items: center; gap: 10px;">
                        ${imgHtml}
                        <div>
                            <div style="font-weight: 600; color: #333;">${item.product_name}</div>
                            ${variantString ? `<div style="font-size: 13px; color: #666; margin-top: 2px;">Phân loại: ${variantString}</div>` : ''}
                        </div>
                    </td>
                    <td style="text-align: center;">${item.quantity}</td>
                    <td style="text-align: right;">${price}</td>
                    <td style="text-align: right; font-weight: 600; color: #2563eb;">${total}</td>
                </tr>`;
                tbody.innerHTML += row;
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center">Không có sản phẩm nào</td></tr>';
        }
    }
    function closeDetailPopup() { document.getElementById("orderDetailPopup").style.display = "none"; }
    function saveOrderChanges() {
        const orderId = document.getElementById("editOrderId").value;
        const customerName = document.getElementById("editCustomerName").value;
        const phone = document.getElementById("editPhone").value;
        const address = document.getElementById("editAddress").value;
        const status = document.getElementById("editStatus").value;
        const note = document.getElementById("editNote").value;
        const paymentStatus = document.getElementById("editPaymentStatus").value;
        if (!orderId) { alert("Lỗi: Không tìm thấy ID đơn hàng!"); return; }
        const data = { id: orderId, fullname: customerName, phone: phone, address: address, status: status, note: note, payment_status: paymentStatus };
        const btnSave = document.querySelector(".btn-save");
        const originalText = btnSave.innerText;
        btnSave.innerText = "Đang lưu...";
        btnSave.disabled = true;
        fetch('admin.php?page=update_order', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        }).then(response => response.json()).then(result => {
            if (result.status === 'success') { alert("✅ " + result.message); location.reload(); } 
            else { alert("❌ " + result.message); }
        }).catch(error => { console.error('Error:', error); alert("❌ Có lỗi xảy ra khi kết nối server!"); })
        .finally(() => { btnSave.innerText = originalText; btnSave.disabled = false; closeEditPopup(); });
    }
    window.onclick = function(event) {
        let editPopup = document.getElementById("orderEditPopup");
        let detailPopup = document.getElementById("orderDetailPopup");
        if (event.target == editPopup) editPopup.style.display = "none";
        if (event.target == detailPopup) detailPopup.style.display = "none";
    }
    function filterOrders() {
        const searchText = document.getElementById('searchInput').value.toLowerCase();
        const statusFilter = document.getElementById('statusFilter').value;
        const timeFilter = document.getElementById('timeFilter').value;
        const rows = document.querySelectorAll('.order-row');
        const today = new Date();
        rows.forEach(row => {
            const searchData = row.getAttribute('data-search');
            const matchesSearch = searchData.includes(searchText);
            const statusData = row.getAttribute('data-status');
            const matchesStatus = (statusFilter === 'all') || (statusData === statusFilter);
            const dateData = row.getAttribute('data-date');
            const rowDate = new Date(dateData);
            let matchesTime = true;
            if (timeFilter !== 'all') {
                const rowDateMidnight = new Date(rowDate.setHours(0, 0, 0, 0));
                const todayMidnight = new Date(new Date().setHours(0, 0, 0, 0));
                if (timeFilter === 'today') { matchesTime = rowDateMidnight.getTime() === todayMidnight.getTime(); } 
                else if (timeFilter === 'week') {
                    const day = today.getDay();
                    const diff = today.getDate() - day + (day == 0 ? -6 : 1);
                    const monday = new Date(today.setDate(diff));
                    monday.setHours(0, 0, 0, 0);
                    matchesTime = rowDateMidnight >= monday;
                } else if (timeFilter === 'month') {
                    const currentMonth = new Date().getMonth();
                    const currentYear = new Date().getFullYear();
                    matchesTime = (rowDate.getMonth() === currentMonth) && (rowDate.getFullYear() === currentYear);
                }
            }
            if (matchesSearch && matchesStatus && matchesTime) { row.style.display = ''; } 
            else { row.style.display = 'none'; }
        });
    }
    function exportToExcel() {
        const table = document.querySelector("table");
        const rows = table.querySelectorAll("tr");
        let csvContent = "\uFEFF";
        rows.forEach(row => {
            if (row.style.display !== 'none') {
                const cols = row.querySelectorAll("td, th");
                let rowData = [];
                cols.forEach(col => {
                    let text = col.innerText.replace(/(\r\n|\n|\r)/gm, " ").trim();
                    if (text.includes(",")) { text = `"${text}"`; }
                    rowData.push(text);
                });
                if (rowData.length > 0) { rowData.pop(); csvContent += rowData.join(",") + "\n"; }
            }
        });
        const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
        const link = document.createElement("a");
        const url = URL.createObjectURL(blob);
        const date = new Date().toISOString().slice(0, 10);
        link.setAttribute("href", url);
        link.setAttribute("download", `Danh_sach_don_hang_${date}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>