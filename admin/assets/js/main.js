const menuBtn = document.getElementById("menuBtn");
const navMenu = document.getElementById("navMenu");

menuBtn.addEventListener("click", function (event) {
  event.stopPropagation();
  navMenu.classList.toggle("admin-header__nav--active");
});

document.addEventListener("click", function (event) {
  if (!navMenu.contains(event.target) && !menuBtn.contains(event.target)) {
    navMenu.classList.remove("admin-header__nav--active");
  }
});
document.getElementById('menuToggle').addEventListener('click', function () {
    document.getElementById('sidebar').classList.toggle('active');
});

document.getElementById('closeSidebar').addEventListener('click', function () {
    document.getElementById('sidebar').classList.remove('active');
});

// Đóng sidebar khi click ngoài (trên mobile)
document.addEventListener('click', function (e) {
    const sidebar = document.getElementById('sidebar');
    const menuToggle = document.getElementById('menuToggle');

    if (window.innerWidth <= 992) {
        if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
            sidebar.classList.remove('active');
        }
    }
});