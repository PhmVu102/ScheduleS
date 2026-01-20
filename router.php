<?php
class Router
{

    private $routes = [];

    public function add($page, $controller, $method)
    {
        $this->routes[$page] = [
            'controller' => $controller,
            'method'     => $method
        ];
    }

    public function dispatch($defaultPage = 'home')
    {
        $page = $_GET['page'] ?? $defaultPage;
        $id = $_GET['id'] ?? NULL;
        $name = $_GET['name'] ?? NULL;
        $brand = $_GET['brand'] ?? NULL;
        $sale = $_GET['sale'] ?? NULL;
        $action = $_GET['action'] ?? '';
        // 1. Kiểm tra xem route có tồn tại không
        if (!array_key_exists($page, $this->routes)) {
            // Thay vì die(), hãy chuyển hướng về 404 hoặc trang chủ
            // header("Location: index.php?page=404"); 
            // Hoặc hiển thị thông báo đẹp hơn:
            echo "<h1 style='text-align:center; margin-top:50px;'>404 - Trang không tồn tại!</h1>";
            exit;
        }

        $controllerName = $this->routes[$page]['controller'];
        $method         = $this->routes[$page]['method'];

        // 2. Kiểm tra file Controller có tồn tại không trước khi require
        $controllerFile = "controllers/$controllerName.php";
        if (!file_exists($controllerFile)) {
            die("Lỗi hệ thống: Không tìm thấy file $controllerFile");
        }

        require_once $controllerFile;

        // 3. Khởi tạo Controller
        $controller = new $controllerName();

        // 4. Kiểm tra Method có tồn tại trong Class không
        if (!method_exists($controller, $method)) {
            die("Lỗi hệ thống: Method '$method' không tồn tại trong class '$controllerName'");
        }

        // 5. Chạy hàm
        $controller->$method();
    }
}
