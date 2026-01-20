<style>
    header.news-header {
        background-color: #0066ff;
        color: white;
        padding: 40px 20px;
        text-align: center;
    }

    header.news-header h1 {
        font-size: 2.5rem;
    }

    main.news-content {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
    }

    /* Bài nổi bật */
    .featured-article {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 50px;
    }

    .featured-article img {
        width: 100%;
        max-height: 400px;
        object-fit: cover;
        border-radius: 10px;
    }

    .featured-text {
        flex: 1;
        min-width: 300px;
    }

    .featured-text h2 {
        color: #0066ff;
        font-size: 2rem;
        margin-bottom: 15px;
    }

    .featured-text p {
        font-size: 1rem;
        line-height: 1.6;
    }

    /* Danh sách bài viết */
    .news-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
    }

    .news-item {
        background-color: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .news-item:hover {
        transform: translateY(-5px);
    }

    .news-item img {
        width: 100%;
        height: 180px;
        object-fit: cover;
    }

    .news-item-content {
        padding: 15px;
    }

    .news-item-content h3 {
        font-size: 1.2rem;
        color: #0066ff;
        margin-bottom: 10px;
    }

    .news-item-content p {
        font-size: 0.95rem;
        color: #555;
    }

    .news-item-content a {
        display: inline-block;
        margin-top: 10px;
        color: #0066ff;
        text-decoration: none;
        font-weight: bold;
    }

    .news-item-content a:hover {
        text-decoration: underline;
    }

    @media(max-width: 768px) {
        .featured-article {
            flex-direction: column;
        }
    }
</style>
<header class="news-header">
    <h1>Tin tức nổi bật</h1>
</header>

<main class="news-content">
    <!-- Bài nổi bật -->
    <section class="featured-article">
        <div class="featured-text">
            <h2>Ra mắt sản phẩm mới năm 2025</h2>
            <p>Chúng tôi tự hào giới thiệu bộ sưu tập sản phẩm mới với thiết kế hiện đại, hiệu năng vượt trội, và giá cả hợp lý. Khám phá ngay để không bỏ lỡ cơ hội sở hữu sản phẩm độc đáo này.</p>
        </div>
        <div class="featured-image">
            <img src="https://metapress.com/wp-content/uploads/2023/09/iphone15-1024x576.jpeg" alt="Bài nổi bật">
        </div>
    </section>

    <!-- Danh sách bài viết khác -->
    <section class="news-list">
        <article class="news-item">
            <img src="https://www.samaa.tv/images/iphon-15-pro-colors.jpg" alt="Tin 1">
            <div class="news-item-content">
                <h3>Khuyến mãi tháng 12</h3>
                <p>Đừng bỏ lỡ các chương trình khuyến mãi đặc biệt trong tháng 12 với nhiều ưu đãi hấp dẫn cho khách hàng.</p>
                <a href="#">Xem chi tiết</a>
            </div>
        </article>

        <article class="news-item">
            <img src="https://techvccloud.mediacdn.vn/280518386289090560/2022/3/16/screen-shot-2022-03-16-at-60207-pm-16474285449661923706608-70-0-1090-1816-crop-1647428553674836916294.png" alt="Tin 2">
            <div class="news-item-content">
                <h3>Cập nhật phần mềm mới</h3>
                <p>Hệ thống đã được nâng cấp với các tính năng mới giúp trải nghiệm người dùng mượt mà và tiện lợi hơn.</p>
                <a href="#">Xem chi tiết</a>
            </div>
        </article>

        <article class="news-item">
            <img src="https://media.vneconomy.vn/w800/images/upload/2022/12/01/thanh-nien-dung-smartphone-truy-cap-internet.jpg" alt="Tin 3">
            <div class="news-item-content">
                <h3>Hướng dẫn sử dụng sản phẩm</h3>
                <p>Bài viết hướng dẫn chi tiết cách sử dụng và bảo quản sản phẩm để kéo dài tuổi thọ và hiệu suất.</p>
                <a href="#">Xem chi tiết</a>
            </div>
        </article>

        <article class="news-item">
            <img src="https://smartagency.com.vn/wp-content/uploads/2023/10/to-chuc-offline-game-1.jpg" alt="Tin 4">
            <div class="news-item-content">
                <h3>Sự kiện offline cuối năm</h3>
                <p>Tham gia sự kiện offline cuối năm để gặp gỡ đội ngũ và nhận nhiều phần quà hấp dẫn.</p>
                <a href="#">Xem chi tiết</a>
            </div>
        </article>
    </section>
</main>