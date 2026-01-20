<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'ScheduleS Store' ?></title>
    <link rel="stylesheet" href="assets/css/style.css" />
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="icon" href="assets/img/logo.png">
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<div class="header_tilte">
    <h3 class="header_content">Ưu đãi 20% cho mọi đơn – Dùng mã: FLASH20</h3>
    <span class="close-btn"><i class="fa-solid fa-xmark"></i></span>
</div>
<?php
// Tính tổng số lượng sản phẩm trong giỏ
$totalQty = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $totalQty += $item['quantity'];
    }
}
?>
<div class="line">
    <header class="header container">
        <div class="header__logo">
            <a href="index.php">
                <img src="assets/img/logo.png" alt="logo">
            </a>
        </div>

        <nav class="header__menu">
            <ul>
                <li><a href="index.php">Trang chủ</a></li>
                <li><a href="index.php?page=products">Tất cả sản phẩm</a></li>

                <li><a href="index.php?page=about">Giới thiệu</a></li>
                <li><a href="index.php?page=contact">Liên hệ</a></li>
                <li><a href="index.php?page=news">Tin tức</a></li>
            </ul>
        </nav>

        <div class="header__icons">
            <div class="location-wrapper" id="getLocationBtn" title="Lấy vị trí hiện tại">
                <i class="fa-solid fa-location-dot"></i>
                <span id="location-text">Vị trí</span>
            </div>
            <a href="" id="search-btn">
                <img src="assets/img/icon/icon_search.png" alt="search">
            </a>

            <div class="search-popup" id="search-popup">
                <form action="index.php" method="GET">
                    <input type="hidden" name="page" value="products">

                    <input type="text" name="keyword" placeholder="Tìm kiếm sản phẩm..." value="<?php echo htmlspecialchars($_GET['keyword'] ?? ''); ?>">
                    <button type="submit">Tìm</button>
                </form>
            </div>
            <div class="header-action">
                <div class="user-wrapper" id="userWrapper">
                    <div class="user-icon-box" onclick="toggleUserPopup()">
                        <img src="assets/img/icon/icon_login.png" alt="user" style="width: 24px; cursor: pointer;">

                        <?php if (isset($_SESSION['user'])): ?>
                            <span class="user-name" style="margin-left: 5px; font-weight: 500; cursor: pointer;">
                                <?= htmlspecialchars($_SESSION['user']['fullname']) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="user-popup">
                        <?php if (isset($_SESSION['user'])): ?>
                            <a href="index.php?page=profile" class="btn-action btn-profile">Tài khoản</a>
                            <a href="index.php?page=order_history" class="btn-action btn-profile">Lịch sử mua hàng</a>
                            <a href="index.php?page=logout" class="btn-action btn-logout">Đăng xuất</a>
                        <?php else: ?>
                            <a href="index.php?page=login" class="btn-action btn-login">Đăng nhập</a>
                            <a href="index.php?page=register" class="btn-action btn-register">Đăng ký</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <a href="?page=wishlists"><img src="assets/img/icon/icon_heart.png" alt="heart"></a>
            <a href="?page=cart" data-count="<?= $totalQty ?>">
                <img src="assets/img/icon/icon_bag.png" alt="bag" class="bag">
            </a>
        </div>

    </header>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const btn = document.getElementById("getLocationBtn");
        const textSpan = document.getElementById("location-text");
        const icon = btn.querySelector("i");

        btn.addEventListener("click", function(e) {
            if (e) e.preventDefault();

            if (!navigator.geolocation) {
                alert("Trình duyệt của bạn không hỗ trợ định vị.");
                return;
            }

            btn.classList.add("location-loading");
            textSpan.textContent = "Đang tìm...";
            icon.className = "fa-solid fa-spinner fa-spin";

            navigator.geolocation.getCurrentPosition(success, error);

            function success(position) {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;

                const url = `https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${lat}&longitude=${lon}&localityLanguage=vi`;

                fetch(url)
                    .then((response) => {
                        if (!response.ok) throw new Error("Lỗi kết nối API bản đồ");
                        return response.json();
                    })
                    .then((data) => {
                        let locationName =
                            data.city ||
                            data.locality ||
                            data.principalSubdivision ||
                            "Không xác định";

                        let fullAddress = [
                                data.locality,
                                data.city,
                                data.principalSubdivision,
                                data.countryName,
                            ]
                            .filter(Boolean)
                            .join(", ");

                        textSpan.textContent = locationName;
                        textSpan.title = fullAddress;

                        // Gọi hàm lưu (đã sửa logic check trùng)
                        saveToMyDatabase(lat, lon, fullAddress);
                    })
                    .catch((err) => {
                        console.error("Chi tiết lỗi:", err);
                        textSpan.textContent = "Lỗi lấy tên";
                        // Vẫn lưu toạ độ dù lỗi tên (tùy nhu cầu)
                        saveToMyDatabase(lat, lon, "Lỗi API bản đồ");
                    })
                    .finally(() => {
                        btn.classList.remove("location-loading");
                        icon.className = "fa-solid fa-location-dot";
                    });
            }

            function error() {
                console.warn("Người dùng chưa cấp quyền hoặc bị chặn.");
                textSpan.textContent = "Chưa cấp quyền";
                btn.classList.remove("location-loading");
                icon.className = "fa-solid fa-location-dot";
            }

            // ============================================================
            // HÀM LƯU DATABASE (ĐÃ SỬA ĐỂ CHẶN TRÙNG LẶP)
            // ============================================================
            function saveToMyDatabase(lat, lon, address) {
                // 1. Lấy dữ liệu cũ từ bộ nhớ trình duyệt
                const lastSaved = localStorage.getItem("last_saved_location");
                
                // 2. Kiểm tra trùng lặp
                if (lastSaved) {
                    const lastData = JSON.parse(lastSaved);
                    
                    // So sánh: Nếu Địa chỉ Giống nhau (hoặc bạn có thể so sánh Lat/Lon)
                    // Ở đây so sánh address vì toạ độ GPS có thể lệch nhẹ dù đứng yên
                    if (lastData.address === address) {
                        return; // DỪNG LẠI, KHÔNG CHẠY FETCH
                    }
                }

                // 3. Nếu không trùng thì mới gửi Fetch
                fetch("?page=saveLocation", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                        },
                        body: JSON.stringify({
                            lat: lat,
                            lon: lon,
                            address: address,
                        }),
                    })
                    .then((response) => response.text())
                    .then((text) => {
                        try {
                            const data = JSON.parse(text);
                            console.log("Server response:", data);
                            
                            // 4. Gửi thành công thì Lưu vào LocalStorage để lần sau so sánh
                            localStorage.setItem("last_saved_location", JSON.stringify({
                                lat: lat,
                                lon: lon,
                                address: address,
                                time: new Date().getTime() // Lưu thêm thời gian nếu cần
                            }));
                            
                        } catch (e) {
                            console.warn("Server trả về không phải JSON:", text);
                        }
                    })
                    .catch((error) => {
                        console.error("Lỗi khi lưu vào DB:", error);
                    });
            }
        });

        // Tự động kích hoạt (giữ nguyên)
        if (navigator.geolocation) {
            setTimeout(() => {
                btn.click();
            }, 500);
        }
    });
</script>