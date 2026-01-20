<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="main-content-wrapper">
    <div class="page-header">
        <div>
            <h2 class="page-title">Quản Lý Banner</h2>
            <p class="page-subtitle">Banner bao gồm 3 slideshow, 1 góc phải (Dưới), 1 Góc phải (Trên), 2 quảng cáo ngang.</p>
        </div>
        <button class="btn-primary-add" onclick="openModal()">
            <i class="fas fa-plus-circle"></i> Thêm Banner Mới
        </button>
    </div>

    <div class="card-box">
        <?php if (empty($banners)): ?>
            <div class="empty-state">
                <img src="https://cdn-icons-png.flaticon.com/512/4076/4076432.png" alt="Empty">
                <p>Chưa có banner nào. Hãy thêm mới ngay!</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th style="width: 120px;">Hình ảnh</th>
                            <th>Thông tin Link / File</th>
                            <th style="width: 150px;">Vị trí</th>
                            <th style="width: 120px;">Trạng thái</th>
                            <th style="width: 150px;" class="text-right">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($banners as $item): ?>
                            <tr>
                                <td><span class="id-badge">#<?= $item['id'] ?></span></td>

                                <td>
                                    <div class="img-preview">
                                        <img src="../<?= $item['image_url'] ?>" alt="Banner Img" onclick="window.open(this.src)">
                                    </div>
                                </td>

                                <td>
                                    <div class="info-group">
                                        <div class="link-url" title="<?= $item['image_url'] ?>">
                                            <i class="far fa-image"></i> <?= basename($item['image_url']) ?>
                                        </div>
                                        <div class="link-target">
                                            <i class="fas fa-link"></i>
                                            <?= $item['link'] == '#' ? 'Không có link' : htmlspecialchars($item['link']) ?>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <?php
                                    $posName = '';
                                    $posClass = 'badge-gray';
                                    switch ($item['position']) {
                                        case 'slideshow':
                                            $posName = 'Slideshow chính';
                                            $posClass = 'badge-purple';
                                            break;
                                        case 'right_top_1':
                                            $posName = 'Góc phải (Trên)';
                                            $posClass = 'badge-blue';
                                            break;
                                        case 'right_top_2':
                                            $posName = 'Góc phải (Dưới)';
                                            $posClass = 'badge-blue';
                                            break;
                                        default:
                                            $posName = 'Quảng cáo ngang';
                                            $posClass = 'badge-orange';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?= $posClass ?>"><?= $posName ?></span>
                                </td>

                                <td>
                                    <a href="?page=banner&action=toggle_status&id=<?= $item['id'] ?>&status=<?= $item['status'] ?>"
                                        class="toggle-status" title="Bấm để đổi trạng thái">
                                        <?php if ($item['status'] == 1): ?>
                                            <span class="badge badge-success"><i class="fas fa-check-circle"></i> Hiển thị</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger"><i class="fas fa-eye-slash"></i> Đang ẩn</span>
                                        <?php endif; ?>
                                    </a>
                                </td>

                                <td class="text-right">
                                    <div class="action-buttons">
                                        <button class="btn-icon btn-copy" onclick="copyToClipboard('<?= $item['image_url'] ?>')" title="Copy Link Ảnh">
                                            <i class="far fa-copy"></i>
                                        </button>
                                        <a href="?page=banner&action=delete&id=<?= $item['id'] ?>"
                                            class="btn-icon btn-delete"
                                            onclick="return confirm('Bạn có chắc chắn muốn xóa banner này không?')"
                                            title="Xóa Banner">
                                            <i class="far fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="addBannerModal" class="modal-backdrop">
    <div class="modal-container">
        <div class="modal-header">
            <h3>Thêm Banner Mới</h3>
            <span class="close-btn" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form action="?page=banner" method="POST" enctype="multipart/form-data" id="bannerForm">
                <input type="hidden" name="action" value="add">

                <div class="form-group">
                    <label>Hình ảnh Banner <span class="required">*</span></label>
                    <div class="file-upload-wrapper">
                        <input type="file" name="image" id="fileInput" required accept="image/*" onchange="previewImage(event)">
                        <div class="upload-placeholder" id="uploadPlaceholder">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Kéo thả hoặc chọn ảnh</span>
                        </div>
                        <img id="imgPreview" class="preview-box" style="display: none;">
                    </div>
                </div>

                <div class="row-group">
                    <div class="form-group half">
                        <label>Vị trí hiển thị</label>
                        <select name="position" class="form-control">
                            <option value="slideshow">Slideshow (Trang chủ)</option>
                            <option value="right_top_1">Bên phải Slider (Trên)</option>
                            <option value="right_top_2">Bên phải Slider (Dưới)</option>
                            <option value="bottom_1">Quảng cáo ngang (Dưới 1)</option>
                            <option value="bottom_2">Quảng cáo ngang (Dưới 2)</option>
                        </select>
                    </div>
                    <div class="form-group half">
                        <label>Trạng thái</label>
                        <select name="status" class="form-control">
                            <option value="1">Hiển thị ngay</option>
                            <option value="0">Tạm ẩn</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Đường dẫn khi click (Link)</label>
                    <input type="text" name="link" class="form-control" value="#" placeholder="VD: index.php?page=products">
                    <small>Nhập '#' nếu không cần chuyển trang</small>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Hủy bỏ</button>
                    <button type="submit" class="btn-submit">Lưu Banner</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Tổng quan */
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f3f4f6;
        color: #1f2937;
    }

    .main-content-wrapper {
        padding: 20px;
        max-width: 1200px;
        margin: 0 auto;
    }

    /* Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }

    .page-title {
        font-size: 24px;
        font-weight: 700;
        color: #111827;
        margin: 0;
    }

    .page-subtitle {
        color: #6b7280;
        font-size: 14px;
        margin-top: 5px;
    }

    .btn-primary-add {
        background: linear-gradient(135deg, #4f46e5, #4338ca);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
        transition: all 0.2s;
    }

    .btn-primary-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 8px -1px rgba(79, 70, 229, 0.3);
    }

    /* Card & Table */
    .card-box {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .table-custom {
        width: 100%;
        border-collapse: collapse;
    }

    .table-custom th {
        background: #f9fafb;
        color: #6b7280;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 12px;
        padding: 16px;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
    }

    .table-custom td {
        padding: 16px;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
        color: #374151;
        font-size: 14px;
    }

    .table-custom tr:last-child td {
        border-bottom: none;
    }

    .table-custom tr:hover {
        background-color: #f9fafb;
    }

    /* Elements trong bảng */
    .id-badge {
        font-weight: 600;
        color: #9ca3af;
        font-size: 12px;
    }

    .img-preview img {
        width: 80px;
        height: 45px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        transition: transform 0.2s;
        cursor: zoom-in;
    }

    .img-preview img:hover {
        transform: scale(1.5);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 10;
        position: relative;
    }

    .info-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .link-url {
        font-size: 12px;
        color: #6b7280;
        font-family: monospace;
        max-width: 200px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .link-target {
        font-weight: 500;
        color: #2563eb;
        font-size: 13px;
    }

    /* Badges */
    .badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .badge-purple {
        background: #eef2ff;
        color: #4f46e5;
    }

    .badge-blue {
        background: #eff6ff;
        color: #2563eb;
    }

    .badge-orange {
        background: #fff7ed;
        color: #ea580c;
    }

    .badge-gray {
        background: #f3f4f6;
        color: #4b5563;
    }

    .badge-success {
        background: #d1fae5;
        color: #059669;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .badge-danger {
        background: #fee2e2;
        color: #dc2626;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .toggle-status {
        text-decoration: none;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
    }

    .btn-icon {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .btn-copy {
        background: #eff6ff;
        color: #2563eb;
    }

    .btn-copy:hover {
        background: #2563eb;
        color: white;
    }

    .btn-delete {
        background: #fee2e2;
        color: #dc2626;
    }

    .btn-delete:hover {
        background: #dc2626;
        color: white;
    }

    .text-right {
        text-align: right;
    }

    /* Empty State */
    .empty-state {
        padding: 40px;
        text-align: center;
    }

    .empty-state img {
        width: 80px;
        margin-bottom: 15px;
        opacity: 0.5;
    }

    .empty-state p {
        color: #6b7280;
        font-size: 15px;
    }

    /* --- MODAL STYLE --- */
    .modal-backdrop {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        animation: fadeIn 0.3s;
    }

    .modal-container {
        background-color: #fff;
        margin: 5% auto;
        width: 500px;
        max-width: 90%;
        border-radius: 12px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        animation: slideIn 0.3s;
        overflow: hidden;
    }

    .modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f9fafb;
    }

    .modal-header h3 {
        margin: 0;
        font-size: 18px;
        color: #111827;
    }

    .close-btn {
        font-size: 24px;
        cursor: pointer;
        color: #9ca3af;
        transition: 0.2s;
    }

    .close-btn:hover {
        color: #dc2626;
    }

    .modal-body {
        padding: 24px;
    }

    .form-group {
        margin-bottom: 16px;
    }

    .row-group {
        display: flex;
        gap: 15px;
    }

    .form-group.half {
        flex: 1;
    }

    .form-group label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
        font-size: 14px;
        color: #374151;
    }

    .required {
        color: #dc2626;
    }

    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        transition: border 0.2s;
        outline: none;
        box-sizing: border-box;
    }

    .form-control:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    /* File Upload Styling */
    .file-upload-wrapper {
        position: relative;
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: 0.2s;
        background: #f9fafb;
    }

    .file-upload-wrapper:hover {
        border-color: #4f46e5;
        background: #eff6ff;
    }

    .file-upload-wrapper input[type="file"] {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        opacity: 0;
        cursor: pointer;
    }

    .upload-placeholder {
        color: #6b7280;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
        font-size: 14px;
    }

    .upload-placeholder i {
        font-size: 24px;
        color: #9ca3af;
    }

    .preview-box {
        width: 100%;
        height: 150px;
        object-fit: contain;
        margin-top: 10px;
        border-radius: 4px;
    }

    .modal-footer {
        padding-top: 20px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        border-top: 1px solid #e5e7eb;
        margin-top: 10px;
    }

    .btn-cancel {
        padding: 10px 20px;
        background: white;
        border: 1px solid #d1d5db;
        color: #374151;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
    }

    .btn-submit {
        padding: 10px 20px;
        background: #4f46e5;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .btn-submit:hover {
        background: #4338ca;
    }

    /* Animations */
    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes slideIn {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
</style>

<script>
    function openModal() {
        document.getElementById('addBannerModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('addBannerModal').style.display = 'none';
        // Reset form khi đóng
        document.getElementById('bannerForm').reset();
        document.getElementById('imgPreview').style.display = 'none';
        document.getElementById('uploadPlaceholder').style.display = 'flex';
    }

    // Xem trước ảnh khi chọn
    function previewImage(event) {
        var input = event.target;
        var placeholder = document.getElementById('uploadPlaceholder');
        var preview = document.getElementById('imgPreview');

        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                placeholder.style.display = 'none';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            // Có thể thay alert bằng toast notification nếu muốn xịn hơn
            alert('✅ Đã copy đường dẫn: ' + text);
        }, function(err) {
            console.error('Lỗi: ', err);
        });
    }

    // Đóng modal khi click ra ngoài vùng trắng
    window.onclick = function(event) {
        var modal = document.getElementById('addBannerModal');
        if (event.target == modal) {
            closeModal();
        }
    }
</script>