<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Header</title>
    <link rel="stylesheet" href="assets/css/style.css" />
    <script src="assets/js/main.js" unescape></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
    <header class="admin-header">
        <div class="admin-header__logo">Admin Panel</div>

        <button class="admin-header__toggle" id="menuBtn">☰</button>

        <nav class="admin-header__nav" id="navMenu">
            <a href="?page=dashboard">Thống kê</a>
            <a href="?page=user_management">Người dùng</a>
            <a href="?page=banner">Banner</a>

            <div class="dropdown">
                <a href="?page=admin_product" class="dropdown-btn">Sản phẩm ▼</a>
                <div class="dropdown-content">
                    <a href="?page=categorys">Danh mục</a>
                    <a href="?page=imei">Imei sản phẩm</a>
                </div>
            </div>
            <div class="dropdown">
                <a href="?page=comments" class="dropdown-btn">Bình luận ▼</a>
                <div class="dropdown-content">
                    <a href="?page=contact">Liên hệ</a>
                    <a href="?page=wishlist">Yêu thích</a>
                </div>
            </div>
            <div class="dropdown">
                <a href="?page=voucher" class="dropdown-btn">Voucher ▼</a>
                <div class="dropdown-content">
                    <a href="?page=voucher_history">Voucher: đã dùng</a>
                </div>
            </div>
            <div class="dropdown">
                <a href="?page=orders" class="dropdown-btn">Đơn hàng ▼</a>
                <div class="dropdown-content">
                    <a href="?page=shipping_tracking">Quản lý Vận chuyển</a>
                    <a href="#">Quản lý hoàn trả</a>
                </div>
            </div>
            <a href="#">Lịch sử</a>
            <a href="../index.php">Đăng xuất</a>
        </nav>
    </header>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const menuBtn = document.getElementById("menuBtn"); // ID của nút 3 gạch
            const navMenu = document.getElementById("navMenu"); // ID của menu nav

            if (menuBtn && navMenu) {
                menuBtn.addEventListener("click", function() {
                    // Thêm/Xóa class "active" để hiện/ẩn menu
                    navMenu.classList.toggle("active");
                });
            }
        });
    </script>