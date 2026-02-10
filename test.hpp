<?php
session_start();
$user = $_SESSION['user'] ?? null;

// Kết nối database (giả sử bạn có file db.php)
require_once 'db.php'; // chứa $conn = new mysqli(...);

$tour = null;

$tour_slug = $_GET['slug'] ?? $_GET['id'] ?? '';  // hỗ trợ cả slug và id cũ

if ($tour_slug) {
    // Ưu tiên tìm theo slug
    $stmt = $conn->prepare("
        SELECT * FROM tours 
        WHERE slug = ? AND is_active = 1 
        LIMIT 1
    ");
    $stmt->bind_param("s", $tour_slug);
    $stmt->execute();
    $result = $stmt->get_result();
    $tour = $result->fetch_assoc();

    // Nếu không tìm thấy slug, thử tìm theo id số (tùy chọn dự phòng)
    if (!$tour && is_numeric($tour_slug)) {
        $stmt = $conn->prepare("SELECT * FROM tours WHERE id = ? AND is_active = 1 LIMIT 1");
        $stmt->bind_param("i", $tour_slug);
        $stmt->execute();
        $result = $stmt->get_result();
        $tour = $result->fetch_assoc();
    }

    $stmt->close();
}

if (!$tour) {
    // Tour không tồn tại → chuyển về trang chủ hoặc hiển thị thông báo
    header("Location: index.php?error=tour_not_found");
    exit;
    // Hoặc: echo "<h1 style='text-align:center; margin:100px;'>Không tìm thấy tour!</h1>"; exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tour['title'] ?? 'Chi tiết Tour') ?> - THTRAVEL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css"> <!-- file CSS chung -->

    <style>
        /* Thêm style tùy chỉnh cho trang chi tiết (giữ nguyên như file gốc) */
        .tour-hero { 
            background: linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.55)), 
                        url('<?= htmlspecialchars($tour['main_image'] ?? 'anhsale/1.png') ?>') center/cover no-repeat; 
            background-size: cover; 
            background-position: center; 
            color: white; 
            padding: 140px 0 100px; 
            text-align: center; 
            position: relative;
            min-height: 500px;
        }
        .tour-hero::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0.3), transparent 50%, rgba(0,0,0,0.4));
        }
        .tour-hero h1 { 
            font-size: 3.8rem; 
            font-weight: 800; 
            text-shadow: 0 4px 15px rgba(0,0,0,0.8); 
            z-index: 2; 
            position: relative; 
        }
        .tour-hero .lead { 
            font-size: 1.4rem; 
            z-index: 2; 
            position: relative; 
        }
        .sticky-sidebar { 
            position: sticky; 
            top: 120px; 
        }
        .price-tag { 
            font-size: 2.5rem; 
            font-weight: 900; 
        }
        .old-price { 
            text-decoration: line-through; 
            opacity: 0.7; 
            font-size: 1.4rem; 
        }
        .photo-gallery .img-fluid { 
            transition: transform 0.4s; 
            cursor: pointer; 
        }
        .photo-gallery .img-fluid:hover { 
            transform: scale(1.05); 
        }
        .review-card { 
            border-left: 5px solid #198754; 
        }
        .faq-accordion .accordion-button { 
            font-weight: 600; 
        }
        .map-embed { 
            height: 400px; 
            border-radius: 16px; 
            overflow: hidden; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.15); 
        }
    </style>
</head>
<body>

    <!-- Nhạc nền lễ hội (đồng bộ với index) -->
    <audio id="festiveMusic" loop>
        <source src="video/VIDEODAU1.mp3" type="audio/mpeg">
        Your browser does not support the audio element.
    </audio>

    <!-- Navbar (giữ nguyên như file gốc) -->
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
                    <?php if ($user): ?>
                        <div class="dropdown">
                            <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user me-2"></i>
                                Xin chào, <?= htmlspecialchars($user['username'] ?? 'Bạn') ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="profile.html"><i class="fas fa-user-circle me-2"></i>Hồ sơ cá nhân</a></li>
                                <li><a class="dropdown-item" href="orders.php"><i class="fas fa-receipt me-2"></i>Đơn hàng của tôi</a></li>
                                <li><a class="dropdown-item" href="favorites.php"><i class="fas fa-heart me-2"></i>Tour yêu thích</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-success">
                            <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero section -->
    <section class="tour-hero" id="tourHeroSection">
        <div class="container position-relative">
            <h1 class="mb-3"><?= htmlspecialchars($tour['title']) ?></h1>
            <p class="lead mt-3"><?= htmlspecialchars($tour['subtitle'] ?? '') ?></p>
            
            <a href="index.php" class="btn btn-outline-light btn-lg mt-4 px-5">
                <i class="fas fa-arrow-left me-2"></i>Quay về trang chủ
            </a>
        </div>
    </section>

    <div class="container my-5 pt-5">
        <div class="row g-5">
            <!-- Nội dung chính bên trái -->
            <div class="col-lg-8">

                <!-- Carousel ảnh tour -->
                <div id="tourCarousel" class="carousel slide mb-5 rounded-4 shadow-lg overflow-hidden" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <img src="<?= htmlspecialchars($tour['main_image'] ?? 'anhsale/1.png') ?>" 
                                 class="d-block w-100" 
                                 alt="<?= htmlspecialchars($tour['title']) ?>" 
                                 style="height: 500px; object-fit: cover;">
                        </div>
                        <!-- Nếu có thêm ảnh từ bảng tour_images thì loop ở đây -->
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#tourCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#tourCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>

                <!-- Mô tả chi tiết -->
                <div class="bg-white p-5 rounded-4 shadow mb-5">
                    <h3 class="mb-4"><i class="bi bi-info-circle-fill text-success me-2"></i>Mô tả tour</h3>
                    <div style="text-align: justify;">
                        <?= nl2br(htmlspecialchars_decode($tour['description'])) ?>
                    </div>
                </div>

                <!-- Thông tin cơ bản -->
                <div class="bg-white p-5 rounded-4 shadow mb-5">
                    <h4 class="mb-4"><i class="bi bi-list-check text-success me-2"></i>Thông tin tour</h4>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <strong><i class="bi bi-clock me-2 text-success"></i>Thời gian:</strong> 
                            <span class="fw-bold"><?= htmlspecialchars($tour['duration'] ?? 'Chưa cập nhật') ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <strong><i class="bi bi-geo-alt me-2 text-success"></i>Khởi hành từ:</strong> 
                            <span><?= htmlspecialchars($tour['departure_from'] ?? 'TP. Hồ Chí Minh') ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <strong><i class="bi bi-currency-dollar me-2 text-success"></i>Giá tour:</strong> 
                            <span class="price-tag text-danger">
                                <?php if (!empty($tour['old_price']) && $tour['old_price'] > 0): ?>
                                    <del class="old-price text-muted small"><?= number_format($tour['old_price']) ?> đ</del><br>
                                <?php endif; ?>
                                <?= number_format($tour['price']) ?> đ
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <strong><i class="bi bi-people me-2 text-success"></i>Số chỗ còn:</strong> 
                            <span class="text-success fw-bold fs-5">Còn <?= $tour['available_seats'] ?? '?' ?> chỗ</span>
                        </li>
                        <?php if (!empty($tour['start_date'])): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <strong><i class="bi bi-calendar-check me-2 text-success"></i>Khởi hành mẫu:</strong> 
                            <span><?= date('d/m/Y', strtotime($tour['start_date'])) ?></span>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Lịch trình chi tiết (có thể lấy từ DB sau) -->
                <div class="bg-white p-5 rounded-4 shadow mb-5">
                    <h4 class="mb-4"><i class="bi bi-calendar-week text-success me-2"></i>Lịch trình chi tiết</h4>
                    <div class="accordion" id="scheduleAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#day1">
                                    Ngày 1: Khởi hành - Đến Cà Mau
                                </button>
                            </h2>
                            <div id="day1" class="accordion-collapse collapse show">
                                <div class="accordion-body">
                                    <p>Khởi hành từ TP.HCM bằng xe giường nằm cao cấp hoặc máy bay. Đến Cà Mau, check-in Mũi Cà Mau - điểm cực Nam Tổ quốc, ngắm biển Đông và biển Tây giao nhau.</p>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#day2">
                                    Ngày 2: Khám phá Vườn quốc gia U Minh Hạ
                                </button>
                            </h2>
                            <div id="day2" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    <p>Thuyền tham quan rừng ngập mặn, xem chim, cá sấu, trải nghiệm văn hóa địa phương.</p>
                                </div>
                            </div>
                        </div>
                        <!-- Thêm ngày khác nếu cần -->
                    </div>
                </div>

                <!-- Photo Gallery (có thể lấy từ bảng tour_images sau) -->
                <div class="bg-white p-5 rounded-4 shadow mb-5">
                    <h4 class="mb-4"><i class="bi bi-images text-success me-2"></i>Thư viện ảnh</h4>
                    <div class="row g-3 photo-gallery">
                        <div class="col-md-4"><img src="<?= htmlspecialchars($tour['main_image'] ?? 'anhsale/1.png') ?>" class="img-fluid rounded" alt="Ảnh chính"></div>
                        <!-- Thêm ảnh phụ nếu có -->
                    </div>
                </div>

                <!-- Review khách hàng (tạm hard-code, sau có thể lấy từ DB) -->
                <div class="bg-white p-5 rounded-4 shadow mb-5">
                    <h4 class="mb-4"><i class="bi bi-star-fill text-warning me-2"></i>Đánh giá từ khách hàng</h4>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="review-card p-4 bg-light rounded">
                                <p>"Tour rất đáng giá! Mũi Cà Mau đẹp mê ly, hướng dẫn viên nhiệt tình."</p>
                                <strong>- Anh Minh, TP.HCM</strong> <span class="text-warning">★★★★★</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="review-card p-4 bg-light rounded">
                                <p>"Lịch trình hợp lý, ăn uống ngon, chỗ ở sạch sẽ."</p>
                                <strong>- Chị Lan, Hà Nội</strong> <span class="text-warning">★★★★★</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bản đồ -->
                <div class="bg-white p-5 rounded-4 shadow">
                    <h4 class="mb-4"><i class="bi bi-geo-alt text-success me-2"></i>Vị trí tour</h4>
                    <div class="map-embed">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3928.911057922!2d104.999!3d8.999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zOHkwMyc1NC4wIk4gMTA0wrAwMSc1NC4wIkU!5e0!3m2!1svi!2s!4v123456789" width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>

            <!-- Sidebar đặt tour -->
            <div class="col-lg-4">
                <div class="card shadow-lg border-0 rounded-4 sticky-sidebar" style="max-width: 380px; margin: 0 auto;">
                    <div class="card-body p-4">
                        <h5 class="text-center mb-4 fw-bold text-success" style="font-size: 1.4rem;">
                            Đặt tour ngay hôm nay
                        </h5>

                        <div class="text-center mb-4 p-3 bg-light rounded-3 border border-success border-2" style="font-size: 0.95rem;">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <i class="fas fa-map-marker-alt text-success me-2"></i>
                                <strong><?= htmlspecialchars($tour['title']) ?></strong>
                            </div>
                            <span class="badge bg-primary px-3 py-1 fs-6">
                                Mã tour: <?= htmlspecialchars($tour['code'] ?? 'Chưa có') ?>
                            </span>
                        </div>

                        <p class="text-center price-tag text-danger mb-4 fw-bold" style="font-size: 1.8rem;">
                            <?= number_format($tour['price']) ?> đ
                        </p>

                        <form id="bookingForm">
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Ngày khởi hành mong muốn</label>
                                <input type="date" id="desired_date" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small">Ghi chú đặc biệt</label>
                                <textarea id="note" class="form-control" rows="3" 
                                          placeholder="Ví dụ: Ăn chay, trẻ em, yêu cầu phòng riêng, dị ứng hải sản..."></textarea>
                            </div>

                            <button type="button" class="btn btn-danger w-100 py-3 fw-bold mb-3" id="submitBooking" style="font-size: 1.1rem;">
                                <i class="fas fa-paper-plane me-2"></i>Gửi yêu cầu đặt chỗ
                            </button>
                        </form>

                        <small class="text-muted d-block text-center mb-3" style="font-size: 0.85rem;">
                            Chúng tôi sẽ liên hệ xác nhận trong vòng 5-10 phút qua điện thoại/Zalo.
                        </small>

                        <div class="text-center">
                            <a href="tel:0909123456" class="btn btn-success w-100 mb-2 py-2" style="font-size: 0.95rem;">
                                <i class="fas fa-phone-alt me-2"></i>Gọi ngay: 0909 123 456
                            </a>
                            <a href="https://zalo.me/0909123456" target="_blank" class="btn btn-primary w-100 py-2" style="font-size: 0.95rem;">
                                <i class="bi bi-chat-dots-fill me-2"></i>Liên hệ Zalo
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal thành công -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header bg-success text-white border-0 pb-0">
                    <h5 class="modal-title fw-bold fs-4" id="successModalLabel">Thành công!</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-5">
                    <i class="fas fa-check-circle text-success" style="font-size: 5.5rem; margin-bottom: 1rem;"></i>
                    <h3 class="fw-bold mb-3">Đặt chỗ thành công!</h3>
                    <p class="text-muted fs-5 mb-4">
                        Cảm ơn bạn đã tin tưởng THTRAVEL!<br>
                        Chúng tôi sẽ liên hệ xác nhận trong vòng <strong>5-10 phút</strong> qua điện thoại hoặc Zalo.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer text-white py-5" style="background: #0d1a26;">
        <div class="container text-center">
            <p>© 2026 THTRAVEL. All rights reserved. | Hotline: 0909 123 456</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Thư viện lễ hội (nếu dùng) -->
    <script src="https://dadevmikey.github.io/The-Holiday-Library/ChristmasOverlay.js"></script>
    <script src="https://dadevmikey.github.io/The-Holiday-Library/NewYearsOverlay.js"></script>

    <!-- Xử lý form booking (giữ nguyên) -->
    <script>
        document.getElementById('submitBooking').addEventListener('click', function() {
            const desiredDate = document.querySelector('#desired_date').value;
            const note = document.querySelector('#note').value;

            if (!desiredDate) {
                alert('Vui lòng chọn ngày khởi hành mong muốn!');
                return;
            }

            const successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();

            document.getElementById('bookingForm').reset();

            // Nếu muốn gửi AJAX thật → thêm fetch về add-booking.php
        });
    </script>

    <!-- File chung (đồng bộ festive, dark mode, v.v.) -->
    <script src="common.js"></script>

</body>
</html>

<?php
$conn->close();
?>