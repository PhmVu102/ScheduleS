document.querySelector(".close-btn").addEventListener("click", function () {
  document.querySelector(".header_tilte").style.display = "none";
});

// ===============================================
// 1. SLIDESHOW BANNER (DỮ LIỆU ĐỘNG TỪ DATABASE)
// ===============================================
const imgEl = document.getElementById("slideshow-image");

if (imgEl) {
  // BƯỚC 1: Lấy dữ liệu chuỗi JSON từ thẻ HTML
  const rawData = imgEl.getAttribute("data-slides");

  // BƯỚC 2: Chuyển đổi từ chuỗi JSON sang mảng JavaScript
  // Nếu không có dữ liệu thì gán mảng rỗng để tránh lỗi
  let slideshowImages = [];
  try {
    slideshowImages = rawData ? JSON.parse(rawData) : [];
  } catch (e) {
    console.error("Lỗi đọc dữ liệu banner:", e);
    slideshowImages = [];
  }

  // BƯỚC 3: Chỉ chạy slideshow nếu có ít nhất 1 ảnh
  if (slideshowImages.length > 0) {
    let currentSlide = 0;
    const dots = document.querySelectorAll(".dot");

    function showSlide(index) {
      // Xử lý vòng lặp: nếu hết ảnh thì quay về đầu/cuối
      if (index >= slideshowImages.length) index = 0;
      if (index < 0) index = slideshowImages.length - 1;

      currentSlide = index;

      // Đổi ảnh
      imgEl.src = slideshowImages[currentSlide];

      // Đổi trạng thái dấu chấm (dots)
      dots.forEach((d) => d.classList.remove("active"));
      // Kiểm tra xem dot có tồn tại không trước khi add class
      if (dots[currentSlide]) {
        dots[currentSlide].classList.add("active");
      }
    }

    // Tự động chạy (Auto play) sau 4 giây
    const autoSlide = setInterval(() => {
      showSlide(currentSlide + 1);
    }, 4000);

    // Sự kiện click vào dấu chấm
    dots.forEach((dot, i) => {
      dot.addEventListener("click", () => {
        showSlide(i);
        // Tùy chọn: Reset lại timer khi người dùng click (để tránh bị trượt ngay lập tức)
        // clearInterval(autoSlide);
      });
    });

    // Khởi động ảnh đầu tiên đúng với dữ liệu
    // (Dòng này quan trọng để đảm bảo ảnh khớp với dot đầu tiên)
    showSlide(0);
  }
}

document.addEventListener("DOMContentLoaded", function () {
  const userBtn = document.querySelector(".user-icon-box");
  const userWrapper = document.getElementById("userWrapper");

  if (userBtn && userWrapper) {
    userBtn.addEventListener("click", function (e) {
      e.stopPropagation(); // Ngăn không cho sự kiện lan ra window
      userWrapper.classList.toggle("show");
    });
    const popup = userWrapper.querySelector(".user-popup");
    if (popup) {
      popup.addEventListener("click", function (e) {
        e.stopPropagation(); // Ngăn không cho lan ra window
      });
    }
  }
  window.addEventListener("click", function (e) {
    if (userWrapper && userWrapper.classList.contains("show")) {
      userWrapper.classList.remove("show");
    }
  });
});

document.getElementById("search-btn").addEventListener("click", function (e) {
  e.preventDefault();

  const popup = document.getElementById("search-popup");
  const overlay = document.getElementById("search-overlay");

  const isActive = popup.classList.contains("active");

  if (!isActive) {
    popup.classList.add("active");
    overlay.classList.add("active ");
    popup.querySelector("input").focus(); // direkter Fokus ins Eingabefeld
  } else {
    popup.classList.remove("active");
    overlay.classList.remove("active");
  }
});

// Schließen beim Klick auf Overlay oder ESC-Taste
document
  .getElementById("search-overlay")
  .addEventListener("click", function () {
    document.getElementById("search-popup").classList.remove("active");
    this.classList.remove("active");
  });

document.addEventListener("keydown", function (e) {
  if (e.key === "Escape") {
    document.getElementById("search-popup").classList.remove("active");
    document.getElementById("search-overlay").classList.remove("active");
  }
});
// Highlight phương thức thanh toán khi click
document.querySelectorAll(".payment-method").forEach((el) => {
  el.addEventListener("click", function () {
    document
      .querySelectorAll(".payment-method")
      .forEach((e) => e.classList.remove("active"));
    this.classList.add("active");
    // Tự động chọn radio bên trong
    this.querySelector("input[type=radio]").checked = true;
  });
});
