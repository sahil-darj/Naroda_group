console.log('Main.js loading - Global Lightbox System initializing...');
// Global Gallery Lightbox System
let lightboxImages = [];
let currentLightboxIndex = 0;

function openLightbox(images, index) {
    // images can be array of strings or array of {src, caption}
    if (!images || !images.length) return;

    lightboxImages = images.map(img => typeof img === 'string' ? { src: img, caption: 'Project Gallery' } : img);
    currentLightboxIndex = index;

    const lightbox = document.getElementById('gallery-lightbox');
    if (!lightbox) return;

    lightbox.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    renderLightboxContent();
    buildLightboxDots();
}
window.openLightbox = openLightbox;

function renderLightboxContent() {
    const img = document.getElementById('lightbox-main-img');
    const counter = document.getElementById('lightbox-counter');
    const caption = document.getElementById('lightbox-caption');
    const current = lightboxImages[currentLightboxIndex];

    if (!img || !current) return;

    // Fade effect
    img.style.opacity = '0';
    img.style.transform = 'scale(0.95)';

    setTimeout(() => {
        img.src = current.src;
        counter.textContent = `${currentLightboxIndex + 1} / ${lightboxImages.length}`;
        caption.textContent = current.caption || 'Project Gallery';
        img.style.opacity = '1';
        img.style.transform = 'scale(1)';
        updateLightboxDots();
    }, 200);
}

function buildLightboxDots() {
    const dotsGrid = document.getElementById('lightbox-dots');
    if (!dotsGrid) return;

    if (lightboxImages.length <= 1 || lightboxImages.length > 20) {
        dotsGrid.innerHTML = '';
        return;
    }

    dotsGrid.innerHTML = lightboxImages.map((_, i) =>
        `<div class="lb-dot ${i === currentLightboxIndex ? 'active' : ''}" onclick="window.changeToLightboxIndex(${i})"></div>`
    ).join('');
}

function updateLightboxDots() {
    const dots = document.querySelectorAll('.lb-dot');
    dots.forEach((dot, i) => {
        dot.classList.toggle('active', i === currentLightboxIndex);
    });
}

function changeToLightboxIndex(index) {
    currentLightboxIndex = index;
    renderLightboxContent();
}
window.changeToLightboxIndex = changeToLightboxIndex;

function closeLightbox() {
    const lightbox = document.getElementById('gallery-lightbox');
    if (lightbox) lightbox.style.display = 'none';
    document.body.style.overflow = '';
}
window.closeLightbox = closeLightbox;

function changeLightboxImage(direction) {
    currentLightboxIndex += direction;
    if (currentLightboxIndex >= lightboxImages.length) currentLightboxIndex = 0;
    if (currentLightboxIndex < 0) currentLightboxIndex = lightboxImages.length - 1;
    renderLightboxContent();
}
window.changeLightboxImage = changeLightboxImage;

// Global Popup System
function showPopup(title, msg, type = 'success', waUrl = null) {
    const popup = document.getElementById('system-popup');
    const card = document.getElementById('popup-card-inner');
    const icon = document.getElementById('popup-icon');
    const titleEl = document.getElementById('popup-title');
    const msgEl = document.getElementById('popup-msg');
    const waLink = document.getElementById('popup-wa-link');

    if (!popup) return;

    titleEl.textContent = title;
    msgEl.textContent = msg;

    card.classList.remove('success', 'error');
    card.classList.add(type);

    if (type === 'success') icon.className = 'fas fa-check-circle';
    else icon.className = 'fas fa-exclamation-circle';

    if (waUrl) {
        waLink.href = waUrl;
        waLink.style.display = 'flex';
    } else waLink.style.display = 'none';

    popup.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
window.showPopup = showPopup;

function closePopup() {
    const popup = document.getElementById('system-popup');
    if (popup) popup.style.display = 'none';
    document.body.style.overflow = '';
}
window.closePopup = closePopup;

// Keyboard navigation for lightbox
document.addEventListener('keydown', (e) => {
    const lb = document.getElementById('gallery-lightbox');
    if (!lb || lb.style.display !== 'flex') return;

    if (e.key === 'Escape') {
        closeLightbox();
    }
    if (e.key === 'ArrowRight') changeLightboxImage(1);
    if (e.key === 'ArrowLeft') changeLightboxImage(-1);
});

// Click background to close
document.addEventListener('click', (e) => {
    const lb = document.getElementById('gallery-lightbox');
    if (lb && lb.style.display === 'flex' && e.target === lb) {
        closeLightbox();
    }
});

function renderHeader() {
    // Inject/Upgrade Font Awesome
    const existingFA = document.querySelector('link[href*="font-awesome"]');
    if (!existingFA || !existingFA.href.includes('6.5.1')) {
        if (existingFA) existingFA.remove(); // Remove old version if present
        const fa = document.createElement('link');
        fa.rel = 'stylesheet';
        fa.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css';
        document.head.appendChild(fa);
    }

    const headerHTML = `
    <!-- Preloader -->
    <div id="preloader">
        <div style="display: flex; flex-direction: column; align-items: center; gap: 20px;">
            <div class="loader"></div>
            <div style="font-family: 'Montserrat', sans-serif; font-weight: 800; letter-spacing: 0.3em; color: #111; font-size: 14px; text-transform: uppercase; animation: pulse 1.5s ease-in-out infinite;">NARODA GROUP</div>
        </div>
    </div>
    <style>
    @keyframes pulse {
        0%, 100% { opacity: 0.6; }
        50% { opacity: 1; }
    }
    </style>

    <nav id="main-nav" style="position: fixed; top: 0; width: 100%; z-index: 1000; transition: all 0.4s ease; font-family: 'Montserrat', sans-serif;">
        <div style="max-width: 1400px; margin: 0 auto; padding: 15px 30px; display: flex; align-items: center; justify-content: space-between; position: relative;">
            
                <!-- Left: Logo -->
            <a href="index.html" style="display: flex; align-items: center; text-decoration: none; z-index: 1002;">
                <span style="font-family: 'Playfair Display', Georgia, serif; font-size: 22px; font-weight: 800; color: #fff; letter-spacing: 0.04em; line-height: 1; text-transform: uppercase; display: flex; flex-direction: column; align-items: flex-start;">
                    <span style="letter-spacing: 0.12em;">NARODA</span>
                    <span style="font-size: 10px; font-weight: 400; letter-spacing: 0.35em; opacity: 0.85; font-style: italic; margin-top: 1px;">GROUP</span>
                </span>
            </a>
            
            <!-- Center: Desktop Menu -->
            <div class="desktop-menu" style="position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); display: flex; gap: 40px; align-items: center;">
                <style>
                    /* ===== MODAL STYLES ===== */
                    #quick-enquiry-modal {
                        position: fixed;
                        inset: 0;
                        background: rgba(0,0,0,0.75);
                        backdrop-filter: blur(8px);
                        z-index: 9999;
                        display: none;
                        align-items: center;
                        justify-content: center;
                        padding: 20px;
                        font-family: 'Inter', sans-serif;
                    }
                    .enquiry-modal-content {
                        background: #fff;
                        width: 100%;
                        max-width: 560px;
                        border-radius: 24px;
                        overflow: hidden;
                        position: relative;
                        color: #111;
                        animation: modalIn 0.35s cubic-bezier(0.16, 1, 0.3, 1);
                        max-height: 90vh;
                        overflow-y: auto;
                        box-shadow: 0 40px 80px rgba(0,0,0,0.3);
                    }
                    .modal-header-img {
                        width: 100%;
                        height: 180px;
                        object-fit: cover;
                        display: block;
                        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
                    }
                    .modal-body {

                        padding: 36px 40px 40px;
                    }
                    @keyframes modalIn { from { transform: scale(0.95) translateY(20px); opacity: 0; } to { transform: scale(1) translateY(0); opacity: 1; } }
                    .modal-close {
                        position: absolute;
                        top: 16px;
                        right: 16px;
                        cursor: pointer;
                        background: rgba(255,255,255,0.9);
                        border-radius: 50%;
                        width: 36px;
                        height: 36px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        transition: all 0.2s;
                        z-index: 10;
                    }
                    .modal-close:hover { background: white; transform: rotate(90deg); }
                    .enquiry-input, .enquiry-textarea, .enquiry-select {
                        width: 100%;
                        padding: 14px 18px;
                        border: 1.5px solid #e5e7eb;
                        border-radius: 12px;
                        margin-bottom: 14px;
                        font-size: 14px;
                        outline: none;
                        transition: border-color 0.2s, box-shadow 0.2s;
                        font-family: 'Inter', sans-serif;
                        color: #111;
                        background: #fafafa;
                        box-sizing: border-box;
                        appearance: auto;
                    }
                    .enquiry-input:focus, .enquiry-textarea:focus, .enquiry-select:focus {
                        border-color: #C5A47E;
                        background: #fff;
                        box-shadow: 0 0 0 4px rgba(197,164,126,0.1);
                    }
                    .enquiry-textarea { min-height: 100px; resize: vertical; }
                    .enquiry-submit {
                        width: 100%;
                        padding: 16px;
                        background: #111;
                        color: #fff;
                        border: none;
                        border-radius: 12px;
                        font-weight: 700;
                        cursor: pointer;
                        font-size: 15px;
                        transition: all 0.3s;
                        font-family: 'Inter', sans-serif;
                        letter-spacing: 0.5px;
                        text-transform: uppercase;
                    }
                    .enquiry-submit:hover { background: #C5A47E; transform: translateY(-2px); box-shadow: 0 10px 25px rgba(197,164,126,0.4); }
                    .form-row { display: flex; gap: 14px; }
                    .form-row .enquiry-input, .form-row .enquiry-select { margin-bottom: 0; flex: 1; }
                    .form-row-wrap { display: flex; gap: 14px; margin-bottom: 14px; }
                    .form-row-wrap > * { flex: 1; margin-bottom: 0 !important; }
                    @media (max-width: 540px) { .form-row-wrap { flex-direction: column; gap: 0; } .modal-body { padding: 28px 24px 32px; } }

                    /* ===== WHATSAPP FLOAT ===== */
                    .whatsapp-float {
                        position: fixed;
                        bottom: 30px;
                        right: 30px;
                        background: #25d366;
                        color: white;
                        width: 60px;
                        height: 60px;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        box-shadow: 0 4px 20px rgba(37,211,102,0.4);
                        z-index: 2000;
                        transition: transform 0.3s, box-shadow 0.3s;
                        text-decoration: none;
                    }
                    .whatsapp-float:hover { transform: scale(1.1); box-shadow: 0 8px 30px rgba(37,211,102,0.5); }
                    .nav-link { 
                        color: #ffffff; 
                        text-decoration: none; 
                        font-size: 14px; 
                        font-weight: 500; 
                        text-transform: capitalize; 
                        padding: 8px 0; 
                        position: relative; 
                        opacity: 0.9;
                        transition: all 0.3s;
                        letter-spacing: 0.5px;
                    }
                    .nav-link::after {
                        content: '';
                        position: absolute;
                        bottom: 0;
                        left: 0;
                        width: 0;
                        height: 2px;
                        background: #fff;
                        transition: width 0.3s;
                    }
                    .nav-link:hover { opacity: 1; }
                    .nav-link:hover::after { width: 100%; }
                    
                    @media (max-width: 1150px) { .desktop-menu { display: none !important; } }

                    /* ===== PRELOADER ===== */
                    #preloader {
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: #fff;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        z-index: 999999;
                        transition: opacity 0.5s ease-out, visibility 0.5s;
                        visibility: visible;
                        opacity: 1;
                    }
                    .loader {
                        width: 50px;
                        height: 50px;
                        border: 3px solid #f3f3f3;
                        border-top: 3px solid #C5A47E;
                        border-radius: 50%;
                        animation: spin 1s linear infinite;
                    }
                    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

                    /* ===== WHATSAPP POPUP ===== */
                    .whatsapp-popup {
                        position: fixed;
                        bottom: 100px;
                        right: 30px;
                        background: white;
                        width: 280px;
                        border-radius: 20px;
                        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
                        display: none;
                        flex-direction: column;
                        z-index: 2001;
                        overflow: hidden;
                        animation: slideUp 0.3s ease-out;
                        font-family: 'Inter', sans-serif;
                    }
                    @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
                    .wa-popup-header {
                        background: #25d366;
                        color: white;
                        padding: 15px 20px;
                        display: flex;
                        align-items: center;
                        gap: 10px;
                    }
                    .wa-popup-header span { font-weight: 700; font-size: 15px; }
                    .wa-popup-row {
                        padding: 15px 20px;
                        display: flex;
                        flex-direction: column;
                        gap: 4px;
                        text-decoration: none;
                        color: #333;
                        transition: background 0.2s;
                        border-bottom: 1px solid #f0f0f0;
                    }
                    .wa-popup-row:last-child { border-bottom: none; }
                    .wa-popup-row:hover { background: #f9fafb; }
                    .wa-project-name { font-weight: 700; font-size: 14px; color: #111; }
                    .wa-number { font-size: 12px; color: #666; }
                    .wa-action { font-size: 12px; color: #25d366; font-weight: 600; margin-top: 2px; }

                    .wa-action { font-size: 12px; color: #25d366; font-weight: 600; margin-top: 2px; }

                    /* ===== SYSTEM POPUP CARD ===== */
                    #system-popup {
                        position: fixed;
                        inset: 0;
                        background: rgba(0,0,0,0.85);
                        backdrop-filter: blur(10px);
                        z-index: 10000;
                        display: none;
                        align-items: center;
                        justify-content: center;
                        padding: 20px;
                        font-family: 'Inter', sans-serif;
                    }
                    .popup-card {
                        background: white;
                        width: 100%;
                        max-width: 450px;
                        border-radius: 30px;
                        padding: 40px;
                        text-align: center;
                        position: relative;
                        animation: popupIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                        box-shadow: 0 40px 100px rgba(0,0,0,0.4);
                    }
                    @keyframes popupIn { from { transform: scale(0.9) translateY(30px); opacity: 0; } to { transform: scale(1) translateY(0); opacity: 1; } }
                    
                    .popup-icon-wrap {
                        width: 100px;
                        height: 100px;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 50px;
                        margin: 0 auto 30px;
                        position: relative;
                    }
                    
                    /* Success state */
                    .popup-card.success .popup-icon-wrap { background: #f0fdf4; color: #22c55e; }
                    .popup-card.success .popup-title { color: #22c55e; }
                    
                    /* Error state */
                    .popup-card.error .popup-icon-wrap { background: #fef2f2; color: #ef4444; }
                    .popup-card.error .popup-title { color: #ef4444; }
                    
                    .popup-title {
                        font-size: 26px;
                        font-weight: 900;
                        margin-bottom: 12px;
                        letter-spacing: -0.5px;
                    }
                    .popup-msg {
                        color: #555;
                        font-size: 16px;
                        line-height: 1.6;
                        margin-bottom: 35px;
                    }
                    .popup-wa-btn {
                        display: none;
                        align-items: center;
                        justify-content: center;
                        gap: 12px;
                        background: #25d366;
                        color: white;
                        padding: 18px 30px;
                        border-radius: 18px;
                        text-decoration: none;
                        font-weight: 800;
                        font-size: 16px;
                        transition: all 0.3s;
                        box-shadow: 0 10px 30px rgba(37,211,102,0.3);
                        width: 100%;
                        box-sizing: border-box;
                    }
                    .popup-wa-btn:hover {
                        transform: translateY(-4px);
                        box-shadow: 0 15px 40px rgba(37,211,102,0.4);
                        background: #1fab58;
                    }
                    .popup-close-btn {
                        width: 100%;
                        padding: 16px;
                        background: #f3f4f6;
                        color: #4b5563;
                        border: none;
                        border-radius: 15px;
                        font-weight: 700;
                        font-size: 15px;
                        cursor: pointer;
                        transition: all 0.2s;
                        margin-top: 10px;
                    }
                    .popup-close-btn:hover { background: #e5e7eb; color: #111; }

                    /* ===== GALLERY LIGHTBOX ===== */
                    #gallery-lightbox {
                        position: fixed;
                        inset: 0;
                        background: rgba(0,0,0,0.95);
                        backdrop-filter: blur(15px);
                        z-index: 11000;
                        display: none;
                        align-items: center;
                        justify-content: center;
                        padding: 40px;
                        font-family: 'Inter', sans-serif;
                        user-select: none;
                    }
                    .lightbox-content {
                        position: relative;
                        width: 100%;
                        max-width: 1200px;
                        height: 100%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        animation: lightboxIn 0.4s cubic-bezier(0.16, 1, 0.3, 1);
                    }
                    @keyframes lightboxIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
                    .lightbox-img {
                        max-width: 100%;
                        max-height: 85vh;
                        object-fit: contain;
                        border-radius: 8px;
                        box-shadow: 0 30px 60px rgba(0,0,0,0.5);
                        transition: opacity 0.3s ease;
                    }
                    .lightbox-close {
                        position: absolute;
                        top: 30px;
                        right: 30px;
                        width: 50px;
                        height: 50px;
                        background: rgba(255,255,255,0.08);
                        color: white;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 20px;
                        cursor: pointer;
                        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                        backdrop-filter: blur(10px);
                        z-index: 1000;
                        border: 1px solid rgba(255,255,255,0.1);
                    }
                    .lightbox-close:hover { 
                        background: white; 
                        color: black;
                        transform: rotate(90deg) scale(1.1); 
                        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
                    }
                    .lightbox-nav {
                        position: absolute;
                        top: 50%;
                        transform: translateY(-50%);
                        width: 50px;
                        height: 50px;
                        background: rgba(255,255,255,0.05);
                        border: 1px solid rgba(255,255,255,0.1);
                        color: white;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        cursor: pointer;
                        font-size: 1.2rem;
                        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                        backdrop-filter: blur(10px);
                        z-index: 10;
                    }
                    .lightbox-nav:hover { background: white; color: black; transform: translateY(-50%) scale(1.1); box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
                    .lightbox-prev { left: 30px; }
                    .lightbox-next { right: 30px; }
                    
                    .lightbox-meta {
                        position: absolute;
                        bottom: 40px;
                        left: 0;
                        right: 0;
                        text-align: center;
                        color: white;
                        z-index: 10;
                    }
                    .lightbox-caption {
                        font-size: 1.25rem;
                        font-weight: 600;
                        margin-bottom: 10px;
                        letter-spacing: -0.5px;
                        text-shadow: 0 2px 10px rgba(0,0,0,0.5);
                    }
                    .lightbox-counter {
                        font-size: 0.85rem;
                        font-weight: 700;
                        text-transform: uppercase;
                        letter-spacing: 2px;
                        opacity: 0.5;
                        margin-bottom: 20px;
                    }
                    .lightbox-dots {
                        display: flex;
                        justify-content: center;
                        gap: 8px;
                        flex-wrap: wrap;
                        max-width: 80vw;
                        margin: 0 auto;
                    }
                    .lb-dot {
                        width: 8px;
                        height: 8px;
                        border-radius: 50%;
                        background: rgba(255,255,255,0.2);
                        cursor: pointer;
                        transition: all 0.3s;
                    }
                    .lb-dot.active {
                        background: white;
                        transform: scale(1.3);
                        box-shadow: 0 0 10px white;
                    }
                    
                    .lightbox-img-wrap {
                        width: 100%;
                        height: 100%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        padding: 80px 120px 160px;
                        box-sizing: border-box;
                    }
                    .lightbox-img {
                        max-width: 100%;
                        max-height: 100%;
                        object-fit: contain;
                        border-radius: 12px;
                        box-shadow: 0 40px 100px rgba(0,0,0,0.8);
                        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                    }
                    
                    @media (max-width: 991px) {
                        .lightbox-img-wrap { padding: 40px 20px 140px; }
                        .lightbox-nav { width: 44px; height: 44px; font-size: 1rem; }
                        .lightbox-prev { left: 10px; }
                        .lightbox-next { right: 10px; }
                        .lightbox-caption { font-size: 1.1rem; }
                    }
                </style>
                <a href="index.html" class="nav-link">Home</a>
                <a href="about-us.html" class="nav-link">About Us</a>
                
                <!-- Dropdown for Projects -->
                <div class="nav-item-dropdown" style="position: relative; display: inline-block;">
                    <a href="projects-residential.html" class="nav-link" style="cursor: pointer; display: flex; align-items: center; gap: 5px;">
                        Projects 
                        <svg width="10" height="6" viewBox="0 0 10 6" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 1L5 5L9 1"/></svg>
                    </a>
                    <div class="dropdown-menu" style="position: absolute; top: 100%; left: 50%; transform: translateX(-50%); background: rgba(0,0,0,0.9); backdrop-filter: blur(20px); min-width: 220px; padding: 15px 0; border-radius: 12px; box-shadow: 0 20px 40px rgba(0,0,0,0.4); margin-top: 15px; border: 1px solid rgba(255,255,255,0.1);">
                        <a href="naroda-lavish.html" style="display: block; padding: 12px 25px; color: #ccc; text-decoration: none; font-size: 14px; transition: all 0.2s;">Naroda Lavish</a>
                        <a href="project-details.html?id=4" style="display: block; padding: 12px 25px; color: #ccc; text-decoration: none; font-size: 14px; transition: all 0.2s;">Naroda Arise</a>
                        <div style="height: 1px; background: rgba(255,255,255,0.1); margin: 8px 0;"></div>
                        <a href="projects-residential.html" style="display: block; padding: 12px 25px; color: white; text-decoration: none; font-size: 14px; font-weight: 600;">View All Projects</a>
                    </div>
                </div>
                <style>
                    .dropdown-menu { display: none; z-index: 10000; }
                    .nav-item-dropdown:hover .dropdown-menu { display: block !important; animation: fadeInUp 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
                    .dropdown-menu::before { content: ""; position: absolute; top: -20px; left: 0; width: 100%; height: 20px; }
                    @keyframes fadeInUp { from { opacity: 0; transform: translate(-50%, 10px); } to { opacity: 1; transform: translate(-50%, 0); } }
                    .dropdown-menu a:hover { color: white !important; background: rgba(255,255,255,0.05); padding-left: 30px; }
                </style>

                <!-- Dropdown for More -->
                <div class="nav-item-dropdown" style="position: relative; display: inline-block;">
                    <a href="javascript:void(0)" class="nav-link" style="cursor: default; display: flex; align-items: center; gap: 5px;">
                        More 
                        <svg width="10" height="6" viewBox="0 0 10 6" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 1L5 5L9 1"/></svg>
                    </a>
                    <div class="dropdown-menu" style="position: absolute; top: 100%; left: 50%; transform: translateX(-50%); background: rgba(0,0,0,0.9); backdrop-filter: blur(20px); min-width: 200px; padding: 15px 0; border-radius: 12px; box-shadow: 0 20px 40px rgba(0,0,0,0.4); margin-top: 15px; border: 1px solid rgba(255,255,255,0.1);">
                        <a href="blog.html" style="display: block; padding: 12px 25px; color: #ccc; text-decoration: none; font-size: 14px; transition: all 0.2s;">Blogs</a>
                        <a href="careers.html" style="display: block; padding: 12px 25px; color: #ccc; text-decoration: none; font-size: 14px; transition: all 0.2s;">Careers</a>
                        <a href="team.html" style="display: block; padding: 12px 25px; color: #ccc; text-decoration: none; font-size: 14px; transition: all 0.2s;">Our Team</a>
                    </div>
                </div>

                <a href="contact-us.html" class="nav-link">Contact Us</a>
                <a href="admin2/home.html" class="nav-link" style="display: flex; align-items: center; gap: 6px;"><i class="fa-solid fa-user-lock" style="font-size: 12px; opacity: 0.8;"></i> Login</a>
            </div>

            <!-- Right: Action Buttons -->
            <div style="display: flex; align-items: center; gap: 20px;">
                <button id="open-enquiry-modal" class="enquiry-btn-outline" style="background: transparent; border: 1px solid rgba(255,255,255,0.7); color: white; padding: 10px 28px; border-radius: 50px; font-size: 13px; font-weight: 500; cursor: pointer; transition: all 0.3s; text-transform: uppercase; letter-spacing: 0.5px;">
                    Enquiry Now
                </button>
                <style>
                    .enquiry-btn-outline:hover { background: white; color: black; border-color: white; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
                    @media (max-width: 768px) { .enquiry-btn-outline { display: none; } }
                </style>
                
                <!-- Mobile Toggle with animation -->
                <div class="mobile-toggle" id="mobile-menu-btn" style="cursor: pointer; color: white; padding: 5px;">
                    <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" height="32" width="32" xmlns="http://www.w3.org/2000/svg"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Rest of the logic mostly unchanged -->
    <!-- Mobile Menu Overlay -->
    <div id="mobile-menu-overlay" style="position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 1001; display: none; backdrop-filter: blur(4px);"></div>
    <div id="mobile-menu" style="position: fixed; top: 0; right: -300px; width: 300px; height: 100%; background: #000; z-index: 1002; transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); padding: 40px 30px; font-family: 'Inter', sans-serif; box-shadow: -10px 0 30px rgba(0,0,0,0.5);">
        <div style="display: flex; justify-content: flex-end; margin-bottom: 50px;">
            <div id="close-menu-btn" style="cursor: pointer; color: white; opacity: 0.7; transition: opacity 0.3s;">
                <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" height="32" width="32" xmlns="http://www.w3.org/2000/svg"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </div>
        </div>
        <div style="display: flex; flex-direction: column; gap: 25px;">
            <a href="index.html" class="mobile-nav-link" style="color: white; text-decoration: none; font-size: 18px; font-weight: 500;">Home</a>
            <a href="about-us.html" class="mobile-nav-link" style="color: white; text-decoration: none; font-size: 18px; font-weight: 500;">About Us</a>
            <a href="projects-residential.html" class="mobile-nav-link" style="color: white; text-decoration: none; font-size: 18px; font-weight: 500;">Projects</a>
            <a href="blog.html" class="mobile-nav-link" style="color: white; text-decoration: none; font-size: 18px; font-weight: 500;">Blogs</a>
            <a href="careers.html" class="mobile-nav-link" style="color: white; text-decoration: none; font-size: 18px; font-weight: 500;">Careers</a>
            <a href="team.html" class="mobile-nav-link" style="color: white; text-decoration: none; font-size: 18px; font-weight: 500;">Our Team</a>
            <a href="contact-us.html" class="mobile-nav-link" style="color: white; text-decoration: none; font-size: 18px; font-weight: 500;">Contact Us</a>
            <a href="admin2/home.html" class="mobile-nav-link" style="color: white; text-decoration: none; font-size: 18px; font-weight: 500; display: flex; align-items: center; gap: 10px;"><i class="fa-solid fa-user-lock"></i> Login</a>
            <button id="mobile-enquiry-btn" style="background: white; color: black; border: none; padding: 15px; border-radius: 50px; font-weight: 700; text-transform: uppercase; cursor: pointer; font-family: 'Inter', sans-serif; margin-top: 20px; width: 100%;">Enquiry Now</button>
        </div>
    </div>
    
    <div id="gallery-lightbox">
        <div class="lightbox-close" onclick="closeLightbox()">
            <i class="fas fa-times"></i>
        </div>
        <div class="lightbox-nav lightbox-prev" onclick="changeLightboxImage(-1)">
            <i class="fas fa-chevron-left"></i>
        </div>
        <div class="lightbox-img-wrap">
            <img src="" alt="Gallery Image" id="lightbox-main-img" class="lightbox-img">
        </div>
        <div class="lightbox-nav lightbox-next" onclick="changeLightboxImage(1)">
            <i class="fas fa-chevron-right"></i>
        </div>
        <div class="lightbox-meta">
            <div class="lightbox-counter" id="lightbox-counter">1 / 1</div>
            <div class="lightbox-caption" id="lightbox-caption"></div>
            <div class="lightbox-dots" id="lightbox-dots"></div>
        </div>
    </div>

    <!-- Enquiry Modal -->
    <div id="quick-enquiry-modal">
        <div class="enquiry-modal-content">
            <!-- Close Button -->
            <div id="close-enquiry-modal" class="modal-close">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" width="18" height="18"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </div>

            <!-- Modal Header Image Banner -->
            <div style="width:100%; height:180px; background: linear-gradient(135deg, #0a0a0a 0%, #1a1208 50%, #2c1f0e 100%); display:flex; align-items:center; justify-content:center; position:relative; overflow:hidden;">
                <img src="naroda_group_assets/arise3.png" alt="" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:0.35;">
                <div style="position:relative;z-index:2;text-align:center;">
                    <span style="font-family:'Playfair Display',Georgia,serif; font-size:28px; font-weight:800; color:#fff; letter-spacing:0.06em; text-transform:uppercase; display:block; line-height:1;">NARODA</span>
                    <span style="font-family:'Playfair Display',Georgia,serif; font-size:13px; font-weight:400; color:rgba(255,255,255,0.8); letter-spacing:0.4em; text-transform:uppercase; font-style:italic; display:block; margin-top:3px;">GROUP</span>
                    <p style="color:rgba(255,255,255,0.65);font-size:11px;letter-spacing:2px;text-transform:uppercase;font-family:'Inter',sans-serif;margin-top:10px;">Premium Real Estate</p>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <h2 style="font-size:22px;font-weight:800;margin-bottom:6px;color:#111;letter-spacing:-0.02em;">Enquire Now</h2>
                <p style="color:#888;margin-bottom:24px;font-size:14px;line-height:1.5;">Fill in your details and we'll connect with you shortly.</p>

                <form id="enquiry-modal-form">
                    <div class="form-row-wrap">
                        <input type="text" placeholder="Your Name *" class="enquiry-input" required id="enquiry-name">
                        <input type="tel" placeholder="Phone Number *" class="enquiry-input" required id="enquiry-phone" pattern="[0-9]{10}" title="Please enter a valid 10-digit phone number">
                    </div>

                    <input type="email" placeholder="Your Email *" class="enquiry-input" required id="enquiry-email">

                    <select class="enquiry-input enquiry-select" required id="enquiry-project">
                        <option value="" disabled selected>Select Project *</option>
                        <option value="Naroda Arise">Naroda Arise</option>
                        <option value="Naroda Lavish">Naroda Lavish</option>
                        <option value="General Inquiry">General Inquiry</option>
                    </select>

                    <textarea placeholder="Your Message (optional)" class="enquiry-textarea" id="enquiry-message"></textarea>

                    <button type="submit" class="enquiry-submit">Send Enquiry</button>
                    
                    <div style="margin-top: 25px; border-top: 1px solid #eee; padding-top: 20px;">
                        <p style="text-align:center; font-size:12px; color:#666; margin-bottom:15px; text-transform:uppercase; letter-spacing:1px; font-weight:600;">Or Connect on WhatsApp</p>
                        <div style="display: flex; gap: 10px;">
                            <a href="https://wa.me/917383185892?text=I'm interested in Naroda Arise" target="_blank" style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 5px; padding: 12px; background: #f0fdf4; border-radius: 12px; text-decoration: none; border: 1px solid #dcfce7; transition: all 0.3s;">
                                <span style="font-size: 11px; font-weight: 700; color: #15803d; text-transform: uppercase;">Arise</span>
                                <div style="display: flex; align-items: center; gap: 5px; color: #166534;">
                                    <i class="fab fa-whatsapp" style="font-size: 16px;"></i>
                                    <span style="font-size: 12px; font-weight: 600;">Chat Now</span>
                                </div>
                            </a>
                            <a href="https://wa.me/919879023570?text=I'm interested in Naroda Lavish" target="_blank" style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 5px; padding: 12px; background: #f0fdf4; border-radius: 12px; text-decoration: none; border: 1px solid #dcfce7; transition: all 0.3s;">
                                <span style="font-size: 11px; font-weight: 700; color: #15803d; text-transform: uppercase;">Lavish</span>
                                <div style="display: flex; align-items: center; gap: 5px; color: #166534;">
                                    <i class="fab fa-whatsapp" style="font-size: 16px;"></i>
                                    <span style="font-size: 12px; font-weight: 600;">Chat Now</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </form>

                <p style="text-align:center;margin-top:20px;font-size:11px;color:black;letter-spacing:0.5px;"> We respect your privacy. No spam, ever.</p>
            </div>
        </div>
    </div>


    <!-- WhatsApp Button & Popup -->
    <div id="wa-popup" class="whatsapp-popup">
        <div class="wa-popup-header">
            <svg fill="currentColor" width="20" height="20" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.414 0 .004 5.408.002 12.043a11.865 11.865 0 001.59 5.853L0 24l6.307-1.654a11.801 11.801 0 005.736 1.493h.005c6.637 0 12.048-5.409 12.05-12.046a11.82 11.82 0 00-3.391-8.525z"></path></svg>
            <span>WhatsApp Us</span>
        </div>
        <a href="https://wa.me/917383185892?text=I'm interested in Naroda Arise" class="wa-popup-row" target="_blank">
            <span class="wa-project-name">Naroda Arise</span>
            <span class="wa-number">+91 73831 85892</span>
            <span class="wa-action">Chat on WhatsApp</span>
        </a>
        <a href="https://wa.me/919879023570?text=I'm interested in Naroda Lavish" class="wa-popup-row" target="_blank">
            <span class="wa-project-name">Naroda Lavish</span>
            <span class="wa-number">+91 98790 23570</span>
            <span class="wa-action">Chat on WhatsApp</span>
        </a>
    </div>

    <a href="javascript:void(0)" id="whatsapp-btn" class="whatsapp-float">
        <svg fill="currentColor" width="30" height="30" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.414 0 .004 5.408.002 12.043a11.865 11.865 0 001.59 5.853L0 24l6.307-1.654a11.801 11.801 0 005.736 1.493h.005c6.637 0 12.048-5.409 12.05-12.046a11.82 11.82 0 00-3.391-8.525z"/></svg>
    </a>

    <!-- System Popup Card -->
    <div id="system-popup">
        <div class="popup-card" id="popup-card-inner">
            <div class="popup-icon-wrap">
                <i id="popup-icon" class="fas fa-check-circle"></i>
            </div>
            <h2 class="popup-title" id="popup-title">Success!</h2>
            <p class="popup-msg" id="popup-msg">Your submission was successful.</p>
            <a href="#" id="popup-wa-link" class="popup-wa-btn" target="_blank">
                <i class="fab fa-whatsapp"></i>
                Continue to WhatsApp
            </a>
            <button class="popup-close-btn" onclick="closePopup()">Dismiss</button>
        </div>
    </div>
    `;


    const container = document.getElementById('header-container');
    if (container) {
        container.innerHTML = headerHTML;

        // Navbar scroll effect
        const navbar = document.getElementById('main-nav');
        const handleScroll = () => {
            if (window.scrollY > 50) {
                // Scrolled state - solid dark
                if (navbar) {
                    navbar.style.background = 'rgba(0,0,0,0.85)';
                    navbar.style.backdropFilter = 'blur(16px)';
                    navbar.style.webkitBackdropFilter = 'blur(16px)';
                    navbar.style.boxShadow = '0 4px 30px rgba(0,0,0,0.2)';
                }
            } else {
                // Top of page — subtle gradient so logo is always visible
                if (navbar) {
                    navbar.style.background = 'linear-gradient(180deg, rgba(0,0,0,0.55) 0%, rgba(0,0,0,0) 100%)';
                    navbar.style.backdropFilter = 'none';
                    navbar.style.webkitBackdropFilter = 'none';
                    navbar.style.boxShadow = 'none';
                }
            }
        };

        window.addEventListener('scroll', handleScroll);
        handleScroll(); // Init

        // Modal logic
        const modal = document.getElementById('quick-enquiry-modal');
        const openBtn = document.getElementById('open-enquiry-modal');
        const mobileOpenBtn = document.getElementById('mobile-enquiry-btn');
        const closeBtn = document.getElementById('close-enquiry-modal');
        const form = document.getElementById('enquiry-modal-form');

        const openModal = () => { if (modal) modal.style.display = 'flex'; };
        const closeModal = () => { if (modal) modal.style.display = 'none'; };

        if (openBtn) openBtn.onclick = openModal;
        if (mobileOpenBtn) {
            mobileOpenBtn.onclick = () => {
                const menu = document.getElementById('mobile-menu');
                const overlay = document.getElementById('mobile-menu-overlay');
                if (menu) menu.style.right = '-300px';
                if (overlay) overlay.style.display = 'none';
                openModal();
            };
        }
        if (closeBtn) closeBtn.onclick = closeModal;
        window.onclick = (e) => { if (e.target == modal) closeModal(); };

        if (form) {
            form.onsubmit = async (e) => {
                e.preventDefault();
                const btn = form.querySelector('button');
                const originalText = btn.textContent;
                btn.textContent = 'Sending...';
                btn.disabled = true;

                const name = document.getElementById('enquiry-name').value;
                const email = document.getElementById('enquiry-email').value;
                const phone = document.getElementById('enquiry-phone').value;
                const project = document.getElementById('enquiry-project').value;
                const message = document.getElementById('enquiry-message').value;

                const formData = new FormData();
                formData.append('name', name);
                formData.append('email', email);
                formData.append('phone', phone);
                formData.append('project', project);
                formData.append('message', message);

                try {
                    const response = await fetch('admin2/api/api.php?action=save_inquiry', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    if (result.success) {
                        // Prepare WhatsApp message
                        let waNumber = '917383185892'; // Default number
                        if (project === 'Naroda Lavish') {
                            waNumber = '919879023570';
                        }

                        const waMessage = `Hello Naroda Group, I'm interested in *${project}*.\n\n*Name:* ${name}\n*Email:* ${email}\n*Phone:* ${phone}\n*Message:* ${message}`;
                        const waUrl = `https://wa.me/${waNumber}?text=${encodeURIComponent(waMessage)}`;

                        showPopup('Thank You!', 'Your inquiry has been submitted. Click below to chat with our team on WhatsApp for a faster response.', 'success', waUrl);
                        closeModal();
                        form.reset();
                    } else {
                        showPopup('Submission Failed', result.error || 'Oops! Something went wrong. Please try again.', 'error');
                    }
                } catch (error) {
                    console.error('Submission error:', error);
                    showPopup('Network Error', 'Could not connect to the server. Please check your internet and try again.', 'error');
                } finally {
                    btn.textContent = originalText;
                    btn.disabled = false;
                }
            };
        }
    };

    // For backward compatibility (if any old code calls these)
    window.showSuccessModal = (url) => showPopup('Success!', 'Operation completed successfully.', 'success', url);
    window.closeSuccessModal = () => closePopup();

    // WhatsApp Popup Toggle
    const waBtn = document.getElementById('whatsapp-btn');
    const waPopup = document.getElementById('wa-popup');
    if (waBtn && waPopup) {
        waBtn.onclick = (e) => {
            e.stopPropagation();
            waPopup.style.display = waPopup.style.display === 'flex' ? 'none' : 'flex';
        };
        document.addEventListener('click', (e) => {
            if (!waPopup.contains(e.target) && e.target !== waBtn) {
                waPopup.style.display = 'none';
            }
        });
    }

    // Hide Preloader on Load
    const hidePreloader = () => {
        const preloader = document.getElementById('preloader');
        if (preloader) {
            preloader.style.opacity = '0';
            setTimeout(() => {
                preloader.style.visibility = 'hidden';
            }, 500);
        }
    };

    if (document.readyState === 'complete') {
        hidePreloader();
    } else {
        window.addEventListener('load', hidePreloader);
        // Fallback: hide preloader after 3 seconds anyway
        setTimeout(hidePreloader, 3000);
    }
}

function renderFooter() {
    const footerHTML = `
    <footer style="background: #fff; color: #111; padding: 60px 20px 30px; font-family: 'Inter', sans-serif; border-top: 1px solid #eee;">
        <div style="max-width: 1400px; margin: 0 auto;">
            <!-- Top Row: Logo & Social -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 50px; flex-wrap: wrap; gap: 30px;">
                <div style="display: flex; flex-direction: column;">
                    <span style="font-family: 'Playfair Display', Georgia, serif; font-size: 28px; font-weight: 800; color: #111; letter-spacing: 0.04em; text-transform: uppercase; line-height: 1;">NARODA</span>
                    <span style="font-family: 'Playfair Display', Georgia, serif; font-size: 11px; font-weight: 400; color: #666; letter-spacing: 0.45em; text-transform: uppercase; font-style: italic; margin-top: 2px;">GROUP</span>
                </div>
                <div style="display: flex; align-items: center; gap: 20px;">
                    <span style="font-size: 14px; color: #666; font-weight: 500;">Follow us on</span>
                    <div style="display: flex; gap: 12px;">
                        <a href="#" style="width: 32px; height: 32px; background: #1877F2; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 14px;"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" style="width: 32px; height: 32px; background: linear-gradient(45deg, #f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%); color: white; border-radius: 8px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 14px;"><i class="fab fa-instagram"></i></a>
                        <a href="#" style="width: 32px; height: 32px; background: #FF0000; color: white; border-radius: 4px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 14px;"><i class="fab fa-youtube"></i></a>
                        <a href="#" style="width: 32px; height: 32px; background: #0077B5; color: white; border-radius: 4px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 14px;"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" style="width: 32px; height: 32px; background: #000; color: white; border-radius: 4px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 14px;"><i class="fa-brands fa-x-twitter"></i></a>
                    </div>
                </div>
            </div>

            <!-- Middle: Columns -->
            <div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr 1.2fr; gap: 40px; margin-bottom: 60px;">
                <!-- Col 1: Brand & Vision -->
                <div>
                    <h4 style="font-size: 15px; font-weight: 700; margin-bottom: 25px; color: #111;">Brand & Vision</h4>
                    <p style="color: #666; line-height: 1.7; font-size: 14px;">To be the obvious, the most trusted choice in real estate creating a better, liveable and comfortable life for everyone.</p>
                </div>

                <!-- Col 2: Explore -->
                <div>
                    <h4 style="font-size: 15px; font-weight: 700; margin-bottom: 25px; color: #111;">Explore</h4>
                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px;">
                        <li><a href="projects-residential.html" style="color: #666; text-decoration: none; font-size: 14px; transition: color 0.3s; font-weight: 600;">Residential Projects</a></li>
                        <li style="padding-left: 10px;"><a href="naroda-lavish.html" style="color: #888; text-decoration: none; font-size: 13px; transition: color 0.3s;">- Naroda Lavish</a></li>
                        <li style="padding-left: 10px;"><a href="naroda-arise.html" style="color: #888; text-decoration: none; font-size: 13px; transition: color 0.3s;">- Naroda Arise</a></li>
                    
                        <li><a href="blog.html" style="color: #666; text-decoration: none; font-size: 14px; transition: color 0.3s; font-weight: 600;">Blogs</a></li>
                    </ul>
                </div>

                <!-- Col 3: About Naroda -->
                <div>
                    <h4 style="font-size: 15px; font-weight: 700; margin-bottom: 25px; color: #111;">About Naroda</h4>
                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px;">
                        <li><a href="about-us.html" style="color: #666; text-decoration: none; font-size: 14px; transition: color 0.3s;">About Us</a></li>
                        <li><a href="team.html" style="color: #666; text-decoration: none; font-size: 14px; transition: color 0.3s;">Our Team</a></li>
                        <li><a href="careers.html" style="color: #666; text-decoration: none; font-size: 14px; transition: color 0.3s;">Careers</a></li>
                        <li><a href="contact-us.html" style="color: #666; text-decoration: none; font-size: 14px; transition: color 0.3s;">Contact Us</a></li>
                    </ul>
                </div>

                <!-- Col 4: Get in Touch -->
                <div>
                    <h4 style="font-size: 15px; font-weight: 700; margin-bottom: 25px; color: #111;">Get in Touch</h4>
                    <div style="display: flex; flex-direction: column; gap: 18px;">
                        <div style="display: flex; gap: 12px; align-items: flex-start;">
                            <i class="fa-solid fa-location-dot" style="margin-top: 4px; font-size: 14px; color: #333;"></i>
                            <p style="color: #666; font-size: 14px; line-height: 1.5; margin: 0;">Opp. Naroda Business Hub,<br>Naroda-Dahegam Road, Hanspura, Naroda,<br>Ahmedabad, Gujarat 382330</p>
                        </div>
                        <div style="display: flex; gap: 12px; align-items: flex-start;">
                            <i class="fa-solid fa-phone" style="margin-top: 4px; font-size: 14px; color: #333;"></i>
                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                <span style="color: #666; font-size: 14px;">+91 98982 11567</span>
                                <span style="color: #666; font-size: 14px;">+91 98985 08567</span>
                            </div>
                        </div>
                        <div style="display: flex; gap: 12px; align-items: flex-start;">
                            <i class="fa-solid fa-envelope" style="margin-top: 4px; font-size: 14px; color: #333;"></i>
                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                <span style="color: #666; font-size: 14px; word-break: break-all;">narodagroup.pvt@gmail.com</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Row -->
            <div style="padding-top: 25px; border-top: 1px solid #f0f0f0; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
                <p style="color: #999; font-size: 12px; margin: 0;">&copy; 2026 All rights reserved</p>
                <div style="display: flex; gap: 15px;">
                    <a href="terms-of-service.html" style="color: #999; text-decoration: none; font-size: 12px;">Terms of Service</a>
                    <span style="color: #ddd;">|</span>
                    <a href="privacy-policy.html" style="color: #999; text-decoration: none; font-size: 12px;">Privacy Policy</a>
                </div>
            </div>
        </div>
    </footer>
    <style>
        @media (max-width: 991px) {
            footer > div > div:nth-child(2) {
                grid-template-columns: 1fr 1fr !important;
            }
        }
        @media (max-width: 576px) {
            footer > div > div:nth-child(2) {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
    `;
    const container = document.getElementById('footer-container');
    if (container) {
        container.innerHTML = footerHTML;
    }
}


function getAmenityIcon(title) {
    if (!title) return 'fa-star';
    const t = title.toLowerCase();

    // Specific matches
    if (t.includes('yoga') || t.includes('meditation')) return 'fa-spa';
    if (t.includes('gym') || t.includes('fitness')) return 'fa-dumbbell';
    if (t.includes('swimming') || (t.includes('pool') && !t.includes('table'))) return 'fa-swimming-pool';
    if (t.includes('pool table')) return 'fa-table-columns';
    if (t.includes('clubhouse') || t.includes('club house')) return 'fa-users';
    if (t.includes('hall') || t.includes('multipurpose')) return 'fa-people-roof';
    if (t.includes('theater') || t.includes('cinema') || t.includes('movie')) return 'fa-film';
    if (t.includes('game') || t.includes('indoor')) return 'fa-gamepad';
    if (t.includes('play') || t.includes('child') || t.includes('toddler') || t.includes('kid')) return 'fa-child-reaching';

    // Check parking before park
    if (t.includes('parking')) return 'fa-square-parking';
    if (t.includes('garden') || t.includes('landscape') || t.includes('park') || t.includes('green') || t.includes('lawn')) return 'fa-leaf';

    if (t.includes('senior')) return 'fa-person-cane';
    if (t.includes('gazebo') || t.includes('sit-out') || t.includes('deck') || t.includes('pavilion')) return 'fa-umbrella-beach';
    if (t.includes('gathering') || t.includes('seating') || t.includes('zone')) return 'fa-users-rectangle';
    if (t.includes('cricket')) return 'fa-baseball-bat-ball';
    if (t.includes('basketball')) return 'fa-basketball';
    if (t.includes('tennis')) return 'fa-table-tennis-paddle-ball';
    if (t.includes('chess')) return 'fa-chess-board';
    if (t.includes('security') || t.includes('shield')) return 'fa-shield-halved';
    if (t.includes('cctv') || t.includes('camera') || t.includes('surveillance')) return 'fa-video';
    if (t.includes('elevator') || t.includes('lift')) return 'fa-elevator';
    if (t.includes('fire') || t.includes('safety')) return 'fa-fire-extinguisher';
    if (t.includes('power') || t.includes('backup') || t.includes('generator')) return 'fa-bolt';
    if (t.includes('solar')) return 'fa-sun';
    if (t.includes('water') || t.includes('borewell') || t.includes('faucet')) return 'fa-faucet-drip';
    if (t.includes('meter')) return 'fa-bolt-lightning';
    if (t.includes('office')) return 'fa-briefcase';
    if (t.includes('drop off')) return 'fa-car-side';
    if (t.includes('intercom')) return 'fa-phone-volume';
    if (t.includes('lobby') || t.includes('reception')) return 'fa-couch';
    if (t.includes('jogging') || t.includes('walking') || t.includes('track')) return 'fa-person-running';
    if (t.includes('library') || t.includes('read')) return 'fa-book';
    if (t.includes('wifi') || t.includes('internet')) return 'fa-wifi';
    if (t.includes('temple') || t.includes('puja')) return 'fa-om';
    if (t.includes('shop') || t.includes('mart') || t.includes('retail')) return 'fa-cart-shopping';

    // Variety Fallback
    const icons = ['fa-circle-check', 'fa-gem', 'fa-medal', 'fa-trophy', 'fa-award', 'fa-crown', 'fa-certificate', 'fa-building', 'fa-house-chimney', 'fa-city'];
    let hash = 0;
    for (let i = 0; i < title.length; i++) hash = ((hash << 5) - hash) + title.charCodeAt(i);
    return icons[Math.abs(hash) % icons.length];
}
window.getAmenityIcon = getAmenityIcon;

function initMobileMenu() {
    const btn = document.getElementById('mobile-menu-btn');
    const closeBtn = document.getElementById('close-menu-btn');
    const menu = document.getElementById('mobile-menu');
    const overlay = document.getElementById('mobile-menu-overlay');

    if (btn && menu && overlay) {
        btn.onclick = () => {
            menu.style.right = '0';
            overlay.style.display = 'block';
        };
    }
    const close = () => {
        if (menu) menu.style.right = '-300px';
        if (overlay) overlay.style.display = 'none';
    };
    if (closeBtn) closeBtn.onclick = close;
    if (overlay) overlay.onclick = close;
}

function init() {
    renderHeader();
    renderFooter();
    initMobileMenu();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}