// common.js - Đồng bộ trạng thái giữa các trang

// === PHẦN 1: ĐĂNG NHẬP / TÀI KHOẢN ===
function updateAccountUI() {
    const accountBtn = document.getElementById('accountBtn');
    const accountDropdown = document.getElementById('accountDropdown');
    
    if (!accountBtn || !accountDropdown) return;

    const savedUser = localStorage.getItem('thtravel_user');
    let user = null;
    
    if (savedUser) {
        try {
            user = JSON.parse(savedUser);
        } catch (e) {
            localStorage.removeItem('thtravel_user');
        }
    }

    if (user && user.name) {
        accountBtn.innerHTML = `<i class="fas fa-user me-2"></i>Xin chào, ${user.name} ▼`;
        accountBtn.setAttribute('data-bs-toggle', 'dropdown');
        
        accountDropdown.innerHTML = `
            <li><a class="dropdown-item" href="profile.html"><i class="fas fa-user-circle me-2"></i>Hồ sơ</a></li>
            <li><a class="dropdown-item" href="orders.html"><i class="fas fa-shopping-bag me-2"></i>Đơn hàng</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="#" id="logoutBtn"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
        `;

        document.getElementById('logoutBtn')?.addEventListener('click', (e) => {
            e.preventDefault();
            localStorage.removeItem('thtravel_user');
            localStorage.removeItem('thtravel_orders');
            updateAccountUI();
        });
    } else {
        accountBtn.innerHTML = `<i class="fas fa-user me-2"></i>Tài khoản`;
        accountDropdown.innerHTML = '';
        accountBtn.removeAttribute('data-bs-toggle');
        
        accountBtn.onclick = (e) => {
            e.preventDefault();
            window.location.href = 'login.html';
        };
    }
}

// === PHẦN 2: ĐỒNG BỘ HIỆU ỨNG LỄ HỘI & DARK MODE ===
function syncFestiveStates() {
    const effectsEnabled   = localStorage.getItem('festive_effects') === 'true';
    const soundEnabled     = localStorage.getItem('festive_sound')   === 'true';
    const brightnessNormal = localStorage.getItem('brightness_normal') === 'true';

    // Cập nhật text trên nút
    document.getElementById('effectsStatus')?.textContent   = effectsEnabled ? 'BẬT' : 'TẮT';
    document.getElementById('soundStatus')?.textContent     = soundEnabled   ? 'BẬT' : 'TẮT';
    document.getElementById('brightnessStatus')?.textContent = brightnessNormal ? 'BÌNH THƯỜNG' : 'TỐI';

    // Áp dụng hiệu ứng (snow, fireworks, music, dark mode)
    if (effectsEnabled) {
        if (typeof christmasOverlaySnow !== 'undefined') christmasOverlaySnow.enable({ snowflakeCount: 120, zIndex: 999 });
        if (typeof FireworksOverlay !== 'undefined') {
            const fw = new FireworksOverlay({ particleCount: 90, interval: 700, zIndex: 1000 });
            fw.startAnimation();
        }
    } else {
        if (typeof christmasOverlaySnow !== 'undefined') christmasOverlaySnow.disable();
        // fireworksInstance?.stopAnimation(); // nếu bạn lưu instance
    }

    const music = document.getElementById('festiveMusic');
    if (music) {
        music.muted = !soundEnabled;
        if (soundEnabled) music.play().catch(() => {});
        else music.pause();
    }

    if (brightnessNormal) {
        document.body.classList.remove('dark-mode');
    } else {
        document.body.classList.add('dark-mode');
    }
}

// Khởi tạo mặc định nếu chưa có
if (!localStorage.getItem('festive_effects'))   localStorage.setItem('festive_effects', 'true');
if (!localStorage.getItem('festive_sound'))     localStorage.setItem('festive_sound', 'false');
if (!localStorage.getItem('brightness_normal')) localStorage.setItem('brightness_normal', 'true');

// Chạy khi load trang
document.addEventListener('DOMContentLoaded', () => {
    updateAccountUI();
    syncFestiveStates();

    // Đồng bộ realtime khi tab khác thay đổi
    window.addEventListener('storage', (e) => {
        if (e.key === 'thtravel_user') updateAccountUI();
        if (['festive_effects', 'festive_sound', 'brightness_normal'].includes(e.key)) {
            syncFestiveStates();
        }
    });
});

// Xử lý click toggle → lưu và đồng bộ
document.addEventListener('click', (e) => {
    if (e.target.closest('#toggleEffectsBtn')) {
        const current = localStorage.getItem('festive_effects') === 'true';
        localStorage.setItem('festive_effects', (!current).toString());
        syncFestiveStates();
    }
    if (e.target.closest('#toggleSoundBtn')) {
        const current = localStorage.getItem('festive_sound') === 'true';
        localStorage.setItem('festive_sound', (!current).toString());
        syncFestiveStates();
    }
    if (e.target.closest('#toggleBrightnessBtn')) {
        const current = localStorage.getItem('brightness_normal') === 'true';
        localStorage.setItem('brightness_normal', (!current).toString());
        syncFestiveStates();
    }
});