<?php
// profile.php - پنل کاربری
require_once 'config.php';

// بررسی لاگین بودن
if (!is_logged_in()) {
    redirect('login.php');
}

// اگر ادمین است، به پنل ادمین هدایت شود
if (is_admin()) {
    redirect('admin_profile.php');
}

$user_id = $_SESSION['user_id'];

// دریافت اطلاعات کاربر
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
$stmt->bindParam(':id', $user_id);
$stmt->execute();
$user = $stmt->fetch();

// پردازش آپدیت پروفایل
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = clean_input($_POST['full_name']);
    $phone = clean_input($_POST['phone']);
    $address = clean_input($_POST['address']);
    $postal_code = clean_input($_POST['postal_code']);
    
    $update_stmt = $conn->prepare("UPDATE users SET full_name = :full_name, phone = :phone, 
                                   address = :address, postal_code = :postal_code 
                                   WHERE id = :id");
    $update_stmt->bindParam(':full_name', $full_name);
    $update_stmt->bindParam(':phone', $phone);
    $update_stmt->bindParam(':address', $address);
    $update_stmt->bindParam(':postal_code', $postal_code);
    $update_stmt->bindParam(':id', $user_id);
    
    if ($update_stmt->execute()) {
        $success_msg = 'اطلاعات با موفقیت بروزرسانی شد';
        // بازخوانی اطلاعات
        $stmt->execute();
        $user = $stmt->fetch();
    }
}

// پردازش تغییر رمز عبور
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (password_verify($current_password, $user['password'])) {
        if (strlen($new_password) >= 6) {
            if ($new_password === $confirm_password) {
                $hashed = password_hash($new_password, PASSWORD_BCRYPT);
                $pass_stmt = $conn->prepare("UPDATE users SET password = :password WHERE id = :id");
                $pass_stmt->bindParam(':password', $hashed);
                $pass_stmt->bindParam(':id', $user_id);
                $pass_stmt->execute();
                $success_msg = 'رمز عبور با موفقیت تغییر یافت';
            } else {
                $error_msg = 'رمز عبور جدید و تکرار آن یکسان نیستند';
            }
        } else {
            $error_msg = 'رمز عبور باید حداقل 6 کاراکتر باشد';
        }
    } else {
        $error_msg = 'رمز عبور فعلی اشتباه است';
    }
}

// آمار کاربر (فرضی - باید با دیتابیس واقعی جایگزین شود)
$total_orders = 12;
$pending_orders = 2;
$completed_orders = 10;
$total_spent = 15750000;

$page_title = 'پنل کاربری';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> | فروشگاه پوشاک لوکس</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #1a1a1a;
            color: #fff;
            font-family: 'Vazir', sans-serif;
        }

        .profile-page {
            padding: 2rem 0;
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .profile-header {
            background: linear-gradient(135deg, rgba(218, 165, 32, 0.1), rgba(184, 134, 11, 0.05));
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(218, 165, 32, 0.2);
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #DAA520, #B8860B);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #000;
            box-shadow: 0 8px 32px rgba(218, 165, 32, 0.3);
            position: relative;
        }

        .avatar-upload {
            position: absolute;
            bottom: 0;
            right: 0;
            background: #000;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 2px solid #DAA520;
        }

        .avatar-upload input {
            display: none;
        }

        .profile-info h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .vip-badge {
            background: linear-gradient(135deg, #DAA520, #B8860B);
            color: #000;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .profile-meta {
            display: flex;
            gap: 2rem;
            color: #999;
            font-size: 0.9rem;
        }

        .profile-meta span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .profile-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 2rem;
        }

        .profile-sidebar {
            background: rgba(30, 30, 30, 0.5);
            border-radius: 20px;
            padding: 1.5rem;
            height: fit-content;
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: sticky;
            top: 2rem;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin-bottom: 0.5rem;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border-radius: 10px;
            color: #999;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(218, 165, 32, 0.1);
            color: #DAA520;
        }

        .sidebar-menu a i {
            width: 20px;
            text-align: center;
        }

        .logout-btn {
            width: 100%;
            margin-top: 1rem;
            padding: 1rem;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 10px;
            color: #ef4444;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.2);
        }

        .profile-content {
            background: rgba(30, 30, 30, 0.5);
            border-radius: 20px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(40, 40, 40, 0.8);
            padding: 1.5rem;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            border-color: #DAA520;
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(218, 165, 32, 0.2);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(218, 165, 32, 0.2), rgba(184, 134, 11, 0.1));
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            font-size: 1.5rem;
            color: #DAA520;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #fff;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #999;
            font-size: 0.9rem;
        }

        .section-title {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(218, 165, 32, 0.2);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            color: #ccc;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 0.9rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: #fff;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.08);
            border-color: #DAA520;
            box-shadow: 0 0 0 3px rgba(218, 165, 32, 0.1);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #DAA520, #B8860B);
            color: #000;
            font-weight: bold;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(218, 165, 32, 0.4);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #22c55e;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }

        .tab-navigation {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        }

        .tab-btn {
            padding: 1rem 1.5rem;
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .tab-btn.active {
            color: #DAA520;
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #DAA520, #B8860B);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .order-card {
            background: rgba(40, 40, 40, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .order-card:hover {
            border-color: #DAA520;
            box-shadow: 0 4px 15px rgba(218, 165, 32, 0.1);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .order-number {
            font-weight: bold;
            color: #DAA520;
        }

        .order-status {
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
        }

        .status-pending {
            background: rgba(251, 191, 36, 0.2);
            color: #fbbf24;
        }

        .status-completed {
            background: rgba(34, 197, 94, 0.2);
            color: #22c55e;
        }

        .status-cancelled {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }

        .order-items {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .order-item {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .order-item-image {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            object-fit: cover;
        }

        .order-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-total {
            font-size: 1.2rem;
            font-weight: bold;
            color: #DAA520;
        }

        /* ریسپانسیو */
        @media (max-width: 1024px) {
            .profile-layout {
                grid-template-columns: 1fr;
            }

            .profile-sidebar {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .tab-navigation {
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="profile-page">
        <div class="container">
            <!-- هدر پروفایل -->
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php if ($user['avatar']): ?>
                        <img src="<?php echo $user['avatar']; ?>" alt="Avatar">
                    <?php else: ?>
                        <i class="fas fa-user"></i>
                    <?php endif; ?>
                    <div class="avatar-upload">
                        <i class="fas fa-camera"></i>
                        <input type="file" accept="image/*">
                    </div>
                </div>
                <div class="profile-info">
                    <h1>
                        <?php echo $user['full_name'] ?: $user['username']; ?>
                        <?php if ($user['is_vip']): ?>
                            <span class="vip-badge">
                                <i class="fas fa-crown"></i> عضو VIP
                            </span>
                        <?php endif; ?>
                    </h1>
                    <div class="profile-meta">
                        <span><i class="fas fa-envelope"></i> <?php echo $user['email']; ?></span>
                        <span><i class="fas fa-phone"></i> <?php echo $user['phone'] ?: 'ثبت نشده'; ?></span>
                        <span><i class="fas fa-calendar"></i> عضویت از: <?php echo date('Y/m/d', strtotime($user['created_at'])); ?></span>
                    </div>
                </div>
            </div>

            <div class="profile-layout">
                <!-- سایدبار -->
                <aside class="profile-sidebar">
                    <ul class="sidebar-menu">
                        <li>
                            <a href="#" class="active" data-tab="dashboard">
                                <i class="fas fa-th-large"></i>
                                <span>داشبورد</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" data-tab="orders">
                                <i class="fas fa-shopping-bag"></i>
                                <span>سفارشات من</span>
                            </a>
                        </li>
                        <li>
                            <a href="wishlist.php">
                                <i class="fas fa-heart"></i>
                                <span>علاقه‌مندی‌ها</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" data-tab="addresses">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>آدرس‌ها</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" data-tab="profile-edit">
                                <i class="fas fa-user-edit"></i>
                                <span>ویرایش پروفایل</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" data-tab="security">
                                <i class="fas fa-shield-alt"></i>
                                <span>امنیت</span>
                            </a>
                        </li>
                    </ul>
                    <button class="logout-btn" onclick="logout()">
                        <i class="fas fa-sign-out-alt"></i> خروج از حساب
                    </button>
                </aside>

                <!-- محتوای اصلی -->
                <main class="profile-content">
                    <?php if (isset($success_msg)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <span><?php echo $success_msg; ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($error_msg)): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <span><?php echo $error_msg; ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- تب داشبورد -->
                    <div class="tab-content active" id="dashboard">
                        <h2 class="section-title">
                            <i class="fas fa-chart-line"></i>
                            آمار کلی
                        </h2>

                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <div class="stat-value"><?php echo $total_orders; ?></div>
                                <div class="stat-label">کل سفارشات</div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="stat-value"><?php echo $pending_orders; ?></div>
                                <div class="stat-label">در انتظار ارسال</div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="stat-value"><?php echo $completed_orders; ?></div>
                                <div class="stat-label">تکمیل شده</div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-wallet"></i>
                                </div>
                                <div class="stat-value"><?php echo number_format($total_spent); ?></div>
                                <div class="stat-label">کل خرید (تومان)</div>
                            </div>
                        </div>

                        <h2 class="section-title">
                            <i class="fas fa-history"></i>
                            آخرین سفارشات
                        </h2>

                        <!-- نمونه سفارشات -->
                        <div class="order-card">
                            <div class="order-header">
                                <div>
                                    <div class="order-number">#12345</div>
                                    <div style="color: #999; font-size: 0.9rem; margin-top: 0.25rem;">1404/11/15</div>
                                </div>
                                <span class="order-status status-pending">در حال پردازش</span>
                            </div>
                            <div class="order-items">
                                <div class="order-item">
                                    <img src="https://via.placeholder.com/60" class="order-item-image" alt="">
                                    <div>
                                        <div style="color: #fff;">پیراهن مردانه</div>
                                        <div style="color: #999; font-size: 0.85rem;">تعداد: 2</div>
                                    </div>
                                </div>
                            </div>
                            <div class="order-footer">
                                <div class="order-total">2,500,000 تومان</div>
                                <button class="btn btn-secondary">مشاهده جزئیات</button>
                            </div>
                        </div>

                        <div class="order-card">
                            <div class="order-header">
                                <div>
                                    <div class="order-number">#12344</div>
                                    <div style="color: #999; font-size: 0.9rem; margin-top: 0.25rem;">1404/11/10</div>
                                </div>
                                <span class="order-status status-completed">تحویل داده شده</span>
                            </div>
                            <div class="order-items">
                                <div class="order-item">
                                    <img src="https://via.placeholder.com/60" class="order-item-image" alt="">
                                    <div>
                                        <div style="color: #fff;">کت و شلوار</div>
                                        <div style="color: #999; font-size: 0.85rem;">تعداد: 1</div>
                                    </div>
                                </div>
                            </div>
                            <div class="order-footer">
                                <div class="order-total">4,200,000 تومان</div>
                                <button class="btn btn-secondary">مشاهده جزئیات</button>
                            </div>
                        </div>
                    </div>

                    <!-- تب سفارشات -->
                    <div class="tab-content" id="orders">
                        <h2 class="section-title">
                            <i class="fas fa-shopping-bag"></i>
                            همه سفارشات
                        </h2>
                        <p style="color: #999; text-align: center; padding: 3rem;">این بخش در حال توسعه است...</p>
                    </div>

                    <!-- تب آدرس‌ها -->
                    <div class="tab-content" id="addresses">
                        <h2 class="section-title">
                            <i class="fas fa-map-marker-alt"></i>
                            آدرس‌های من
                        </h2>
                        <p style="color: #999; text-align: center; padding: 3rem;">هنوز آدرسی ثبت نکرده‌اید</p>
                    </div>

                    <!-- تب ویرایش پروفایل -->
                    <div class="tab-content" id="profile-edit">
                        <h2 class="section-title">
                            <i class="fas fa-user-edit"></i>
                            ویرایش اطلاعات شخصی
                        </h2>

                        <form method="POST">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>نام کاربری</label>
                                    <input type="text" class="form-control" value="<?php echo $user['username']; ?>" disabled>
                                </div>

                                <div class="form-group">
                                    <label>ایمیل</label>
                                    <input type="email" class="form-control" value="<?php echo $user['email']; ?>" disabled>
                                </div>

                                <div class="form-group">
                                    <label>نام و نام خانوادگی</label>
                                    <input type="text" name="full_name" class="form-control" 
                                           value="<?php echo $user['full_name']; ?>" placeholder="نام کامل خود را وارد کنید">
                                </div>

                                <div class="form-group">
                                    <label>شماره موبایل</label>
                                    <input type="tel" name="phone" class="form-control" 
                                           value="<?php echo $user['phone']; ?>" placeholder="09123456789">
                                </div>

                                <div class="form-group full-width">
                                    <label>آدرس</label>
                                    <textarea name="address" class="form-control" 
                                              placeholder="آدرس کامل خود را وارد کنید"><?php echo $user['address']; ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label>کد پستی</label>
                                    <input type="text" name="postal_code" class="form-control" 
                                           value="<?php echo $user['postal_code']; ?>" placeholder="1234567890">
                                </div>
                            </div>

                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save"></i> ذخیره تغییرات
                            </button>
                        </form>
                    </div>

                    <!-- تب امنیت -->
                    <div class="tab-content" id="security">
                        <h2 class="section-title">
                            <i class="fas fa-shield-alt"></i>
                            تغییر رمز عبور
                        </h2>

                        <form method="POST" style="max-width: 600px;">
                            <div class="form-group">
                                <label>رمز عبور فعلی</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label>رمز عبور جدید</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label>تکرار رمز عبور جدید</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>

                            <button type="submit" name="change_password" class="btn btn-primary">
                                <i class="fas fa-key"></i> تغییر رمز عبور
                            </button>
                        </form>

                        <hr style="margin: 2rem 0; border-color: rgba(255,255,255,0.1);">

                        <h2 class="section-title">
                            <i class="fas fa-history"></i>
                            فعالیت‌های اخیر
                        </h2>
                        <p style="color: #999;">آخرین ورود: <?php echo $user['last_login'] ? date('Y/m/d H:i', strtotime($user['last_login'])) : 'نامشخص'; ?></p>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <script>
        // تغییر تب‌ها
        document.querySelectorAll('[data-tab]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const tabName = this.dataset.tab;
                
                // حذف active از همه
                document.querySelectorAll('[data-tab]').forEach(l => l.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
                
                // اضافه کردن active
                this.classList.add('active');
                document.getElementById(tabName).classList.add('active');
            });
        });

        // خروج از حساب
        function logout() {
            if (confirm('آیا مطمئن هستید که می‌خواهید خارج شوید؟')) {
                window.location.href = 'logout.php';
            }
        }

        // آپلود آواتار
        document.querySelector('.avatar-upload input').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // اینجا باید آواتار را با AJAX آپلود کنید
                    alert('قابلیت آپلود عکس در نسخه بعدی اضافه می‌شود');
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
