<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý IMEI & In Tem</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    
    <style>
        /* --- COPY NGUYÊN BỘ CSS CHUẨN CỦA TRANG ĐƠN HÀNG QUA ĐÂY --- */
        
        /* 1. KHUNG CARD & HEADER */
        .card {
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            border-radius: 0.5rem;
            background: #fff;
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #e3e6f0;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap; gap: 10px;
        }
        .header-title { color: #4e73df; font-weight: 700; margin: 0; font-size: 1.1rem; }

        /* 2. BỘ LỌC & INPUT */
        .filter-group { display: flex; gap: 10px; flex-wrap: wrap; }
        .form-control-sm {
            height: calc(1.5em + 0.5rem + 2px);
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 0.2rem;
            border: 1px solid #d1d3e2;
            color: #6e707e;
            outline: none;
        }
        .form-control-sm:focus { border-color: #bac8f3; box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25); }

        /* 3. TABLE STYLE */
        .table-responsive { width: 100%; overflow-x: auto; }
        .table { width: 100%; margin-bottom: 0; color: #858796; border-collapse: collapse; }
        .table-bordered { border: 1px solid #e3e6f0; }
        .table thead th {
            background-color: #4e73df; color: #fff;
            text-transform: uppercase; font-size: 0.8rem;
            border-bottom: none; vertical-align: middle; text-align: center;
            padding: 12px; white-space: nowrap;
        }
        .table tbody td {
            vertical-align: middle; font-size: 0.9rem;
            padding: 12px; text-align: center; border: 1px solid #e3e6f0;
        }
        .table-hover tbody tr:hover { background-color: #f8f9fc; }
        .text-start { text-align: left !important; }

        /* 4. BADGES TRẠNG THÁI IMEI */
        .badge-custom {
            padding: 5px 10px; border-radius: 50rem;
            font-size: 0.75rem; font-weight: 700;
            display: inline-block; min-width: 90px;
            text-align: center; color: #fff;
        }
        .bg-available { background-color: #1cc88a; } 
        .bg-sold { background-color: #858796; }      
        .bg-error { background-color: #e74a3b; }     

        /* 5. ACTION BUTTONS */
        .btn-action {
            width: 32px; height: 32px; border-radius: 4px;
            display: inline-flex; align-items: center; justify-content: center;
            margin: 0 2px; transition: 0.2s; text-decoration: none; border: none; cursor: pointer;
        }
        .btn-edit { background: #eef2ff; color: #4e73df; }
        .btn-edit:hover { background: #4e73df; color: #fff; }
        .btn-delete { background: #ffe3e3; color: #e74a3b; }
        .btn-delete:hover { background: #e74a3b; color: #fff; }
        
        .btn-add-new {
            background-color: #4e73df; color: white; padding: 6px 15px;
            border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.9rem; border: none; cursor: pointer;
        }
        .btn-print {
            background-color: #36b9cc; color: white; padding: 6px 15px;
            border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.9rem; border: none; cursor: pointer;
        }
        .btn-print:hover { background-color: #2c9faf; }

        /* 6. PAGINATION */
        .pagination-container { padding: 1rem; border-top: 1px solid #e3e6f0; display: flex; justify-content: flex-end; }
        .pagination { display: flex; list-style: none; padding: 0; margin: 0; }
        .page-item { margin: 0 2px; }
        .page-link {
            display: block; padding: 0.5rem 0.75rem; color: #4e73df; background-color: #fff;
            border: 1px solid #dddfeb; border-radius: 0.35rem; text-decoration: none;
        }
        .page-item.active .page-link { background-color: #4e73df; color: #fff; border-color: #4e73df; }
        .page-item.disabled .page-link { color: #858796; pointer-events: none; }

        /* 7. POPUP STYLES */
        .custom-popup {
            display: none; position: fixed; z-index: 1050; left: 0; top: 0; width: 100%; height: 100%;
            overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(2px);
            justify-content: center; align-items: center;
        }
        .popup-content {
            background-color: #fff; padding: 25px; border-radius: 8px;
            width: 500px; max-width: 95%;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            display: flex; flex-direction: column; gap: 15px;
        }
        .popup-content h2 { margin-top: 0; font-size: 1.25rem; color: #4e73df; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .popup-content label { font-weight: 600; font-size: 0.85rem; color: #5a5c69; margin-bottom: 5px; display: block;}
        .popup-content select, .popup-content textarea, .popup-content input {
            padding: 8px 12px; border: 1px solid #d1d3e2; border-radius: 4px; font-size: 0.9rem; width: 100%; box-sizing: border-box;
        }
        .popup-buttons { display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px; }
        .btn-save { padding: 8px 20px; background: #4e73df; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; }
        .btn-close { padding: 8px 20px; background: #858796; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; }
        
        .container-fluid { padding: 0 20px; }
        .imei-text { font-family: 'Courier New', Courier, monospace; font-weight: bold; color: #333; letter-spacing: 1px; }

        /* --- VÙNG IN ẤN (Chỉ hiển thị khi in) --- */
        #print-area { display: none; }

        @media print {
            /* Ẩn tất cả mọi thứ */
            body * { visibility: hidden; }
            .container-fluid, .card, .pagination-container { display: none; }
            
            /* Chỉ hiện vùng in */
            #print-area, #print-area * { visibility: visible; display: block; }
            #print-area {
                position: absolute; left: 0; top: 0; width: 100%;
                display: grid;
                grid-template-columns: repeat(4, 1fr); /* 4 tem trên 1 hàng */
                gap: 15px;
                padding: 20px;
            }

            /* Style cho từng tem */
            .qr-sticker {
                border: 1px dashed #ccc;
                padding: 10px;
                text-align: center;
                page-break-inside: avoid;
                border-radius: 5px;
            }
            .qr-sticker img {
                margin: 0 auto;
                width: 100px; /* Kích thước QR khi in */
                height: 100px;
            }
            .qr-code-text {
                font-size: 12px;
                font-family: monospace;
                margin-top: 5px;
                font-weight: bold;
            }
            .qr-product-name {
                font-size: 10px;
                margin-bottom: 5px;
                overflow: hidden;
                white-space: nowrap;
                text-overflow: ellipsis;
            }
        }
    </style>
</head>
<body>

<div id="print-area">
    </div>

<?php
// --- 1. LOGIC PHÂN TRANG (PHP) ---
$limit = 20;
$current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($current_page < 1) $current_page = 1;
?>

<div class="container-fluid px-4">
    <h1 class="mt-4 mb-4" style="color: #5a5c69; font-size: 1.75rem;">Quản Lý Kho IMEI / Serial</h1>

    <div class="card mb-4">
        <div class="card-header">
            <h6 class="header-title">
                <i class="fas fa-barcode me-1"></i> Danh sách mã máy
            </h6>
            
            <div class="filter-group">
                <form method="GET" style="display: flex; gap: 10px; align-items: center;">
                    <input type="hidden" name="page" value="imei">
                    
                    <input type="text" name="search" class="form-control-sm" style="width: 180px;" 
                           placeholder="Tìm IMEI hoặc SP..." value="<?= htmlspecialchars($search ?? '') ?>">
                    
                    <select name="status" class="form-control-sm" onchange="this.form.submit()">
                        <option value="">-- Trạng thái --</option>
                        <option value="available" <?= ($status_filter ?? '') == 'available' ? 'selected' : '' ?>>Trong kho</option>
                        <option value="sold" <?= ($status_filter ?? '') == 'sold' ? 'selected' : '' ?>>Đã bán</option>
                        <option value="error" <?= ($status_filter ?? '') == 'error' ? 'selected' : '' ?>>Lỗi/Hỏng</option>
                    </select>
                    
                    <button type="submit" class="btn-action" style="width: auto; padding: 0 10px; background: #4e73df; color: white;">
                        <i class="fas fa-search"></i>
                    </button>
                </form>

                <button onclick="printSelectedDetails()" class="btn-print">
                    <i class="fas fa-print me-1"></i> In QR
                </button>

                <button onclick="document.getElementById('addModal').style.display='flex'" class="btn-add-new">
                    <i class="fas fa-plus me-1"></i> Nhập Kho
                </button>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="5%"><input type="checkbox" id="selectAll" onclick="toggleAll(this)"></th>
                            <th width="5%">ID</th>
                            <th class="text-start" width="35%">Sản phẩm / Phiên bản</th>
                            <th width="25%">Mã IMEI / Serial</th>
                            <th width="15%">Trạng thái</th>
                            <th width="15%">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($imeis)): ?>
                            <tr><td colspan="6" class="text-center py-4">Không tìm thấy dữ liệu phù hợp.</td></tr>
                        <?php else: foreach ($imeis as $item): 
                            $statusClass = 'bg-available';
                            $statusText = 'Trong kho';
                            if ($item['status'] == 'sold') { $statusClass = 'bg-sold'; $statusText = 'Đã bán'; }
                            if ($item['status'] == 'error') { $statusClass = 'bg-error'; $statusText = 'Lỗi/Hỏng'; }
                        ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="imei-checkbox" 
                                           value="<?= htmlspecialchars($item['imei']) ?>" 
                                           data-name="<?= htmlspecialchars($item['product_name']) ?> - <?= $item['color'] ?>">
                                </td>
                                <td>#<?= $item['id'] ?></td>
                                
                                <td class="text-start">
                                    <div style="font-weight: 600; color: #4e73df; margin-bottom: 3px;">
                                        <?= htmlspecialchars($item['product_name']) ?>
                                    </div>
                                    <div style="font-size: 0.8rem; color: #858796;">
                                        <i class="fas fa-memory me-1"></i> <?= $item['ram'] ?> / <?= $item['rom'] ?> 
                                        <span style="margin: 0 5px;">|</span>
                                        <i class="fas fa-palette me-1"></i> <?= $item['color'] ?>
                                    </div>
                                </td>

                                <td>
                                    <span class="imei-text"><?= htmlspecialchars($item['imei']) ?></span>
                                </td>

                                <td>
                                    <span class="badge-custom <?= $statusClass ?>"><?= $statusText ?></span>
                                </td>

                                <td>
                                    <button class="btn-action btn-edit" title="Cập nhật trạng thái"
                                        onclick="openEditModal(
                                            '<?= $item['id'] ?>', 
                                            '<?= $item['status'] ?>', 
                                            '<?= htmlspecialchars($item['imei']) ?>'
                                        )">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <?php if ($item['status'] != 'sold'): ?>
                                        <a href="?page=imei&action=delete&id=<?= $item['id'] ?>" 
                                           class="btn-action btn-delete" title="Xóa"
                                           onclick="return confirm('Bạn có chắc chắn muốn xóa mã IMEI này?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if (isset($total_pages) && $total_pages > 1): ?>
            <div class="pagination-container">
                <ul class="pagination">
                    <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=imei&p=<?= $current_page - 1 ?>&search=<?= $search ?>&status=<?= $status_filter ?>">&laquo;</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=imei&p=<?= $i ?>&search=<?= $search ?>&status=<?= $status_filter ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=imei&p=<?= $current_page + 1 ?>&search=<?= $search ?>&status=<?= $status_filter ?>">&raquo;</a>
                    </li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="addModal" class="custom-popup">
    <div class="popup-content">
        <h2>Nhập Kho IMEI Mới</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add_imei">
            
            <div>
                <label>Chọn Sản phẩm (Phiên bản):</label>
                <select name="product_variant_id" required>
                    <option value="">-- Chọn sản phẩm --</option>
                    <?php if(!empty($variants)): foreach ($variants as $v): ?>
                        <option value="<?= $v['id'] ?>">
                            <?= $v['name'] ?> (<?= $v['ram'] ?> - <?= $v['rom'] ?> - <?= $v['color'] ?>)
                        </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>

            <div>
                <label>Danh sách IMEI (Mỗi dòng 1 mã):</label>
                <textarea name="imei_list" rows="6" placeholder="Sử dụng máy quét hoặc copy/paste danh sách IMEI vào đây..." required></textarea>
                <small style="color: #858796; font-style: italic;">* Hệ thống sẽ tự động bỏ qua các mã trùng lặp.</small>
            </div>

            <div class="popup-buttons">
                <button type="submit" class="btn-save">Lưu dữ liệu</button>
                <button type="button" class="btn-close" onclick="document.getElementById('addModal').style.display='none'">Hủy</button>
            </div>
        </form>
    </div>
</div>

<div id="editModal" class="custom-popup">
    <div class="popup-content" style="width: 400px;">
        <h2>Cập nhật trạng thái</h2>
        <div style="background: #f8f9fc; padding: 10px; border-radius: 4px; text-align: center; margin-bottom: 10px;">
            <span style="color: #858796;">Mã IMEI:</span><br>
            <strong id="displayImei" style="font-family: monospace; font-size: 1.2rem; color: #4e73df;"></strong>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="id" id="editId">
            <label>Trạng thái hiện tại:</label>
            <select name="status" id="editStatus">
                <option value="available">Trong kho (Available)</option>
                <option value="sold">Đã bán (Sold)</option>
                <option value="error">Lỗi / Hỏng (Error)</option>
            </select>
            <div class="popup-buttons">
                <button type="submit" class="btn-save">Cập nhật</button>
                <button type="button" class="btn-close" onclick="document.getElementById('editModal').style.display='none'">Đóng</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, status, imei) {
        document.getElementById('editId').value = id;
        document.getElementById('editStatus').value = status;
        document.getElementById('displayImei').innerText = imei;
        document.getElementById('editModal').style.display = 'flex'; 
    }

    window.onclick = function(event) {
        if (event.target.classList.contains('custom-popup')) {
            event.target.style.display = "none";
        }
    }

    // --- CHỨC NĂNG CHỌN TẤT CẢ ---
    function toggleAll(source) {
        const checkboxes = document.querySelectorAll('.imei-checkbox');
        checkboxes.forEach(cb => cb.checked = source.checked);
    }

    // --- CHỨC NĂNG IN MÃ QR ---
    function printSelectedDetails() {
        const printArea = document.getElementById('print-area');
        printArea.innerHTML = ""; // Xóa dữ liệu cũ

        const selected = document.querySelectorAll('.imei-checkbox:checked');
        if (selected.length === 0) {
            alert("Vui lòng chọn ít nhất 1 IMEI để in!");
            return;
        }

        // Tạo các tem
        selected.forEach(cb => {
            const imei = cb.value;
            const name = cb.getAttribute('data-name');

            // Tạo khung tem
            const sticker = document.createElement('div');
            sticker.className = 'qr-sticker';

            // Tạo tên sản phẩm
            const prodName = document.createElement('div');
            prodName.className = 'qr-product-name';
            prodName.innerText = name;
            sticker.appendChild(prodName);

            // Tạo thẻ div chứa QR
            const qrDiv = document.createElement('div');
            // Dùng thư viện qrcodejs để vẽ
            new QRCode(qrDiv, {
                text: imei,
                width: 100,
                height: 100
            });
            sticker.appendChild(qrDiv);

            // Tạo text IMEI bên dưới
            const codeText = document.createElement('div');
            codeText.className = 'qr-code-text';
            codeText.innerText = imei;
            sticker.appendChild(codeText);

            printArea.appendChild(sticker);
        });

        // Đợi 1 chút để QR render xong rồi mới in
        setTimeout(() => {
            window.print();
        }, 500);
    }
</script>

</body>
</html>