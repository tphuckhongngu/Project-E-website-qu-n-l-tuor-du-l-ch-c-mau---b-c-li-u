<?php
session_start();

// Xóa toàn bộ session (đăng xuất user)
session_unset();          // Xóa tất cả biến session
session_destroy();        // Hủy session hoàn toàn

// Optional: Xóa cookie nếu có (nếu dùng remember me)
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/'); // Xóa cookie
}

// Redirect về trang chủ hoặc trang login
header("Location: index.php");  // Hoặc login.php nếu muốn quay về login
exit;
?>