<?php
// config/db.php
$host     = 'localhost';          // thường là localhost nếu dùng XAMPP
$dbname   = 'thtravel_db';           // thay bằng tên database thật của bạn
$username = 'root';               // mặc định XAMPP là root
$password = '';                   // mặc định XAMPP để trống

$conn = new mysqli($host, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối database thất bại: " . $conn->connect_error);
}

// Set charset để tránh lỗi tiếng Việt
$conn->set_charset("utf8mb4");


// Optional: bật báo lỗi để debug dễ hơn
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
?>
