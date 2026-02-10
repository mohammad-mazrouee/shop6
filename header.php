<?php
/**
 * ==========================================
 * فایل هدر سایت - Header با منوی حرفه‌ای
 * ==========================================
 * نسخه: 1.0
 * تاریخ: 1404/11/20
 * ==========================================
 */

// اتصال به فایل تنظیمات
require_once 'config.php';

// دریافت اطلاعات کاربر (در صورت لاگین)
$isLoggedIn = Helper::isLoggedIn();
$userName = '';
$cartCount = 0;
$wishlistCount = 0;

if ($isLoggedIn) {
    $userId = $_SESSION['user_id'];
    
    // دریافت نام کاربر
    $user = Database::fetchOne("SELECT name FROM users WHERE id = ?", [$userId]);
    $userName = $user['name'] ?? 'کاربر';
    
    // تعداد محصولات سبد خرید
    $cartCount = Database::fetchOne(
        "SELECT COUNT(*) as count FROM cart WHERE user_id = ?",
        [$userId]
    )['count'] ?? 0;
    
    // تعداد محصولات علاقه‌مندی
    $wishlistCount = Database::fetchOne(
        "SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?",
        [$userId]
    )['count'] ?? 0;
}

// دریافت دسته‌بندی‌های اصلی برای منو
$mainCategories = Database::fetchAll(
    "SELECT c.*, COUNT(DISTINCT p.id) as product_count 
     FROM categories c 
     LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active' AND p.deleted_at IS NULL
     WHERE c.parent_id IS NULL AND c.status = 'active' AND c.deleted_at IS NULL
     GROUP BY c.id 
     ORDER BY c.sort_order ASC, c.name ASC 
     LIMIT 6"
);

// دریافت زیر دسته‌بندی‌ها
$subCategories = [];
foreach ($mainCategories as $mainCat) {
    $subs = Database::fetchAll(
        "SELECT c.*, COUNT(DISTINCT p.id) as product_count 
         FROM categories c 
         LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active' AND p.deleted_at IS NULL
         WHERE c.parent_id = ? AND c.status = 'active' AND c.deleted_at IS NULL
         GROUP BY c.id 
         ORDER BY c.sort_order ASC, c.name ASC",
        [$mainCat['id']]
    );
    $subCategories[$mainCat['id']] = $subs;
}

// دریافت محصولات برجسته برای Mega Menu
$featuredProducts = Database::fetchAll(
    "SELECT id, name, price, discount_price, main_image 
     FROM products 
     WHERE is_featured = 1 AND status = 'active' AND deleted_at IS NULL 
     ORDER BY created_at DESC 
     LIMIT 3"
);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="فروشگاه آنلاین پوشاک، کیف و کفش با بهترین کیفیت و قیمت مناسب. ارسال سریع به سراسر کشور.">
    <meta name="keywords" content="فروشگاه پوشاک، خرید آنلاین لباس، کیف، کفش، مد و پوشاک">
    <meta name="author" content="<?php echo SITE_NAME; ?>">
    
    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo SITE_NAME; ?> - فروشگاه پوشاک آنلاین">
    <meta property="og:description" content="بهترین برندهای پوشاک، کیف و کفش">
    <meta property="og:url" content="<?php echo SITE_URL; ?>">
    
    <title><?php echo SITE_NAME; ?> - فروشگاه پوشاک حرفه‌ای</title>
    
    <!-- Google Fonts - استفاده از فونت‌های متمایز و زیبا -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="style.css">
    
    <!-- Inline Critical CSS for Header -->
    <style>
        :root {
            /* فونت‌های اختصاصی */
            --font-display: 'Playfair Display', Georgia, serif;
            --font-body: 'Montserrat', -apple-system, BlinkMacSystemFont, sans-serif;
            
            /* پالت رنگی لوکس و شیک */
            --color-primary: #1a1a1a;
            --color-secondary: #d4af37;
            --color-accent: #c41e3a;
            --color-bg-light: #fafafa;
            --color-bg-dark: #0a0a0a;
            --color-text-primary: #1a1a1a;
            --color-text-secondary: #666;
            --color-border: #e5e5e5;
            
            /* سایه‌ها */
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.08);
            --shadow-md: 0 4px 16px rgba(0,0,0,0.12);
            --shadow-lg: 0 8px 32px rgba(0,0,0,0.16);
            
            /* انیمیشن‌ها */
            --transition-fast: 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-smooth: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: var(--font-body);
            color: var(--color-text-primary);
            background: white;
            overflow-x: hidden;
        }
        
        /* ==========================================
           Announcement Bar - نوار اطلاع‌رسانی
           ========================================== */
        .announcement-bar {
            background: linear-gradient(135deg, var(--color-primary) 0%, #2a2a2a 100%);
            color: white;
            padding: 10px 20px;
            text-align: center;
            font-size: 13px;
            font-weight: 500;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
            animation: slideDown 0.5s ease-out;
        }
        
        @keyframes slideDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .announcement-bar::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            animation: shimmer 3s infinite;
        }
        
        @keyframes shimmer {
            to {
                left: 200%;
            }
        }
        
        .announcement-content {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .announcement-icon {
            font-size: 16px;
            color: var(--color-secondary);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        
        .announcement-close {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            opacity: 0.7;
            transition: var(--transition-fast);
            z-index: 2;
        }
        
        .announcement-close:hover {
            opacity: 1;
            transform: translateY(-50%) rotate(90deg);
        }
        
        /* ==========================================
           Main Header - هدر اصلی
           ========================================== */
        .main-header {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--color-border);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: var(--transition-smooth);
            box-shadow: var(--shadow-sm);
        }
        
        .main-header.scrolled {
            padding: 5px 0;
            box-shadow: var(--shadow-md);
        }
        
        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 90px;
            transition: var(--transition-smooth);
        }
        
        .main-header.scrolled .header-container {
            height: 70px;
        }
        
        /* ==========================================
           Logo Section
           ========================================== */
        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-shrink: 0;
        }
        
        .logo-link {
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: var(--transition-smooth);
        }
        
        .logo-link:hover {
            transform: translateY(-2px);
        }
        
        .logo-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--color-primary), #333);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            transition: var(--transition-smooth);
        }
        
        .main-header.scrolled .logo-icon {
            width: 40px;
            height: 40px;
        }
        
        .logo-icon::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(
                transparent,
                var(--color-secondary),
                transparent 30%
            );
            animation: rotate 4s linear infinite;
        }
        
        @keyframes rotate {
            100% {
                transform: rotate(1turn);
            }
        }
        
        .logo-icon::after {
            content: '✦';
            position: absolute;
            font-size: 28px;
            color: white;
            z-index: 1;
        }
        
        .logo-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        
        .logo-title {
            font-family: var(--font-display);
            font-size: 24px;
            font-weight: 700;
            color: var(--color-primary);
            letter-spacing: 1px;
            transition: var(--transition-smooth);
        }
        
        .main-header.scrolled .logo-title {
            font-size: 20px;
        }
        
        .logo-subtitle {
            font-size: 11px;
            font-weight: 500;
            color: var(--color-text-secondary);
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        
        /* ==========================================
           Navigation Menu - منوی اصلی
           ========================================== */
        .main-nav {
            flex: 1;
            display: flex;
            justify-content: center;
        }
        
        .nav-menu {
            list-style: none;
            display: flex;
            gap: 5px;
            align-items: center;
        }
        
        .nav-item {
            position: relative;
        }
        
        .nav-link {
            text-decoration: none;
            color: var(--color-text-primary);
            font-size: 14px;
            font-weight: 500;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 6px;
            border-radius: 8px;
            transition: var(--transition-fast);
            position: relative;
            letter-spacing: 0.3px;
        }
        
        .nav-link::before {
            content: '';
            position: absolute;
            bottom: 8px;
            right: 50%;
            transform: translateX(50%);
            width: 0;
            height: 2px;
            background: var(--color-secondary);
            transition: var(--transition-smooth);
        }
        
        .nav-link:hover {
            color: var(--color-primary);
            background: rgba(212, 175, 55, 0.08);
        }
        
        .nav-link:hover::before {
            width: 30px;
        }
        
        .nav-icon {
            font-size: 16px;
            opacity: 0.7;
        }
        
        .nav-chevron {
            font-size: 12px;
            transition: var(--transition-fast);
        }
        
        .nav-item:hover .nav-chevron {
            transform: rotate(180deg);
        }
        
        /* ==========================================
           Mega Menu - منوی کشویی بزرگ
           ========================================== */
        .mega-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            min-width: 800px;
            padding: 30px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
            transition: var(--transition-smooth);
            margin-top: 10px;
            border: 1px solid var(--color-border);
        }
        
        .nav-item:hover .mega-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .mega-menu-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }
        
        .mega-category {
            animation: fadeInUp 0.4s ease-out backwards;
        }
        
        .mega-category:nth-child(1) { animation-delay: 0.1s; }
        .mega-category:nth-child(2) { animation-delay: 0.2s; }
        .mega-category:nth-child(3) { animation-delay: 0.3s; }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .mega-category-title {
            font-family: var(--font-display);
            font-size: 16px;
            font-weight: 600;
            color: var(--color-primary);
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--color-secondary);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .mega-category-icon {
            font-size: 18px;
            color: var(--color-secondary);
        }
        
        .mega-subcategories {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .mega-subcat-link {
            text-decoration: none;
            color: var(--color-text-secondary);
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 12px;
            border-radius: 6px;
            transition: var(--transition-fast);
        }
        
        .mega-subcat-link:hover {
            background: var(--color-bg-light);
            color: var(--color-primary);
            padding-right: 16px;
        }
        
        .mega-subcat-count {
            font-size: 11px;
            color: var(--color-text-secondary);
            background: var(--color-bg-light);
            padding: 2px 8px;
            border-radius: 10px;
        }
        
        /* محصولات ویژه در Mega Menu */
        .mega-featured {
            grid-column: 1 / -1;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--color-border);
        }
        
        .mega-featured-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--color-text-secondary);
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .mega-products {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        
        .mega-product-card {
            display: flex;
            gap: 12px;
            padding: 12px;
            border-radius: 8px;
            transition: var(--transition-smooth);
            text-decoration: none;
            border: 1px solid transparent;
        }
        
        .mega-product-card:hover {
            background: var(--color-bg-light);
            border-color: var(--color-border);
            transform: translateY(-2px);
        }
        
        .mega-product-img {
            width: 60px;
            height: 80px;
            object-fit: cover;
            border-radius: 6px;
        }
        
        .mega-product-info {
            flex: 1;
        }
        
        .mega-product-name {
            font-size: 13px;
            font-weight: 500;
            color: var(--color-text-primary);
            margin-bottom: 6px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .mega-product-price {
            font-size: 14px;
            font-weight: 600;
            color: var(--color-accent);
        }
        
        .mega-product-price.has-discount {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .mega-product-original {
            font-size: 12px;
            color: var(--color-text-secondary);
            text-decoration: line-through;
        }
        
        /* ==========================================
           Header Actions - دکمه‌های عملیاتی
           ========================================== */
        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }
        
        .action-btn {
            position: relative;
            background: none;
            border: none;
            color: var(--color-text-primary);
            cursor: pointer;
            padding: 10px;
            border-radius: 50%;
            transition: var(--transition-fast);
            font-size: 20px;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .action-btn:hover {
            background: var(--color-bg-light);
            color: var(--color-primary);
            transform: translateY(-2px);
        }
        
        .action-badge {
            position: absolute;
            top: 4px;
            left: 4px;
            background: var(--color-accent);
            color: white;
            font-size: 10px;
            font-weight: 600;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
            animation: bounceIn 0.5s ease-out;
        }
        
        @keyframes bounceIn {
            0% {
                transform: scale(0);
            }
            50% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
            }
        }
        
        /* دکمه ورود/حساب کاربری */
        .user-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 25px;
            background: var(--color-primary);
            color: white;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: var(--transition-smooth);
            border: none;
            cursor: pointer;
        }
        
        .user-btn:hover {
            background: #2a2a2a;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .user-icon {
            font-size: 16px;
        }
        
        /* منوی کاربر */
        .user-dropdown {
            position: relative;
        }
        
        .user-menu {
            position: absolute;
            top: calc(100% + 10px);
            left: 0;
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            min-width: 200px;
            padding: 8px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: var(--transition-smooth);
            border: 1px solid var(--color-border);
        }
        
        .user-dropdown:hover .user-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .user-menu-item {
            display: block;
            padding: 10px 16px;
            color: var(--color-text-primary);
            text-decoration: none;
            font-size: 13px;
            border-radius: 6px;
            transition: var(--transition-fast);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-menu-item:hover {
            background: var(--color-bg-light);
        }
        
        .user-menu-divider {
            height: 1px;
            background: var(--color-border);
            margin: 8px 0;
        }
        
        /* ==========================================
           Mobile Menu Toggle
           ========================================== */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: var(--color-text-primary);
            cursor: pointer;
            padding: 10px;
        }
        
        /* ==========================================
           Responsive Design
           ========================================== */
        @media (max-width: 1200px) {
            .header-container {
                padding: 0 30px;
            }
            
            .mega-menu {
                min-width: 700px;
            }
        }
        
        @media (max-width: 992px) {
            .main-nav {
                display: none;
            }
            
            .mobile-menu-toggle {
                display: block;
            }
            
            .header-container {
                padding: 0 20px;
            }
        }
        
        @media (max-width: 768px) {
            .header-container {
                height: 70px;
            }
            
            .logo-title {
                font-size: 20px;
            }
            
            .logo-icon {
                width: 40px;
                height: 40px;
            }
            
            .logo-subtitle {
                display: none;
            }
            
            .action-btn {
                width: 40px;
                height: 40px;
                font-size: 18px;
            }
            
            .user-btn-text {
                display: none;
            }
        }
    </style>
</head>
<body>

<!-- Announcement Bar -->
<div class="announcement-bar" id="announcementBar">
    <button class="announcement-close" onclick="closeAnnouncement()" aria-label="بستن">
        <i class="fas fa-times"></i>
    </button>
    <div class="announcement-content">
        <i class="fas fa-fire announcement-icon"></i>
        <span>تخفیف ویژه تا ۵۰٪ برای محصولات منتخب | ارسال رایگان برای خریدهای بالای ۵۰۰ هزار تومان</span>
    </div>
</div>

<!-- Main Header -->
<header class="main-header" id="mainHeader">
    <div class="header-container">
        
        <!-- Logo Section -->
        <div class="logo-section">
            <a href="index.php" class="logo-link">
                <div class="logo-icon"></div>
                <div class="logo-text">
                    <div class="logo-title"><?php echo SITE_NAME; ?></div>
                    <div class="logo-subtitle">Premium Fashion</div>
                </div>
            </a>
        </div>
        
        <!-- Main Navigation -->
        <nav class="main-nav">
            <ul class="nav-menu">
                
                <!-- خانه -->
                <li class="nav-item">
                    <a href="index.php" class="nav-link">
                        <i class="fas fa-home nav-icon"></i>
                        <span>خانه</span>
                    </a>
                </li>
                
                <!-- محصولات با Mega Menu -->
                <li class="nav-item">
                    <a href="products.php" class="nav-link">
                        <i class="fas fa-shopping-bag nav-icon"></i>
                        <span>محصولات</span>
                        <i class="fas fa-chevron-down nav-chevron"></i>
                    </a>
                    
                    <!-- Mega Menu -->
                    <div class="mega-menu">
                        <div class="mega-menu-grid">
                            <?php foreach ($mainCategories as $index => $category): ?>
                                <div class="mega-category">
                                    <h3 class="mega-category-title">
                                        <?php 
                                        // آیکون‌های مختلف برای هر دسته
                                        $icons = [
                                            'fa-tshirt',
                                            'fa-shoe-prints',
                                            'fa-bag-shopping',
                                            'fa-crown',
                                            'fa-gem',
                                            'fa-hat-cowboy'
                                        ];
                                        $icon = $icons[$index % count($icons)];
                                        ?>
                                        <i class="fas <?php echo $icon; ?> mega-category-icon"></i>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </h3>
                                    
                                    <?php if (isset($subCategories[$category['id']]) && !empty($subCategories[$category['id']])): ?>
                                        <ul class="mega-subcategories">
                                            <?php foreach ($subCategories[$category['id']] as $subCat): ?>
                                                <li>
                                                    <a href="products.php?category=<?php echo $subCat['id']; ?>" 
                                                       class="mega-subcat-link">
                                                        <span><?php echo htmlspecialchars($subCat['name']); ?></span>
                                                        <span class="mega-subcat-count"><?php echo $subCat['product_count']; ?></span>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- محصولات ویژه -->
                        <?php if (!empty($featuredProducts)): ?>
                        <div class="mega-featured">
                            <h4 class="mega-featured-title">محصولات ویژه</h4>
                            <div class="mega-products">
                                <?php foreach ($featuredProducts as $product): ?>
                                    <a href="product_single.php?id=<?php echo $product['id']; ?>" 
                                       class="mega-product-card">
                                        <img src="<?php echo IMAGE_URL_PATH . htmlspecialchars($product['main_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                                             class="mega-product-img">
                                        <div class="mega-product-info">
                                            <div class="mega-product-name">
                                                <?php echo htmlspecialchars($product['name']); ?>
                                            </div>
                                            <div class="mega-product-price <?php echo $product['discount_price'] ? 'has-discount' : ''; ?>">
                                                <?php if ($product['discount_price']): ?>
                                                    <span><?php echo Helper::formatPrice($product['discount_price']); ?></span>
                                                    <span class="mega-product-original">
                                                        <?php echo Helper::formatPrice($product['price']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <?php echo Helper::formatPrice($product['price']); ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </li>
                
                <!-- درباره ما -->
                <li class="nav-item">
                    <a href="about.php" class="nav-link">
                        <i class="fas fa-info-circle nav-icon"></i>
                        <span>درباره ما</span>
                    </a>
                </li>
                
                <!-- تماس با ما -->
                <li class="nav-item">
                    <a href="contact.php" class="nav-link">
                        <i class="fas fa-phone nav-icon"></i>
                        <span>تماس با ما</span>
                    </a>
                </li>
                
            </ul>
        </nav>
        
        <!-- Header Actions -->
        <div class="header-actions">
            
            <!-- علاقه‌مندی‌ها -->
            <a href="wishlist.php" class="action-btn" aria-label="علاقه‌مندی‌ها">
                <i class="far fa-heart"></i>
                <?php if ($wishlistCount > 0): ?>
                    <span class="action-badge"><?php echo $wishlistCount; ?></span>
                <?php endif; ?>
            </a>
            
            <!-- سبد خرید -->
            <a href="cart.php" class="action-btn" aria-label="سبد خرید">
                <i class="fas fa-shopping-cart"></i>
                <?php if ($cartCount > 0): ?>
                    <span class="action-badge"><?php echo $cartCount; ?></span>
                <?php endif; ?>
            </a>
            
            <!-- حساب کاربری -->
            <?php if ($isLoggedIn): ?>
                <div class="user-dropdown">
                    <a href="profile.php" class="user-btn">
                        <i class="fas fa-user user-icon"></i>
                        <span class="user-btn-text"><?php echo htmlspecialchars($userName); ?></span>
                    </a>
                    <div class="user-menu">
                        <a href="profile.php" class="user-menu-item">
                            <i class="fas fa-user"></i>
                            حساب کاربری
                        </a>
                        <a href="profile.php?tab=orders" class="user-menu-item">
                            <i class="fas fa-box"></i>
                            سفارشات من
                        </a>
                        <a href="profile.php?tab=addresses" class="user-menu-item">
                            <i class="fas fa-map-marker-alt"></i>
                            آدرس‌ها
                        </a>
                        <?php if (Helper::isAdmin()): ?>
                            <div class="user-menu-divider"></div>
                            <a href="admin_panel.php" class="user-menu-item">
                                <i class="fas fa-cog"></i>
                                پنل مدیریت
                            </a>
                        <?php endif; ?>
                        <div class="user-menu-divider"></div>
                        <a href="logout.php" class="user-menu-item">
                            <i class="fas fa-sign-out-alt"></i>
                            خروج
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php" class="user-btn">
                    <i class="fas fa-user user-icon"></i>
                    <span class="user-btn-text">ورود / ثبت‌نام</span>
                </a>
            <?php endif; ?>
            
            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-toggle" aria-label="منو">
                <i class="fas fa-bars"></i>
            </button>
            
        </div>
        
    </div>
</header>

<!-- JavaScript for Header Functionality -->
<script>
    // ==========================================
    // Sticky Header on Scroll
    // ==========================================
    window.addEventListener('scroll', function() {
        const header = document.getElementById('mainHeader');
        if (window.scrollY > 100) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });
    
    // ==========================================
    // Close Announcement Bar
    // ==========================================
    function closeAnnouncement() {
        const bar = document.getElementById('announcementBar');
        bar.style.animation = 'slideUp 0.3s ease-out forwards';
        
        setTimeout(() => {
            bar.style.display = 'none';
        }, 300);
        
        // ذخیره در localStorage
        localStorage.setItem('announcementClosed', 'true');
    }
    
    // بررسی localStorage برای نمایش/عدم نمایش
    window.addEventListener('DOMContentLoaded', function() {
        if (localStorage.getItem('announcementClosed') === 'true') {
            document.getElementById('announcementBar').style.display = 'none';
        }
    });
    
    // انیمیشن slideUp
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideUp {
            to {
                transform: translateY(-100%);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
</script>
