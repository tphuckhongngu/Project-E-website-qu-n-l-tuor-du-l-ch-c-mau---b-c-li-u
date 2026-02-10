<?php
// index.php
$page_title = "True Happy Travel - Chào mừng";
include 'header.php';   // nếu sau này bạn tách header
?>

<!-- Nội dung chính giống phiên bản 1 -->

<?php include 'footer.php'; ?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="videobackground.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Fullscreen Video</title>

<style>
html, body {
    margin: 0;
    width: 100%;
    height: 100%;
    background: black;
    overflow: hidden;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

video {
    position: fixed;
    inset: 0;
    width: 100vw;
    height: 100vh;
    object-fit: cover;
    z-index: 1;
}


/* === Chữ trắng chính giữa - đẹp & có hiệu ứng nhẹ === */


.main-title {
    font-size: clamp(3.2rem, 9vw, 7.5rem);
    font-weight: 800;
    letter-spacing: -1px;
    margin: 0 0 0.8rem 0;
    text-shadow: 
        0 0 20px rgba(255,255,255,0.6),
        0 0 40px rgba(255,255,255,0.3);
    animation: glow 4s ease-in-out infinite alternate;
}

.subtitle {
    font-size: clamp(1.4rem, 4.5vw, 2.2rem);
    font-weight: 400;
    opacity: 0.92;
    letter-spacing: 1.5px;
    text-shadow: 0 2px 12px rgba(0,0,0,0.7);
    animation: fadeInUp 2.2s ease-out forwards;
    animation-delay: 0.6s;
    opacity: 0;
}

@keyframes glow {
    from {
        text-shadow: 
            0 0 10px rgba(255,255,255,0.4),
            0 0 20px rgba(255,255,255,0.2);
    }
    to {
        text-shadow: 
            0 0 30px rgba(255,255,255,0.9),
            0 0 60px rgba(255,255,255,0.5);
    }
}

@keyframes fadeInUp {
    to {
        opacity: 0.92;
        transform: translateY(0);
    }
}

/* Giữ hiệu ứng nút xanh (nhưng ẩn hoàn toàn) */
.action-btn {
    /* giữ nguyên code hiệu ứng cũ nếu sau này muốn bật lại */
    display: none !important;
}
</style>
</head>
<body>

<video id="v" autoplay muted loop playsinline>
    <source src="video/0130.mp4" type="video/mp4">
</video>

<!-- Chữ hiển thị -->
<div class="text-overlay">
    <div class="main-title">TRUE HAPPY TRAVEL</div>
    <div class="subtitle">CHÀO MỪNG BẠN ĐẾN VỚI THẾ GIỚI CỦA TÔI</div>
    
    
    <!-- hoặc dùng <button> nếu không cần chuyển trang thật -->

 <a href="index.php" class="next-btn ">NEXT →</a>
 </div>
<script>
// Click bất kỳ đâu để bật tiếng (giữ nguyên)
const video = document.getElementById("v");
document.addEventListener("click", () => {
    video.muted = false;
    video.volume = 1;
}, { once: true });
</script>
<div class="particles"></div>

</body>
</html>