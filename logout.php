<?php
// logout.php - خروج از حساب کاربری
require_once 'config.php';

if (is_logged_in()) {
    // ثبت زمان خروج در لاگ
    $stmt = $conn->prepare("UPDATE login_logs SET logout_time = NOW() 
                           WHERE user_id = :user_id AND logout_time IS NULL 
                           ORDER BY login_time DESC LIMIT 1");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
}

// پاک کردن سشن
session_unset();
session_destroy();

// پاک کردن کوکی remember me
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

// هدایت به صفحه لاگین
redirect('login.php?logout=1');
?>
