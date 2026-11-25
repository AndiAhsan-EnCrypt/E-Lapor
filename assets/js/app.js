// assets/js/app.js - Modern & Interaktif
// Cegah halaman lama muncul saat tekan Back setelah logout (bfcache)
window.addEventListener('pageshow', function (e) {
    if (e.persisted) { window.location.reload(); }
});

document.addEventListener('DOMContentLoaded', function () {

    // === ANIMATED COUNTERS ===
    document.querySelectorAll('.stat-card .num').forEach(el => {
        const target = parseInt(el.textContent, 10);
        if (isNaN(target)) return;
        let cur = 0;
        const step = Math.max(1, Math.ceil(target / 40));
        const iv = setInterval(() => {
            cur += step;
            if (cur >= target) { cur = target; clearInterval(iv); }
            el.textContent = cur;
        }, 30);
    });

    // === PREVIEW GAMBAR UPLOAD ===
    const fileInput = document.getElementById('lampiran');
    const preview = document.getElementById('previewImg');
    if (fileInput && preview) {
        fileInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function (ev) { preview.src = ev.target.result; preview.style.display = 'block'; };
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        });
    }

    // === KONFIRMASI HAPUS ===
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!confirm(form.dataset.confirm)) e.preventDefault();
        });
    });

    // === RIPPLE EFFECT PADA TOMBOL ===
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            const rect = btn.getBoundingClientRect();
            const ripple = document.createElement('span');
            const size = Math.max(rect.width, rect.height);
            ripple.style.cssText = `position:absolute;border-radius:50%;background:rgba(255,255,255,.4);width:${size}px;height:${size}px;left:${e.clientX-rect.left-size/2}px;top:${e.clientY-rect.top-size/2}px;transform:scale(0);animation:ripple .6s linear;pointer-events:none;`;
            btn.appendChild(ripple);
            setTimeout(() => ripple.remove(), 600);
        });
    });

    // === SMOOTH SCROLL ===
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', e => {
            const target = document.querySelector(a.getAttribute('href'));
            if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth' }); }
        });
    });

    // === FADE-IN ON SCROLL ===
    const observer = new IntersectionObserver(entries => {
        entries.forEach(en => {
            if (en.isIntersecting) {
                en.target.classList.add('fade-in-up');
                observer.unobserve(en.target);
            }
        });
    }, { threshold: 0.1 });
    document.querySelectorAll('.card, .stat-card').forEach(el => observer.observe(el));
});

// === TOAST NOTIFICATION ===
function showToast(msg, type = 'info') {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    toast.className = 'toast-msg';
    const icon = type === 'success' ? 'bi-check-circle-fill text-success'
              : type === 'error' ? 'bi-x-circle-fill text-danger'
              : 'bi-info-circle-fill text-primary';
    toast.innerHTML = `<i class="bi ${icon} me-2"></i>${msg}`;
    container.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transform = 'translateX(100%)'; setTimeout(() => toast.remove(), 400); }, 3000);
}