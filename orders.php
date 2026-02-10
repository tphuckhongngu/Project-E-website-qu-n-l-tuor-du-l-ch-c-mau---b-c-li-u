<?php
session_start();
require_once 'db.php'; // file kết nối $conn (mysqli)

$user = $_SESSION['user'] ?? null;

if (!$user) {
    // Chưa login → yêu cầu đăng nhập
    header("Location: login.php?redirect=orders.php");
    exit;
}

$user_id = $user['id']; // giả sử $_SESSION['user'] có key 'id' từ bảng users

// Query tất cả đơn hàng của user này
$stmt = $conn->prepare("
    SELECT o.id, o.total_amount, o.status, o.created_at,
           GROUP_CONCAT(t.title SEPARATOR ', ') AS tour_titles,
           GROUP_CONCAT(t.main_image SEPARATOR ',') AS tour_images
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN tours t ON oi.tour_id = t.id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);

// Thống kê nhanh
$total_orders = count($orders);
$completed = 0; $pending = 0; $cancelled = 0;
foreach ($orders as $order) {
    if ($order['status'] === 'paid' || $order['status'] === 'confirmed') $completed++;
    if ($order['status'] === 'pending') $pending++;
    if ($order['status'] === 'cancelled') $cancelled++;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Đơn hàng của tôi - THTRAVEL</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="orders.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@700;800&family=Playfair+Display:wght@700&display=swap');
    </style>
</head>
<body class="body-bg">

    <!-- Navbar (copy từ index.php của anh) -->
    <!-- ... paste navbar giống hệt index.php ... -->

    <div class="container my-5 pt-5">
        <h2 class="title-glow text-center mb-5 fw-bold">
            <i class="fas fa-ticket-alt me-2"></i>ĐƠN HÀNG CỦA TÔI
        </h2>

        <!-- Thống kê nhanh (dữ liệu thật) -->
        <div class="row g-4 mb-5 stats-row">
            <div class="col-md-3 col-6">
                <div class="stat-card text-center p-4 rounded-4 shadow">
                    <h5 class="fw-bold text-primary"><?= $total_orders ?></h5>
                    <p class="text-muted small mb-0">Tổng đơn</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card text-center p-4 rounded-4 shadow">
                    <h5 class="fw-bold text-success"><?= $completed ?></h5>
                    <p class="text-muted small mb-0">Hoàn thành</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card text-center p-4 rounded-4 shadow">
                    <h5 class="fw-bold text-warning"><?= $pending ?></h5>
                    <p class="text-muted small mb-0">Chờ xác nhận</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card text-center p-4 rounded-4 shadow">
                    <h5 class="fw-bold text-danger"><?= $cancelled ?></h5>
                    <p class="text-muted small mb-0">Đã hủy</p>
                </div>
            </div>
        </div>

        <!-- Danh sách đơn hàng từ DB -->
        <div class="order-list">
            <?php if (empty($orders)): ?>
                <div class="alert alert-info text-center py-5">
                    <i class="fas fa-inbox fs-1 mb-3 d-block"></i>
                    Bạn chưa có đơn hàng nào.<br>
                    <p class="mt-2">Các tour bạn đặt sẽ hiển thị ở đây sau khi hoàn tất thanh toán.</p>
                    <a href="index.php" class="btn btn-success mt-3">Khám phá tour ngay</a>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): 
                    $images = explode(',', $order['tour_images'] ?? '');
                    $first_image = $images[0] ?: 'anhsale/default-tour.jpg';
                    $badge_class = match($order['status']) {
                        'pending' => 'bg-warning',
                        'confirmed' => 'bg-info',
                        'paid' => 'bg-success',
                        'cancelled' => 'bg-danger',
                        default => 'bg-secondary'
                    };
                    $badge_text = match($order['status']) {
                        'pending' => 'Chờ xác nhận',
                        'confirmed' => 'Đã xác nhận',
                        'paid' => 'Đã thanh toán',
                        'cancelled' => 'Đã hủy',
                        default => 'Không xác định'
                    };
                ?>
                    <div class="order-card mb-5 shadow-xl rounded-4 overflow-hidden">
                        <div class="row g-0">
                            <div class="col-md-4">
                                <img src="<?= htmlspecialchars($first_image) ?>" 
                                     class="img-fluid h-100 object-fit-cover" 
                                     alt="Tour trong đơn hàng">
                            </div>
                            <div class="col-md-8">
                                <div class="p-4 p-md-5 content-bg">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h4 class="fw-bold mb-1 text-dark">
                                                <?= htmlspecialchars($order['tour_titles'] ?: 'Đơn hàng #' . $order['id']) ?>
                                            </h4>
                                        </div>
                                        <span class="badge <?= $badge_class ?> fs-5 px-4 py-2 text-dark">
                                            <?= $badge_text ?>
                                        </span>
                                    </div>

                                    <hr class="my-4 border-success">

                                    <div class="row text-center">
                                        <div class="col-4">
                                            <p class="fw-bold mb-0">Tổng tiền</p>
                                            <h5 class="text-success"><?= number_format($order['total_amount']) ?> ₫</h5>
                                        </div>
                                        <div class="col-4">
                                            <p class="fw-bold mb-0">Mã đơn</p>
                                            <h5 class="text-dark">#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></h5>
                                        </div>
                                        <div class="col-4">
                                            <p class="fw-bold mb-0">Ngày đặt</p>
                                            <p class="text-muted"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
                                        </div>
                                    </div>

                                    <div class="mt-4 d-flex gap-3 flex-wrap">
                                        <a href="order-detail.php?id=<?= $order['id'] ?>" class="btn btn-outline-success flex-fill">
                                            <i class="fas fa-eye me-2"></i>Xem chi tiết
                                        </a>
                                        <?php if ($order['status'] === 'pending'): ?>
                                            <button class="btn btn-outline-danger flex-fill" onclick="cancelOrder(<?= $order['id'] ?>)">
                                                <i class="fas fa-times me-2"></i>Hủy đơn
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

    <!-- Footer (copy từ index) -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function cancelOrder(orderId) {
            if (confirm("Bạn có chắc muốn hủy đơn hàng này?")) {
                // Sau này thay bằng AJAX hoặc redirect đến file xử lý hủy
                window.location.href = "cancel-order.php?id=" + orderId;
            }
        }
    </script>
</body>
</html>