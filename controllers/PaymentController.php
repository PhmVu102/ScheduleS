<?php
require_once 'models/UserModel.php';

class PaymentController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function webhook()
    {
        // 1. Nhận dữ liệu từ cổng thanh toán
        $inputData = file_get_contents('php://input');
        $transaction = json_decode($inputData, true);

        if (!$transaction) {
            http_response_code(400);
            die('Invalid Data');
        }

        // 2. Lấy thông tin (Xử lý đa cấu trúc JSON để tương thích SePay/Casso)
        if (isset($transaction['content'])) {
            // Cấu trúc phẳng
            $content = $transaction['content'];
            $amount = $transaction['transferAmount'];
            $transCode = $transaction['id'];
        } elseif (isset($transaction['data'])) {
            // Cấu trúc lồng trong object 'data'
            $content = $transaction['data']['description'];
            $amount = $transaction['data']['amount'];
            $transCode = $transaction['data']['id'] ?? 'API_' . time();
        } else {
            // Không đúng định dạng mong đợi thì dừng
            return;
        }
        // 3. Tìm mã đơn hàng (Ví dụ: SSB6A3DAC)
        preg_match('/SSB[A-Z0-9]+/i', $content, $matches);
        $orderCode = isset($matches[0]) ? strtoupper($matches[0]) : null;

        if ($orderCode) {
            // Lấy thông tin đơn hàng từ DB
            $order = $this->userModel->getOrderByCode($orderCode);

            if ($order) {
                // Kiểm tra số tiền (Khách phải chuyển đủ hoặc thừa)
                if ($amount >= $order['final_money']) {

                    // --- BƯỚC 1: CẬP NHẬT TRẠNG THÁI ĐƠN HÀNG ---
                    // Status = 2 (Đã thanh toán / Chờ giao hàng)
                    $this->userModel->updateOrderStatus($order['id'], 2);

                    // --- BƯỚC 2: GHI LỊCH SỬ GIAO DỊCH (Lưu bằng chứng tiền về) ---
                    $this->userModel->updatePaymentHistory($order['id'], $transCode, $amount, 'success');
                    // --- BƯỚC 3: TRỪ SỐ LƯỢNG TỒN KHO ---
                    // Kiểm tra xem method này có tồn tại trong Model chưa để tránh lỗi Fatal
                    if (method_exists($this->userModel, 'getOrderItemsForStockUpdate')) {
                        $items = $this->userModel->getOrderItemsForStockUpdate($order['id']);

                        if ($items) {
                            foreach ($items as $item) {
                                // Xử lý linh hoạt tên cột (product_variant_id hoặc variant_id)
                                $variantId = $item['product_variant_id'] ?? $item['variant_id'] ?? null;

                                if ($variantId) {
                                    $this->userModel->decreaseProductStock($variantId, $item['quantity']);
                                }
                            }
                        }
                    }

                    // Trả về tín hiệu thành công
                    echo json_encode(['status' => 'success']);
                }
            }
        }

        // Luôn trả về 200 OK để bên Ngân hàng/SePay biết là đã nhận tin thành công
        http_response_code(200);
    }
}
?>
