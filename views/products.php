<div class="products-wrapper">
    <header class="products-header">
        <h1 class="products-title" style="font-family: tahoma;">
            <?= $category === 'other'
                ? 'Nhỏ gọn từng chi tiết – Chất lượng vượt mong đợi'
                : ($category === 'phone'
                    ? 'Điện Thoại Chính Hãng – Công Nghệ Dẫn Đầu'
                    : 'Khám Phá Thế Giới Sản Phẩm Đa Dạng')
            ?>
        </h1>
        <p class="products-subtitle">
            Cập nhật xu hướng mới nhất • Giá tốt • Chất lượng đảm bảo • 2026
        </p>
    </header>


    <nav class="category-nav">
        <a href="<?= $urlHelper(['category' => null]) ?>" class="cat-tab <?= !$category ? 'active' : '' ?>">Tất cả</a>
        <a href="<?= $urlHelper(['category' => 'phone']) ?>" class="cat-tab <?= $category === 'phone' ? 'active' : '' ?>">Điện thoại</a>
        <a href="<?= $urlHelper(['category' => 'other']) ?>" class="cat-tab <?= $category === 'other' ? 'active' : '' ?>">Khác</a>
    </nav>

    <div class="filter-summary">
        <span class="result-text">
            Tìm thấy <strong><?= count($products) ?></strong> sản phẩm
            <?= $hasFilter ? ' <span class="filtered-note">(Đã áp dụng bộ lọc)</span>' : '' ?>
        </span>
        <?php if ($hasFilter): ?>
            <a href="<?= $urlHelper(['brand' => '', 'price' => '']) ?>" class="clear-filter-btn">Xóa bộ lọc</a>
        <?php endif; ?>
    </div>

    <form method="GET" class="filter-form">
        <input type="hidden" name="page" value="products">
        <?php if ($category): ?>
            <input type="hidden" name="category" value="<?= $category ?>">
        <?php endif; ?>
        <?php if (!empty($keyword)): ?>
            <input type="hidden" name="keyword" value="<?= htmlspecialchars($keyword) ?>">
        <?php endif; ?>

        <select name="brand" onchange="this.form.submit()">
            <option value="">Tất cả hãng</option>
            <?php if (!empty($brandList)): ?>
                <?php foreach ($brandList as $b): ?>
                    <option value="<?= htmlspecialchars($b['name']) ?>"
                        <?= (isset($brand) && $brand === $b['name']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($b['name']) ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>

        <select name="price" onchange="this.form.submit()">
            <option value="">Khoảng giá</option>
            <option value="under15" <?= $price === 'under15' ? 'selected' : '' ?>>Dưới 15 triệu</option>
            <option value="15-25" <?= $price === '15-25' ? 'selected' : '' ?>>15 - 25 triệu</option>
            <option value="25-35" <?= $price === '25-35' ? 'selected' : '' ?>>25 - 35 triệu</option>
            <option value="over35" <?= $price === 'over35' ? 'selected' : '' ?>>Trên 35 triệu</option>
        </select>
    </form>
    <?php if (!empty($products) && isset($products[0]['suggestion_new'])): ?>
        <div class="ai-suggestion-alert">
            <div class="icon-box">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
            </div>
            <div class="content-box">
                <p>
                    Không tìm thấy kết quả chính xác cho:
                    <span class="original-keyword">"<?= htmlspecialchars($products[0]['suggestion_original'] ?? $_GET['keyword']) ?>"</span>
                </p>
                <p>
                    Hiển thị kết quả cho:
                    <span class="new-keyword">"<?= htmlspecialchars($products[0]['suggestion_new']) ?>"</span>
                </p>
            </div>
        </div>
    <?php endif; ?>

    <div class="products-grid">
        <?php if (empty($products)): ?>
            <div class="no-result">
                Không tìm thấy sản phẩm nào phù hợp<br><br>
                <a href="index.php">Xem tất cả sản phẩm</a>
            </div>
            <?php else: foreach ($products as $p): ?>
                <article class="product-item">
                    <?php
                    // Kiểm tra xem sản phẩm này có trong danh sách đã like không
                    $isLiked = in_array($p['id'], $wishlist_ids ?? []);
                    $heartClass = $isLiked ? 'fa-solid' : 'fa-regular';
                    $heartColor = $isLiked ? 'active' : '';
                    ?>
                    <button class="btn-wishlist <?= $heartColor ?>" onclick="toggleWishlist(<?= $p['id'] ?>, this)">
                        <i class="<?= $heartClass ?> fa-heart"></i>
                    </button>
                    <?php
                    // 1. Logic tính toán giảm giá
                    $is_sale = false;
                    $percent_off = 0;

                    // Nếu có giá sale VÀ giá sale nhỏ hơn giá gốc
                    if (!empty($p['price_sale']) && $p['price_sale'] < $p['price']) {
                        $is_sale = true;
                        // Tính % giảm: (Gốc - Sale) / Gốc * 100
                        $percent_off = round((($p['price'] - $p['price_sale']) / $p['price']) * 100);
                    }
                    ?>

                    <div class="badges">
                        <?php if ($p['is_featured']): ?>
                            <span class="tag hot">Hot</span>
                        <?php endif; ?>

                        <?php if ($is_sale): ?>
                            <span class="tag sale">-<?= $percent_off ?>%</span>
                        <?php endif; ?>
                    </div>

                    <?php $img = $p['variant_image'] ? $p['variant_image'] : $p['thumbnail']; ?>
                    <div class="product-thumb">
                        <img src="<?= $img ? $img : 'https://via.placeholder.com/500x400?text=' . urlencode($p['name']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                    </div>

                    <div class="product-body">
                        <span class="product-cat">
                            <?= $p['type'] === 'laptop' ? 'Laptop' : ($p['type'] === 'phone' ? 'Điện thoại' : 'Khác') ?> • <?= $p['brand_name'] ?>
                        </span>

                        <h3 class="product-name">
                            <a href="index.php?page=product_details&id=<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></a>
                        </h3>

                        <div class="product-price-box">
                            <?php if ($is_sale): ?>
                                <span class="price-current"><?= number_format($p['price_sale'], 0, ',', '.') ?>₫</span>
                                <span class="price-old"><?= number_format($p['price'], 0, ',', '.') ?>₫</span>
                            <?php else: ?>
                                <span class="price-current"><?= number_format($p['price'], 0, ',', '.') ?>₫</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-spec">
                            <?php
                            $specs = []; // Mảng chứa thông số để hiển thị
                            // 1. Xử lý cho LAPTOP
                            if ($p['type'] === 'laptop') {
                                if (!empty($p['cpu'])) $specs[] = strtoupper($p['cpu']); // VD: CORE I5
                                if (!empty($p['ram'])) $specs[] = $p['ram']; // VD: 8GB
                                if (!empty($p['lap_screen'])) $specs[] = $p['lap_screen']; // VD: 15.6 inch
                            }
                            // 2. Xử lý cho ĐIỆN THOẠI
                            elseif ($p['type'] === 'phone') {
                                if (!empty($p['chipset'])) $specs[] = $p['chipset']; // VD: Snapdragon 8 Gen 3
                                if (!empty($p['ram'])) $specs[] = $p['ram']; // VD: 8GB
                                if (!empty($p['phone_screen'])) $specs[] = $p['phone_screen']; // VD: 6.7 inch
                            }
                            // 3. Xử lý cho ĐỒ ĂN VẶT / KHÁC (Type = other)
                            else {
                                // Sản phẩm khác không có thông số kỹ thuật, bỏ qua để xuống phần else hiển thị mô tả
                            }
                            // HIỂN THỊ RA MÀN HÌNH
                            if (!empty($specs)):
                                echo implode(' • ', $specs);
                            else:
                                // Nếu không có thông số (hoặc là đồ ăn vặt), hiển thị mô tả ngắn
                                echo !empty($p['description'])
                                    ? htmlspecialchars(mb_substr(strip_tags($p['description']), 0, 50)) . '...'
                                    : '';
                            endif;
                            ?>
                        </div>
                        <a href="index.php?page=product_details&id=<?= $p['id'] ?>" class="btn-view">Xem chi tiết</a>
                    </div>
                </article>
        <?php endforeach;
        endif; ?>
    </div>
</div>
<?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="<?= $urlHelper(['p' => $page - 1]) ?>" class="page-link"><i class="fa-solid fa-angles-left"></i></a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="<?= $urlHelper(['p' => $i]) ?>"
                class="page-link <?= ($i == $page) ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="<?= $urlHelper(['p' => $page + 1]) ?>" class="page-link"><i class="fa-solid fa-angles-right"></i></a>
        <?php endif; ?>
    </div>
<?php endif; ?>
<script>
    function toggleWishlist(productId, btn) {
        // Tạo FormData
        const formData = new FormData();
        formData.append("product_id", productId);

        fetch("index.php?page=toggle_wishlist", {
                method: "POST",
                body: formData,
            })
            .then((res) => res.json())
            .then((data) => {
                if (data.status === "login_required") {
                    if (confirm(data.message)) {
                        window.location.href = "index.php?page=login";
                    }
                } else if (data.status === "success") {
                    const icon = btn.querySelector("i");

                    if (data.action === "added") {
                        // Đổi sang tim đặc (Đã like)
                        btn.classList.add("active");
                        icon.classList.remove("fa-regular");
                        icon.classList.add("fa-solid");
                    } else {
                        // Đổi sang tim rỗng (Bỏ like)
                        btn.classList.remove("active");
                        icon.classList.remove("fa-solid");
                        icon.classList.add("fa-regular");
                    }
                } else {
                    alert("Lỗi: " + data.message);
                }
            })
            .catch((err) => console.error(err));
    }
</script>