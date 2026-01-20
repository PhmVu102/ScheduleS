<style>
    /* --- STYLE CHUNG (Giống trang Đơn hàng) --- */
    .card {
        border: none;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        border-radius: 0.5rem;
    }

    .card-header {
        background-color: #fff;
        border-bottom: 1px solid #e3e6f0;
        font-weight: 700;
        color: #4e73df;
        /* Màu xanh chủ đạo */
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* --- TABLE HEADER --- */
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
    }

    /* --- HÌNH ẢNH SẢN PHẨM --- */
    .wishlist-img-box {
        width: 60px;
        height: 60px;
        border-radius: 6px;
        border: 1px solid #e3e6f0;
        padding: 2px;
        background: #fff;
        margin: 0 auto;
        overflow: hidden;
    }

    .wishlist-img-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.2s;
    }

    .wishlist-img-box:hover img {
        transform: scale(1.1);
    }

    /* --- NÚT XÓA (Style giống trang Order) --- */
    .btn-action-delete {
        background-color: #ffe3e3;
        color: #e74a3b;
        border: none;
        width: 35px;
        height: 35px;
        border-radius: 5px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-action-delete:hover {
        background-color: #e74a3b;
        color: #fff;
        box-shadow: 0 2px 5px rgba(231, 74, 59, 0.3);
    }

    /* --- TEXT STYLE --- */
    .text-price {
        color: #e74a3b;
        font-weight: 700;
    }

    .text-customer {
        color: #4e73df;
        font-weight: 600;
    }
    .text-center{
        text-align: center;
    }
</style>
<div class="container-fluid px-4">
    <h1 class="mt-4 mb-4 text-gray-800">Quản Lý Yêu Thích</h1>

    <div class="card mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-heart me-1"></i> Danh sách khách hàng quan tâm sản phẩm
            </h6>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th width="20%" class="text-start">Khách hàng</th>
                            <th width="10%">Hình ảnh</th>
                            <th width="30%" class="text-start">Sản phẩm</th>
                            <th width="15%">Giá hiện tại</th>
                            <th width="10%">Ngày thích</th>
                            <th width="10%">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($list_wishlist)): ?>
                            <?php foreach ($list_wishlist as $item): ?>

                                <?php
                                // --- LOGIC XỬ LÝ ẢNH (Giữ nguyên logic của bạn) ---
                                $thumb = $item['thumbnail'];
                                $img_src = '';

                                if (!empty($thumb)) {
                                    if (strpos($thumb, 'http') === 0) {
                                        $img_src = $thumb;
                                    } elseif (strpos($thumb, 'assets/') === 0) {
                                        $img_src = '../' . $thumb;
                                    } else {
                                        $img_src = '../assets/img/product/' . $thumb;
                                    }
                                } else {
                                    $img_src = 'https://via.placeholder.com/80?text=No+Img';
                                }
                                ?>

                                <tr>
                                    <td class="text-center font-weight-bold text-secondary">#<?= $item['id'] ?></td>

                                    <td>
                                        <div class="text-customer">
                                            <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($item['user_name']) ?>
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <div class="wishlist-img-box">
                                            <img src="<?= htmlspecialchars($img_src) ?>" alt="Product">
                                        </div>
                                    </td>

                                    <td>
                                        <div style="font-weight: 500; color: #333;">
                                            <?= htmlspecialchars($item['product_name']) ?>
                                        </div>
                                    </td>

                                    <td class="text-center text-price">
                                        <?= ($item['price']) ? number_format($item['price'], 0, ',', '.') . ' đ' : '<span class="badge bg-secondary">Liên hệ</span>' ?>
                                    </td>

                                    <td class="text-center small text-muted">
                                        <?= date('d/m/Y', strtotime($item['created_at'])) ?>
                                        <br>
                                        <?= date('H:i', strtotime($item['created_at'])) ?>
                                    </td>

                                    <td class="text-center">
                                        <a href="index.php?act=delete_wishlist&id=<?= $item['id'] ?>"
                                            class="btn-action-delete"
                                            title="Xóa khỏi danh sách"
                                            onclick="return confirm('Bạn chắc chắn muốn xóa mục này khỏi danh sách yêu thích?');">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="60" class="mb-3 opacity-50">
                                    <br>
                                    Chưa có dữ liệu sản phẩm yêu thích nào.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
