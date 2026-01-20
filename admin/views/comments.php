<style>
    /* --- CẤU TRÚC CHUNG --- */    
    .comment-container {
        background: #fff;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        margin: 20px auto;
    }
    /* --- SEARCH BOX --- */
    .search-box { margin-bottom: 20px; text-align: right; }
    .search-box input {
        padding: 10px 15px;
        width: 280px;
        border: 1px solid #d1d3e2;
        border-radius: 5px;
        font-size: 14px;
        color: #6e707e;
    }
    .search-box input:focus { outline: none; border-color: #4e73df; box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25); }

    /* --- TABLE STYLE --- */
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    
    th {
        background: #4e73df; /* Màu xanh chủ đạo */
        color: white;
        padding: 12px;
        font-size: 0.85rem;
        text-transform: uppercase;
        border: none;
        text-align: center;
    }
    
    td {
        padding: 12px;
        border-bottom: 1px solid #e3e6f0;
        vertical-align: middle;
        color: #5a5c69;
        font-size: 0.95rem;
    }
    
    tr:hover { background: #f8f9fc; }

    /* --- SAO ĐÁNH GIÁ --- */
    .star { color: #f6c23e; font-size: 14px; letter-spacing: 1px; }

    /* --- BADGE TRẠNG THÁI --- */
    .badge { padding: 5px 10px; border-radius: 50rem; font-size: 0.75rem; font-weight: 700; display: inline-block; min-width: 80px; text-align: center; }
    .badge-approved { background: #1cc88a; color: #fff; } /* Xanh lá */
    .badge-pending { background: #f6c23e; color: #fff; } /* Vàng */

    /* --- TEXT AREA KHI SỬA --- */
    .edit-mode textarea {
        width: 100%; min-height: 80px; padding: 10px;
        border: 1px solid #4e73df; border-radius: 5px;
        font-family: inherit; font-size: 14px;
        margin-bottom: 5px;
    }
    .edit-row { background-color: #f0f4ff !important; }

    /* --- CÁC NÚT BẤM (BUTTONS) - STYLE MỚI --- */
    .action-btns {
        display: flex; justify-content: center; gap: 5px;
    }

    .btn-icon {
        width: 35px; height: 35px;
        border-radius: 5px;
        border: none;
        display: inline-flex; align-items: center; justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 14px;
    }

    /* 1. Nút Duyệt (Toggle) */
    .btn-toggle-approve { background: #e2e6ea; color: #858796; } /* Mặc định xám */
    .btn-toggle-approve:hover { background: #5a5c69; color: #fff; }
    
    .btn-toggle-approve.is-approved { background: #d4edda; color: #155724; } /* Đã duyệt: Xanh nhạt */
    .btn-toggle-approve.is-approved:hover { background: #1cc88a; color: white; }
    
    .btn-toggle-approve.is-pending { background: #fff3cd; color: #856404; } /* Chờ: Vàng nhạt */
    .btn-toggle-approve.is-pending:hover { background: #f6c23e; color: white; }

    /* 2. Nút Sửa */
    .btn-edit-icon { background: #eef2ff; color: #4e73df; }
    .btn-edit-icon:hover { background: #4e73df; color: white; }

    /* 3. Nút Xóa */
    .btn-delete-icon { background: #ffe3e3; color: #e74a3b; }
    .btn-delete-icon:hover { background: #e74a3b; color: white; }

    /* 4. Nút Lưu / Hủy (Dạng chữ) */
    .btn-sm { padding: 4px 10px; font-size: 12px; border-radius: 4px; border: none; cursor: pointer; color: white; font-weight: 600; margin-right: 5px;}
    .btn-save { background: #4e73df; }
    .btn-save:hover { background: #2e59d9; }
    .btn-cancel { background: #858796; }
    .btn-cancel:hover { background: #60616f; }

</style>
<body>
    <div class="comment-container">

        <h1>✨ Quản Lý Đánh Giá & Bình Luận</h1>

        <div class="search-box">
            <form method="get">
                <input type="hidden" name="page" value="comments">
                <input type="text" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" placeholder="🔍 Tìm theo sản phẩm, khách hàng, nội dung...">
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="5%">ID</th>
                    <th width="15%">Sản Phẩm</th>
                    <th width="15%">Khách hàng</th>
                    <th width="10%">Đánh giá</th>
                    <th width="25%">Nội dung</th>
                    <th width="10%">Ngày</th>
                    <th width="10%">Trạng thái</th>
                    <th width="10%">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($comments)): ?>
                    <tr>
                        <td colspan="8" style="text-align:center; padding:40px; color:#858796;">
                            <i class="fas fa-comments fa-2x mb-3" style="opacity: 0.5"></i><br>
                            Chưa có đánh giá nào.
                        </td>
                    </tr>
                    <?php else: foreach ($comments as $c): ?>
                        <tr data-id="<?= $c['id'] ?>">
                            <td class="text-center font-weight-bold">#<?= $c['id'] ?></td>

                            <td>
                                <div style="font-weight: 600; color: #4e73df; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 150px;" title="<?= htmlspecialchars($c['product_name']) ?>">
                                    <?= htmlspecialchars($c['product_name'] ?? 'Sản phẩm đã xóa') ?>
                                </div>
                            </td>

                            <td>
                                <i class="fas fa-user-circle text-gray-400"></i> <?= htmlspecialchars($c['user_name'] ?? 'Khách ẩn danh') ?>
                            </td>

                            <td class="text-center">
                                <?php if ($c['rating'] > 0): ?>
                                    <div style="white-space: nowrap;">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="star" style="color: <?= $i <= $c['rating'] ? '#f6c23e' : '#e3e6f0' ?>">★</span>
                                        <?php endfor; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small">Chưa rate</span>
                                <?php endif; ?>
                            </td>

                            <td class="comment-content">
                                <div class="view-mode" style="font-size: 14px; line-height: 1.5; color: #333;">
                                    <?= nl2br(htmlspecialchars($c['content'])) ?>
                                </div>

                                <div class="edit-mode" style="display:none;">
                                    <textarea><?= htmlspecialchars($c['content']) ?></textarea>
                                    <div style="text-align: right;">
                                        <button class="btn-sm btn-cancel cancel-edit">Hủy</button>
                                        <button class="btn-sm btn-save save-edit"><i class="fas fa-save"></i> Lưu lại</button>
                                    </div>
                                </div>
                            </td>

                            <td class="text-center small text-muted">
                                <?= date('d/m/Y', strtotime($c['created_at'])) ?>
                            </td>

                            <td class="text-center">
                                <span class="badge <?= $c['status'] == 1 ? 'badge-approved' : 'badge-pending' ?>">
                                    <?= $c['status'] == 1 ? 'Hiển thị' : 'Chờ duyệt' ?>
                                </span>
                            </td>

                            <td class="action-btns">
                                <?php
                                $toggleTitle = $c['status'] == 1 ? 'Ẩn bình luận này' : 'Duyệt bình luận này';
                                $toggleClass = $c['status'] == 1 ? 'is-approved' : 'is-pending';
                                $toggleIcon  = $c['status'] == 1 ? 'fa-eye' : 'fa-check'; // Nếu đã duyệt thì hiện mắt (để ẩn), nếu chưa thì hiện tích (để duyệt)
                                // Hoặc logic ngược lại tùy bạn: Đang hiện thì icon mắt, đang ẩn thì icon mắt gạch chéo
                                if ($c['status'] == 1) {
                                    $toggleIcon = 'fa-eye-slash'; // Icon biểu thị hành động tiếp theo: Ẩn đi
                                } else {
                                    $toggleIcon = 'fa-check'; // Icon biểu thị hành động tiếp theo: Duyệt
                                }
                                ?>
                                <button class="btn-icon btn-toggle-approve <?= $toggleClass ?> btn-toggle" title="<?= $toggleTitle ?>">
                                    <i class="fas <?= $toggleIcon ?>"></i>
                                </button>

                                <button class="btn-icon btn-edit-icon btn-edit" title="Sửa nội dung">
                                    <i class="fas fa-pen"></i>
                                </button>

                                <button class="btn-icon btn-delete-icon btn-delete" title="Xóa vĩnh viễn">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                <?php endforeach;
                endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Toggle trạng thái duyệt
        document.querySelectorAll('.btn-toggle').forEach(btn => {
            btn.onclick = function() {
                const id = this.closest('tr').dataset.id;
                // Hiệu ứng loading nhẹ
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'action=toggle&id=' + id
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Có lỗi xảy ra!');
                        location.reload();
                    }
                });
            };
        });

        // Xóa comment
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.onclick = function() {
                const id = this.closest('tr').dataset.id;
                if (confirm('CẢNH BÁO: Bạn có chắc muốn xóa vĩnh viễn bình luận này?')) {
                    fetch('', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'action=delete&id=' + id
                    }).then(r => r.json()).then(data => {
                        if (data.success) {
                            // Xóa dòng khỏi bảng ngay lập tức cho mượt
                            this.closest('tr').remove();
                        } else {
                            alert('Xóa thất bại!');
                        }
                    });
                }
            };
        });

        // Chỉnh sửa comment
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.onclick = function() {
                const row = this.closest('tr');
                row.classList.add('edit-row'); // Thêm class đổi màu nền
                row.querySelector('.view-mode').style.display = 'none';
                row.querySelector('.edit-mode').style.display = 'block';
                // Ẩn nút sửa đi để tránh bấm nhiều lần
                this.style.visibility = 'hidden';
            };
        });

        // Hủy chỉnh sửa
        document.querySelectorAll('.cancel-edit').forEach(btn => {
            btn.onclick = function() {
                const row = this.closest('tr');
                row.classList.remove('edit-row');
                row.querySelector('.view-mode').style.display = 'block';
                row.querySelector('.edit-mode').style.display = 'none';
                // Hiện lại nút sửa
                row.querySelector('.btn-edit').style.visibility = 'visible';
            };
        });

        // Lưu chỉnh sửa
        document.querySelectorAll('.save-edit').forEach(btn => {
            btn.onclick = function() {
                const row = this.closest('tr');
                const id = row.dataset.id;
                const textarea = row.querySelector('textarea');
                const content = textarea.value.trim();

                if (content === '') {
                    textarea.style.border = '1px solid red';
                    return alert('Nội dung không được để trống!');
                }

                // Đổi nút thành đang lưu
                const originalText = this.innerHTML;
                this.innerHTML = 'Lưu...';
                this.disabled = true;

                fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'action=update&id=' + id + '&content=' + encodeURIComponent(content)
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        row.querySelector('.view-mode').innerHTML = data.content; // Cập nhật nội dung mới
                        row.querySelector('.view-mode').style.display = 'block';
                        row.querySelector('.edit-mode').style.display = 'none';
                        row.querySelector('.btn-edit').style.visibility = 'visible';
                        row.classList.remove('edit-row');
                    } else {
                        alert('Lưu thất bại: ' + (data.message || 'Lỗi server'));
                    }
                }).finally(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                });
            };
        });
    </script>