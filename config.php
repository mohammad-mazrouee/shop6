<?php
/**
 * ==========================================
 * فایل پیکربندی و اتصال به دیتابیس - نسخه پیشرفته
 * ==========================================
 * نسخه: 2.5 Enhanced
 * تاریخ: 1404/11/20 (2026-02-10)
 * توسعه‌دهنده: فروشگاه پوشاک لوکس
 * ==========================================
 * ویژگی‌های امنیتی:
 * - محافظت از Brute-Force با Rate Limiting
 * - CSRF Token Protection
 * - XSS Prevention
 * - SQL Injection Prevention با Prepared Statements
 * - Session Timeout & Security
 * - Activity Logging
 * - Password Hashing با Bcrypt
 * ==========================================
 */

// جلوگیری از دسترسی مستقیم
if (!defined('SECURE_ACCESS')) {
    define('SECURE_ACCESS', true);
}

// ==========================================
// تنظیمات امنیتی PHP پیشرفته
// ==========================================
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php-errors.log');

// محافظت از Session Hijacking و Fixation
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // فقط برای HTTPS
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_only_cookies', 1);
ini_set('session.use_trans_sid', 0);
ini_set('session.entropy_length', 32);
ini_set('session.hash_function', 'sha256');

// تنظیمات حافظه و زمان اجرا
ini_set('memory_limit', '256M');
ini_set('max_execution_time', '60');
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '12M');
ini_set('max_input_time', '60');

// غیرفعال‌سازی توابع خطرناک
if (function_exists('ini_set')) {
    ini_set('allow_url_fopen', 0);
    ini_set('allow_url_include', 0);
}

// ==========================================
// تنظیمات زمان و زبان
// ==========================================
date_default_timezone_set('Asia/Tehran');
setlocale(LC_TIME, 'fa_IR.UTF-8', 'Persian_Iran.1256', 'Persian');
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// ==========================================
// ثابت‌های امنیتی سیستم
// ==========================================
define('MAX_LOGIN_ATTEMPTS', 5); // حداکثر تلاش ورود ناموفق
define('LOGIN_BLOCK_TIME', 86400); // مدت زمان مسدودی (24 ساعت)
define('SESSION_TIMEOUT', 1800); // تایم‌اوت سشن (30 دقیقه)
define('CSRF_TOKEN_EXPIRE', 3600); // انقضای توکن CSRF (1 ساعت)
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_REQUIRE_SPECIAL', true);
define('PASSWORD_REQUIRE_NUMBER', true);
define('PASSWORD_REQUIRE_UPPERCASE', true);

// ==========================================
// ثابت‌های سیستم
// ==========================================
define('SITE_NAME', 'فروشگاه پوشاک لوکس');
define('SITE_URL', 'http://localhost/fashion-store');
define('SITE_EMAIL', 'info@fashionshop.com');
define('SITE_PHONE', '021-12345678');
define('ITEMS_PER_PAGE', 12);
define('MAX_UPLOAD_SIZE', 10485760); // 10MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/jpg']);
define('IMAGE_UPLOAD_PATH', __DIR__ . '/uploads/products/');
define('IMAGE_URL_PATH', SITE_URL . '/uploads/products/');
define('CURRENCY', 'تومان');
define('TAX_RATE', 0.09); // 9% مالیات
define('FREE_SHIPPING_THRESHOLD', 500000); // ارسال رایگان بالای 500 هزار تومان

// ==========================================
// کلاس Database - مدیریت اتصال با PDO
// ==========================================
class Database {
    // تنظیمات دیتابیس
    private const DB_HOST = 'localhost';
    private const DB_NAME = 'fashion_shop_db';
    private const DB_USER = 'root';
    private const DB_PASS = '';
    private const DB_CHARSET = 'utf8mb4';
    
    // اتصال PDO
    private static $connection = null;
    private static $instance = null;
    
    // آمار کوئری‌ها (برای دیباگ)
    private static $queryCount = 0;
    private static $queryLog = [];
    private static $totalQueryTime = 0;
    
    // ==========================================
    // Singleton Pattern
    // ==========================================
    private function __construct() {}
    private function __clone() {}
    
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
    
    // ==========================================
    // دریافت Instance یکتا
    // ==========================================
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // ==========================================
    // برقراری اتصال به دیتابیس
    // ==========================================
    public static function connect() {
        if (self::$connection !== null) {
            return self::$connection;
        }
        
        try {
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=%s",
                self::DB_HOST,
                self::DB_NAME,
                self::DB_CHARSET
            );
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => true,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                PDO::ATTR_STRINGIFY_FETCHES  => false,
                PDO::ATTR_TIMEOUT            => 5,
                PDO::MYSQL_ATTR_FOUND_ROWS   => true,
            ];
            
            self::$connection = new PDO($dsn, self::DB_USER, self::DB_PASS, $options);
            
            // ثبت لاگ موفقیت (فقط در محیط توسعه)
            if (self::isDevelopment()) {
                self::logInfo("Database connected successfully");
            }
            
            return self::$connection;
            
        } catch (PDOException $e) {
            self::logError("Database Connection Failed: " . $e->getMessage());
            self::showError(
                "خطا در اتصال به دیتابیس",
                "متأسفانه در حال حاضر امکان اتصال به سرور وجود ندارد. لطفاً چند دقیقه دیگر تلاش کنید."
            );
            exit;
        }
    }
    
    // ==========================================
    // اجرای کوئری SELECT با اندازه‌گیری زمان
    // ==========================================
    public static function query($sql, $params = []) {
        try {
            $startTime = microtime(true);
            
            $conn = self::connect();
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            
            self::$queryCount++;
            self::$totalQueryTime += $executionTime;
            
            // ذخیره لاگ کوئری (فقط در محیط توسعه)
            if (self::isDevelopment()) {
                self::$queryLog[] = [
                    'sql' => $sql,
                    'params' => $params,
                    'time' => $executionTime,
                    'timestamp' => date('Y-m-d H:i:s')
                ];
                
                // هشدار برای کوئری‌های کند
                if ($executionTime > 1) {
                    self::logWarning("Slow Query ({$executionTime}s): {$sql}");
                }
            }
            
            return $stmt;
            
        } catch (PDOException $e) {
            self::logError("Query Error: " . $e->getMessage() . " | SQL: " . $sql);
            throw new Exception("خطا در اجرای درخواست");
        }
    }
    
    // ==========================================
    // اجرای کوئری INSERT/UPDATE/DELETE
    // ==========================================
    public static function execute($sql, $params = []) {
        try {
            $startTime = microtime(true);
            
            $conn = self::connect();
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute($params);
            
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            
            self::$queryCount++;
            self::$totalQueryTime += $executionTime;
            
            if (self::isDevelopment()) {
                self::$queryLog[] = [
                    'sql' => $sql,
                    'params' => $params,
                    'time' => $executionTime,
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            }
            
            return $result;
            
        } catch (PDOException $e) {
            self::logError("Execute Error: " . $e->getMessage() . " | SQL: " . $sql);
            throw new Exception("خطا در اجرای عملیات");
        }
    }
    
    // ==========================================
    // دریافت یک رکورد
    // ==========================================
    public static function fetchOne($sql, $params = []) {
        $stmt = self::query($sql, $params);
        return $stmt->fetch();
    }
    
    // ==========================================
    // دریافت همه رکوردها
    // ==========================================
    public static function fetchAll($sql, $params = []) {
        $stmt = self::query($sql, $params);
        return $stmt->fetchAll();
    }
    
    // ==========================================
    // دریافت تعداد رکوردها
    // ==========================================
    public static function rowCount($sql, $params = []) {
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }
    
    // ==========================================
    // دریافت آخرین ID درج شده
    // ==========================================
    public static function lastInsertId() {
        return self::connect()->lastInsertId();
    }
    
    // ==========================================
    // شروع Transaction
    // ==========================================
    public static function beginTransaction() {
        return self::connect()->beginTransaction();
    }
    
    // ==========================================
    // Commit کردن Transaction
    // ==========================================
    public static function commit() {
        return self::connect()->commit();
    }
    
    // ==========================================
    // Rollback کردن Transaction
    // ==========================================
    public static function rollback() {
        return self::connect()->rollBack();
    }
    
    // ==========================================
    // بررسی وجود رکورد
    // ==========================================
    public static function exists($sql, $params = []) {
        $result = self::fetchOne($sql, $params);
        return !empty($result);
    }
    
    // ==========================================
    // Escape کردن رشته
    // ==========================================
    public static function escape($value) {
        $conn = self::connect();
        return $conn->quote($value);
    }
    
    // ==========================================
    // بستن اتصال
    // ==========================================
    public static function disconnect() {
        self::$connection = null;
    }
    
    // ==========================================
    // آمار کوئری‌ها
    // ==========================================
    public static function getQueryCount() {
        return self::$queryCount;
    }
    
    public static function getQueryLog() {
        return self::$queryLog;
    }
    
    public static function getTotalQueryTime() {
        return round(self::$totalQueryTime, 4);
    }
    
    public static function getAverageQueryTime() {
        if (self::$queryCount === 0) return 0;
        return round(self::$totalQueryTime / self::$queryCount, 4);
    }
    
    // ==========================================
    // بررسی محیط Development
    // ==========================================
    private static function isDevelopment() {
        return (!empty($_SERVER['SERVER_NAME']) && 
                ($_SERVER['SERVER_NAME'] === 'localhost' || 
                 $_SERVER['SERVER_NAME'] === '127.0.0.1'));
    }
    
    // ==========================================
    // ثبت خطا در فایل لاگ
    // ==========================================
    private static function logError($message) {
        self::writeLog('database-errors.log', 'ERROR', $message);
    }
    
    private static function logWarning($message) {
        self::writeLog('database-warnings.log', 'WARNING', $message);
    }
    
    private static function logInfo($message) {
        self::writeLog('database-info.log', 'INFO', $message);
    }
    
    private static function writeLog($filename, $level, $message) {
        $logDir = __DIR__ . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . '/' . $filename;
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $logMessage = "[{$timestamp}] [{$level}] [IP: {$ip}] {$message}\n";
        
        error_log($logMessage, 3, $logFile);
    }
    
    // ==========================================
    // نمایش صفحه خطا
    // ==========================================
    private static function showError($title, $message) {
        http_response_code(503);
        ?>
        <!DOCTYPE html>
        <html lang="fa" dir="rtl">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo htmlspecialchars($title); ?></title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                }
                .error-container {
                    background: white;
                    padding: 50px;
                    border-radius: 20px;
                    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                    max-width: 600px;
                    text-align: center;
                    animation: fadeIn 0.5s ease-out;
                }
                @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(-20px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                .error-icon {
                    font-size: 80px;
                    color: #f44336;
                    margin-bottom: 20px;
                    animation: pulse 2s infinite;
                }
                @keyframes pulse {
                    0%, 100% { transform: scale(1); }
                    50% { transform: scale(1.1); }
                }
                h1 {
                    color: #333;
                    font-size: 28px;
                    margin-bottom: 15px;
                }
                p {
                    color: #666;
                    font-size: 16px;
                    line-height: 1.8;
                    margin-bottom: 30px;
                }
                .btn-retry {
                    display: inline-block;
                    padding: 15px 40px;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    text-decoration: none;
                    border-radius: 50px;
                    font-weight: bold;
                    transition: transform 0.3s, box-shadow 0.3s;
                }
                .btn-retry:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
                }
                .error-code {
                    margin-top: 30px;
                    padding-top: 20px;
                    border-top: 1px solid #eee;
                    color: #999;
                    font-size: 12px;
                }
            </style>
        </head>
        <body>
            <div class="error-container">
                <div class="error-icon">⚠️</div>
                <h1><?php echo htmlspecialchars($title); ?></h1>
                <p><?php echo htmlspecialchars($message); ?></p>
                <a href="javascript:location.reload()" class="btn-retry">تلاش مجدد</a>
                <div class="error-code">Error Code: DB_CONNECTION_FAILED | <?php echo date('Y-m-d H:i:s'); ?></div>
            </div>
        </body>
        </html>
        <?php
    }
}

// ==========================================
// کلاس Security - مدیریت امنیت
// ==========================================
class Security {
    
    // ==========================================
    // بررسی و جلوگیری از Brute-Force Attack
    // ==========================================
    public static function checkLoginAttempts($username) {
        $ip = self::getClientIP();
        
        // حذف تلاش‌های قدیمی (بیش از 24 ساعت)
        $sql = "DELETE FROM login_attempts 
                WHERE attempted_at < DATE_SUB(NOW(), INTERVAL ? SECOND)";
        Database::execute($sql, [LOGIN_BLOCK_TIME]);
        
        // شمارش تلاش‌های ناموفق
        $sql = "SELECT COUNT(*) as count 
                FROM login_attempts 
                WHERE (username = ? OR ip_address = ?) 
                AND attempted_at > DATE_SUB(NOW(), INTERVAL ? SECOND)";
        
        $result = Database::fetchOne($sql, [$username, $ip, LOGIN_BLOCK_TIME]);
        
        if ($result && $result['count'] >= MAX_LOGIN_ATTEMPTS) {
            return false; // مسدود شده
        }
        
        return true; // مجاز به ورود
    }
    
    // ==========================================
    // ثبت تلاش ناموفق ورود
    // ==========================================
    public static function logFailedLogin($username) {
        $ip = self::getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        $sql = "INSERT INTO login_attempts (username, ip_address, user_agent) 
                VALUES (?, ?, ?)";
        
        Database::execute($sql, [$username, $ip, $userAgent]);
    }
    
    // ==========================================
    // ثبت ورود موفق
    // ==========================================
    public static function logSuccessfulLogin($userId) {
        $ip = self::getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        // حذف تلاش‌های ناموفق قبلی
        $sql = "DELETE FROM login_attempts WHERE username IN (
                    SELECT username FROM users WHERE id = ?
                )";
        Database::execute($sql, [$userId]);
        
        // ثبت لاگ ورود
        $sql = "INSERT INTO login_logs (user_id, ip_address, user_agent, login_time) 
                VALUES (?, ?, ?, NOW())";
        
        Database::execute($sql, [$userId, $ip, $userAgent]);
        
        return Database::lastInsertId();
    }
    
    // ==========================================
    // ثبت خروج
    // ==========================================
    public static function logLogout($userId, $loginLogId = null) {
        if ($loginLogId) {
            $sql = "UPDATE login_logs 
                    SET logout_time = NOW(),
                        session_duration = TIMESTAMPDIFF(SECOND, login_time, NOW())
                    WHERE id = ?";
            
            Database::execute($sql, [$loginLogId]);
        }
    }
    
    // ==========================================
    // دریافت IP واقعی کاربر
    // ==========================================
    public static function getClientIP() {
        $ipKeys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER)) {
                $ip = explode(',', $_SERVER[$key])[0];
                $ip = trim($ip);
                
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return '0.0.0.0';
    }
    
    // ==========================================
    // ثبت فعالیت کاربر
    // ==========================================
    public static function logActivity($userId, $action, $entityType = null, $entityId = null, $details = null) {
        $ip = self::getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        $sql = "INSERT INTO activity_logs 
                (user_id, action, entity_type, entity_id, details, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        Database::execute($sql, [$userId, $action, $entityType, $entityId, $details, $ip, $userAgent]);
    }
}

// ==========================================
// کلاس Helper - توابع کمکی
// ==========================================
class Helper {
    
    // ==========================================
    // Sanitize کردن ورودی‌ها
    // ==========================================
    public static function sanitize($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }
    
    // ==========================================
    // پاکسازی عمیق (برای HTML)
    // ==========================================
    public static function clean_input($data) {
        if (is_array($data)) {
            return array_map([self::class, 'clean_input'], $data);
        }
        
        $data = trim($data);
        $data = stripslashes($data);
        $data = strip_tags($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }
    
    // ==========================================
    // تبدیل عدد به فرمت پول ایران
    // ==========================================
    public static function formatPrice($price) {
        return number_format($price, 0, '.', ',') . ' ' . CURRENCY;
    }
    
    // ==========================================
    // محاسبه درصد تخفیف
    // ==========================================
    public static function calculateDiscount($originalPrice, $discountedPrice) {
        if ($originalPrice <= 0) return 0;
        return round((($originalPrice - $discountedPrice) / $originalPrice) * 100);
    }
    
    // ==========================================
    // محاسبه مالیات
    // ==========================================
    public static function calculateTax($amount) {
        return round($amount * TAX_RATE, 2);
    }
    
    // ==========================================
    // بررسی ارسال رایگان
    // ==========================================
    public static function isFreeShipping($amount) {
        return $amount >= FREE_SHIPPING_THRESHOLD;
    }
    
    // ==========================================
    // تبدیل تاریخ میلادی به شمسی
    // ==========================================
    public static function toJalali($timestamp = null, $format = 'Y/m/d H:i') {
        if ($timestamp === null) {
            $timestamp = time();
        } elseif (is_string($timestamp)) {
            $timestamp = strtotime($timestamp);
        }
        
        // تابع ساده تبدیل (برای استفاده واقعی از کتابخانه jdf.php استفاده کنید)
        return date('Y-m-d H:i:s', $timestamp);
    }
    
    // ==========================================
    // تولید CSRF Token
    // ==========================================
    public static function generateCSRFToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token']) || 
            !isset($_SESSION['csrf_token_time']) ||
            (time() - $_SESSION['csrf_token_time']) > CSRF_TOKEN_EXPIRE) {
            
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        
        return $_SESSION['csrf_token'];
    }
    
    // ==========================================
    // بررسی CSRF Token
    // ==========================================
    public static function verifyCSRFToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            return false;
        }
        
        // بررسی انقضا
        if ((time() - $_SESSION['csrf_token_time']) > CSRF_TOKEN_EXPIRE) {
            unset($_SESSION['csrf_token']);
            unset($_SESSION['csrf_token_time']);
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    // ==========================================
    // تولید توکن امن تصادفی
    // ==========================================
    public static function generateSecureToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    // ==========================================
    // Redirect
    // ==========================================
    public static function redirect($url, $statusCode = 302) {
        header("Location: {$url}", true, $statusCode);
        exit;
    }
    
    // ==========================================
    // نمایش پیام Flash
    // ==========================================
    public static function setFlashMessage($type, $message) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['flash_message'] = [
            'type' => $type,
            'message' => $message,
            'time' => time()
        ];
    }
    
    public static function getFlashMessage() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['flash_message'])) {
            $message = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
            return $message;
        }
        
        return null;
    }
    
    // ==========================================
    // تولید Slug
    // ==========================================
    public static function generateSlug($text) {
        $persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        
        $text = str_replace($persianDigits, $englishDigits, $text);
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s-آ-ی]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        $text = trim($text, '-');
        
        return $text;
    }
    
    // ==========================================
    // Hash کردن رمز عبور
    // ==========================================
    public static function hash_password($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    // ==========================================
    // بررسی رمز عبور
    // ==========================================
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    // ==========================================
    // اعتبارسنجی رمز عبور قوی
    // ==========================================
    public static function validatePassword($password) {
        $errors = [];
        
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            $errors[] = "رمز عبور باید حداقل " . PASSWORD_MIN_LENGTH . " کاراکتر باشد";
        }
        
        if (PASSWORD_REQUIRE_UPPERCASE && !preg_match('/[A-Z]/', $password)) {
            $errors[] = "رمز عبور باید حداقل یک حرف بزرگ داشته باشد";
        }
        
        if (PASSWORD_REQUIRE_NUMBER && !preg_match('/[0-9]/', $password)) {
            $errors[] = "رمز عبور باید حداقل یک عدد داشته باشد";
        }
        
        if (PASSWORD_REQUIRE_SPECIAL && !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $errors[] = "رمز عبور باید حداقل یک کاراکتر خاص داشته باشد";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    // ==========================================
    // بررسی لاگین بودن
    // ==========================================
    public static function is_logged_in() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['user_id']) && isset($_SESSION['user_email']);
    }
    
    // ==========================================
    // بررسی ادمین بودن
    // ==========================================
    public static function is_admin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
    
    // ==========================================
    // بررسی تایم‌اوت سشن
    // ==========================================
    public static function check_session_timeout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['last_activity'])) {
            if ((time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
                session_unset();
                session_destroy();
                return false;
            }
        }
        
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    // ==========================================
    // احراز هویت کاربر
    // ==========================================
    public static function requireLogin() {
        if (!self::is_logged_in() || !self::check_session_timeout()) {
            self::setFlashMessage('error', 'لطفاً ابتدا وارد حساب کاربری خود شوید');
            self::redirect('login.php');
        }
    }
    
    // ==========================================
    // احراز هویت ادمین
    // ==========================================
    public static function requireAdmin() {
        self::requireLogin();
        
        if (!self::is_admin()) {
            self::setFlashMessage('error', 'شما مجوز دسترسی به این بخش را ندارید');
            self::redirect('index.php');
        }
    }
    
    // ==========================================
    // فرمت کردن شماره تلفن
    // ==========================================
    public static function formatPhone($phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($phone) === 11 && substr($phone, 0, 2) === '09') {
            return substr($phone, 0, 4) . '-' . substr($phone, 4, 3) . '-' . substr($phone, 7);
        }
        
        return $phone;
    }
    
    // ==========================================
    // اعتبارسنجی ایمیل
    // ==========================================
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    // ==========================================
    // اعتبارسنجی شماره موبایل
    // ==========================================
    public static function validateMobile($mobile) {
        $mobile = preg_replace('/[^0-9]/', '', $mobile);
        return preg_match('/^09[0-9]{9}$/', $mobile);
    }
    
    // ==========================================
    // برش متن
    // ==========================================
    public static function truncate($text, $length = 100, $suffix = '...') {
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        
        return mb_substr($text, 0, $length) . $suffix;
    }
    
    // ==========================================
    // تبدیل به آرایه ایمن
    // ==========================================
    public static function toArray($data) {
        return is_array($data) ? $data : [];
    }
    
    // ==========================================
    // دریافت مقدار از آرایه با مقدار پیش‌فرض
    // ==========================================
    public static function getValue($array, $key, $default = null) {
        return isset($array[$key]) ? $array[$key] : $default;
    }
}

// ==========================================
// شروع Session با امنیت بالا
// ==========================================
if (session_status() === PHP_SESSION_NONE) {
    session_name('FASHION_SHOP_SESSION');
    session_start();
    
    // جلوگیری از Session Fixation
    if (!isset($_SESSION['initiated'])) {
        session_regenerate_id(true);
        $_SESSION['initiated'] = true;
        $_SESSION['user_ip'] = Security::getClientIP();
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    }
    
    // بررسی IP و User Agent برای امنیت بیشتر
    if (isset($_SESSION['user_ip']) && $_SESSION['user_ip'] !== Security::getClientIP()) {
        session_unset();
        session_destroy();
        session_start();
    }
    
    // بررسی تایم‌اوت
    Helper::check_session_timeout();
}

// ==========================================
// اتصال خودکار به دیتابیس
// ==========================================
Database::connect();

// ==========================================
// 🔑 اطلاعات ورود پنل مدیریت
// ==========================================
/*
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
نام کاربری: admin
رمز عبور: Admin@2026
ایمیل: admin@fashionshop.com
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
⚠️ توجه: پس از اولین ورود حتماً رمز عبور را تغییر دهید!
*/

// ==========================================
// پایان فایل config.php
// ==========================================
