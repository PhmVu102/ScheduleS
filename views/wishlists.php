<div class="container" style="margin-top: 30px; min-height: 400px; margin-bottom: 30px;">
    <h1 style="text-align: center; margin-bottom: 30px; color: #2c3e50;">Sản phẩm yêu thích ❤️</h1>

    <?php if (empty($products)): ?>
        <div style="text-align: center; padding: 50px; color: #777;">
            <i class="far fa-heart" style="font-size: 60px; margin-bottom: 20px; color: #ccc;"></i>
            <p>Bạn chưa yêu thích sản phẩm nào.</p>
            <a href="index.php?page=products" style="display: inline-block; margin-top: 10px; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">Dạo một vòng xem sao</a>
        </div>
    <?php else: ?>
        
        <div class="products-grid">
            <?php foreach ($products as $p): ?>
                <article class="product-item">
                    
                    <button class="btn-wishlist active" onclick="removeWishlist(<?= $p['id'] ?>, this)">
                        <i class="fa-solid fa-heart"></i>
                    </button>

                    <?php 
                        $is_sale = false; $percent_off = 0;
                        if (!empty($p['price_sale']) && $p['price_sale'] < $p['price']) {
                            $is_sale = true;
                            $percent_off = round((($p['price'] - $p['price_sale']) / $p['price']) * 100);
                        }
                    ?>

                    <div class="badges">
                        <?php if ($is_sale): ?>
                            <span class="tag sale">-<?= $percent_off ?>%</span>
                        <?php endif; ?>
                    </div>

                    <?php $img = $p['variant_image'] ? $p['variant_image'] : $p['thumbnail']; ?>
                    <div class="product-thumb">
                        <img src="<?= $img ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                    </div>

                    <div class="product-body">
                        <span class="product-cat"><?= $p['type'] === 'laptop' ? 'Laptop' : 'Điện thoại' ?> • <?= $p['brand_name'] ?></span>
                        
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
                        
                        <a href="index.php?page=product_details&id=<?= $p['id'] ?>" class="btn-view">Xem chi tiết</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    /* Nút tim ở trang wishlist luôn đỏ (active) */
    .btn-wishlist {
        position: absolute; top: 10px; right: 10px; z-index: 10;
        background: rgba(255,255,255,0.9); border: none; width: 35px; height: 35px;
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        cursor: pointer; font-size: 18px; color: #ff4d4d;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1); transition: 0.3s;
    }
    .btn-wishlist:hover { transform: scale(1.1); }
</style>
<script>
    // Hàm xóa khỏi wishlist (Sử dụng lại API toggle_wishlist)
    function removeWishlist(productId, btn) {
        if(!confirm('Bỏ sản phẩm này khỏi danh sách yêu thích?')) return;

        const formData = new FormData();
        formData.append('product_id', productId);

        fetch('index.php?page=toggle_wishlist', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                // Xóa phần tử khỏi giao diện ngay lập tức
                const item = btn.closest('.product-item');
                item.style.transition = '0.3s';
                item.style.opacity = '0';
                setTimeout(() => item.remove(), 300);
                
                // Nếu xóa hết thì reload để hiện thông báo trống
                const grid = document.querySelector('.products-grid');
                if(grid.children.length <= 1) {
                    setTimeout(() => location.reload(), 300);
                }
            } else {
                alert(data.message);
            }
        });
    }
</script>