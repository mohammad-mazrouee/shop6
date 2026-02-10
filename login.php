<?php
// login.php - صفحه ورود و ثبت‌نام
require_once 'config.php';

// اگر کاربر لاگین است، به پنل کاربری هدایت شود
if (is_logged_in()) {
    if (is_admin()) {
        redirect('admin_profile.php');
    } else {
        redirect('profile.php');
    }
}

$error = '';
$success = '';
$info = '';

// پیام‌های خاص
if (isset($_GET['timeout'])) {
    $info = 'جلسه کاری شما به پایان رسید. لطفاً دوباره وارد شوید.';
}
if (isset($_GET['registered'])) {
    $success = 'ثبت‌نام با موفقیت انجام شد! اکنون می‌توانید وارد شوید.';
}
if (isset($_GET['logout'])) {
    $success = 'با موفقیت از حساب کاربری خارج شدید.';
}

// تابع بررسی محدودیت ورود
function check_login_attempts($ip, $username = null) {
    global $conn;
    
    $lockout_time = date('Y-m-d H:i:s', time() - LOCKOUT_TIME);
    
    $query = "SELECT COUNT(*) as attempts FROM login_attempts 
              WHERE ip_address = :ip 
              AND attempt_time > :lockout_time 
              AND is_successful = FALSE";
    
    if ($username) {
        $query .= " AND username = :username";
    }
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':ip', $ip);
    $stmt->bindParam(':lockout_time', $lockout_time);
    if ($username) {
        $stmt->bindParam(':username', $username);
    }
    $stmt->execute();
    
    $result = $stmt->fetch();
    return $result['attempts'] >= MAX_LOGIN_ATTEMPTS;
}

// ثبت تلاش ورود
function log_login_attempt($ip, $username, $success) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO login_attempts (ip_address, username, is_successful) 
                            VALUES (:ip, :username, :success)");
    $stmt->bindParam(':ip', $ip);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':success', $success, PDO::PARAM_BOOL);
    $stmt->execute();
}

// پردازش فرم ورود
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    $ip = $_SERVER['REMOTE_ADDR'];
    
    // بررسی محدودیت ورود
    if (check_login_attempts($ip, $username)) {
        $error = 'تعداد تلاش‌های ورود شما بیش از حد مجاز است. لطفاً 24 ساعت صبر کنید.';
    } else {
        // جستجوی کاربر
        $stmt = $conn->prepare("SELECT * FROM users WHERE (username = :username OR email = :username) AND is_active = TRUE");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch();
            
            // بررسی رمز عبور
            if (password_verify($password, $user['password'])) {
                // ورود موفق
                log_login_attempt($ip, $username, true);
                
                // ایجاد سشن
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['is_vip'] = $user['is_vip'];
                $_SESSION['last_activity'] = time();
                
                // آپدیت زمان آخرین ورود
                $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
                $update_stmt->bindParam(':id', $user['id']);
                $update_stmt->execute();
                
                // ثبت لاگ ورود
                $log_stmt = $conn->prepare("INSERT INTO login_logs (user_id, ip_address, user_agent) 
                                           VALUES (:user_id, :ip, :user_agent)");
                $log_stmt->bindParam(':user_id', $user['id']);
                $log_stmt->bindParam(':ip', $ip);
                $user_agent = $_SERVER['HTTP_USER_AGENT'];
                $log_stmt->bindParam(':user_agent', $user_agent);
                $log_stmt->execute();
                
                // Remember Me
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', true, true);
                }
                
                // هدایت به پنل مناسب
                if ($user['role'] === 'admin') {
                    redirect('admin_profile.php');
                } else {
                    redirect('profile.php');
                }
            } else {
                // رمز عبور اشتباه
                log_login_attempt($ip, $username, false);
                $error = 'نام کاربری یا رمز عبور اشتباه است.';
            }
        } else {
            // کاربر یافت نشد
            log_login_attempt($ip, $username, false);
            $error = 'نام کاربری یا رمز عبور اشتباه است.';
        }
    }
}

// پردازش فرم ثبت‌نام
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = clean_input($_POST['reg_username']);
    $email = clean_input($_POST['reg_email']);
    $phone = clean_input($_POST['reg_phone']);
    $password = $_POST['reg_password'];
    $confirm_password = $_POST['reg_confirm_password'];
    
    // اعتبارسنجی
    if (strlen($username) < 3) {
        $error = 'نام کاربری باید حداقل 3 کاراکتر باشد.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'آدرس ایمیل معتبر نیست.';
    } elseif (strlen($password) < 6) {
        $error = 'رمز عبور باید حداقل 6 کاراکتر باشد.';
    } elseif ($password !== $confirm_password) {
        $error = 'رمز عبور و تکرار آن یکسان نیستند.';
    } else {
        // بررسی وجود قبلی کاربر
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
        $check_stmt->bindParam(':username', $username);
        $check_stmt->bindParam(':email', $email);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() > 0) {
            $error = 'نام کاربری یا ایمیل قبلاً ثبت شده است.';
        } else {
            // ایجاد کاربر جدید
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            $insert_stmt = $conn->prepare("INSERT INTO users (username, email, phone, password, role) 
                                          VALUES (:username, :email, :phone, :password, 'user')");
            $insert_stmt->bindParam(':username', $username);
            $insert_stmt->bindParam(':email', $email);
            $insert_stmt->bindParam(':phone', $phone);
            $insert_stmt->bindParam(':password', $hashed_password);
            
            if ($insert_stmt->execute()) {
                redirect('login.php?registered=1');
            } else {
                $error = 'خطا در ثبت‌نام. لطفاً دوباره تلاش کنید.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود | فروشگاه پوشاک لوکس</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* استایل‌های صفحه ورود */
        .login-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .login-page::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(218, 165, 32, 0.05) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .login-container {
            max-width: 450px;
            width: 100%;
            position: relative;
            z-index: 1;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #DAA520, #B8860B);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            color: #000;
            box-shadow: 0 4px 20px rgba(218, 165, 32, 0.3);
        }

        .login-header h1 {
            color: #DAA520;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: #999;
            font-size: 0.95rem;
        }

        .login-box {
            background: rgba(30, 30, 30, 0.95);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(218, 165, 32, 0.1);
            backdrop-filter: blur(10px);
        }

        .tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        }

        .tab-btn {
            flex: 1;
            padding: 1rem;
            background: none;
            border: none;
            color: #999;
            font-size: 1rem;
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

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            color: #ccc;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            padding: 0.9rem 1rem 0.9rem 3rem;
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

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .checkbox-wrapper input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #DAA520;
        }

        .checkbox-wrapper label {
            color: #ccc;
            font-size: 0.85rem;
            margin: 0;
        }

        .forgot-link {
            color: #DAA520;
            font-size: 0.85rem;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .forgot-link:hover {
            color: #B8860B;
        }

        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #DAA520, #B8860B);
            border: none;
            border-radius: 10px;
            color: #000;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s ease;
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(218, 165, 32, 0.4);
        }

        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideDown 0.4s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #22c55e;
        }

        .alert-info {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #3b82f6;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.5rem 0;
            color: #666;
            font-size: 0.85rem;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
        }

        .social-login {
            display: flex;
            gap: 1rem;
        }

        .social-btn {
            flex: 1;
            padding: 0.9rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .social-btn:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .back-home {
            text-align: center;
            margin-top: 2rem;
        }

        .back-home a {
            color: #DAA520;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .back-home a:hover {
            gap: 0.75rem;
        }

        .password-toggle {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: #DAA520;
        }

        /* ریسپانسیو */
        @media (max-width: 768px) {
            .login-page {
                padding: 1rem;
            }

            .login-box {
                padding: 1.5rem;
            }

            .login-header h1 {
                font-size: 1.5rem;
            }

            .social-login {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="login-page">
        <div class="login-container">
            <div class="login-header">
                <div class="logo">
                    <i class="fas fa-crown"></i>
                </div>
                <h1>فروشگاه پوشاک لوکس</h1>
                <p>به دنیای مد و زیبایی خوش آمدید</p>
            </div>

            <div class="login-box">
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span><?php echo $success; ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($info): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <span><?php echo $info; ?></span>
                    </div>
                <?php endif; ?>

                <div class="tabs">
                    <button class="tab-btn active" data-tab="login">
                        <i class="fas fa-sign-in-alt"></i> ورود
                    </button>
                    <button class="tab-btn" data-tab="register">
                        <i class="fas fa-user-plus"></i> ثبت‌نام
                    </button>
                </div>

                <!-- فرم ورود -->
                <div class="tab-content active" id="login">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label>نام کاربری یا ایمیل</label>
                            <div class="input-wrapper">
                                <i class="fas fa-user"></i>
                                <input type="text" name="username" class="form-control" 
                                       placeholder="نام کاربری یا ایمیل خود را وارد کنید" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>رمز عبور</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="password" class="form-control" 
                                       placeholder="رمز عبور خود را وارد کنید" id="loginPassword" required>
                                <i class="fas fa-eye password-toggle" onclick="togglePassword('loginPassword')"></i>
                            </div>
                        </div>

                        <div class="form-options">
                            <div class="checkbox-wrapper">
                                <input type="checkbox" name="remember" id="remember">
                                <label for="remember">مرا به خاطر بسپار</label>
                            </div>
                            <a href="#" class="forgot-link">فراموشی رمز عبور؟</a>
                        </div>

                        <button type="submit" name="login" class="submit-btn">
                            <i class="fas fa-sign-in-alt"></i> ورود به حساب کاربری
                        </button>
                    </form>

                    <div class="divider">یا ورود با</div>

                    <div class="social-login">
                        <a href="#" class="social-btn">
                            <i class="fab fa-google"></i> گوگل
                        </a>
                        <a href="#" class="social-btn">
                            <i class="fab fa-telegram"></i> تلگرام
                        </a>
                    </div>
                </div>

                <!-- فرم ثبت‌نام -->
                <div class="tab-content" id="register">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label>نام کاربری</label>
                            <div class="input-wrapper">
                                <i class="fas fa-user"></i>
                                <input type="text" name="reg_username" class="form-control" 
                                       placeholder="نام کاربری دلخواه خود را انتخاب کنید" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>ایمیل</label>
                            <div class="input-wrapper">
                                <i class="fas fa-envelope"></i>
                                <input type="email" name="reg_email" class="form-control" 
                                       placeholder="آدرس ایمیل خود را وارد کنید" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>شماره موبایل</label>
                            <div class="input-wrapper">
                                <i class="fas fa-phone"></i>
                                <input type="tel" name="reg_phone" class="form-control" 
                                       placeholder="09123456789" pattern="09[0-9]{9}" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>رمز عبور</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="reg_password" class="form-control" 
                                       placeholder="رمز عبور خود را وارد کنید" id="regPassword" required>
                                <i class="fas fa-eye password-toggle" onclick="togglePassword('regPassword')"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>تکرار رمز عبور</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="reg_confirm_password" class="form-control" 
                                       placeholder="رمز عبور را دوباره وارد کنید" id="regConfirmPassword" required>
                                <i class="fas fa-eye password-toggle" onclick="togglePassword('regConfirmPassword')"></i>
                            </div>
                        </div>

                        <div class="checkbox-wrapper" style="margin-bottom: 1.5rem;">
                            <input type="checkbox" id="terms" required>
                            <label for="terms">
                                <a href="#" style="color: #DAA520;">قوانین و مقررات</a> را مطالعه کرده و می‌پذیرم
                            </label>
                        </div>

                        <button type="submit" name="register" class="submit-btn">
                            <i class="fas fa-user-plus"></i> ثبت‌نام در فروشگاه
                        </button>
                    </form>
                </div>
            </div>

            <div class="back-home">
                <a href="index.php">
                    <i class="fas fa-arrow-left"></i>
                    بازگشت به صفحه اصلی
                </a>
            </div>
        </div>
    </div>

    <script>
        // تعویض تب‌ها
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const tabName = this.dataset.tab;
                
                // حذف active از همه
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // اضافه کردن active به تب انتخاب شده
                this.classList.add('active');
                document.getElementById(tabName).classList.add('active');
            });
        });

        // نمایش/مخفی کردن رمز عبور
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.parentElement.querySelector('.password-toggle');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // اعتبارسنجی فرم ثبت‌نام
        document.querySelector('#register form').addEventListener('submit', function(e) {
            const password = document.querySelector('[name="reg_password"]').value;
            const confirmPassword = document.querySelector('[name="reg_confirm_password"]').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('رمز عبور و تکرار آن یکسان نیستند!');
            }
        });
    </script>
</body>
</html>
