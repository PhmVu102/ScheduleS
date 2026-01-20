<div class="container">
    <div class="h1_giohang">Giỏ hàng của bạn</div>

    <div class="cart-layout">
        <div class="cart-products">
            <?php if (empty($cart)): ?>
                <div style="padding: 40px; text-align: center;">
                    <img src="assets/img/icon/empty-cart.jpg" alt="Empty">
                    <p style="margin-top: 20px; color: #777;">Giỏ hàng của bạn đang trống!</p>
                    <a href="?page=products" style="color: #3498db; font-weight: bold; text-decoration: none;">Mua sắm ngay</a>
                </div>
            <?php else: ?>
                <?php foreach ($cart as $key => $item): ?>
                    <div class="cart-product-item">
                        
                        <img src="<?= $item['image'] ?>" alt="<?= htmlspecialchars($item['name']) ?>">

                        <div class="cart-product-details">
                            <h3 class="cart-product-name">
                                <a href="index.php?page=detail&id=<?= $item['id'] ?>">
                                    <?= htmlspecialchars($item['name']) ?>
                                </a>
                            </h3>

                            <?php if (!empty($item['variant_text'])): ?>
                                <div class="cart-product-variant">
                                    Phân loại: <span><?= htmlspecialchars($item['variant_text']) ?></span>
                                </div>
                            <?php endif; ?>

                            <div class="cart-product-price"><?= number_format($item['price'], 0, ',', '.') ?>₫</div>
                        </div>

                        <div class="quantity">
                            <button onclick="window.location.href='index.php?page=cart&action=update&type=dec&id=<?= $key ?>'">-</button>
                            <input type="text" value="<?= $item['quantity'] ?>" readonly>
                            <button onclick="window.location.href='index.php?page=cart&action=update&type=inc&id=<?= $key ?>'">+</button>
                        </div>

                        <div class="remove-btn" onclick="if(confirm('Bạn có chắc muốn xóa?')) window.location.href='index.php?page=cart&action=delete&id=<?= $key ?>'">
                            <i class="fas fa-trash-alt"></i>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="cart-sidebar">
            <div class="payment-card">
                <div class="coupon-box">
                    <h3>Nhập mã giảm giá</h3>
                    <div class="coupon-input">
                        <input type="text" id="couponInput" placeholder="FLASH20">
                        <button onclick="applyCoupon()">Áp dụng</button>
                    </div>
                    <div id="couponMessage" style="margin-top:12px; font-weight:bold; font-size: 14px;"></div>
                </div>

                <div class="summary">
                    <div class="summary-row">
                        <span>Tạm tính (<?= $total_items ?> sản phẩm):</span>
                        <span id="subtotal" data-price="<?= $total_price ?>"><?= number_format($total_price, 0, ',', '.') ?>₫</span>
                    </div>

                    <div id="discountRow" class="summary-row discount-row" style="display:none;">
                        <span>Giảm giá (<span id="couponCodeDisplay"></span>):</span>
                        <span>-<span id="discountAmount">0₫</span></span>
                    </div>

                    <div class="summary-row">
                        <span>Phí vận chuyển:</span>
                        <span>Miễn phí</span>
                    </div>

                    <div class="summary-row total">
                        <span>Tổng thanh toán:</span>
                        <span id="finalTotal"><?= number_format($total_price, 0, ',', '.') ?>₫</span>
                    </div>

                    <?php if (!empty($cart)): ?>
                        <button class="checkout-btn" onclick="window.location.href='index.php?page=payment'">
                            TIẾN HÀNH THANH TOÁN
                        </button>
                    <?php else: ?>
                        <button class="checkout-btn" style="background: #ccc; cursor: not-allowed;" disabled>
                            TIẾN HÀNH THANH TOÁN
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="continue-shopping">
        <a href="index.php?page=products">Tiếp tục mua sắm</a>
    </div>
</div>

<script>
    function applyCoupon() {
        const input = document.getElementById('couponInput').value.trim();
        const msg = document.getElementById('couponMessage');
        const subtotalEl = document.getElementById('subtotal');
        const originalPrice = parseInt(subtotalEl.getAttribute('data-price'));

        if (!input) {
            msg.style.color = 'red';
            msg.innerText = "Vui lòng nhập mã!";
            return;
        }

        const formData = new FormData();
        formData.append('code', input);
        formData.append('total_amount', originalPrice);

        fetch('index.php?page=check_coupon', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    msg.style.color = '#27ae60';
                    msg.innerText = data.message;
                    document.getElementById('discountRow').style.display = 'flex';
                    document.getElementById('couponCodeDisplay').innerText = data.code;
                    document.getElementById('discountAmount').innerText = formatMoney(data.discount);
                    let finalPrice = originalPrice - data.discount;
                    if (finalPrice < 0) finalPrice = 0;
                    document.getElementById('finalTotal').innerText = formatMoney(finalPrice);
                } else {
                    msg.style.color = '#e74c3c';
                    msg.innerText = data.message;
                    document.getElementById('discountRow').style.display = 'none';
                    document.getElementById('finalTotal').innerText = formatMoney(originalPrice);
                }
            })
            .catch(error => {
                console.error('Lỗi:', error);
                msg.innerText = "Có lỗi xảy ra, vui lòng thử lại!";
            });
    }

    function formatMoney(amount) {
        return parseInt(amount).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + '₫';
    }
</script>