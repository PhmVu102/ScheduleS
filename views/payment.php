<div class="pm-wrapper">
    <h1 class="pm-title">Thanh toán đơn hàng</h1>

    <div class="pm-layout">

        <div class="pm-form-card">
            <h2 class="pm-section-title">Thông tin giao hàng</h2>
            <form id="pmCheckoutForm" action="index.php?page=process_order" method="POST">

                <?php $saved_addresses = isset($saved_addresses) ? $saved_addresses : []; ?>

                <?php if (!empty($saved_addresses)): ?>
                    <div class="pm-form-group">
                        <label class="pm-label">Chọn địa chỉ nhận hàng:</label>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <?php foreach ($saved_addresses as $addr): ?>
                                <label class="pm-method-item" onclick="toggleAddressForm(false)" style="padding: 10px;">
                                    <input type="radio" name="selected_address_id" value="<?= $addr['id'] ?>"
                                        <?= $addr['is_default'] ? 'checked' : '' ?> style="margin-right: 10px;">
                                    <div style="font-size: 14px;">
                                        <b><?= htmlspecialchars($addr['recipient_name']) ?> (<?= htmlspecialchars($addr['phone']) ?>)</b><br>
                                        <?= htmlspecialchars($addr['address']) ?>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                            <label class="pm-method-item" onclick="toggleAddressForm(true)" style="padding: 10px;">
                                <input type="radio" name="selected_address_id" value="new" style="margin-right: 10px;">
                                <b>+ Giao đến địa chỉ khác</b>
                            </label>
                        </div>
                    </div>
                <?php endif; ?>

                <div id="newAddressForm" style="<?= !empty($saved_addresses) ? 'display:none;' : '' ?>">
                    <div class="pm-form-group">
                        <label class="pm-label">Họ và tên người nhận *</label>
                        <input type="text" name="fullname" class="pm-input" placeholder="Ví dụ: Nguyễn Văn A">
                    </div>

                    <div class="pm-form-group">
                        <label class="pm-label">Số điện thoại liên hệ *</label>
                        <input type="number" name="phone" class="pm-input" placeholder="Ví dụ: 0987654321">
                    </div>

                    <div class="pm-form-group">
                        <label class="pm-label">Email (Để nhận thông báo)</label>
                        <input type="email" name="email" class="pm-input" placeholder="example@gmail.com">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="pm-form-group">
                            <label class="pm-label">Tỉnh / Thành phố *</label>
                            <select id="province" class="pm-select">
                                <option value="">-- Chọn Tỉnh/Thành --</option>
                            </select>
                            <input type="hidden" name="city" id="city_text">
                        </div>

                        <div class="pm-form-group">
                            <label class="pm-label">Phường / Xã / Thị trấn *</label>
                            <select id="ward" class="pm-select" disabled>
                                <option value="">-- Chọn Phường/Xã --</option>
                            </select>
                            <input type="hidden" id="ward_text">
                        </div>
                    </div>

                    <div class="pm-form-group">
                        <label class="pm-label">Số nhà, Tên đường (Chi tiết) *</label>
                        <input type="text" name="address_detail" class="pm-input" placeholder="Số nhà, đường, thôn/xóm...">
                    </div>

                    <input type="hidden" name="address" id="full_address_combined">
                </div>

                <div class="pm-form-group">
                    <label class="pm-label">Ghi chú đơn hàng (Tùy chọn)</label>
                    <textarea name="note" rows="2" class="pm-textarea" placeholder="Ví dụ: Giao giờ hành chính..."></textarea>
                </div>

                <h2 class="pm-section-title" style="margin-top: 40px;">Phương thức thanh toán</h2>
                <div class="pm-methods">
                    <label class="pm-method-item active" id="method-cod">
                        <input type="radio" name="payment" value="cod" checked hidden>
                        <i class="fas fa-money-bill-wave pm-method-icon" style="color:#27ae60;"></i>
                        <div class="pm-method-text">
                            <b>Thanh toán khi nhận hàng (COD)</b>
                            <div>Bạn chỉ phải thanh toán khi nhận được hàng.</div>
                        </div>
                    </label>

                    <label class="pm-method-item">
                        <input type="radio" name="payment" value="bank" hidden>
                        <i class="fas fa-university pm-method-icon" style="color:#3498db;"></i>
                        <div class="pm-method-text">
                            <b>Chuyển khoản ngân hàng</b>
                            <div>Hỗ trợ quét mã QR mọi ngân hàng.</div>
                        </div>
                    </label>

                    <label class="pm-method-item">
                        <input type="radio" name="payment" value="momo" hidden>
                        <i class="fas fa-wallet pm-method-icon" style="color:#d82d8b;"></i>
                        <div class="pm-method-text">
                            <b>Ví điện tử MoMo</b>
                            <div>Thanh toán nhanh chóng qua ví MoMo.</div>
                        </div>
                    </label>
                </div>

                <button type="submit" id="pmSubmitBtn" style="display:none;"></button>
            </form>
        </div>

        <div class="pm-summary-card">
            <h2 class="pm-section-title">Đơn hàng của bạn</h2>

            <div class="pm-product-list">
                <?php if (!empty($cart)): foreach ($cart as $item): ?>
                        <div class="pm-product-row">
                            <span style="flex:1; padding-right:10px;">
                                <b><?= $item['quantity'] ?>x</b> <?= htmlspecialchars($item['name']) ?>
                            </span>
                            <span style="font-weight:600;">
                                <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>₫
                            </span>
                        </div>
                <?php endforeach;
                endif; ?>
            </div>

            <div class="pm-total-row">
                <span>Tạm tính:</span>
                <b><?= number_format($total_price, 0, ',', '.') ?>₫</b>
            </div>

            <?php if ($discount > 0): ?>
                <div class="pm-total-row" style="color:#27ae60;">
                    <span>Giảm giá (<?= $couponCode ?>):</span>
                    <b>-<?= number_format($discount, 0, ',', '.') ?>₫</b>
                </div>
            <?php endif; ?>

            <div class="pm-total-row">
                <span>Phí vận chuyển:</span>
                <b>Miễn phí</b>
            </div>

            <div class="pm-grand-total">
                <span>Tổng thanh toán:</span>
                <span><?= number_format($final_total, 0, ',', '.') ?>₫</span>
            </div>

            <button type="submit" class="pm-btn-submit" onclick="validateAndSubmit(event)">
                HOÀN TẤT ĐẶT HÀNG
            </button>

            <div class="pm-back-link">
                <a href="index.php?page=cart"><i class="fas fa-arrow-left"></i> Quay lại giỏ hàng</a>
            </div>
        </div>

    </div>
</div>

<script>
    // --- 1. HÀM CHUẨN HÓA TIẾNG VIỆT (Giữ nguyên) ---
    function removeVietnameseTones(str) {
        str = str.replace(/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/g, "a");
        str = str.replace(/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/g, "e");
        str = str.replace(/ì|í|ị|ỉ|ĩ/g, "i");
        str = str.replace(/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/g, "o");
        str = str.replace(/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/g, "u");
        str = str.replace(/ỳ|ý|ỵ|ỷ|ỹ/g, "y");
        str = str.replace(/đ/g, "d");
        str = str.replace(/À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ/g, "A");
        str = str.replace(/È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ/g, "E");
        str = str.replace(/Ì|Í|Ị|Ỉ|Ĩ/g, "I");
        str = str.replace(/Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ/g, "O");
        str = str.replace(/Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|ự|Ử|Ữ/g, "U");
        str = str.replace(/Ỳ|Ý|Ỵ|Ỷ|Ỹ/g, "Y");
        str = str.replace(/Đ/g, "D");
        str = str.replace(/\u0300|\u0301|\u0303|\u0309|\u0323/g, "");
        str = str.replace(/\u02C6|\u0306|\u031B/g, "");
        str = str.replace(/tinh|thanh pho/gi, "").trim();
        return str.toLowerCase().trim();
    }

    // --- 2. HÀM SUBMIT FORM (Giữ nguyên) ---
    function validateAndSubmit(event) {
        if (event) event.preventDefault();
        const form = document.getElementById('pmCheckoutForm');
        const existingAddressRadios = document.querySelectorAll('input[name="selected_address_id"]');
        const hasSavedAddresses = existingAddressRadios.length > 0;
        let isNewAddress = false;

        if (hasSavedAddresses) {
            const selectedRadio = document.querySelector('input[name="selected_address_id"]:checked');
            if (!selectedRadio) {
                alert('Vui lòng chọn địa chỉ nhận hàng!');
                return;
            }
            isNewAddress = (selectedRadio.value === 'new');
        } else {
            isNewAddress = true;
        }

        if (isNewAddress) {
            const fullnameEl = document.querySelector('input[name="fullname"]');
            const phoneEl = document.querySelector('input[name="phone"]');
            const provinceEl = document.getElementById('province');
            const wardEl = document.getElementById('ward');
            const addressEl = document.querySelector('input[name="address_detail"]');

            const fullname = fullnameEl ? fullnameEl.value.trim() : '';
            const phone = phoneEl ? phoneEl.value.trim() : '';
            const province = provinceEl ? provinceEl.value : '';
            const ward = wardEl ? wardEl.value : '';
            const addressDetail = addressEl ? addressEl.value.trim() : '';

            if (!fullname) return alert('Vui lòng nhập họ tên'), fullnameEl.focus();
            if (!phone) return alert('Vui lòng nhập số điện thoại'), phoneEl.focus();
            if (!province) return alert('Vui lòng chọn Tỉnh/Thành phố'), provinceEl.focus();
            if (!ward) return alert('Vui lòng chọn Phường/Xã'), wardEl.focus();
            if (!addressDetail) return alert('Vui lòng nhập địa chỉ chi tiết'), addressEl.focus();

            if (!hasSavedAddresses) {
                const hiddenInput = document.createElement("input");
                hiddenInput.type = "hidden";
                hiddenInput.name = "selected_address_id";
                hiddenInput.value = "new";
                form.appendChild(hiddenInput);
            }
        }
        form.submit();
    }

    // --- 3. ỨNG DỤNG CHÍNH ---
    const CheckoutApp = (() => {
        const CONFIG = {
            apiHost: "https://esgoo.net/api-tinhthanh-new",
            dom: {
                province: document.getElementById('province'),
                ward: document.getElementById('ward'),
                cityText: document.getElementById('city_text'),
                wardText: document.getElementById('ward_text'),
                fullAddress: document.getElementById('full_address_combined'),
                houseNumber: document.querySelector('input[name="address_detail"]'),
                newAddressForm: document.getElementById('newAddressForm'),
                paymentItems: document.querySelectorAll('.pm-method-item')
            }
        };

        const AddressService = {
            async fetchLocation(endpoint) {
                try {
                    const response = await fetch(`${CONFIG.apiHost}/${endpoint}.htm`);
                    const result = await response.json();
                    return result.error === 0 ? result.data : [];
                } catch (error) {
                    console.error("❌ Lỗi API:", error);
                    return [];
                }
            }
        };

        // --- ĐÂY LÀ PHẦN BẠN BỊ THIẾU: LOGIC TỰ ĐỘNG ĐIỀN ---
        const AutoLocator = {
            async detectAndFill() {
                // Kiểm tra xem form nhập mới có đang hiện không
                const isFormVisible = CONFIG.dom.newAddressForm.style.display !== 'none';
                if (!isFormVisible) return;

                if (!navigator.geolocation) return;

                console.log("📍 Đang lấy vị trí...");

                navigator.geolocation.getCurrentPosition(async (position) => {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;

                    // Gọi API lấy tên Tỉnh từ tọa độ
                    const url = `https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${lat}&longitude=${lon}&localityLanguage=vi`;

                    try {
                        const response = await fetch(url);
                        const data = await response.json();

                        const detectedCity = data.principalSubdivision || data.city;
                        console.log("📍 Tỉnh phát hiện được:", detectedCity);

                        if (detectedCity) {
                            this.selectProvinceByText(detectedCity);
                        }
                    } catch (err) {
                        console.warn("Lỗi API bản đồ:", err);
                    }
                });
            },

            selectProvinceByText(apiName) {
                const select = CONFIG.dom.province;
                const normalizedApiName = removeVietnameseTones(apiName);

                let foundValue = "";
                // Tìm trong thẻ select xem có tỉnh nào trùng tên không
                for (let i = 0; i < select.options.length; i++) {
                    const optionName = removeVietnameseTones(select.options[i].text);
                    if (optionName.includes(normalizedApiName) || normalizedApiName.includes(optionName)) {
                        foundValue = select.options[i].value;
                        break;
                    }
                }

                if (foundValue) {
                    select.value = foundValue;
                    // Kích hoạt sự kiện để load tiếp Huyện/Xã
                    select.dispatchEvent(new Event('change'));
                    console.log("✅ Đã tự động chọn:", select.options[select.selectedIndex].text);
                }
            }
        };
        // ----------------------------------------------------

        const UI = {
            populateSelect(selectElement, data, placeholder) {
                selectElement.innerHTML = `<option value="">${placeholder}</option>`;
                data.forEach(item => {
                    const option = document.createElement("option");
                    option.value = item.id;
                    option.text = item.full_name;
                    option.dataset.name = item.full_name;
                    selectElement.appendChild(option);
                });
                selectElement.disabled = data.length === 0;
            },

            updateHiddenInput(selectElement, hiddenInput) {
                const selected = selectElement.options[selectElement.selectedIndex];
                hiddenInput.value = selected && selected.value ? (selected.dataset.name || selected.text) : '';
            },

            combineAddress() {
                const {
                    houseNumber,
                    wardText,
                    cityText,
                    fullAddress
                } = CONFIG.dom;
                if (!houseNumber) return;
                const parts = [houseNumber.value.trim(), wardText.value, cityText.value];
                fullAddress.value = parts.filter(str => str !== "").join(", ");
            },

            toggleAddressForm(isShow) {
                const {
                    newAddressForm
                } = CONFIG.dom;
                if (newAddressForm) {
                    newAddressForm.style.display = isShow ? 'block' : 'none';
                    const inputs = newAddressForm.querySelectorAll('input, select');
                    inputs.forEach(el => {
                        if (isShow) el.setAttribute('required', 'true');
                        else el.removeAttribute('required');
                    });

                    // Nếu bật form thì chạy định vị lại
                    if (isShow) AutoLocator.detectAndFill();
                }
            }
        };

        const handleEvents = () => {
            const {
                province,
                ward,
                houseNumber,
                paymentItems
            } = CONFIG.dom;

            if (province) {
                province.addEventListener('change', async function() {
                    UI.updateHiddenInput(this, CONFIG.dom.cityText);
                    UI.combineAddress();
                    UI.populateSelect(ward, [], "-- Đang tải... --");

                    if (this.value) {
                        const data = await AddressService.fetchLocation(`2/${this.value}`);
                        UI.populateSelect(ward, data, "-- Chọn Phường/Xã --");
                    } else {
                        UI.populateSelect(ward, [], "-- Chọn Phường/Xã --");
                    }
                });
            }

            if (ward) {
                ward.addEventListener('change', function() {
                    UI.updateHiddenInput(this, CONFIG.dom.wardText);
                    UI.combineAddress();
                });
            }

            if (houseNumber) {
                houseNumber.addEventListener('input', UI.combineAddress);
            }

            if (paymentItems) {
                paymentItems.forEach(item => {
                    item.addEventListener('click', function() {
                        paymentItems.forEach(el => el.classList.remove('active'));
                        this.classList.add('active');
                        const radio = this.querySelector('input[type="radio"]');
                        if (radio) radio.checked = true;
                    });
                });
            }
        };

        const init = async () => {
            console.log("🚀 App Initialized");

            // 1. Load danh sách Tỉnh/Thành trước
            const provinces = await AddressService.fetchLocation('4/0');
            if (CONFIG.dom.province) {
                UI.populateSelect(CONFIG.dom.province, provinces, "-- Chọn Tỉnh/Thành --");
            }

            handleEvents();

            // 2. GỌI HÀM TỰ ĐỘNG ĐIỀN (Cái này lúc nãy bạn thiếu)
            AutoLocator.detectAndFill();

            window.toggleAddressForm = UI.toggleAddressForm;
        };

        return {
            init
        };
    })();

    document.addEventListener('DOMContentLoaded', CheckoutApp.init);
</script>