<style>
    /* --- KHUNG CARD & HEADER (Đồng bộ) --- */
    .card {
        border: none;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        border-radius: 0.5rem;
    }

    .card-header {
        background-color: #fff;
        border-bottom: 1px solid #e3e6f0;
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        /* Đẩy nút Thêm sang phải */
    }

    .header-title {
        color: #4e73df;
        font-weight: 700;
        margin: 0;
        font-size: 1rem;
    }

    /* --- NÚT THÊM MỚI --- */
    .btn-add-new {
        background-color: #4e73df;
        color: white;
        padding: 6px 15px;
        border-radius: 4px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        transition: 0.2s;
        border: none;
    }

    .btn-add-new:hover {
        background-color: #2e59d9;
        color: white;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    /* --- TABLE STYLE --- */
    .table thead th {
        background-color: #4e73df;
        color: #fff;
        text-transform: uppercase;
        font-size: 0.8rem;
        border-bottom: none;
        vertical-align: middle;
        text-align: center;
        padding: 12px;
        white-space: nowrap;
    }

    .table tbody td {
        vertical-align: middle;
        color: #5a5c69;
        font-size: 0.9rem;
        padding: 12px;
        text-align: center;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fc;
    }

    /* --- BADGES (NHÃN TRẠNG THÁI/VAI TRÒ) --- */
    .badge-custom {
        padding: 5px 10px;
        border-radius: 50rem;
        font-size: 0.75rem;
        font-weight: 700;
        display: inline-block;
        min-width: 80px;
        text-align: center;
    }

    /* Vai trò */
    .badge-role-admin {
        background-color: #f6c23e;
        color: #fff;
    }

    /* Vàng */
    .badge-role-user {
        background-color: #36b9cc;
        color: #fff;
    }

    /* Xanh ngọc */

    /* Trạng thái */
    .badge-status-active {
        background-color: #1cc88a;
        color: #fff;
    }

    /* Xanh lá */
    .badge-status-blocked {
        background-color: #e74a3b;
        color: #fff;
    }

    /* Đỏ */

    /* --- ACTION BUTTONS (NÚT THAO TÁC) --- */
    .btn-action {
        width: 32px;
        height: 32px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 2px;
        transition: 0.2s;
        text-decoration: none;
        border: none;
    }

    /* Sửa */
    .btn-edit {
        background: #eef2ff;
        color: #4e73df;
    }

    .btn-edit:hover {
        background: #4e73df;
        color: #fff;
    }

    /* Khóa */
    .btn-lock {
        background: #fff3cd;
        color: #f6c23e;
    }

    .btn-lock:hover {
        background: #f6c23e;
        color: #fff;
    }

    /* Mở khóa */
    .btn-unlock {
        background: #d4edda;
        color: #155724;
    }

    .btn-unlock:hover {
        background: #28a745;
        color: #fff;
    }

    /* Xóa */
    .btn-delete {
        background: #ffe3e3;
        color: #e74a3b;
    }

    .btn-delete:hover {
        background: #e74a3b;
        color: #fff;
    }

    /* Avatar nhỏ */
    .user-avatar-small {
        width: 30px;
        height: 30px;
        background: #eaecf4;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #858796;
        margin-right: 10px;
        font-size: 14px;
    }

    .container-fluid {
        padding: 0 30px;
    }

    .t-l div {
        text-align: left;
    }

    h1 {
        margin-bottom: 10px;
    }
    .d-flex{
        display: flex;
        align-items: center;
    }
</style>
<?php
$limit = 10;
$current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($current_page < 1) $current_page = 1;
$total_records = count($users);
$total_pages = ceil($total_records / $limit);
$offset = ($current_page - 1) * $limit;
$list_current = array_slice($users, $offset, $limit);
?>
<?php
// --- BƯỚC 1: LOGIC PHÂN TRANG (Đặt ngay đầu file view) ---

// 1. Cấu hình số lượng bản ghi trên 1 trang
$limit = 10;

// 2. Lấy trang hiện tại từ URL (ví dụ: admin.php?page=users&p=2)
// Nếu không có biến p thì mặc định là trang 1
$current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($current_page < 1) $current_page = 1;

// 3. Tính toán tổng số trang
$total_records = count($users); // Tổng số người dùng hiện có
$total_pages = ceil($total_records / $limit); // Làm tròn lên (ví dụ 11 người / 10 = 1.1 => 2 trang)

// 4. Tính vị trí cắt dữ liệu (Offset)
$offset = ($current_page - 1) * $limit;

// 5. Cắt mảng $users để chỉ lấy dữ liệu cho trang hiện tại
// Lưu ý: Nếu bạn xử lý LIMIT trong SQL rồi thì bỏ dòng này, gán $list_current = $users;
$list_current = array_slice($users, $offset, $limit);
?>

<div class="container-fluid px-4">
    <h1 class="mt-4 mb-4 text-gray-800">Quản Lý Người Dùng</h1>

    <div class="card mb-4">
        <div class="card-header">
            <h6 class="header-title">
                <i class="fas fa-users me-1"></i> Danh sách thành viên
                <span class="badge bg-secondary ms-2" style="font-size: 0.7rem;">Tổng: <?= $total_records ?></span>
            </h6>
            <a href="?page=formthem_user" class="btn-add-new">
                <i class="fas fa-plus me-1"></i> Thêm người dùng
            </a>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th width="20%" class="text-start">Thông tin chung</th>
                            <th width="20%" class="text-start">Liên hệ</th>
                            <th width="10%">Vai trò</th>
                            <th width="10%">Trạng thái</th>
                            <th width="15%">Ngày tạo</th>
                            <th width="20%">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($list_current)): ?>
                            <?php foreach ($list_current as $u) : ?>
                                <?php
                                $roleClass = (stripos($u['role_name'], 'admin') !== false) ? 'badge-role-admin' : 'badge-role-user';
                                $isActive = ($u['status'] == 1);
                                $statusClass = $isActive ? 'badge-status-active' : 'badge-status-blocked';
                                $statusText = $isActive ? 'Hoạt động' : 'Đã khóa';
                                ?>
                                <tr>
                                    <td class="text-center font-weight-bold">#<?= $u['id'] ?></td>
                                    <td class="t-l">
                                        <div class="d-flex">
                                            <div class="user-avatar-small"><i class="fas fa-user"></i></div>
                                            <div>
                                                <div style="font-weight: 600; color: #4e73df;">
                                                    <?= htmlspecialchars($u['fullname'] ?? 'No Name') ?>
                                                </div>
                                                <?php if (empty($u['fullname'])): ?>
                                                    <small class="text-muted"><?= htmlspecialchars($u['email']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="t-l">
                                        <div style="font-size: 0.9rem;">
                                            <i class="fas fa-envelope fa-xs text-muted me-1"></i> <?= htmlspecialchars($u['email']) ?>
                                        </div>
                                        <?php if (!empty($u['phone'])): ?>
                                            <div style="font-size: 0.85rem; margin-top: 3px;">
                                                <i class="fas fa-phone fa-xs text-muted me-1"></i> <?= htmlspecialchars($u['phone']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-custom <?= $roleClass ?>"><?= htmlspecialchars($u['role_name']) ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-custom <?= $statusClass ?>"><?= $statusText ?></span>
                                    </td>
                                    <td class="text-center small text-muted">
                                        <?= date('d/m/Y', strtotime($u['created_at'])) ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="admin.php?page=sua_user&id=<?= $u['id'] ?>" class="btn-action btn-edit"><i class="fas fa-edit"></i></a>
                                        <?php if ($isActive) : ?>
                                            <a href="admin.php?page=khoa_user&id=<?= $u['id'] ?>" class="btn-action btn-lock" onclick="return confirm('Khóa tài khoản?');"><i class="fas fa-lock"></i></a>
                                        <?php else : ?>
                                            <a href="admin.php?page=mo_khoa_user&id=<?= $u['id'] ?>" class="btn-action btn-unlock"><i class="fas fa-unlock"></i></a>
                                        <?php endif; ?>
                                        <a href="admin.php?page=xoa&id=<?= $u['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Xóa vĩnh viễn?');"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">Không có dữ liệu.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="pagination-container">
                <nav aria-label="Page navigation">
                    <ul class="pagination">

                        <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=user_management&p=<?= $current_page - 1 ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                                <a class="page-link" href="?page=user_management&p=<?= $i ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=user_management&p=<?= $current_page + 1 ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>

                    </ul>
                </nav>
            </div>
        <?php endif; ?>

    </div>
</div>