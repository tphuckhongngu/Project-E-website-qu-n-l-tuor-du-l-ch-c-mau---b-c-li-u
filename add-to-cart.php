<?php
session_start();
require_once 'db.php'; // kết nối $conn (mysqli)

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'cart_count' => 0
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['tour_id']) || !is_numeric($_POST['tour_id'])) {
    $response['message'] = 'Yêu cầu không hợp lệ';
    echo json_encode($response);
    exit;
}

$tour_id = (int)$_POST['tour_id'];
$quantity = isset($_POST['quantity']) && is_numeric($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;

// Lấy thông tin tour
$stmt = $conn->prepare("SELECT id, title, price, available_seats FROM tours WHERE id = ? AND is_active = 1");
$stmt->bind_param("i", $tour_id);
$stmt->execute();
$result = $stmt->get_result();
$tour = $result->fetch_assoc();

if (!$tour) {
    $response['message'] = 'Tour không tồn tại hoặc đã ngừng hoạt động';
    echo json_encode($response);
    exit;
}

if ($tour['available_seats'] < $quantity) {
    $response['message'] = 'Tour đã hết chỗ hoặc không đủ số lượng yêu cầu';
    echo json_encode($response);
    exit;
}

$session_id = session_id();
$user_id = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null; // nếu login

// Add vào giỏ tạm (cart)
$check = $conn->prepare("SELECT id, quantity FROM cart WHERE tour_id = ? AND session_id = ?");
$check->bind_param("is", $tour_id, $session_id);
$check->execute();
$existing = $check->get_result()->fetch_assoc();

if ($existing) {
    $new_qty = $existing['quantity'] + $quantity;
    $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $update->bind_param("ii", $new_qty, $existing['id']);
    $update->execute();
} else {
    $insert = $conn->prepare("INSERT INTO cart (session_id, user_id, tour_id, quantity, price) VALUES (?, ?, ?, ?, ?)");
    $insert->bind_param("siidi", $session_id, $user_id, $tour_id, $quantity, $tour['price']);
    $insert->execute();
}

// Giảm chỗ
$update_seats = $conn->prepare("UPDATE tours SET available_seats = available_seats - ? WHERE id = ?");
$update_seats->bind_param("ii", $quantity, $tour_id);
$update_seats->execute();

// Tạo đơn hàng pending ngay (để hiện ở order.php)
$total = $tour['price'] * $quantity;
$insert_order = $conn->prepare("
    INSERT INTO orders (user_id, total_amount, status, created_at) 
    VALUES (?, ?, 'pending', NOW())
");
$insert_order->bind_param("id", $user_id, $total);
$insert_order->execute();
$order_id = $conn->insert_id;

// Thêm item vào order_items
$insert_item = $conn->prepare("
    INSERT INTO order_items (order_id, tour_id, quantity, price) 
    VALUES (?, ?, ?, ?)
");
$insert_item->bind_param("iiid", $order_id, $tour_id, $quantity, $tour['price']);
$insert_item->execute();

// Xóa giỏ tạm (vì đã chuyển thành đơn)
$delete_cart = $conn->prepare("DELETE FROM cart WHERE session_id = ? AND tour_id = ?");
$delete_cart->bind_param("si", $session_id, $tour_id);
$delete_cart->execute();

$response['success'] = true;
$response['message'] = "Đã gửi yêu cầu đặt chỗ thành công! Đơn hàng đang chờ xác nhận.";
$response['cart_count'] = 0; // giỏ tạm đã xóa

echo json_encode($response);
$conn->close();
exit;
?>