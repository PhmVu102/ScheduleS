<?php
require_once './models/UserModel.php';

$userModel = new UserModel();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (empty($fullname) || empty($email) || empty($password) || empty($confirm)) {
        $error = 'Vui lòng điền đầy đủ các trường bắt buộc!';
    }
    elseif ($password !== $confirm) {
        $error = 'Mật khẩu xác nhận không khớp!';
    }
    elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự!';
    }
    elseif ($userModel->emailExists($email)) {
        $error = 'Email này đã được đăng ký rồi!';
    }
    else {
        // Đăng ký thành công
        $result = $userModel->register($fullname, $email, $password, $phone);
        if ($result) {
            $success = 'Đăng ký thành công! Đang chuyển đến trang đăng nhập...';
            echo "<script>setTimeout(() => location.href='index.php?page=login', 2000);</script>";
        } else {
            $error = 'Có lỗi xảy ra, vui lòng thử lại!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký - Schedules Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .register-main {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            background: var(--gradient);
            background-size: 400% 400%;
            animation: gradient 12s ease infinite;
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
        }
        @keyframes gradient { 0%,100%{background-position:0% 50%} 50%{background-position:100% 50%} }

        .register-card {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(16px);
            border-radius: 24px;
            border: 1px solid rgba(255,255,255,0.2);
            padding: 50px 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-20px)} }

        h2 { text-align:center; color:#fff; font-size:28px; margin-bottom:10px; }
        p.tagline { text-align:center; color:rgba(255,255,255,0.8); margin-bottom:30px; }

        .input-group { margin-bottom:20px; }
        .input-group input {
            width:100%; padding:16px 20px; border:none; border-radius:50px;
            background:rgba(255,255,255,0.2); color:#fff; font-size:16px;
            outline:none; transition:all .3s;
        }
        .input-group input::placeholder { color:rgba(255,255,255,0.7); }
        .input-group input:focus {
            background:rgba(255,255,255,0.35);
            box-shadow:0 0 25px rgba(255,255,255,0.4);
        }

        .form-register button {
            width:100%; padding:16px; border:none; border-radius:50px;
            background: linear-gradient(45deg, #ff9a9e, #fad0c4);
            color:#fff; font-size:18px; font-weight:600; cursor:pointer;
            transition:all .4s; margin-top:10px;
        }
        .form-register button:hover { transform:translateY(-4px); box-shadow:0 15px 35px rgba(0,0,0,0.4); }

        .error { background:rgba(255,107,107,0.25); color:#ff6b6b; padding:12px; border-radius:12px; text-align:center; margin:15px 0; font-weight:500; }
        .success { background:rgba(72,219,251,0.25); color:#48dbfb; padding:15px; border-radius:12px; text-align:center; margin:15px 0; font-weight:600; }

        .links { text-align:center; margin-top:25px; }
        .links a { color:rgba(255,255,255,0.9); text-decoration:none; font-weight:600; }
        .links a:hover { text-decoration:underline; }
    </style>
</head>
<body>
    <div class="register-main">
        <div class="register-card">
            <h2>Schedules Store</h2>
            <p class="tagline">Tạo tài khoản mới</p>

            <?php if($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="success"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST" autocomplete="off" class="form-register">
                <div class="input-group">
                    <input type="text" name="fullname" placeholder="Họ và tên" required value="<?= htmlspecialchars($fullname ?? '') ?>">
                </div>
                <div class="input-group">
                    <input type="email" name="email" placeholder="Email đăng nhập" required value="<?= htmlspecialchars($email ?? '') ?>">
                </div>
                <div class="input-group">
                    <input type="text" name="phone" placeholder="Số điện thoại (không bắt buộc)" value="<?= htmlspecialchars($phone ?? '') ?>">
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="Mật khẩu" required>
                </div>
                <div class="input-group">
                    <input type="password" name="confirm" placeholder="Nhập lại mật khẩu" required>
                </div>
                <button type="submit">Đăng Ký Ngay</button>
            </form>

            <div class="links">
                <a href="index.php?page=login">Đã có tài khoản? Đăng nhập</a>
            </div>
        </div>
    </div>
</body>