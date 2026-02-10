<?php
// admin_profile.php - پنل مدیریت فروشگاه
require_once 'config.php';

// بررسی لاگین و سطح دسترسی ادمین
if (!is_logged_in()) {
    redirect('login.php');
}

if (!is_admin()) {
    redirect('profile.php');
}

$user_id = $_SESSION['user_id'];

// دریافت اطلاعات ادمین
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
$stmt->bindParam(':id', $user_id);
$stmt->execute();
$admin = $stmt->fetch();

// آمار داشبورد (فرضی - باید با دیتابیس واقعی جایگزین شود)
$total_users = 1247;
$total_orders = 523;
$pending_orders = 18;
$total_revenue = 187500000;
$online_users = 23;
$low_stock_products = 12;
$new_comments = 7;

// آمار امروز
$today_orders = 15;
$today_revenue = 8500000;
$today_users = 5;

// محصولات در حال اتمام
$low_stock_query = "SELECT * FROM products WHERE stock < 5 ORDER BY stock ASC LIMIT 5";

$page_title = 'پنل مدیریت';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> | فروشگاه پوشاک لوکس</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #0f0f0f;
            color: #fff;
            font-family: 'Vazir', 'Segoe UI', sans-serif;
            overflow-x: hidden;
        }

        /* Sidebar */
        .admin-sidebar {
            position: fixed;
            right: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: linear-gradient(180deg, #1a1a1a 0%, #0f0f0f 100%);
            border-left: 1px solid rgba(218, 165, 32, 0.2);
            padding: 2rem 0;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: -5px 0 30px rgba(0, 0, 0, 0.5);
        }

        .admin-logo {
            text-align: center;
            padding: 0 1.5rem 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .admin-logo h2 {
            background: linear-gradient(135deg, #DAA520, #FFD700);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .admin-logo p {
            font-size: 0.85rem;
            color: #888;
        }

        .admin-menu {
            padding: 1.5rem 0;
        }

        .menu-section {
            margin-bottom: 2rem;
        }

        .menu-section-title {
            color: #666;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0 1.5rem;
            margin-bottom: 1rem;
        }

        .menu-item {
            list-style: none;
        }

        .menu-item a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.9rem 1.5rem;
            color: #999;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
        }

        .menu-item a::before {
            content: '';
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background: linear-gradient(180deg, #DAA520, #B8860B);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .menu-item a:hover,
        .menu-item a.active {
            background: rgba(218, 165, 32, 0.1);
            color: #DAA520;
        }

        .menu-item a:hover::before,
        .menu-item a.active::before {
            transform: scaleY(1);
        }

        .menu-item a i {
            width: 20px;
            text-align: center;
        }

        .menu-badge {
            background: #ef4444;
            color: #fff;
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
            border-radius: 10px;
            margin-right: auto;
        }

        /* Main Content */
        .admin-main {
            margin-right: 280px;
            padding: 2rem;
            min-height: 100vh;
        }

        /* Top Bar */
        .admin-topbar {
            background: rgba(30, 30, 30, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .topbar-welcome h1 {
            font-size: 1.8rem;
            margin-bottom: 0.3rem;
        }

        .topbar-welcome p {
            color: #888;
            font-size: 0.9rem;
        }

        .topbar-status {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .status-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.7rem 1.2rem;
            background: rgba(40, 40, 40, 0.5);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .status-item i {
            color: #22c55e;
        }

        .status-item .value {
            font-weight: bold;
            color: #DAA520;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(30, 30, 30, 0.8), rgba(20, 20, 20, 0.8));
            border-radius: 20px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #DAA520, #B8860B);
            transform: scaleX(0);
            transform-origin: right;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: rgba(218, 165, 32, 0.3);
            box-shadow: 0 10px 30px rgba(218, 165, 32, 0.2);
        }

        .stat-card:hover::before {
            transform: scaleX(1);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
        }

        .stat-icon.blue {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .stat-icon.green {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
        }

        .stat-icon.yellow {
            background: rgba(251, 191, 36, 0.1);
            color: #fbbf24;
        }

        .stat-icon.gold {
            background: rgba(218, 165, 32, 0.1);
            color: #DAA520;
        }

        .stat-trend {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.85rem;
            padding: 0.4rem 0.8rem;
            border-radius: 10px;
        }

        .stat-trend.up {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
        }

        .stat-trend.down {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: #fff;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #888;
            font-size: 0.9rem;
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .content-card {
            background: rgba(30, 30, 30, 0.8);
            border-radius: 20px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .card-title {
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-title i {
            color: #DAA520;
        }

        .view-all {
            color: #DAA520;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .view-all:hover {
            color: #FFD700;
        }

        /* Order List */
        .order-list {
            list-style: none;
        }

        .order-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: rgba(40, 40, 40, 0.5);
            border-radius: 12px;
            margin-bottom: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        .order-item:hover {
            background: rgba(50, 50, 50, 0.5);
            border-color: rgba(218, 165, 32, 0.3);
        }

        .order-id {
            font-weight: bold;
            color: #DAA520;
            min-width: 80px;
        }

        .order-customer {
            flex: 1;
            color: #ccc;
        }

        .order-amount {
            font-weight: bold;
            color: #22c55e;
        }

        .order-status {
            padding: 0.4rem 0.9rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .status-new {
            background: rgba(59, 130, 246, 0.2);
            color: #3b82f6;
        }

        .status-processing {
            background: rgba(251, 191, 36, 0.2);
            color: #fbbf24;
        }

        /* Activity Log */
        .activity-list {
            list-style: none;
        }

        .activity-item {
            padding: 1rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .activity-icon.success {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
        }

        .activity-icon.warning {
            background: rgba(251, 191, 36, 0.1);
            color: #fbbf24;
        }

        .activity-icon.info {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .activity-content {
            flex: 1;
        }

        .activity-text {
            color: #ccc;
            font-size: 0.9rem;
            margin-bottom: 0.3rem;
        }

        .activity-time {
            color: #666;
            font-size: 0.8rem;
        }

        /* Low Stock Alert */
        .alert-list {
            list-style: none;
        }

        .alert-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 12px;
            margin-bottom: 0.8rem;
        }

        .alert-product {
            flex: 1;
            color: #ccc;
        }

        .alert-stock {
            font-weight: bold;
            color: #ef4444;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }

        .action-btn {
            padding: 1.2rem;
            background: rgba(40, 40, 40, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: #fff;
        }

        .action-btn:hover {
            background: rgba(218, 165, 32, 0.1);
            border-color: #DAA520;
            transform: translateY(-3px);
        }

        .action-btn i {
            font-size: 2rem;
            color: #DAA520;
            margin-bottom: 0.8rem;
            display: block;
        }

        .action-btn span {
            display: block;
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .admin-sidebar {
                width: 0;
                transform: translateX(100%);
                transition: transform 0.3s ease;
            }

            .admin-sidebar.open {
                width: 280px;
                transform: translateX(0);
            }

            .admin-main {
                margin-right: 0;
                padding: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .topbar-status {
                display: none;
            }

            .mobile-menu-toggle {
                display: block;
                position: fixed;
                bottom: 2rem;
                left: 2rem;
                width: 60px;
                height: 60px;
                background: linear-gradient(135deg, #DAA520, #B8860B);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #000;
                font-size: 1.5rem;
                cursor: pointer;
                z-index: 999;
                box-shadow: 0 4px 20px rgba(218, 165, 32, 0.4);
            }
        }

        .mobile-menu-toggle {
            display: none;
        }

        /* Animations */
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stat-card,
        .content-card {
            animation: slideUp 0.5s ease forwards;
        }

        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-logo">
            <h2><i class="fas fa-shield-alt"></i> پنل مدیریت</h2>
            <p>فروشگاه پوشاک لوکس</p>
        </div>

        <nav class="admin-menu">
            <div class="menu-section">
                <div class="menu-section-title">منوی اصلی</div>
                <ul>
                    <li class="menu-item">
                        <a href="#" class="active">
                            <i class="fas fa-th-large"></i>
                            <span>داشبورد</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="#">
                            <i class="fas fa-shopping-bag"></i>
                            <span>سفارشات</span>
                            <span class="menu-badge"><?php echo $pending_orders; ?></span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="#">
                            <i class="fas fa-box"></i>
                            <span>محصولات</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="#">
                            <i class="fas fa-tags"></i>
                            <span>دسته‌بندی‌ها</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="#">
                            <i class="fas fa-users"></i>
                            <span>کاربران</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="menu-section">
                <div class="menu-section-title">گزارشات</div>
                <ul>
                    <li class="menu-item">
                        <a href="#">
                            <i class="fas fa-chart-line"></i>
                            <span>فروش</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="#">
                            <i class="fas fa-warehouse"></i>
                            <span>موجودی انبار</span>
                            <span class="menu-badge"><?php echo $low_stock_products; ?></span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="#">
                            <i class="fas fa-comments"></i>
                            <span>نظرات</span>
                            <span class="menu-badge"><?php echo $new_comments; ?></span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="menu-section">
                <div class="menu-section-title">تنظیمات</div>
                <ul>
                    <li class="menu-item">
                        <a href="#">
                            <i class="fas fa-cog"></i>
                            <span>تنظیمات سایت</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="#">
                            <i class="fas fa-palette"></i>
                            <span>ظاهر سایت</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="#">
                            <i class="fas fa-shipping-fast"></i>
                            <span>تنظیمات ارسال</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="#">
                            <i class="fas fa-credit-card"></i>
                            <span>درگاه پرداخت</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="menu-section">
                <ul>
                    <li class="menu-item">
                        <a href="index.php" target="_blank">
                            <i class="fas fa-external-link-alt"></i>
                            <span>مشاهده سایت</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="logout.php" style="color: #ef4444;">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>خروج</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        <!-- Top Bar -->
        <div class="admin-topbar">
            <div class="topbar-welcome">
                <h1>سلام، <?php echo $admin['full_name'] ?: $admin['username']; ?> 👋</h1>
                <p>به پنل مدیریت فروشگاه خوش آمدید</p>
            </div>
            <div class="topbar-status">
                <div class="status-item">
                    <i class="fas fa-circle"></i>
                    <span>کاربران آنلاین:</span>
                    <span class="value"><?php echo $online_users; ?></span>
                </div>
                <div class="status-item">
                    <i class="fas fa-server"></i>
                    <span>وضعیت سرور:</span>
                    <span class="value" style="color: #22c55e;">عالی</span>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon blue">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-trend up">
                        <i class="fas fa-arrow-up"></i>
                        <span>+12%</span>
                    </div>
                </div>
                <div class="stat-value"><?php echo number_format($total_users); ?></div>
                <div class="stat-label">کل کاربران</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon green">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-trend up">
                        <i class="fas fa-arrow-up"></i>
                        <span>+8%</span>
                    </div>
                </div>
                <div class="stat-value"><?php echo number_format($total_orders); ?></div>
                <div class="stat-label">کل سفارشات</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon gold">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="stat-trend up">
                        <i class="fas fa-arrow-up"></i>
                        <span>+24%</span>
                    </div>
                </div>
                <div class="stat-value"><?php echo number_format($total_revenue / 1000000); ?>M</div>
                <div class="stat-label">کل درآمد (تومان)</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon yellow">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-trend down">
                        <i class="fas fa-arrow-down"></i>
                        <span>-3%</span>
                    </div>
                </div>
                <div class="stat-value"><?php echo $pending_orders; ?></div>
                <div class="stat-label">سفارشات در انتظار</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="content-card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bolt"></i>
                    دسترسی سریع
                </h3>
            </div>
            <div class="quick-actions">
                <a href="#" class="action-btn">
                    <i class="fas fa-plus-circle"></i>
                    <span>افزودن محصول</span>
                </a>
                <a href="#" class="action-btn">
                    <i class="fas fa-user-plus"></i>
                    <span>کاربر جدید</span>
                </a>
                <a href="#" class="action-btn">
                    <i class="fas fa-tags"></i>
                    <span>تخفیف جدید</span>
                </a>
                <a href="#" class="action-btn">
                    <i class="fas fa-file-excel"></i>
                    <span>خروجی Excel</span>
                </a>
                <a href="#" class="action-btn">
                    <i class="fas fa-database"></i>
                    <span>پشتیبان‌گیری</span>
                </a>
                <a href="#" class="action-btn">
                    <i class="fas fa-bell"></i>
                    <span>ارسال اطلاعیه</span>
                </a>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Recent Orders -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shopping-bag"></i>
                        سفارشات اخیر
                    </h3>
                    <a href="#" class="view-all">مشاهده همه →</a>
                </div>
                <ul class="order-list">
                    <li class="order-item">
                        <span class="order-id">#12548</span>
                        <span class="order-customer">علی احمدی</span>
                        <span class="order-amount">2,500,000 تومان</span>
                        <span class="order-status status-new">جدید</span>
                    </li>
                    <li class="order-item">
                        <span class="order-id">#12547</span>
                        <span class="order-customer">مریم رضایی</span>
                        <span class="order-amount">1,800,000 تومان</span>
                        <span class="order-status status-processing">در حال پردازش</span>
                    </li>
                    <li class="order-item">
                        <span class="order-id">#12546</span>
                        <span class="order-customer">حسین کریمی</span>
                        <span class="order-amount">3,200,000 تومان</span>
                        <span class="order-status status-new">جدید</span>
                    </li>
                    <li class="order-item">
                        <span class="order-id">#12545</span>
                        <span class="order-customer">فاطمه محمدی</span>
                        <span class="order-amount">950,000 تومان</span>
                        <span class="order-status status-processing">در حال پردازش</span>
                    </li>
                    <li class="order-item">
                        <span class="order-id">#12544</span>
                        <span class="order-customer">رضا نوری</span>
                        <span class="order-amount">4,100,000 تومان</span>
                        <span class="order-status status-new">جدید</span>
                    </li>
                </ul>
            </div>

            <!-- Right Column -->
            <div>
                <!-- Activity Log -->
                <div class="content-card" style="margin-bottom: 1.5rem;">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-history"></i>
                            فعالیت‌های اخیر
                        </h3>
                    </div>
                    <ul class="activity-list">
                        <li class="activity-item">
                            <div class="activity-icon success">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-text">سفارش #12548 ثبت شد</div>
                                <div class="activity-time">5 دقیقه پیش</div>
                            </div>
                        </li>
                        <li class="activity-item">
                            <div class="activity-icon info">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-text">کاربر جدید ثبت‌نام کرد</div>
                                <div class="activity-time">15 دقیقه پیش</div>
                            </div>
                        </li>
                        <li class="activity-item">
                            <div class="activity-icon warning">
                                <i class="fas fa-exclamation"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-text">موجودی محصول کمتر از 5 شد</div>
                                <div class="activity-time">1 ساعت پیش</div>
                            </div>
                        </li>
                        <li class="activity-item">
                            <div class="activity-icon success">
                                <i class="fas fa-comment"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-text">نظر جدید در انتظار تایید</div>
                                <div class="activity-time">2 ساعت پیش</div>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Low Stock Alert -->
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-exclamation-triangle"></i>
                            هشدار موجودی
                        </h3>
                        <a href="#" class="view-all">مشاهده همه →</a>
                    </div>
                    <ul class="alert-list">
                        <li class="alert-item">
                            <span class="alert-product">پیراهن مردانه کلاسیک</span>
                            <span class="alert-stock">3 عدد</span>
                        </li>
                        <li class="alert-item">
                            <span class="alert-product">کت و شلوار اسپرت</span>
                            <span class="alert-stock">2 عدد</span>
                        </li>
                        <li class="alert-item">
                            <span class="alert-product">پلیور زمستانی</span>
                            <span class="alert-stock">4 عدد</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </main>

    <!-- Mobile Menu Toggle -->
    <div class="mobile-menu-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('adminSidebar').classList.toggle('open');
        }

        // Auto-refresh stats every 30 seconds (optional)
        setInterval(() => {
            // AJAX call to update stats
            console.log('Stats updated');
        }, 30000);
    </script>
</body>
</html>
