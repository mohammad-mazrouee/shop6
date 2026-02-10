<?php
/**
 * ==========================================
 * صفحه اصلی (Homepage)
 * ==========================================
 * نسخه: 1.0
 * تاریخ: 1404/11/20
 * ==========================================
 */

require_once 'config.php';

// دریافت محصولات برای بخش‌های مختلف
$newArrivals = Database::fetchAll(
    "SELECT p.*, 
            (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id AND pi.is_main = 1 LIMIT 1) as main_image,
            (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id AND pi.is_main = 0 LIMIT 1) as hover_image
     FROM products p 
     WHERE p.status = 'active' AND p.deleted_at IS NULL 
     ORDER BY p.created_at DESC 
     LIMIT 8"
);

$bestSellers = Database::fetchAll(
    "SELECT p.*, 
            (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id AND pi.is_main = 1 LIMIT 1) as main_image,
            (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id AND pi.is_main = 0 LIMIT 1) as hover_image,
            (SELECT COUNT(*) FROM order_items oi WHERE oi.product_id = p.id) as sales_count
     FROM products p 
     WHERE p.status = 'active' AND p.deleted_at IS NULL 
     ORDER BY sales_count DESC 
     LIMIT 8"
);

$trending = Database::fetchAll(
    "SELECT p.*, 
            (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id AND pi.is_main = 1 LIMIT 1) as main_image,
            (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id AND pi.is_main = 0 LIMIT 1) as hover_image
     FROM products p 
     WHERE p.status = 'active' AND p.is_featured = 1 AND p.deleted_at IS NULL 
     ORDER BY p.view_count DESC 
     LIMIT 6"
);

// دریافت نظرات VIP
$vipReviews = Database::fetchAll(
    "SELECT r.*, u.full_name, u.avatar, p.name as product_name,
            (SELECT pv.value FROM product_attribute_values pav 
             JOIN product_variant_values pvv ON pav.id = pvv.attribute_value_id
             JOIN product_variants pv ON pvv.variant_id = pv.id
             JOIN attributes a ON pav.attribute_id = a.id
             WHERE pvv.variant_id IN (
                 SELECT oi.variant_id FROM order_items oi 
                 JOIN orders o ON oi.order_id = o.id 
                 WHERE o.user_id = r.user_id AND oi.product_id = r.product_id 
                 LIMIT 1
             ) AND a.name = 'سایز' LIMIT 1) as purchased_size,
            (SELECT o.created_at FROM orders o 
             JOIN order_items oi ON o.id = oi.order_id 
             WHERE o.user_id = r.user_id AND oi.product_id = r.product_id 
             ORDER BY o.created_at DESC LIMIT 1) as purchase_date
     FROM reviews r
     JOIN users u ON r.user_id = u.id
     JOIN products p ON r.product_id = p.id
     WHERE r.status = 'approved' AND r.rating >= 4
     ORDER BY r.rating DESC, r.created_at DESC
     LIMIT 6"
);

// دریافت دسته‌بندی‌ها برای Hero Slider
$heroCategories = Database::fetchAll(
    "SELECT c.*, 
            (SELECT COUNT(*) FROM products WHERE category_id = c.id AND status = 'active' AND deleted_at IS NULL) as product_count
     FROM categories c
     WHERE c.parent_id IS NULL AND c.status = 'active' AND c.deleted_at IS NULL
     ORDER BY c.sort_order ASC
     LIMIT 3"
);

include 'header.php';
?>

<!-- Inline Critical CSS for Homepage -->
<style>
    /* ==========================================
       Hero Section - قسمت بنر اصلی
       ========================================== */
    .hero-section {
        position: relative;
        height: 90vh;
        min-height: 650px;
        overflow: hidden;
        margin-top: -90px;
        padding-top: 90px;
    }
    
    .hero-slide {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        transition: opacity 1s ease-in-out;
        background-size: cover;
        background-position: center;
    }
    
    .hero-slide.active {
        opacity: 1;
        z-index: 1;
    }
    
    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.3) 100%);
        z-index: 2;
    }
    
    .hero-content {
        position: absolute;
        top: 50%;
        right: 10%;
        transform: translateY(-50%);
        z-index: 3;
        max-width: 600px;
        color: white;
    }
    
    .hero-subtitle {
        font-size: 14px;
        letter-spacing: 3px;
        text-transform: uppercase;
        color: var(--color-secondary);
        margin-bottom: 15px;
        animation: fadeInDown 0.8s ease-out;
    }
    
    .hero-title {
        font-family: var(--font-display);
        font-size: 64px;
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: 20px;
        animation: fadeInUp 0.8s ease-out 0.2s backwards;
    }
    
    .hero-description {
        font-size: 18px;
        line-height: 1.6;
        margin-bottom: 35px;
        color: rgba(255,255,255,0.9);
        animation: fadeInUp 0.8s ease-out 0.4s backwards;
    }
    
    .hero-cta {
        display: inline-flex;
        align-items: center;
        gap: 15px;
        padding: 16px 40px;
        background: white;
        color: var(--color-primary);
        text-decoration: none;
        font-weight: 600;
        border-radius: 50px;
        transition: var(--transition-smooth);
        animation: fadeInUp 0.8s ease-out 0.6s backwards;
    }
    
    .hero-cta:hover {
        background: var(--color-secondary);
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(212, 175, 55, 0.3);
    }
    
    .hero-indicators {
        position: absolute;
        bottom: 40px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 3;
        display: flex;
        gap: 12px;
    }
    
    .hero-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: rgba(255,255,255,0.4);
        border: 2px solid white;
        cursor: pointer;
        transition: var(--transition-fast);
    }
    
    .hero-indicator.active {
        background: var(--color-secondary);
        width: 40px;
        border-radius: 6px;
    }
    
    /* ==========================================
       Section Headers - عناوین بخش‌ها
       ========================================== */
    .section {
        padding: 80px 0;
    }
    
    .section-header {
        text-align: center;
        margin-bottom: 60px;
    }
    
    .section-subtitle {
        font-size: 13px;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: var(--color-secondary);
        margin-bottom: 10px;
    }
    
    .section-title {
        font-family: var(--font-display);
        font-size: 42px;
        font-weight: 700;
        color: var(--color-primary);
        margin-bottom: 15px;
    }
    
    .section-description {
        font-size: 16px;
        color: #666;
        max-width: 600px;
        margin: 0 auto;
    }
    
    /* ==========================================
       Product Grid - شبکه محصولات
       ========================================== */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 30px;
    }
    
    /* ==========================================
       Slider Container - کانتینر اسلایدر
       ========================================== */
    .slider-container {
        position: relative;
        overflow: hidden;
        padding: 0 50px;
    }
    
    .slider-track {
        display: flex;
        gap: 30px;
        transition: transform 0.5s ease-out;
    }
    
    .slider-item {
        flex: 0 0 calc(33.333% - 20px);
        min-width: calc(33.333% - 20px);
    }
    
    .slider-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 45px;
        height: 45px;
        background: white;
        border: 1px solid #eee;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--transition-smooth);
        z-index: 2;
    }
    
    .slider-nav:hover {
        background: var(--color-secondary);
        color: white;
        border-color: var(--color-secondary);
        transform: translateY(-50%) scale(1.1);
    }
    
    .slider-nav.prev { right: 0; }
    .slider-nav.next { left: 0; }
    
    /* ==========================================
       VIP Reviews - نظرات ویژه
       ========================================== */
    .reviews-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
    }
    
    .review-card {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        padding: 30px;
        transition: var(--transition-smooth);
        position: relative;
        overflow: hidden;
    }
    
    .review-card::before {
        content: '"';
        position: absolute;
        top: 20px;
        left: 20px;
        font-family: var(--font-display);
        font-size: 120px;
        color: rgba(212, 175, 55, 0.1);
        line-height: 1;
    }
    
    .review-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        border-color: rgba(212, 175, 55, 0.3);
    }
    
    .review-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
        position: relative;
        z-index: 1;
    }
    
    .review-avatar {
        width: 55px;
        height: 55px;
        border-radius: 50%;
        border: 2px solid var(--color-secondary);
        object-fit: cover;
    }
    
    .review-user-info h4 {
        font-size: 16px;
        font-weight: 600;
        color: var(--color-primary);
        margin-bottom: 5px;
    }
    
    .review-verified {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        color: #22c55e;
    }
    
    .review-rating {
        display: flex;
        gap: 3px;
        margin-bottom: 15px;
    }
    
    .review-rating i {
        color: #fbbf24;
        font-size: 14px;
    }
    
    .review-text {
        font-size: 14px;
        line-height: 1.8;
        color: #555;
        margin-bottom: 20px;
    }
    
    .review-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 15px;
        border-top: 1px solid rgba(0,0,0,0.05);
        font-size: 12px;
        color: #999;
    }
    
    .review-product {
        font-weight: 600;
        color: var(--color-primary);
    }
    
    /* ==========================================
       VIP Club Signup - عضویت باشگاه
       ========================================== */
    .vip-club-section {
        background: linear-gradient(135deg, var(--color-primary) 0%, #2d2d2d 100%);
        padding: 60px 0;
        position: relative;
        overflow: hidden;
    }
    
    .vip-club-section::before {
        content: '✦';
        position: absolute;
        top: 50%;
        left: 10%;
        transform: translate(-50%, -50%);
        font-size: 300px;
        color: rgba(212, 175, 55, 0.05);
    }
    
    .vip-club-content {
        max-width: 800px;
        margin: 0 auto;
        text-align: center;
        position: relative;
        z-index: 1;
    }
    
    .vip-club-title {
        font-family: var(--font-display);
        font-size: 36px;
        font-weight: 700;
        color: white;
        margin-bottom: 15px;
    }
    
    .vip-club-description {
        font-size: 16px;
        color: rgba(255,255,255,0.8);
        margin-bottom: 35px;
    }
    
    .vip-signup-form {
        display: flex;
        gap: 15px;
        max-width: 600px;
        margin: 0 auto;
    }
    
    .vip-input {
        flex: 1;
        padding: 18px 25px;
        border: 2px solid rgba(255,255,255,0.2);
        background: rgba(255,255,255,0.1);
        border-radius: 50px;
        color: white;
        font-size: 15px;
        outline: none;
        transition: var(--transition-fast);
    }
    
    .vip-input::placeholder {
        color: rgba(255,255,255,0.5);
    }
    
    .vip-input:focus {
        border-color: var(--color-secondary);
        background: rgba(255,255,255,0.15);
    }
    
    .vip-submit {
        padding: 18px 45px;
        background: var(--color-secondary);
        color: var(--color-primary);
        border: none;
        border-radius: 50px;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition-smooth);
        white-space: nowrap;
    }
    
    .vip-submit:hover {
        background: #e4bf47;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(212, 175, 55, 0.4);
    }
    
    /* ==========================================
       View All Button - دکمه مشاهده همه
       ========================================== */
    .view-all-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 14px 35px;
        background: transparent;
        color: var(--color-primary);
        border: 2px solid var(--color-primary);
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        transition: var(--transition-smooth);
        margin: 40px auto 0;
    }
    
    .view-all-btn:hover {
        background: var(--color-primary);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }
    
    /* ==========================================
       Responsive Design
       ========================================== */
    @media (max-width: 1200px) {
        .products-grid {
            grid-template-columns: repeat(3, 1fr);
        }
        
        .slider-item {
            flex: 0 0 calc(50% - 15px);
            min-width: calc(50% - 15px);
        }
    }
    
    @media (max-width: 992px) {
        .hero-title {
            font-size: 48px;
        }
        
        .products-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .reviews-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .hero-section {
            height: 70vh;
            min-height: 500px;
        }
        
        .hero-content {
            right: 5%;
            left: 5%;
        }
        
        .hero-title {
            font-size: 36px;
        }
        
        .products-grid {
            grid-template-columns: 1fr;
        }
        
        .reviews-grid {
            grid-template-columns: 1fr;
        }
        
        .slider-item {
            flex: 0 0 100%;
            min-width: 100%;
        }
        
        .vip-signup-form {
            flex-direction: column;
        }
        
        .section-title {
            font-size: 32px;
        }
    }
</style>

<!-- Hero Section -->
<section class="hero-section">
    <?php foreach ($heroCategories as $index => $category): ?>
        <div class="hero-slide <?php echo $index === 0 ? 'active' : ''; ?>" 
             style="background-image: url('assets/images/hero-<?php echo $index + 1; ?>.jpg');">
            <div class="hero-overlay"></div>
            <div class="hero-content">
                <p class="hero-subtitle">کالکشن <?php echo date('Y'); ?></p>
                <h1 class="hero-title"><?php echo htmlspecialchars($category['name']); ?></h1>
                <p class="hero-description">
                    جدیدترین و شیک‌ترین مدل‌های <?php echo htmlspecialchars($category['name']); ?> 
                    با کیفیتی بی‌نظیر و قیمتی مناسب
                </p>
                <a href="products.php?category=<?php echo $category['id']; ?>" class="hero-cta">
                    مشاهده محصولات
                    <i class="fas fa-arrow-left"></i>
                </a>
            </div>
        </div>
    <?php endforeach; ?>
    
    <div class="hero-indicators">
        <?php foreach ($heroCategories as $index => $category): ?>
            <div class="hero-indicator <?php echo $index === 0 ? 'active' : ''; ?>" 
                 data-slide="<?php echo $index; ?>"></div>
        <?php endforeach; ?>
    </div>
</section>

<!-- محصولات جدید (4-Column Grid) -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <p class="section-subtitle">تازه‌ترین‌ها</p>
            <h2 class="section-title">محصولات جدید</h2>
            <p class="section-description">
                آخرین محصولات اضافه شده به مجموعه ما با طراحی‌های منحصربفرد
            </p>
        </div>
        
        <div class="products-grid">
            <?php foreach (array_slice($newArrivals, 0, 4) as $product): ?>
                <?php include 'product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- پرفروش‌ترین‌ها (3-Item Slider) -->
<section class="section" style="background: #f9f9f9;">
    <div class="container">
        <div class="section-header">
            <p class="section-subtitle">محبوب‌ترین‌ها</p>
            <h2 class="section-title">پرفروش‌ترین محصولات</h2>
            <p class="section-description">
                محصولاتی که بیشترین استقبال را از سوی مشتریان داشته‌اند
            </p>
        </div>
        
        <div class="slider-container" id="bestSellersSlider">
            <div class="slider-track">
                <?php foreach ($bestSellers as $product): ?>
                    <div class="slider-item">
                        <?php include 'product-card.php'; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="slider-nav prev" onclick="slideProducts('bestSellersSlider', -1)">
                <i class="fas fa-chevron-right"></i>
            </div>
            <div class="slider-nav next" onclick="slideProducts('bestSellersSlider', 1)">
                <i class="fas fa-chevron-left"></i>
            </div>
        </div>
    </div>
</section>

<!-- پیشنهاد هوشمند (Single Row) -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <p class="section-subtitle">پیشنهاد ویژه</p>
            <h2 class="section-title">پربازدیدترین‌های اخیر</h2>
            <p class="section-description">
                محصولاتی که مورد توجه خریداران قرار گرفته‌اند
            </p>
        </div>
        
        <div class="slider-container" id="trendingSlider">
            <div class="slider-track">
                <?php foreach ($trending as $product): ?>
                    <div class="slider-item">
                        <?php include 'product-card.php'; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="slider-nav prev" onclick="slideProducts('trendingSlider', -1)">
                <i class="fas fa-chevron-right"></i>
            </div>
            <div class="slider-nav next" onclick="slideProducts('trendingSlider', 1)">
                <i class="fas fa-chevron-left"></i>
            </div>
        </div>
    </div>
</section>

<!-- نظرات VIP (3-Column Grid) -->
<section class="section" style="background: linear-gradient(135deg, #fafafa 0%, #f0f0f0 100%);">
    <div class="container">
        <div class="section-header">
            <p class="section-subtitle">نظرات مشتریان</p>
            <h2 class="section-title">تجربه خریداران ما</h2>
            <p class="section-description">
                آنچه مشتریان راضی ما درباره کیفیت محصولات می‌گویند
            </p>
        </div>
        
        <div class="reviews-grid">
            <?php foreach ($vipReviews as $review): ?>
                <div class="review-card">
                    <div class="review-header">
                        <img src="<?php echo $review['avatar'] ?: 'assets/images/default-avatar.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($review['full_name']); ?>" 
                             class="review-avatar">
                        <div class="review-user-info">
                            <h4><?php echo htmlspecialchars($review['full_name']); ?></h4>
                            <p class="review-verified">
                                <i class="fas fa-check-circle"></i>
                                خریدار تایید شده
                            </p>
                        </div>
                    </div>
                    
                    <div class="review-rating">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="<?php echo $i <= $review['rating'] ? 'fas' : 'far'; ?> fa-star"></i>
                        <?php endfor; ?>
                    </div>
                    
                    <p class="review-text">
                        <?php echo htmlspecialchars($review['comment']); ?>
                    </p>
                    
                    <div class="review-footer">
                        <span class="review-product">
                            <?php echo htmlspecialchars($review['product_name']); ?>
                        </span>
                        <span class="review-date">
                            خریداری شده در <?php echo $review['purchased_size'] ? toJalali($review['purchase_date']) . ' - سایز ' . $review['purchased_size'] : toJalali($review['purchase_date']); ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align: center;">
            <a href="reviews.php" class="view-all-btn">
                مشاهده همه نظرات
                <i class="fas fa-arrow-left"></i>
            </a>
        </div>
    </div>
</section>

<!-- VIP Club Signup -->
<section class="vip-club-section">
    <div class="container">
        <div class="vip-club-content">
            <h2 class="vip-club-title">عضویت در باشگاه مشتریان</h2>
            <p class="vip-club-description">
                از جدیدترین کالکشن‌ها قبل از بقیه باخبر شوید و از تخفیف‌های ویژه بهره‌مند گردید
            </p>
            
            <form class="vip-signup-form" method="post" action="vip-signup.php">
                <input type="email" 
                       name="email" 
                       class="vip-input" 
                       placeholder="ایمیل یا شماره تماس خود را وارد کنید..." 
                       required>
                <button type="submit" class="vip-submit">
                    عضویت در باشگاه
                </button>
            </form>
        </div>
    </div>
</section>

<!-- Homepage JavaScript -->
<script>
    // ==========================================
    // Hero Slider
    // ==========================================
    let currentSlide = 0;
    const slides = document.querySelectorAll('.hero-slide');
    const indicators = document.querySelectorAll('.hero-indicator');
    
    function showSlide(index) {
        slides.forEach(slide => slide.classList.remove('active'));
        indicators.forEach(indicator => indicator.classList.remove('active'));
        
        slides[index].classList.add('active');
        indicators[index].classList.add('active');
    }
    
    function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }
    
    // Auto slide every 5 seconds
    setInterval(nextSlide, 5000);
    
    // Indicator click
    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', () => {
            currentSlide = index;
            showSlide(currentSlide);
        });
    });
    
    // ==========================================
    // Product Sliders
    // ==========================================
    let sliderPositions = {};
    
    function slideProducts(sliderId, direction) {
        const container = document.getElementById(sliderId);
        const track = container.querySelector('.slider-track');
        const items = track.querySelectorAll('.slider-item');
        const itemWidth = items[0].offsetWidth + 30; // width + gap
        
        if (!sliderPositions[sliderId]) {
            sliderPositions[sliderId] = 0;
        }
        
        const maxScroll = -(itemWidth * (items.length - 3));
        
        sliderPositions[sliderId] += (direction * itemWidth);
        
        if (sliderPositions[sliderId] > 0) {
            sliderPositions[sliderId] = 0;
        }
        if (sliderPositions[sliderId] < maxScroll) {
            sliderPositions[sliderId] = maxScroll;
        }
        
        track.style.transform = `translateX(${sliderPositions[sliderId]}px)`;
    }
</script>

<?php include 'footer.php'; ?>
