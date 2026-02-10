// Smooth scroll
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

// Fade-in animation khi scroll
const observerOptions = {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px"
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = "1";
            entry.target.style.transform = "translateY(0)";
        }
    });
}, observerOptions);

document.querySelectorAll('.card-tour').forEach(card => {
    card.style.opacity = "0";
    card.style.transform = "translateY(40px)";
    card.style.transition = "all 0.7s ease";
    observer.observe(card);
});

// Thêm 4 destination cards (bạn có thể chỉnh sửa)
document.addEventListener('DOMContentLoaded', function() {
    const destinationsContainer = document.getElementById('destinations');
    if (!destinationsContainer) return;

    const destinations = [
        { name: "Vịnh Hạ Long", place: "Quảng Ninh", days: "2N1Đ", price: "1.290k", img: "https://images.unsplash.com/photo-1559598467-f8b76c5e2d68" },
        { name: "Phú Quốc", place: "Kiên Giang", days: "3N2Đ", price: "2.890k", img: "https://images.unsplash.com/photo-1507525428034-b723cf961d3e" },
        { name: "Sapa", place: "Lào Cai", days: "2N1Đ", price: "1.890k", img: "https://images.unsplash.com/photo-1518834107812-67b0b7c58434" },
        { name: "Đà Nẵng - Hội An", place: "Đà Nẵng", days: "3N2Đ", price: "3.290k", img: "https://images.unsplash.com/photo-1563492065599-4b1a5a2b1b8c" }
    ];

    destinations.forEach(dest => {
        const cardHTML = `
            <div class="col-lg-3 col-md-6">
                <div class="card-tour h-100 position-relative shadow-sm">
                    <div class="position-relative overflow-hidden">
                        <img src="${dest.img}" class="card-img-top" height="230" style="object-fit: cover;">
                        <div class="badge-price">Từ ${dest.price}</div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">${dest.name}</h5>
                        <p class="text-muted mb-1">${dest.place}</p>
                        <small class="text-muted">${dest.days}</small>
                    </div>
                </div>
            </div>
        `;
        destinationsContainer.innerHTML += cardHTML;
    });
});
document.getElementById('promo-tet-container').innerHTML = html;

// Fade-in khi scroll vào phần Điểm đến yêu thích
document.addEventListener('DOMContentLoaded', function() {
    const destSection = document.querySelector('#favorite-destinations .destinations-grid');
    if (!destSection) return;

    const destObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('show');
                destObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    destObserver.observe(destSection);
});
document.addEventListener('DOMContentLoaded', function() {
    fetch('sections/favorite-destinations.html')  // thay bằng đường dẫn file đúng của bạn
        .then(response => {
            if (!response.ok) {
                throw new Error('Không load được file favorite-destinations.html');
            }
            return response.text();
        })
        .then(html => {
            const container = document.getElementById('favorite-destinations-container');
            container.innerHTML = html;

            // === PHẦN QUAN TRỌNG: KHỞI TẠO LẠI TẤT CẢ TABS ===
            const tabContainer = container.querySelector('.nav-pills'); // hoặc '.nav' nếu dùng class khác
            if (tabContainer && typeof bootstrap !== 'undefined') {
                // Khởi tạo từng tab button
                const tabButtons = tabContainer.querySelectorAll('[data-bs-toggle="tab"]');
                tabButtons.forEach(button => {
                    new bootstrap.Tab(button);
                });

                // Đảm bảo tab đầu tiên active và hiển thị nội dung
                const activeTab = tabContainer.querySelector('.nav-link.active');
                if (activeTab) {
                    const tabInstance = new bootstrap.Tab(activeTab);
                    tabInstance.show();
                }
            }
        })
        
        .catch(error => {
            console.error('Lỗi load Điểm đến yêu thích:', error);
            document.getElementById('favorite-destinations-container').innerHTML = 
                '<p class="text-danger text-center">Không tải được phần Điểm đến. Kiểm tra file hoặc mạng.</p>';
        });
});

// Bật tuyết rơi Giáng sinh ngay lập tức
christmasOverlaySnow.enable();

// Bật pháo hoa Năm mới (tự động bắn theo interval)
const fireworks = new FireworksOverlay();  // dùng config mặc định
fireworks.startAnimation();
/* ================ 
 Stats
  =================== */
$(function () {
  const counterUp = window.counterUp.default;

  const callback = (entries) => {
    entries.forEach((entry) => {
      const el = entry.target;
      if (entry.isIntersecting && !el.classList.contains("is-visible")) {
        counterUp(el, {
          duration: 2000,
          delay: 16,
        });
        el.classList.add("is-visible");
      }
    });
  };

  const IO = new IntersectionObserver(callback, { threshold: 1 });

  const el = document
    .querySelectorAll(".counter")
    .forEach((node) => IO.observe(node));
});
