<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .login-main {
            font-family: 'Poppins', sans-serif;
            background: var(--gradient);
            background-size: 400% 400%;
            animation: gradient 12s ease infinite;
            display: flex; align-items: center; justify-content: center;
            padding: 50px;
        }
        @keyframes gradient { 0%,100%{background-position:0% 50%} 50%{background-position:100% 50%} }

        .login-card {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(16px);
            border-radius: 24px;
            border: 1px solid rgba(255,255,255,0.2);
            padding: 50px 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-20px)} }

        h2 { text-align:center; color:#fff; font-size:28px; margin-bottom:10px; }
        p.tagline { text-align:center; color:rgba(255,255,255,0.8); margin-bottom:30px; font-size:15px; }

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

        .form-login button {
            width:100%; padding:16px; border:none; border-radius:50px;
            background: linear-gradient(45deg, #4facfe, #00f2fe);
            color:#fff; font-size:18px; font-weight:600; cursor:pointer;
            transition:all .4s; margin-top:10px;
        }
        .form-login button:hover { transform:translateY(-4px); box-shadow:0 15px 35px rgba(0,0,0,0.4); }

        .error { background:rgba(255,107,107,0.25); color:#ff6b6b; padding:12px; border-radius:12px; text-align:center; margin:15px 0; font-weight:500; }
        .links { text-align:center; margin-top:25px; }
        .links a { color:rgba(255,255,255,0.8); text-decoration:none; font-size:14px; }
        .links a:hover { text-decoration:underline; }
    </style>
</head>
<body>
    <div class="login-main">
        <div class="login-card">
            <h2>Schedules Store</h2>
            <p class="tagline">Đăng nhập tài khoản của bạn</p>

            <?php if($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" autocomplete="off" class="form-login">
                <div class="input-group">
                    <input type="email" name="email" placeholder="Email đăng nhập" required autofocus value="<?= htmlspecialchars($email ?? '') ?>">
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="Mật khẩu" required>
                </div>
                <button type="submit">Đăng Nhập Ngay</button>
            </form>

            <div class="links">
                <a href="index.php?page=register">Chưa có tài khoản?</a> • 
                <a href="index.php?page=forgot_password">Quên mật khẩu?</a>
            </div>
        </div>
    </div>
</body>