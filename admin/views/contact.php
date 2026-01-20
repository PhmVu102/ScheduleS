<style>
    /* Reuse CSS chuẩn từ các trang trước */
    .card { border: none; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); border-radius: 0.5rem; background: #fff; }
    .card-header { background-color: #fff; border-bottom: 1px solid #e3e6f0; padding: 1rem 1.25rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;}
    .header-title { color: #4e73df; font-weight: 700; margin: 0; font-size: 1.1rem; }
    
    /* Search & Filter */
    .filter-group { display: flex; gap: 10px; }
    .form-control-sm { height: calc(1.5em + 0.5rem + 2px); padding: 0.25rem 0.5rem; font-size: 0.875rem; border-radius: 0.2rem; border: 1px solid #d1d3e2; color: #6e707e; outline: none;}
    .btn-search { background: #4e73df; color: white; border: none; padding: 0 10px; border-radius: 4px; cursor: pointer; }
    
    /* Bulk Action */
    .btn-bulk-delete { background: #e74a3b; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem; font-weight: 600; cursor: pointer; display: none; /* Mặc định ẩn */ }
    .btn-bulk-delete:hover { background: #c0392b; }

    /* Table */
    .table-responsive { width: 100%; overflow-x: auto; }
    .table { width: 100%; margin-bottom: 0; color: #858796; border-collapse: collapse; }
    .table-bordered { border: 1px solid #e3e6f0; }
    .table thead th { background-color: #4e73df; color: #fff; text-transform: uppercase; font-size: 0.8rem; padding: 12px; text-align: center; vertical-align: middle;}
    .table tbody td { vertical-align: middle; font-size: 0.9rem; padding: 12px; border: 1px solid #e3e6f0; }
    .table-hover tbody tr:hover { background-color: #f8f9fc; }
    
    .text-truncate-custom { max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: inline-block; vertical-align: middle;}
    .text-start { text-align: left !important; }

    /* Badges */
    .badge-custom { padding: 5px 10px; border-radius: 50rem; font-size: 0.75rem; font-weight: 700; color: #fff; display: inline-block;}
    .bg-new { background-color: #e74a3b; } 
    .bg-read { background-color: #1cc88a; } 

    /* Pagination */
    .pagination-container { padding: 1rem; border-top: 1px solid #e3e6f0; display: flex; justify-content: flex-end; }
    .pagination { display: flex; list-style: none; padding: 0; margin: 0; }
    .page-item { margin: 0 2px; }
    .page-link { display: block; padding: 0.5rem 0.75rem; color: #4e73df; background-color: #fff; border: 1px solid #dddfeb; border-radius: 0.35rem; text-decoration: none; }
    .page-item.active .page-link { background-color: #4e73df; color: #fff; border-color: #4e73df; }    
    /* Action Buttons */
    .btn-action { width: 32px; height: 32px; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center; margin: 0 2px; transition: 0.2s; text-decoration: none; border: none; cursor: pointer; }
    .btn-view { background: #eef2ff; color: #4e73df; }
    .btn-delete { background: #ffe3e3; color: #e74a3b; }
</style>

<div class="container-fluid px-4">
    <h1 class="mt-4 mb-4" style="color: #5a5c69; font-size: 1.75rem;">Quản Lý Liên Hệ</h1>

    <div class="card mb-4">
        <div class="card-header">
            <div style="display: flex; align-items: center; gap: 15px;">
                <h6 class="header-title"><i class="fas fa-envelope me-1"></i> Hộp thư</h6>
                <button onclick="deleteSelected()" id="bulkDeleteBtn" class="btn-bulk-delete">
                    <i class="fas fa-trash"></i> Xóa mục đã chọn
                </button>
            </div>

            <form method="GET" action="admin.php" class="filter-group">
                <input type="hidden" name="page" value="contact">
                
                <input type="text" name="search" class="form-control-sm" style="width: 200px;" 
                       placeholder="Tên, Email, SĐT..." value="<?= htmlspecialchars($search ?? '') ?>">
                
                <select name="status" class="form-control-sm" onchange="this.form.submit()">
                    <option value="">-- Trạng thái --</option>
                    <option value="0" <?= ($status_filter ?? '') === '0' ? 'selected' : '' ?>>Mới</option>
                    <option value="1" <?= ($status_filter ?? '') === '1' ? 'selected' : '' ?>>Đã xem</option>
                </select>

                <button type="submit" class="btn-search"><i class="fas fa-search"></i></button>
                
                <?php if(!empty($search) || $status_filter !== ''): ?>
                    <a href="admin.php?page=contact" class="btn-action btn-delete" title="Xóa lọc" style="width: auto; padding: 0 10px;">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="3%"><input type="checkbox" id="selectAll" onclick="toggleAll(this)"></th>
                            <th width="5%">ID</th>
                            <th class="text-start" width="20%">Người gửi</th>
                            <th class="text-start" width="20%">Liên hệ</th>
                            <th class="text-start" width="30%">Nội dung</th>
                            <th width="10%">Ngày gửi</th>
                            <th width="7%">TT</th>
                            <th width="10%">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($contacts)): ?>
                            <tr><td colspan="8" class="text-center py-4">Không tìm thấy dữ liệu phù hợp.</td></tr>
                        <?php else: foreach ($contacts as $row): ?>
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="contact-check" value="<?= $row['id'] ?>" onclick="checkSelection()">
                                </td>
                                <td style="font-weight: bold; color: #858796;">#<?= $row['id'] ?></td>
                                
                                <td class="text-start">
                                    <div style="font-weight: 600; color: #4e73df;"><?= htmlspecialchars($row['fullname']) ?></div>
                                    <div style="font-size: 0.8rem; color: #858796;">
                                        <?= $row['user_id'] ? '<i class="fas fa-user-check" style="color: #1cc88a;"></i> Thành viên' : '<i class="fas fa-user" style="color: #858796;"></i> Khách' ?>
                                    </div>
                                </td>

                                <td class="text-start" style="font-size: 0.9rem;">
                                    <div><i class="fas fa-envelope fa-xs text-gray-400"></i> <?= htmlspecialchars($row['email']) ?></div>
                                    <?php if(!empty($row['phone'])): ?>
                                        <div><i class="fas fa-phone fa-xs text-gray-400"></i> <?= htmlspecialchars($row['phone']) ?></div>
                                    <?php endif; ?>
                                </td>

                                <td class="text-start">
                                    <div style="font-weight: 600; color: #5a5c69; margin-bottom: 3px;">
                                        <?= htmlspecialchars($row['subject']) ?>
                                        <?php if (!empty($row['order_code'])): ?>
                                            <span style="font-size: 0.75rem; background: #eef2ff; color: #4e73df; padding: 2px 6px; border-radius: 4px;">
                                                #<?= htmlspecialchars($row['order_code']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-truncate-custom" title="<?= htmlspecialchars($row['message']) ?>" style="color: #858796; font-size: 0.85rem;">
                                        <?= htmlspecialchars($row['message']) ?>
                                    </div>
                                </td>

                                <td>
                                    <div style="font-size: 0.85rem;"><?= date('d/m/Y', strtotime($row['created_at'])) ?></div>
                                    <div style="font-size: 0.75rem; color: #aaa;"><?= date('H:i', strtotime($row['created_at'])) ?></div>
                                </td>

                                <td class="text-center">
                                    <?php if ($row['status'] == 0): ?>
                                        <span class="badge-custom bg-new">Mới</span>
                                    <?php else: ?>
                                        <span class="badge-custom bg-read">Xem</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <button class="btn-action btn-view" title="Xem chi tiết" onclick="alert('Nội dung:\n<?= htmlspecialchars($row['message']) ?>')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    <a href="admin.php?page=contact&action=delete&id=<?= $row['id'] ?>" 
                                       class="btn-action btn-delete" 
                                       onclick="return confirm('Xóa tin nhắn này?');" title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </a>
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
                        <a class="page-link" href="admin.php?page=contact&p=<?= $current_page - 1 ?>&search=<?= $search ?>&status=<?= $status_filter ?>">&laquo;</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                            <a class="page-link" href="admin.php?page=contact&p=<?= $i ?>&search=<?= $search ?>&status=<?= $status_filter ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="admin.php?page=contact&p=<?= $current_page + 1 ?>&search=<?= $search ?>&status=<?= $status_filter ?>">&raquo;</a>
                    </li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // 1. Chọn tất cả Checkbox
    function toggleAll(source) {
        const checkboxes = document.querySelectorAll('.contact-check');
        checkboxes.forEach(cb => cb.checked = source.checked);
        checkSelection();
    }

    // 2. Kiểm tra xem có mục nào được chọn không để hiện nút Xóa
    function checkSelection() {
        const checkedCount = document.querySelectorAll('.contact-check:checked').length;
        const btnDelete = document.getElementById('bulkDeleteBtn');
        if (checkedCount > 0) {
            btnDelete.style.display = 'inline-block';
            btnDelete.innerText = `Xóa (${checkedCount})`;
        } else {
            btnDelete.style.display = 'none';
        }
    }

    // 3. Gửi yêu cầu xóa nhiều qua AJAX
    function deleteSelected() {
        const checkedBoxes = document.querySelectorAll('.contact-check:checked');
        const ids = Array.from(checkedBoxes).map(cb => cb.value);

        if (ids.length === 0) return;

        if (confirm(`Bạn có chắc chắn muốn xóa ${ids.length} tin nhắn đã chọn?`)) {
            // CẬP NHẬT 5: Link fetch AJAX cho xóa nhiều
            fetch('admin.php?page=contact&action=bulk_delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ids: ids })
            })
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    alert("✅ " + result.message);
                    location.reload();
                } else {
                    alert("❌ " + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("Lỗi kết nối server!");
            });
        }
    }
</script>