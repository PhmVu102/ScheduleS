  <?php
  // Hàm hỗ trợ lấy 1 banner đầu tiên từ danh sách theo vị trí
  if (!function_exists('getBanner')) {
    function getBanner($list, $position)
    {
      if (isset($list[$position]) && !empty($list[$position])) {
        return $list[$position][0]; // Trả về banner đầu tiên tìm thấy
      }
      return null;
    }
  }

  // Đảm bảo biến $bannerList tồn tại để tránh lỗi Undefined variable
  $bannerList = isset($bannerList) ? $bannerList : [];
  ?>
  <div class="container">
    <div class="div_1 container">
      <div class="parent">

        <?php
        $slides = isset($bannerList['slideshow']) ? $bannerList['slideshow'] : [];
        // Lấy ảnh đầu tiên làm mặc định, nếu không có thì dùng ảnh placeholder
        $firstSlide = !empty($slides) ? $slides[0]['image_url'] : 'assets/img/default.jpg';

        // Chuyển mảng ảnh thành JSON để JS xử lý chuyển cảnh
        $slideImages = array_column($slides, 'image_url');
        $slideJson = htmlspecialchars(json_encode($slideImages), ENT_QUOTES, 'UTF-8');
        ?>
        <div class="left-banner">
          <div class="left-banner_img">
            <img id="slideshow-image"
              src="<?= $firstSlide ?>"
              data-slides="<?= $slideJson ?>"
              alt="Slideshow Image" width="100%" height="100%">

            <div class="dots" id="dots">
              <?php if (!empty($slides)): ?>
                <?php foreach ($slides as $index => $slide): ?>
                  <span class="dot <?= $index == 0 ? 'active' : '' ?>" data-index="<?= $index ?>"></span>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="right-banners">
          <?php $rb1 = getBanner($bannerList, 'right_top_1'); ?>
          <?php if ($rb1): ?>
            <div class="banner banner_tion" style="background-image: url('<?= $rb1['image_url'] ?>'); background-size: cover;">
              <a href="<?= $rb1['link'] ?>" class="btn-small">
                <p>shop now</p>
              </a>
            </div>
          <?php endif; ?>

          <?php $rb2 = getBanner($bannerList, 'right_top_2'); ?>
          <?php if ($rb2): ?>
            <div class="banner" style="background-image: url('<?= $rb2['image_url'] ?>')">
              <a href="<?= $rb2['link'] ?>" class="btn-small">
                <p>shop now</p>
              </a>
            </div>
          <?php endif; ?>
        </div>

      </div>
    </div>

    <div class="line">
      <div class="div_2 container">
        <section class="the_loai">
          <div class="title">
            <div class="title_start">
              <p>Danh mục</p>
            </div>
          </div>
          <div class="the_loai_products">
            <div class="product">
              <div class="image_prod">
                <img src="assets/img/laptop.jpg" alt="Laptop" />
                <div class="bongmo">
                  <a href="?page=products&category=laptop" class="icon-arrow btn">
                    <i class="fa-solid fa-arrow-right"></i>
                  </a>
                </div>
              </div>
              <div class="content_prod">
                <a href="?page=products&category=laptop">
                  <p>Laptop</p>
                </a>
              </div>
            </div>

            <div class="product">
              <div class="image_prod">
                <img src="assets/img/dienthoai.jpg" alt="Điện thoại" />
                <div class="bongmo">
                  <a href="?page=products&category=phone" class="icon-arrow btn">
                    <i class="fa-solid fa-arrow-right"></i>
                  </a>
                </div>
              </div>
              <div class="content_prod">
                <a href="?page=products&category=phone">
                  <p>Điện thoại</p>
                </a>
              </div>
            </div>
          </div>
        </section>
      </div>
      <div class="line"></div>
      <div class="div_3 container">
        <div class="header_products">
          <h2 class="header__title">Sản phẩm<br />Bán chạy</h2>
        </div>

        <div class="grid">
          <?php if (!empty($bestsellers)): ?>
            <?php foreach ($bestsellers as $index => $p): ?>
              <?php
              $cardClass = ($index === 0) ? 'card card--large' : 'card';
              $img = !empty($p['thumbnail']) ? $p['thumbnail'] : 'assets/img/product/default.png';
              $rating = round($p['rating']);
              ?>

              <div class="<?= $cardClass ?>">
                <a href="index.php?page=product_details&id=<?= $p['id'] ?>" style="text-decoration: none; color: inherit;">

                  <img src="<?= $img ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="card__image" />

                  <div class="card__info">
                    <p class="card__name"><?= htmlspecialchars($p['name']) ?></p>

                    <div class="card__meta" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                      <div class="card__stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                          <?php if ($i <= $rating): ?>
                            <i class="fas fa-star" style="color: #ffc107;"></i>
                          <?php else: ?>
                            <i class="far fa-star" style="color: #ccc;"></i>
                          <?php endif; ?>
                        <?php endfor; ?>
                      </div>

                      <div class="card__views" style="font-size: 0.85rem; color: #666;">
                        <i class="fa-regular fa-eye"></i> <?= number_format($p['view_count']) ?>
                      </div>
                    </div>
                  </div>

                  <?php
                  $price = $p['price'];
                  $price_sale = $p['price_sale'];
                  $is_sale = ($price_sale > 0 && $price_sale < $price);
                  $percent_off = $is_sale ? round((($price - $price_sale) / $price) * 100) : 0;
                  ?>

                  <div class="card__price-box">
                    <?php if ($is_sale): ?>
                      <span class="price--old"><?= number_format($price, 0, ',', '.') ?>₫</span>
                      <span class="price--new"><?= number_format($price_sale, 0, ',', '.') ?>₫</span>
                      <span class="tag--sale">-<?= $percent_off ?>%</span>
                    <?php else: ?>
                      <span class="price--new"><?= number_format($price, 0, ',', '.') ?>₫</span>
                    <?php endif; ?>
                  </div>
                </a>
              </div>

            <?php endforeach; ?>
          <?php else: ?>
            <p>Chưa có sản phẩm nổi bật nào.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="div_4 container">
      <div class="thongtinvc">
        <div class="colum">
          <img src="https://png.pngtree.com/png-clipart/20250102/original/pngtree-world-icon-png-image_4945972.png" alt="" width="50%" style="margin-left: 15%;" class="imgthongtinvc" />
          <div class="nd">
            <a href="">Miễn phí giao hàng</a>
            <p>Giao hàng
              tận nơi khắp các tỉnh thành trên cả nước</p>
          </div>
        </div>
        <div class="colum">
          <img src="https://file.hstatic.net/200000053174/file/doi_tra_aab8fa9f7151418da279d47d821153d7.svg" alt="" width="50%" class="imgthongtinvc" />
          <div class="nd nd_dimaond">
            <a href="">Đổi trả sản phẩm</a>
            <p>Hoàn lại 100% tiền nếu không vừa ý hoặc lỗi từ nhà sản xuất (trong 7 ngày)</p>
          </div>
        </div>
        <div class="colum">
          <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQfMZLcKnKrc98SdzZrEFa-lvvzPjniKgORag&s" alt="" width="40%" class="imgthongtinvc" />
          <div class="nd nd_active">
            <a href="">Ưu đãi thành viên
            </a>
            <p>Giảm 20% cho
              thành viên hoặc khách hàng có thẻ thành viên</p>
          </div>
        </div>
        <div class="colum1">
          <img width="50%" src="https://media.istockphoto.com/id/1144489611/vi/vec-to/hotline-icon-vector-d%E1%BB%AF-li%E1%BB%87u-nam-d%E1%BB%8Bch-v%E1%BB%A5-h%E1%BB%97-tr%E1%BB%A3-kh%C3%A1ch-h%C3%A0ng-h%E1%BB%93-s%C6%A1-avatar-v%E1%BB%9Bi-tai-nghe-v%C3%A0-%C4%91%E1%BB%93.jpg?s=612x612&w=0&k=20&c=2_GrDFouHLIL_9EAlWIURe3IzESdNOU6_Zpqj89OiUo=" alt="" class="imgthongtinvc" />
          <div class="nd nd_active">
            <a href="">Hỗ trợ khách hàng</a>
            <p>Đội ngũ tư vấn viên, chăm sóc khách hàng luôn sẵn sàng hỗ trợ 24/7</p>
          </div>
        </div>
      </div>
    </div>
    <div class="div_5 container">
      <div class="box_it">

        <?php $bb1 = getBanner($bannerList, 'bottom_1'); ?>
        <?php if ($bb1): ?>
          <div class="it">
            <div class="it1">
              <img src="<?= $bb1['image_url'] ?>" alt="Bottom Banner 1" />
            </div>
            <div class="it3">
              <a href="<?= $bb1['link'] ?>" class="bts">SHOP NOW</a>
            </div>
          </div>
        <?php endif; ?>

        <?php $bb2 = getBanner($bannerList, 'bottom_2'); ?>
        <?php if ($bb2): ?>
          <div class="it">
            <div class="it1">
              <img src="<?= $bb2['image_url'] ?>" alt="Bottom Banner 2" />
            </div>
            <div class="it2">
              <a class="bts" href="<?= $bb2['link'] ?>">SHOP NOW</a>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>