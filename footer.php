<?php
/**
 * ==========================================
 * فایل فوتر سایت - Footer حرفه‌ای
 * ==========================================
 * نسخه: 1.0
 * تاریخ: 1404/11/20
 * ==========================================
 */

// دریافت دسته‌بندی‌های اصلی برای لینک‌های سریع
$footerCategories = Database::fetchAll(
    "SELECT id, name, slug 
     FROM categories 
     WHERE parent_id IS NULL AND status = 'active' AND deleted_at IS NULL 
     ORDER BY sort_order ASC 
     LIMIT 5"
);

// آمار سایت برای نمایش
$stats = [
    'products' => Database::fetchOne("SELECT COUNT(*) as count FROM products WHERE status = 'active' AND deleted_at IS NULL")['count'] ?? 0,
    'customers' => Database::fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'customer' AND deleted_at IS NULL")['count'] ?? 0,
    'orders' => Database::fetchOne("SELECT COUNT(*) as count FROM orders WHERE deleted_at IS NULL")['count'] ?? 0
];
?>

<!-- Inline Critical CSS for Footer -->
<style>
    /* ==========================================
       Footer Styles - استایل فوتر
       ========================================== */
    .main-footer {
        background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
        color: #e0e0e0;
        padding: 60px 0 0;
        margin-top: 80px;
        position: relative;
        overflow: hidden;
    }
    
    .main-footer::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, 
            transparent, 
            var(--color-secondary), 
            transparent
        );
    }
    
    .footer-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 40px;
    }
    
    /* ==========================================
       Footer Grid - شبکه فوتر
       ========================================== */
    .footer-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1.5fr;
        gap: 50px;
        margin-bottom: 50px;
    }
    
    /* ==========================================
       Footer Column - ستون‌های فوتر
       ========================================== */
    .footer-column {
        animation: fadeInUp 0.6s ease-out backwards;
    }
    
    .footer-column:nth-child(1) { animation-delay: 0.1s; }
    .footer-column:nth-child(2) { animation-delay: 0.2s; }
    .footer-column:nth-child(3) { animation-delay: 0.3s; }
    .footer-column:nth-child(4) { animation-delay: 0.4s; }
    
    .footer-title {
        font-family: var(--font-display);
        font-size: 18px;
        font-weight: 600;
        color: white;
        margin-bottom: 25px;
        position: relative;
        padding-bottom: 12px;
    }
    
    .footer-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        right: 0;
        width: 40px;
        height: 2px;
        background: var(--color-secondary);
    }
    
    /* ==========================================
       About Column - ستون درباره ما
       ========================================== */
    .footer-about {
        max-width: 350px;
    }
    
    .footer-logo {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        text-decoration: none;
    }
    
    .footer-logo-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, var(--color-secondary), #b8960e);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
        transition: var(--transition-smooth);
    }
    
    .footer-logo:hover .footer-logo-icon {
        transform: rotate(5deg) scale(1.05);
    }
    
    .footer-logo-text {
        font-family: var(--font-display);
        font-size: 22px;
        font-weight: 700;
        color: white;
        letter-spacing: 1px;
    }
    
    .footer-description {
        color: #b0b0b0;
        font-size: 14px;
        line-height: 1.8;
        margin-bottom: 25px;
    }
    
    /* Social Links */
    .footer-social {
        display: flex;
        gap: 12px;
    }
    
    .social-link {
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #b0b0b0;
        text-decoration: none;
        font-size: 16px;
        transition: var(--transition-smooth);
        position: relative;
        overflow: hidden;
    }
    
    .social-link::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        background: var(--color-secondary);
        border-radius: 50%;
        transform: translate(-50%, -50%);
        transition: var(--transition-smooth);
    }
    
    .social-link:hover {
        color: white;
        border-color: var(--color-secondary);
        transform: translateY(-3px);
    }
    
    .social-link:hover::before {
        width: 100%;
        height: 100%;
    }
    
    .social-link i {
        position: relative;
        z-index: 1;
    }
    
    /* ==========================================
       Quick Links - لینک‌های سریع
       ========================================== */
    .footer-links {
        list-style: none;
        padding: 0;
    }
    
    .footer-link-item {
        margin-bottom: 12px;
    }
    
    .footer-link {
        color: #b0b0b0;
        text-decoration: none;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: var(--transition-fast);
        padding: 6px 0;
    }
    
    .footer-link i {
        font-size: 12px;
        color: var(--color-secondary);
        transition: var(--transition-fast);
    }
    
    .footer-link:hover {
        color: white;
        padding-right: 8px;
    }
    
    .footer-link:hover i {
        transform: translateX(-3px);
    }
    
    /* ==========================================
       Contact Info - اطلاعات تماس
       ========================================== */
    .footer-contact-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 20px;
        color: #b0b0b0;
        font-size: 14px;
        line-height: 1.6;
    }
    
    .footer-contact-icon {
        width: 35px;
        height: 35px;
        background: rgba(212, 175, 55, 0.1);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--color-secondary);
        font-size: 14px;
        flex-shrink: 0;
    }
    
    .footer-contact-text a {
        color: #b0b0b0;
        text-decoration: none;
        transition: var(--transition-fast);
    }
    
    .footer-contact-text a:hover {
        color: var(--color-secondary);
    }
    
    /* ==========================================
       Trust Badges - نمادهای اعتماد
       ========================================== */
    .footer-badges {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-top: 25px;
    }
    
    .trust-badge {
        background: white;
        border-radius: 12px;
        padding: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition-smooth);
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }
    
    .trust-badge::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(212, 175, 55, 0.1), transparent);
        transition: var(--transition-slow);
    }
    
    .trust-badge:hover::before {
        left: 100%;
    }
    
    .trust-badge:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(212, 175, 55, 0.2);
    }
    
    .trust-badge img {
        height: 50px;
        object-fit: contain;
        filter: grayscale(100%);
        transition: var(--transition-smooth);
    }
    
    .trust-badge:hover img {
        filter: grayscale(0%);
    }
    
    /* ==========================================
       Newsletter - خبرنامه
       ========================================== */
    .footer-newsletter {
        margin-top: 30px;
    }
    
    .newsletter-description {
        color: #b0b0b0;
        font-size: 13px;
        margin-bottom: 15px;
        line-height: 1.6;
    }
    
    .newsletter-form {
        display: flex;
        gap: 10px;
    }
    
    .newsletter-input {
        flex: 1;
        padding: 12px 16px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(255, 255, 255, 0.05);
        border-radius: 8px;
        color: white;
        font-size: 13px;
        transition: var(--transition-fast);
        outline: none;
    }
    
    .newsletter-input::placeholder {
        color: #666;
    }
    
    .newsletter-input:focus {
        border-color: var(--color-secondary);
        background: rgba(255, 255, 255, 0.08);
    }
    
    .newsletter-submit {
        padding: 12px 24px;
        background: var(--color-secondary);
        color: var(--color-primary);
        border: none;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition-smooth);
        white-space: nowrap;
    }
    
    .newsletter-submit:hover {
        background: #e4bf47;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
    }
    
    /* ==========================================
       Stats Bar - نوار آمار
       ========================================== */
    .footer-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
        padding: 40px 0;
        border-top: 1px solid rgba(255, 255, 255, 0.05);
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        margin-bottom: 40px;
    }
    
    .stat-item {
        text-align: center;
    }
    
    .stat-number {
        font-family: var(--font-display);
        font-size: 32px;
        font-weight: 700;
        color: var(--color-secondary);
        display: block;
        margin-bottom: 8px;
    }
    
    .stat-label {
        color: #b0b0b0;
        font-size: 13px;
    }
    
    /* ==========================================
       Footer Bottom - کپی‌رایت
       ========================================== */
    .footer-bottom {
        padding: 25px 0;
        border-top: 1px solid rgba(255, 255, 255, 0.05);
    }
    
    .footer-bottom-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #666;
        font-size: 13px;
    }
    
    .footer-copyright {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .footer-heart {
        color: var(--color-accent);
        animation: heartbeat 1.5s infinite;
    }
    
    @keyframes heartbeat {
        0%, 100% { transform: scale(1); }
        25% { transform: scale(1.1); }
        50% { transform: scale(1); }
    }
    
    .footer-developer {
        color: var(--color-secondary);
        text-decoration: none;
        font-weight: 600;
        transition: var(--transition-fast);
    }
    
    .footer-developer:hover {
        color: #e4bf47;
    }
    
    .footer-payment-icons {
        display: flex;
        gap: 15px;
        align-items: center;
    }
    
    .payment-icon {
        width: 45px;
        height: 30px;
        background: white;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 5px;
        opacity: 0.7;
        transition: var(--transition-fast);
    }
    
    .payment-icon:hover {
        opacity: 1;
        transform: translateY(-2px);
    }
    
    .payment-icon img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
    
    /* ==========================================
       Responsive Design
       ========================================== */
    @media (max-width: 1200px) {
        .footer-grid {
            grid-template-columns: 1.5fr 1fr 1fr 1.5fr;
            gap: 40px;
        }
    }
    
    @media (max-width: 992px) {
        .footer-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 40px;
        }
        
        .footer-stats {
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
    }
    
    @media (max-width: 768px) {
        .main-footer {
            padding: 40px 0 0;
            margin-top: 60px;
        }
        
        .footer-container {
            padding: 0 20px;
        }
        
        .footer-grid {
            grid-template-columns: 1fr;
            gap: 35px;
        }
        
        .footer-about {
            max-width: 100%;
        }
        
        .footer-stats {
            grid-template-columns: 1fr;
            gap: 25px;
        }
        
        .footer-bottom-content {
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }
        
        .footer-payment-icons {
            justify-content: center;
        }
        
        .newsletter-form {
            flex-direction: column;
        }
        
        .footer-badges {
            grid-template-columns: 1fr;
        }
    }
    
    /* ==========================================
       Back to Top Button
       ========================================== */
    .back-to-top {
        position: fixed;
        bottom: 30px;
        left: 30px;
        width: 50px;
        height: 50px;
        background: var(--color-secondary);
        color: var(--color-primary);
        border: none;
        border-radius: 50%;
        font-size: 20px;
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        transform: translateY(20px);
        transition: var(--transition-smooth);
        box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
        z-index: 999;
    }
    
    .back-to-top.visible {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    
    .back-to-top:hover {
        background: #e4bf47;
        transform: translateY(-5px);
        box-shadow: 0 6px 20px rgba(212, 175, 55, 0.4);
    }
</style>

<!-- Main Footer -->
<footer class="main-footer">
    <div class="footer-container">
        
        <!-- Footer Grid -->
        <div class="footer-grid">
            
            <!-- ستون اول: درباره ما و شبکه‌های اجتماعی -->
            <div class="footer-column">
                <div class="footer-about">
                    <a href="index.php" class="footer-logo">
                        <div class="footer-logo-icon">✦</div>
                        <div class="footer-logo-text"><?php echo SITE_NAME; ?></div>
                    </a>
                    
                    <p class="footer-description">
                        ما با افتخار بهترین و باکیفیت‌ترین محصولات پوشاک، کیف و کفش را 
                        با قیمت مناسب و ارسال سریع به سراسر کشور ارائه می‌دهیم. 
                        رضایت شما افتخار ماست.
                    </p>
                    
                    <!-- شبکه‌های اجتماعی -->
                    <div class="footer-social">
                        <a href="#" class="social-link" aria-label="اینستاگرام">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="تلگرام">
                            <i class="fab fa-telegram"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="واتساپ">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="لینکدین">
                            <i class="fab fa-linkedin"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="توییتر">
                            <i class="fab fa-twitter"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- ستون دوم: دسترسی سریع -->
            <div class="footer-column">
                <h3 class="footer-title">دسترسی سریع</h3>
                <ul class="footer-links">
                    <li class="footer-link-item">
                        <a href="index.php" class="footer-link">
                            <i class="fas fa-chevron-left"></i>
                            خانه
                        </a>
                    </li>
                    <li class="footer-link-item">
                        <a href="products.php" class="footer-link">
                            <i class="fas fa-chevron-left"></i>
                            محصولات
                        </a>
                    </li>
                    <?php foreach ($footerCategories as $category): ?>
                        <li class="footer-link-item">
                            <a href="products.php?category=<?php echo $category['id']; ?>" class="footer-link">
                                <i class="fas fa-chevron-left"></i>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <li class="footer-link-item">
                        <a href="about.php" class="footer-link">
                            <i class="fas fa-chevron-left"></i>
                            درباره ما
                        </a>
                    </li>
                    <li class="footer-link-item">
                        <a href="contact.php" class="footer-link">
                            <i class="fas fa-chevron-left"></i>
                            تماس با ما
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- ستون سوم: خدمات مشتریان -->
            <div class="footer-column">
                <h3 class="footer-title">خدمات مشتریان</h3>
                <ul class="footer-links">
                    <li class="footer-link-item">
                        <a href="profile.php" class="footer-link">
                            <i class="fas fa-chevron-left"></i>
                            حساب کاربری
                        </a>
                    </li>
                    <li class="footer-link-item">
                        <a href="profile.php?tab=orders" class="footer-link">
                            <i class="fas fa-chevron-left"></i>
                            پیگیری سفارش
                        </a>
                    </li>
                    <li class="footer-link-item">
                        <a href="wishlist.php" class="footer-link">
                            <i class="fas fa-chevron-left"></i>
                            لیست علاقه‌مندی‌ها
                        </a>
                    </li>
                    <li class="footer-link-item">
                        <a href="cart.php" class="footer-link">
                            <i class="fas fa-chevron-left"></i>
                            سبد خرید
                        </a>
                    </li>
                    <li class="footer-link-item">
                        <a href="#" class="footer-link">
                            <i class="fas fa-chevron-left"></i>
                            سوالات متداول
                        </a>
                    </li>
                    <li class="footer-link-item">
                        <a href="#" class="footer-link">
                            <i class="fas fa-chevron-left"></i>
                            قوانین و مقررات
                        </a>
                    </li>
                    <li class="footer-link-item">
                        <a href="#" class="footer-link">
                            <i class="fas fa-chevron-left"></i>
                            رویه‌های بازگشت کالا
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- ستون چهارم: تماس با ما و نمادها -->
            <div class="footer-column">
                <h3 class="footer-title">تماس با ما</h3>
                
                <div class="footer-contact-item">
                    <div class="footer-contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="footer-contact-text">
                        <strong>تلفن پشتیبانی:</strong><br>
                        <a href="tel:02112345678">021-1234-5678</a>
                    </div>
                </div>
                
                <div class="footer-contact-item">
                    <div class="footer-contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="footer-contact-text">
                        <strong>ایمیل:</strong><br>
                        <a href="mailto:info@example.com">info@example.com</a>
                    </div>
                </div>
                
                <div class="footer-contact-item">
                    <div class="footer-contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="footer-contact-text">
                        <strong>آدرس:</strong><br>
                        تهران، خیابان ولیعصر، پلاک ۱۲۳
                    </div>
                </div>
                
                <!-- نمادهای اعتماد -->
                <div class="footer-badges">
                    <div class="trust-badge">
                        <img src="https://trustseal.enamad.ir/logo.aspx?id=placeholder&amp;Code=placeholder" 
                             alt="نماد اعتماد الکترونیکی">
                    </div>
                    <div class="trust-badge">
                        <img src="https://logo.samandehi.ir/logo.aspx?id=placeholder&amp;p=placeholder" 
                             alt="ساماندهی">
                    </div>
                </div>
                
                <!-- خبرنامه -->
                <div class="footer-newsletter">
                    <p class="newsletter-description">
                        برای دریافت جدیدترین محصولات و تخفیف‌های ویژه، ایمیل خود را وارد کنید:
                    </p>
                    <form class="newsletter-form" method="post" action="subscribe.php">
                        <input type="email" 
                               name="email" 
                               class="newsletter-input" 
                               placeholder="ایمیل شما..." 
                               required>
                        <button type="submit" class="newsletter-submit">
                            عضویت
                        </button>
                    </form>
                </div>
            </div>
            
        </div>
        
        <!-- Stats Bar -->
        <div class="footer-stats">
            <div class="stat-item">
                <span class="stat-number" data-target="<?php echo $stats['products']; ?>">0</span>
                <span class="stat-label">محصول فعال</span>
            </div>
            <div class="stat-item">
                <span class="stat-number" data-target="<?php echo $stats['customers']; ?>">0</span>
                <span class="stat-label">مشتری راضی</span>
            </div>
            <div class="stat-item">
                <span class="stat-number" data-target="<?php echo $stats['orders']; ?>">0</span>
                <span class="stat-label">سفارش تکمیل شده</span>
            </div>
        </div>
        
        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <div class="footer-copyright">
                    © <?php echo date('Y'); ?> 
                    <span><?php echo SITE_NAME; ?></span>. 
                    تمامی حقوق محفوظ است. 
                    ساخته شده با 
                    <i class="fas fa-heart footer-heart"></i> 
                    توسط 
                    <a href="#" class="footer-developer">تیم توسعه</a>
                </div>
                
                <div class="footer-payment-icons">
                    <div class="payment-icon" title="درگاه زرین‌پال">
                        💳
                    </div>
                    <div class="payment-icon" title="پرداخت آنلاین">
                        💰
                    </div>
                    <div class="payment-icon" title="پرداخت در محل">
                        📦
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</footer>

<!-- Back to Top Button -->
<button class="back-to-top" id="backToTop" aria-label="بازگشت به بالا">
    <i class="fas fa-arrow-up"></i>
</button>

<!-- Footer JavaScript -->
<script>
    // ==========================================
    // Stats Counter Animation
    // ==========================================
    function animateCounter(element) {
        const target = parseInt(element.getAttribute('data-target'));
        const duration = 2000;
        const increment = target / (duration / 16);
        let current = 0;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                element.textContent = target.toLocaleString('fa-IR');
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(current).toLocaleString('fa-IR');
            }
        }, 16);
    }
    
    // Intersection Observer برای شروع انیمیشن هنگام نمایش
    const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counters = entry.target.querySelectorAll('.stat-number');
                counters.forEach(counter => animateCounter(counter));
                statsObserver.unobserve(entry.target);
            }
        });
    });
    
    const statsSection = document.querySelector('.footer-stats');
    if (statsSection) {
        statsObserver.observe(statsSection);
    }
    
    // ==========================================
    // Back to Top Button
    // ==========================================
    const backToTopBtn = document.getElementById('backToTop');
    
    window.addEventListener('scroll', () => {
        if (window.scrollY > 500) {
            backToTopBtn.classList.add('visible');
        } else {
            backToTopBtn.classList.remove('visible');
        }
    });
    
    backToTopBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // ==========================================
    // Newsletter Form Submission
    // ==========================================
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const emailInput = this.querySelector('input[name="email"]');
            const submitBtn = this.querySelector('.newsletter-submit');
            
            // Disable button
            submitBtn.disabled = true;
            submitBtn.textContent = 'در حال ثبت...';
            
            // Simulate AJAX request
            setTimeout(() => {
                alert('✅ ایمیل شما با موفقیت ثبت شد!');
                emailInput.value = '';
                submitBtn.disabled = false;
                submitBtn.textContent = 'عضویت';
            }, 1000);
        });
    }
</script>

</body>
</html>
