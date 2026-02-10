<?php
session_start();
require_once 'db.php';

$session_id = session_id();

$stmt = $conn->prepare("
    SELECT c.*, t.title, t.main_image, t.price, t.duration 
    FROM cart c 
    JOIN tours t ON c.tour_id = t.id 
    WHERE c.session_id = ?
");
$stmt->bind_param("s", $session_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Giỏ hàng - THTRAVEL</title>
    <!-- head giống tour-detail -->
</head>
<body>
    <div class="container my-5 pt-5">
        <h2>Giỏ hàng của bạn</h2>

        <?php if (empty($cart_items)): ?>
            <div class="alert alert-info text-center">
                Giỏ hàng trống. <a href="index.php" class="btn btn-success btn-sm">Tiếp tục mua sắm</a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($cart_items as $item): ?>
                    <div class="col-12 mb-3">
                        <div class="card">
                            <div class="row g-0">
                                <div class="col-md-3">
                                    <img src="<?= htmlspecialchars($item['main_image']) ?>" class="img-fluid" alt="">
                                </div>
                                <div class="col-md-9">
                                    <div class="card-body">
                                        <h5><?= htmlspecialchars($item['title']) ?></h5>
                                        <p>Giá: <?= number_format($item['price']) ?> ₫ x <?= $item['quantity'] ?> chỗ</p>
                                        <p>Thời gian: <?= htmlspecialchars($item['duration']) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-end mt-4">
                <h4>Tổng tiền: <span class="text-danger"><?= number_format($total) ?> ₫</span></h4>
                <a href="checkout.php" class="btn btn-success btn-lg">Tiến hành thanh toán</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>