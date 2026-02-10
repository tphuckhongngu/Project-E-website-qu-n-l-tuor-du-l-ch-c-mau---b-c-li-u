<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user']['id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: tour-detail.php");
    exit;
}

$tour_slug = $_POST['tour_slug'] ?? '';
$desired_date = $_POST['desired_date'] ?? '';
$note = trim($_POST['note'] ?? '');

if (empty($tour_slug) || empty($desired_date)) {
    $_SESSION['message'] = "Vui lòng điền đầy đủ thông tin!";
    header("Location: tour-detail.php?slug=" . urlencode($tour_slug));
    exit;
}

// Lấy thông tin tour
$stmt = $conn->prepare("SELECT id, title, price_sale, code FROM destinations WHERE slug = ?");
$stmt->bind_param("s", $tour_slug);
$stmt->execute();
$result = $stmt->get_result();

if ($tour = $result->fetch_assoc()) {
    $tour_id = $tour['id'];
    $tour_name = $tour['title'];
    $price = $tour['price_sale'] ?? 5990000;
    $tour_code = $tour['code'] ?? 'CMTR2026001';
    $detail_link = "tour-detail.php?slug=" . urlencode($tour_slug);
} else {
    $_SESSION['message'] = "Tour không tồn tại!";
    header("Location: index.php");
    exit;
}
$stmt->close();

$user_id = (int)$_SESSION['user']['id'];
$status = 'pending';

// Lưu vào bảng orders (khớp cột hiện tại)
$stmt = $conn->prepare("
    INSERT INTO orders (user_id, tour_name, tour_date, price, status, people, detail_link, created_at)
    VALUES (?, ?, ?, ?, ?, 1, ?, NOW())
");
$stmt->bind_param("isssis", $user_id, $tour_name, $desired_date, $price, $status, $detail_link);

if ($stmt->execute()) {
    $_SESSION['booking_success'] = true;
    header("Location: tour-detail.php?slug=" . urlencode($tour_slug));
} else {
    $_SESSION['message'] = "Lỗi khi lưu đơn hàng: " . $conn->error;
    header("Location: tour-detail.php?slug=" . urlencode($tour_slug));
}

$stmt->close();
$conn->close();
exit;
?>