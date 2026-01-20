<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Danh Mục</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f2f5; }
        
        .cate-container {
            padding: 25px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .cate-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        /* Ô tìm kiếm */
        .search-box input {
            padding: 10px 15px;
            padding-left: 15px;
            width: 280px;
            border: 1px solid #ddd;
            border-radius: 6px;
            outline: none;
            transition: 0.3s;
        }
        .search-box input:focus { border-color: #0066ff; box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.1); }
        
        /* Nút thêm */
        .btn-add {
            text-decoration: none; display: inline-block; padding: 10px 20px;
            background: #0066ff; color: #fff; border-radius: 6px; font-weight: 500; transition: 0.2s;
        }
        .btn-add:hover { background: #0052cc; }

        /* Bảng */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { text-align: left; padding: 14px 15px; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; color: #444; font-weight: 600; text-transform: uppercase; font-size: 13px; }
        tr:hover { background-color: #f9f9f9; }

        /* Badge trạng thái */
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-active { background: #dcfce7; color: #166534; }
        .badge-hidden { background: #f3f4f6; color: #4b5563; }

        /* Nút hành động */
        .action-links a {
            text-decoration: none; display: inline-block; padding: 6px 12px;
            border-radius: 4px; font-size: 13px; font-weight: 500; margin-right: 5px; color: white; transition: 0.2s;
        }
        .btn-edit { background: #10b981; }
        .btn-edit:hover { background: #059669; }
        .btn-delete { background: #ef4444; }
        .btn-delete:hover { background: #dc2626; }

        /* --- CSS PHÂN TRANG (MỚI) --- */
        .pagination-wrapper {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end; /* Căn phải */
        }
        .pagination {
            display: flex;
            list-style: none;
            gap: 5px;
            padding: 0;
            margin: 0;
        }
        .page-link {
            display: block;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: #0066ff;
            text-decoration: none;
            transition: 0.2s;
            font-size: 14px;
        }
        .page-link:hover { background-color: #f1f5f9; }
        .page-item.active .page-link {
            background-color: #0066ff;
            color: white;
            border-color: #0066ff;
        }
        .page-item.disabled .page-link {
            color: #999;
            background-color: #fff;
            cursor: not-allowed;
        }
    </style>
</head>
<body>

    <div class="cate-container">
        <h1>Quản Lý Danh Mục Sản Phẩm</h1>

        <div class="cate-header">
            <div class="search-box">
                <input type="text" id="searchCate" placeholder="🔍 Tìm kiếm danh mục trên trang này...">
            </div>
            
            <a href="?page=add_category" class="btn-add">
                <i class="fas fa-plus"></i> Thêm Danh Mục
            </a>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="5%">ID</th>
                    <th width="25%">Tên Danh Mục</th>
                    <th width="20%">Slug (Đường dẫn)</th>
                    <th width="15%">Ngày Tạo</th>
                    <th width="15%">Trạng Thái</th>
                    <th width="20%">Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($list_current)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 30px; color: #888;">
                            Không có dữ liệu.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($list_current as $cat): ?>
                        <tr>
                            <td>#<?= $cat['id'] ?></td>
                            
                            <td style="font-weight: 600; color: #2c3e50;">
                                <?= htmlspecialchars($cat['name']) ?>
                            </td>
                            
                            <td style="color: #666; font-family: monospace;">
                                <?= htmlspecialchars($cat['slug']) ?>
                            </td>
                            
                            <td>
                                <?= date('d/m/Y', strtotime($cat['created_at'])) ?>
                            </td>
                            
                            <td>
                                <?php if ($cat['status'] == 1): ?>
                                    <span class="badge badge-active">Hiển thị</span>
                                <?php else: ?>
                                    <span class="badge badge-hidden">Đang ẩn</span>
                                <?php endif; ?>
                            </td>
                            
                            <td class="action-links">
                                <a href="?page=edit_category&id=<?= $cat['id'] ?>" class="btn-edit">Sửa</a>
                                <a href="?page=delete_category&id=<?= $cat['id'] ?>" 
                                   class="btn-delete"
                                   onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục: <?= $cat['name'] ?> không?');">
                                    Xóa
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
        <div class="pagination-wrapper">
            <ul class="pagination">
                <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=list_category&p=<?= $current_page - 1 ?>">«</a>
                </li>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=list_category&p=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=list_category&p=<?= $current_page + 1 ?>">»</a>
                </li>
            </ul>
        </div>
        <?php endif; ?>
        
    </div>

    <script>
        // Lưu ý: Script này chỉ tìm được những dòng ĐANG HIỂN THỊ trên trang hiện tại
        document.getElementById('searchCate').addEventListener('keyup', function() {
            let searchValue = this.value.toLowerCase();
            let rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                let text = row.innerText.toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            });
        });
    </script>
</body>
</html>