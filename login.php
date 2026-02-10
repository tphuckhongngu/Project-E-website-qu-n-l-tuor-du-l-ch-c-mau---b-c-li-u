<?php
// KHÔNG CÓ KHOẢNG TRẮNG, DÒNG TRỐNG, ECHO TRƯỚC ĐÂY
session_start();
include 'db.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $errors[] = "Vui lòng điền đầy đủ thông tin!";
    }
    if ($password !== $confirm) {
        $errors[] = "Mật khẩu xác nhận không khớp!";
    }
    if (strlen($password) < 6) {
        $errors[] = "Mật khẩu phải ít nhất 6 ký tự!";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ!";
    }

    $check = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $check->bind_param("ss", $email, $username);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $errors[] = "Email hoặc tên đăng nhập đã tồn tại!";
    }
    $check->close();

    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username, email, phone, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $phone, $hashed);

        if ($stmt->execute()) {
            $user_id = $conn->insert_id;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['user'] = [
                'id' => $user_id,
                'username' => $username,
                'email' => $email,
                'phone' => $phone
            ];

            header("Location: index.php");
            exit(); // DỪNG NGAY, KHÔNG CHẠY HTML BÊN DƯỚI
        } else {
            $errors[] = "Đăng ký thất bại: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - THTRAVEL</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">

    <style>
        body { margin: 0; padding: 0; font-family: 'Inter', sans-serif; background: #f8f9fa; height: 100vh; overflow: hidden; }
        .wave { position: fixed; bottom: 0; left: 0; width: 100%; z-index: -1; }
        .login-container { position: relative; width: 100%; height: 100vh; display: grid; grid-template-columns: repeat(2, 1fr); grid-gap: 7rem; padding: 0 2rem; }
        .img { display: flex; justify-content: flex-end; align-items: center; }
        .img img { width: 500px; }
        .login-content { display: flex; align-items: center; text-align: center; }
        .login-form { width: 360px; }
        .login-form img { height: 100px; margin-bottom: 1rem; }
        .login-form h2 { margin: 15px 0; color: #00a97f; text-transform: uppercase; font-weight: 700; font-size: 2.5rem; font-family: 'Playfair Display', serif; }
        .input-div { position: relative; display: grid; grid-template-columns: 7% 93%; margin: 25px 0; padding: 5px 0; border-bottom: 2px solid #d9d9d9; }
        .input-div.one { margin-top: 0; }
        .i { color: #d9d9d9; display: flex; justify-content: center; align-items: center; }
        .i i { transition: .3s; }
        .input-div > div { position: relative; height: 45px; }
        .input-div > div > h5 { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #999; font-size: 18px; transition: .3s; }
        .input-div > div > input { position: absolute; left: 0; top: 0; width: 100%; height: 100%; border: none; outline: none; background: none; padding: 0.5rem 0.7rem; font-size: 1.2rem; color: #555; font-family: 'Inter', sans-serif; }
        .input-div.pass { margin-bottom: 10px; }
        .input-div.one.focus .i i,
        .input-div.pass.focus .i i { color: #00a97f; }
        .input-div.one.focus div h5,
        .input-div.one.focus div input,
        .input-div.pass.focus div h5,
        .input-div.pass.focus div input { top: -5px; font-size: 15px; }
        .input-div.one.focus,
        .input-div.pass.focus { border-bottom: 2px solid #00a97f; }
        .btn-login {
            display: block;
            width: 100%;
            height: 50px;
            border-radius: 25px;
            outline: none;
            border: none;
            background-image: linear-gradient(to right, #00a97f, #198754, #00a97f);
            background-size: 200%;
            color: #fff;
            font-family: 'Inter', sans-serif;
            text-transform: uppercase;
            font-weight: 600;
            margin: 1rem 0;
            cursor: pointer;
            transition: .5s;
        }
        .btn-login:hover { background-position: right; }
        .forgot { display: block; text-align: right; text-decoration: none; color: #999; font-size: 0.9rem; transition: .3s; }
        .forgot:hover { color: #00a97f; }
        .error-msg { color: #dc3545; font-size: 0.9rem; margin: 10px 0; text-align: center; }

        @media screen and (max-width: 1050px) {
            .login-container { grid-template-columns: 1fr; }
            .img { display: none; }
            .wave { display: none; }
            .login-content { justify-content: center; }
        }
        @media screen and (max-width: 600px) {
            .login-form { width: 90%; }
        }
    </style>

    <script src="https://dadevmikey.github.io/The-Holiday-Library/ChristmasOverlay.js"></script>
    <script src="https://dadevmikey.github.io/The-Holiday-Library/NewYearsOverlay.js"></script>
</head>
<body>

    <audio id="festiveMusic" loop>
        <source src="video/VIDEODAU1.mp3" type="audio/mpeg">
        Your browser does not support the audio element.
    </audio>

    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold fs-3 text-success" href="index.php">
                <i class="fas fa-plane"></i> THTRAVEL
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Trang chủ</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Tìm kiếm</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Điểm đến</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Tour nổi bật</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Liên Hệ</a></li>
                    <li class="nav-item position-relative">
                        <span id="toggleFestiveBtn" class="ms-2" style="font-size: 1.4rem; cursor: pointer;" title="Menu lễ hội">☰</span>
                        <div id="festiveMenu" class="position-absolute bg-white shadow rounded p-3" style="top: 100%; right: 0; min-width: 180px; display: none; z-index: 1050;">
                            <button id="toggleEffectsBtn" class="btn btn-outline-success w-100 mb-2">
                                <i class="fas fa-snowflake me-2"></i> Hiệu ứng: <span id="effectsStatus">BẬT</span>
                            </button>
                            <button id="toggleSoundBtn" class="btn btn-outline-primary w-100">
                                <i class="fas fa-volume-up me-2"></i> Âm thanh: <span id="soundStatus">BẬT</span>
                            </button>
                            <button id="toggleBrightnessBtn" class="btn btn-outline-warning w-100">
                                <i class="fas fa-sun me-2"></i> Độ sáng: <span id="brightnessStatus">BÌNH THƯỜNG</span>
                            </button>
                        </div>
                    </li>
                </ul>
                <div class="d-flex align-items-center ms-lg-4 mt-3 mt-lg-0 gap-2">
                    <a href="login.php" class="btn btn-success">Đăng nhập</a>
                </div>
            </div>
        </div>
    </nav>

    <img class="wave" src="img/wave.png" alt="wave background">

    <div class="container login-container">
        <div class="img">
            <img src="img/bg.svg" alt="background svg">
        </div>
        <div class="login-content">
            <form class="login-form" method="POST" action="">
                <img src="img/avatar.svg" alt="avatar">
                <h2 class="title">Đăng ký THTRAVEL</h2>

                <?php if (!empty($errors)): ?>
                    <div class="error-msg">
                        <?php foreach ($errors as $err): ?>
                            <div><?php echo $err; ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="input-div one">
                    <div class="i"><i class="fas fa-user"></i></div>
                    <div class="div">
                        <h5>Tên đăng nhập</h5>
                        <input type="text" name="username" class="input" required>
                    </div>
                </div>

                <div class="input-div one">
                    <div class="i"><i class="fas fa-envelope"></i></div>
                    <div class="div">
                        <h5>Email</h5>
                        <input type="email" name="email" class="input" required>
                    </div>
                </div>

                <div class="input-div one">
                    <div class="i"><i class="fas fa-phone"></i></div>
                    <div class="div">
                        <h5>Số điện thoại (tùy chọn)</h5>
                        <input type="tel" name="phone" class="input">
                    </div>
                </div>

                <div class="input-div pass">
                    <div class="i"><i class="fas fa-lock"></i></div>
                    <div class="div">
                        <h5>Mật khẩu</h5>
                        <input type="password" name="password" class="input" required>
                    </div>
                </div>

                <div class="input-div pass">
                    <div class="i"><i class="fas fa-lock"></i></div>
                    <div class="div">
                        <h5>Xác nhận mật khẩu</h5>
                        <input type="password" name="confirm_password" class="input" required>
                    </div>
                </div>

                <button type="submit" class="btn-login">Đăng ký ngay</button>

                <p class="mt-3">Đã có tài khoản? <a href="login.php" class="text-success fw-bold">Đăng nhập</a></p>
            </form>
        </div>
    </div>

    <script>
        const inputs = document.querySelectorAll(".input");

        function addcl() {
            let parent = this.parentNode.parentNode;
            parent.classList.add("focus");
        }

        function remcl() {
            let parent = this.parentNode.parentNode;
            if (this.value == "") {
                parent.classList.remove("focus");
            }
        }

        inputs.forEach(input => {
            input.addEventListener("focus", addcl);
            input.addEventListener("blur", remcl);
        });
    </script>

    <script>
        let effectsEnabled = true;
        let soundEnabled = false;
        let brightnessNormal = true;
        let snowInstance = null;
        let fireworksInstance = null;
        const music = document.getElementById('festiveMusic');

        document.addEventListener('DOMContentLoaded', function() {
            if (typeof christmasOverlaySnow !== 'undefined') {
                snowInstance = christmasOverlaySnow;
                snowInstance.enable({ snowflakeCount: 120, zIndex: 999 });
            }
            if (typeof FireworksOverlay !== 'undefined') {
                fireworksInstance = new FireworksOverlay({
                    particleCount: 90,
                    interval: 700,
                    zIndex: 1000
                });
                fireworksInstance.startAnimation();
            }
            if (music) {
                music.volume = 0.3;
                music.play().catch(err => console.log('Autoplay blocked:', err));
            }
            // ... phần toggle buttons, dark mode, v.v. giống hệt code cũ của bạn ...
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>