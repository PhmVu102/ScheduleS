<style>
    /* Thêm CSS cho trạng thái hết hàng */
    .btn-buy:disabled {
        background-color: #ccc !important;
        cursor: not-allowed;
        color: #666;
    }

    .stock-status {
        margin-bottom: 10px;
        font-weight: 600;
    }

    .text-green {
        color: #28a745;
    }

    .text-red {
        color: #dc3545;
    }
</style>

<div class="pd-container">
    <div class="pd-left">
        <div class="pd-image">
            <?php $img = $product['thumbnail'] ? $product['thumbnail'] : 'https://via.placeholder.com/600x600'; ?>
            <img src="<?= $img ?>" alt="<?= htmlspecialchars($product['name']) ?>">
        </div>
    </div>

    <div class="pd-right">
        <h1 class="pd-title"><?= htmlspecialchars($product['name']) ?></h1>
        <div class="pd-brand">Thương hiệu: <strong><?= $product['brand_name'] ?></strong> | Loại: <?= ucfirst($product['type']) ?></div>

        <div class="pd-price-box">
            <?php
            // Lấy biến thể đầu tiên để hiển thị mặc định
            $first_variant = $product['variants'][0] ?? null;
            $p_price = $first_variant ? $first_variant['price'] : 0;
            $p_sale  = $first_variant ? $first_variant['price_sale'] : 0;
            $p_stock = $first_variant ? $first_variant['stock'] : 0; // Lấy tồn kho
            ?>

            <?php if ($p_sale > 0 && $p_sale < $p_price): ?>
                <span class="pd-price-current"><?= number_format($p_sale, 0, ',', '.') ?>₫</span>
                <span class="pd-price-old"><?= number_format($p_price, 0, ',', '.') ?>₫</span>
                <span class="pd-sale-tag">Giảm <?= round((($p_price - $p_sale) / $p_price) * 100) ?>%</span>
            <?php else: ?>
                <span class="pd-price-current"><?= number_format($p_price, 0, ',', '.') ?>₫</span>
            <?php endif; ?>
        </div>

        <div class="stock-status">
            Trạng thái:
            <span id="stock-label" class="<?= $p_stock > 0 ? 'text-green' : 'text-red' ?>">
                <?= $p_stock > 0 ? 'Còn hàng (' . $p_stock . ' sản phẩm)' : 'Hết hàng' ?>
            </span>
        </div>

        <div class="pd-variants">
            <label>Chọn phiên bản:</label>
            <?php foreach ($product['variants'] as $index => $v): ?>
                <?php
                $v_img = !empty($v['image']) ? $v['image'] : $product['thumbnail'];
                // [QUAN TRỌNG] Truyền thêm v['stock'] và v['id'] vào hàm JS
                ?>
                <button class="variant-btn <?= $index === 0 ? 'active' : '' ?>"
                    onclick="updateVariant(
                        <?= $v['id'] ?>, 
                        <?= $v['price'] ?>, 
                        <?= $v['price_sale'] ?>, 
                        '<?= $v_img ?>', 
                        <?= $v['stock'] ?>, 
                        this
                    )">
                    <?= $v['ram'] ?> - <?= $v['rom'] ?> - <?= $v['color'] ?>
                </button>
            <?php endforeach; ?>
        </div>

        <form action="index.php?page=cart&action=add" method="POST" id="add-to-cart-form">
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
            <input type="hidden" name="variant_id" id="selected_variant" value="<?= $product['variants'][0]['id'] ?? '' ?>">

            <?php if ($p_stock > 0): ?>
                <button type="submit" class="btn-buy" id="btn-buy-submit">THÊM VÀO GIỎ HÀNG</button>
            <?php else: ?>
                <button type="button" class="btn-buy" id="btn-buy-submit" disabled>TẠM HẾT HÀNG</button>
            <?php endif; ?>
        </form>

        <?php
        // Kiểm tra xem có dữ liệu specs không và loại sản phẩm có hợp lệ không trước khi hiển thị
        if (isset($product['specs']) && !empty($product['specs']) && ($product['type'] == 'laptop' || $product['type'] == 'phone')):
        ?>
            <h3>Thông số kỹ thuật</h3>
            <table class="pd-specs-table">
                <?php if ($product['type'] == 'laptop'): ?>
                    <tr>
                        <th>CPU</th>
                        <td><?= $product['specs']['cpu'] ?></td>
                    </tr>
                    <tr>
                        <th>RAM</th>
                        <td><?= $product['specs']['ram'] ?></td>
                    </tr>
                    <tr>
                        <th>Ổ cứng</th>
                        <td><?= $product['specs']['storage'] ?></td>
                    </tr>
                    <tr>
                        <th>Màn hình</th>
                        <td><?= $product['specs']['screen'] ?></td>
                    </tr>
                    <tr>
                        <th>Card đồ họa</th>
                        <td><?= $product['specs']['gpu'] ?></td>
                    </tr>
                    <tr>
                        <th>Pin</th>
                        <td><?= $product['specs']['battery'] ?></td>
                    </tr>
                <?php elseif ($product['type'] == 'phone'): ?>
                    <tr>
                        <th>Chipset</th>
                        <td><?= $product['specs']['chipset'] ?></td>
                    </tr>
                    <tr>
                        <th>RAM</th>
                        <td><?= $product['specs']['ram'] ?></td>
                    </tr>
                    <tr>
                        <th>Bộ nhớ trong</th>
                        <td><?= $product['specs']['rom'] ?></td>
                    </tr>
                    <tr>
                        <th>Màn hình</th>
                        <td><?= $product['specs']['screen'] ?></td>
                    </tr>
                    <tr>
                        <th>Camera</th>
                        <td><?= $product['specs']['camera'] ?></td>
                    </tr>
                    <tr>
                        <th>Pin</th>
                        <td><?= $product['specs']['battery'] ?></td>
                    </tr>
                <?php endif; ?>
            </table>
        <?php endif; ?>

        <div style="margin-top: 20px;">
            <h3>Mô tả sản phẩm</h3>
            <div class="pd-desc">
                <?= nl2br(htmlspecialchars($product['description'] ?? 'Đang cập nhật...')) ?>
            </div>
        </div>
    </div>
</div>
<div class="pd-container" style="display: block; margin-top: 50px;">
    <h2 class="section-title">Sản phẩm liên quan</h2>
    <div class="related-grid">
        <?php if (empty($related_products)): ?>
            <p>Không có sản phẩm liên quan.</p>
            <?php else: foreach ($related_products as $rp): ?>
                <div class="related-item">
                    <a href="index.php?page=product_details&id=<?= $rp['id'] ?>">
                        <img src="<?= $rp['thumbnail'] ?>" alt="<?= htmlspecialchars($rp['name']) ?>">
                        <h4><?= htmlspecialchars($rp['name']) ?></h4>
                        <div class="related-price">
                            <?= number_format($rp['price'], 0, ',', '.') ?>₫
                        </div>
                    </a>
                </div>
        <?php endforeach;
        endif; ?>
    </div>
</div>

<div id="reviews" class="pd-container" style="display: block; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
    <h2 class="section-title">Đánh giá sản phẩm</h2>

    <div class="review-form-box">
        <?php if (isset($_SESSION['user'])): ?>
            <form action="index.php?page=post_review" method="POST">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <div class="form-group">
                    <label>Đánh giá của bạn:</label>
                    <select name="rating" required style="padding: 5px; border-radius: 4px;">
                        <option value="5">⭐⭐⭐⭐⭐ (Tuyệt vời)</option>
                        <option value="4">⭐⭐⭐⭐ (Tốt)</option>
                        <option value="3">⭐⭐⭐ (Bình thường)</option>
                        <option value="2">⭐⭐ (Tệ)</option>
                        <option value="1">⭐ (Rất tệ)</option>
                    </select>
                </div>
                <div class="form-group" style="margin-top: 10px;">
                    <textarea name="comment" rows="3" placeholder="Nhập nội dung đánh giá..." required style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ccc;"></textarea>
                </div>
                <button type="submit" class="btn-review" style="margin-top:10px; padding:8px 20px; background:#d70018; color:white; border:none; border-radius:4px;">Gửi đánh giá</button>
            </form>
        <?php else: ?>
            <p>Vui lòng <a href="index.php?page=login" style="color: #d70018;">đăng nhập</a> để viết đánh giá.</p>
        <?php endif; ?>
    </div>

    <div class="review-list" style="margin-top: 30px;">
        <?php
        // 1. Lọc ra các comment cha (parent_id == NULL)
        $parents = array_filter($reviews, function ($r) {
            return empty($r['parent_id']);
        });
        ?>

        <?php if (empty($parents)): ?>
            <p style="color: #666; font-style: italic;">Chưa có đánh giá nào.</p>
        <?php else: ?>
            <?php foreach ($parents as $rv): ?>
                <div class="review-item">

                    <div class="review-header">
                        <div>
                            <span class="review-name"><?= htmlspecialchars($rv['fullname']) ?></span>
                            <?php if ($rv['role'] == 1): ?>
                                <span class="badge-staff" style="background:#2ecc71; color:#fff; padding:2px 6px; border-radius:4px; font-size:11px; margin-left:5px;">Quản trị viên</span>
                            <?php endif; ?>
                        </div>
                        <span class="review-date">
                            <?= date('H:i - d/m/Y', strtotime($rv['created_at'])) ?>
                        </span>
                    </div>

                    <div class="review-stars">
                        <?php for ($i = 1; $i <= 5; $i++) echo ($i <= $rv['rating']) ? '★' : '<span style="color:#ccc">★</span>'; ?>
                    </div>

                    <p class="review-content"><?= nl2br(htmlspecialchars($rv['comment'])) ?></p>

                    <?php if (isset($_SESSION['user'])): ?>
                        <button type="button" class="btn-reply-trigger" onclick="toggleReplyForm(<?= $rv['id'] ?>)">
                            <i class="fas fa-reply"></i> Trả lời
                        </button>
                    <?php endif; ?>

                    <?php
                    // 1. Lọc các comment con
                    $replies = array_filter($reviews, function ($sub) use ($rv) {
                        return $sub['parent_id'] == $rv['id'];
                    });

                    // 2. [MỚI] Sắp xếp comment con: Cũ nhất lên trước (ASC) để đọc xuôi theo thời gian
                    usort($replies, function ($a, $b) {
                        return strtotime($a['created_at']) - strtotime($b['created_at']);
                    });
                    ?>

                    <?php foreach ($replies as $rep): ?>
                        <?php
                        $isAdminReply = ($rep['role'] == 1);
                        $borderColor = $isAdminReply ? '#2ecc71' : '#ddd';
                        $bgColor = $isAdminReply ? '#f9fdfa' : '#f9f9f9';
                        ?>
                        <div class="reply-box" style="margin-left: 40px; background: <?= $bgColor ?>; padding: 10px 15px; border-radius: 8px; margin-top: 10px; border-left: 3px solid <?= $borderColor ?>;">
                            <div class="review-header">
                                <div>
                                    <span class="review-name"><?= htmlspecialchars($rep['fullname']) ?></span>
                                    <?php if ($isAdminReply): ?>
                                        <span class="badge-staff" style="background-color: #2ecc71; color: white; font-size: 11px; padding: 2px 6px; border-radius: 4px; margin-left: 5px;">
                                            <i class="fas fa-check-circle"></i> Nhân viên
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <span class="review-date">
                                    <?= date('H:i - d/m/Y', strtotime($rep['created_at'])) ?>
                                </span>
                            </div>
                            <p class="review-content" style="margin-bottom:0; color:#333;"><?= nl2br(htmlspecialchars($rep['comment'])) ?></p>
                        </div>
                    <?php endforeach; ?>

                    <?php if (isset($_SESSION['user'])): ?>
                        <div id="reply-form-<?= $rv['id'] ?>" class="reply-form-container" style="display: none; margin-left: 40px; margin-top: 10px;">
                            <form action="index.php?page=post_review" method="POST">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <input type="hidden" name="parent_id" value="<?= $rv['id'] ?>">
                                <input type="hidden" name="rating" value="5">

                                <div style="display: flex; gap: 10px;">
                                    <div style="flex: 1;">
                                        <textarea name="comment" class="reply-input" rows="2"
                                            placeholder="Nhập câu trả lời..." required
                                            style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"></textarea>

                                        <button type="submit" class="btn-submit-reply"
                                            style="margin-top: 5px; background: #3498db; color: white; border: none; padding: 5px 15px; border-radius: 4px; cursor: pointer;">
                                            Gửi trả lời
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>

                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<script>
    function toggleReplyForm(id) {
        var form = document.getElementById('reply-form-' + id);
        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
            // Tự động focus vào ô nhập
            form.querySelector('textarea').focus();
        } else {
            form.style.display = 'none';
        }
    }

    function updateVariant(id, price, priceSale, image, stock, btn) {

        // 1. Cập nhật giao diện nút bấm (Active class)
        document.querySelectorAll('.variant-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        // 2. Cập nhật ID biến thể vào Form (Để khi submit biết mua cái nào)
        document.getElementById('selected_variant').value = id;

        // 3. Cập nhật hình ảnh
        const mainImage = document.querySelector('.pd-image img');
        if (mainImage && image) {
            mainImage.src = image;
        }

        // 4. Cập nhật Giá tiền
        const format = (n) => n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + '₫';
        const priceBox = document.querySelector('.pd-price-box');

        if (priceSale > 0 && priceSale < price) {
            const percent = Math.round(((price - priceSale) / price) * 100);
            priceBox.innerHTML = `
                <span class="pd-price-current">${format(priceSale)}</span>
                <span class="pd-price-old">${format(price)}</span>
                <span class="pd-sale-tag">Giảm ${percent}%</span>
            `;
        } else {
            priceBox.innerHTML = `<span class="pd-price-current">${format(price)}</span>`;
        }

        // 5. [QUAN TRỌNG] Kiểm tra Tồn kho & Cập nhật nút Mua
        const btnBuy = document.getElementById('btn-buy-submit');
        const stockLabel = document.getElementById('stock-label');

        if (stock > 0) {
            // Còn hàng
            btnBuy.disabled = false;
            btnBuy.innerHTML = 'THÊM VÀO GIỎ HÀNG';
            btnBuy.type = 'submit'; // Đảm bảo nút submit hoạt động

            stockLabel.innerHTML = `Còn hàng (${stock} sản phẩm)`;
            stockLabel.className = 'text-green';
        } else {
            // Hết hàng
            btnBuy.disabled = true;
            btnBuy.innerHTML = 'TẠM HẾT HÀNG';
            btnBuy.type = 'button'; // Chặn submit

            stockLabel.innerHTML = 'Hết hàng';
            stockLabel.className = 'text-red';
        }
    }

    function updatePrice(price, priceSale, image, btn) {
        // 1. Xử lý giao diện nút bấm
        document.querySelectorAll('.variant-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        // 2. Xử lý đổi hình ảnh
        // Chọn thẻ img nằm trong class .pd-image
        const mainImage = document.querySelector('.pd-image img');
        if (mainImage && image) {
            mainImage.src = image;
        }

        // 3. Xử lý giá tiền (Format tiền tệ)
        const format = (n) => n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + '₫';
        const priceBox = document.querySelector('.pd-price-box');

        if (priceSale > 0 && priceSale < price) {
            const percent = Math.round(((price - priceSale) / price) * 100);
            priceBox.innerHTML = `
            <span class="pd-price-current">${format(priceSale)}</span>
            <span class="pd-price-old">${format(price)}</span>
            <span class="pd-sale-tag">Giảm ${percent}%</span>
        `;
        } else {
            priceBox.innerHTML = `<span class="pd-price-current">${format(price)}</span>`;
        }

        // 4. (Tùy chọn) Cập nhật giá trị vào input hidden form thêm giỏ hàng nếu cần
        // Nếu logic thêm giỏ hàng của bạn cần ID biến thể, bạn có thể truyền thêm variant_id vào hàm này
    }
</script>