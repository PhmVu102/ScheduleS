<style>
    /* CSS Giữ nguyên như cũ */
    .edit-container { max-width: 900px; margin: 20px auto; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
    .form-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
    .form-header h1 { margin: 0; font-size: 24px; color: #333; }
    .btn-back { text-decoration: none; color: #555; background: #f0f0f0; padding: 8px 15px; border-radius: 5px; font-size: 14px; transition: 0.3s; }
    .btn-back:hover { background: #e0e0e0; }
    .form-card { background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    .form-row { display: flex; gap: 20px; margin-bottom: 20px; }
    .form-group { flex: 1; display: flex; flex-direction: column; }
    .form-group label { font-weight: 600; margin-bottom: 8px; color: #444; font-size: 14px; }
    .form-group input, .form-group select { padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 15px; outline: none; transition: border 0.3s; }
    .form-group input:focus, .form-group select:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
    .input-code { text-transform: uppercase; font-weight: bold; color: #2563eb; background-color: #f8fafc; letter-spacing: 1px; }
    .form-actions { margin-top: 30px; text-align: right; border-top: 1px solid #f0f0f0; padding-top: 20px; }
    .btn-save { background: #2563eb; color: white; border: none; padding: 12px 30px; border-radius: 6px; font-size: 16px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: background 0.2s; }
    .btn-save:hover { background: #1d4ed8; }
    .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; }
    .alert-danger { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
</style>

<div class="edit-container">
    
    <div class="form-header">
        <h1><i class="fas fa-edit"></i> Sửa Voucher: #<?= $voucher['id'] ?></h1>
        <a href="?page=voucher" class="btn-back">
            <i class="fas fa-arrow-left"></i> Quay lại danh sách
        </a>
    </div>

    <?= $msg ?>

    <div class="form-card">
        <form method="POST" action="">
            
            <div class="form-row">
                <div class="form-group">
                    <label>Mã Voucher <span style="color:red">*</span></label>
                    <input type="text" name="code" class="input-code" 
                           value="<?= htmlspecialchars($voucher['code']) ?>" required placeholder="VD: TET2025">
                </div>
                
                <div class="form-group">
                    <label>Trạng thái</label>
                    <select name="status">
                        <option value="1" <?= $voucher['status'] == 1 ? 'selected' : '' ?>>🟢 Đang hoạt động</option>
                        <option value="0" <?= $voucher['status'] == 0 ? 'selected' : '' ?>>🔴 Tạm ẩn / Dừng</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Loại giảm giá</label>
                    <select name="discount_type">
                        <option value="percent" <?= $voucher['discount_type'] == 'percent' ? 'selected' : '' ?>>% Phần trăm</option>
                        <option value="fixed" <?= $voucher['discount_type'] == 'fixed' ? 'selected' : '' ?>>₫ Số tiền cố định</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Giá trị giảm <span style="color:red">*</span></label>
                    <input type="number" name="discount_value" 
                           value="<?= $voucher['discount_value'] ?>" required min="0" placeholder="VD: 10 hoặc 50000">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Đơn hàng tối thiểu (VNĐ)</label>
                    <input type="number" name="min_order_amount" 
                           value="<?= $voucher['min_order_amount'] ?>" required min="0" placeholder="0 = Không giới hạn">
                </div>

                <div class="form-group">
                    <label>Số lượng mã (SL)</label>
                    <input type="number" name="quantity" 
                           value="<?= $voucher['quantity'] ?>" required min="0">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Thời gian Bắt đầu</label>
                    <input type="datetime-local" name="start_date" 
                           value="<?= date('Y-m-d\TH:i', strtotime($voucher['start_date'])) ?>" required>
                </div>

                <div class="form-group">
                    <label>Thời gian Kết thúc</label>
                    <input type="datetime-local" name="end_date" 
                           value="<?= date('Y-m-d\TH:i', strtotime($voucher['end_date'])) ?>" required>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Lưu Thay Đổi
                </button>
            </div>
        </form>
    </div>
</div>