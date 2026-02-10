<?php
/**
 * فایل تنظیمات پروژه فروشگاه پوشاک
 * طراح: محمد مزروعی
 * تاریخ: 1404/11/19
 */

// نمایش خطاها برای دیباگ (در محیط Production باید false باشد)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// تنظیمات دیتابیس
define('DB_HOST', 'localhost');
define('DB_NAME', 'iran');           // ✅ نام دیتابیس شما
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// تنظیمات سایت
define('SITE_NAME', 'فروشگاه پوشاک');
define('SITE_URL', 'http://localhost/shop');
define('ADMIN_EMAIL', 'admin@shop.com');

// تنظیمات امنیتی
define('HASH_ALGORITHM', 'sha256');
define('SESSION_LIFETIME', 3600); // 1 ساعت

// مسیرها
define('BASE_PATH', __DIR__);
define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('PRODUCT_IMAGE_PATH', UPLOAD_PATH . '/products');

// شروع Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * کلاس اتصال به دیتابیس (Singleton Pattern)
 * این کلاس تضمین می‌کند که فقط یک نمونه از اتصال دیتابیس وجود داشته باشد
 */
class Database {
    private static $instance = null;
    private $connection;
    private $isConnected = false;
    
    /**
     * Constructor خصوصی برای جلوگیری از ساخت مستقیم
     */
    private function __construct() {
        $this->connect();
    }
    
    /**
     * متد اتصال به دیتابیس
     */
    private function connect() {
        try {
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=%s",
                DB_HOST,
                DB_NAME,
                DB_CHARSET
            );
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                PDO::ATTR_PERSISTENT => false
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            $this->isConnected = true;
            
            // تنظیم timezone
            $this->connection->exec("SET time_zone = '+03:30'");
            
        } catch(PDOException $e) {
            $this->isConnected = false;
            $this->logError("خطای اتصال به دیتابیس: " . $e->getMessage());
            die("❌ خطای اتصال به دیتابیس. لطفاً تنظیمات را بررسی کنید.");
        }
    }
    
    /**
     * دریافت نمونه یکتا از کلاس (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * دریافت اتصال PDO
     */
    public function getConnection() {
        if (!$this->isConnected || $this->connection === null) {
            $this->connect();
        }
        return $this->connection;
    }
    
    /**
     * بررسی وضعیت اتصال
     */
    public function isConnected() {
        return $this->isConnected;
    }
    
    /**
     * اجرای کوئری SELECT با Prepared Statement
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            $this->logError("خطای کوئری: " . $e->getMessage() . " | SQL: " . $sql);
            throw $e;
        }
    }
    
    /**
     * اجرای کوئری INSERT/UPDATE/DELETE
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($params);
        } catch(PDOException $e) {
            $this->logError("خطای اجرای کوئری: " . $e->getMessage() . " | SQL: " . $sql);
            throw $e;
        }
    }
    
    /**
     * دریافت آخرین ID درج شده
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    /**
     * شروع Transaction
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * تایید Transaction
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * لغو Transaction
     */
    public function rollback() {
        return $this->connection->rollBack();
    }
    
    /**
     * ثبت خطا در فایل لاگ
     */
    private function logError($message) {
        $logFile = __DIR__ . '/error.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
    
    /**
     * جلوگیری از کپی کردن (Clone)
     */
    private function __clone() {}
    
    /**
     * جلوگیری از Unserialize
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
    
    /**
     * بستن اتصال
     */
    public function __destruct() {
        $this->connection = null;
    }
}

/**
 * توابع کمکی (Helper Functions)
 */

/**
 * پاکسازی ورودی کاربر (XSS Protection)
 */
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * هش کردن رمز عبور
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * بررسی رمز عبور
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * تولید توکن تصادفی
 */
function generate_token($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * بررسی لاگین بودن کاربر
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * بررسی ادمین بودن کاربر
 */
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * ریدایرکت به صفحه دیگر
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * نمایش پیام فلش
 */
function set_flash_message($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * دریافت پیام فلش
 */
function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * فرمت کردن قیمت
 */
function format_price($price) {
    return number_format($price, 0, '.', ',') . ' تومان';
}

/**
 * فرمت کردن تاریخ شمسی
 */
function format_date($date) {
    // اینجا می‌توانید از کتابخانه jDateTime استفاده کنید
    return date('Y/m/d H:i', strtotime($date));
}

/**
 * آپلود فایل
 */
function upload_file($file, $destination) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed_types)) {
        return ['success' => false, 'message' => 'فرمت فایل مجاز نیست'];
    }
    
    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'حجم فایل بیش از حد مجاز است'];
    }
    
    $filename = uniqid() . '_' . basename($file['name']);
    $filepath = $destination . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['success' => false, 'message' => 'خطا در آپلود فایل'];
}

/**
 * حذف فایل
 */
function delete_file($filepath) {
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

/**
 * تولید Slug از متن فارسی
 */
function generate_slug($text) {
    $text = trim($text);
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/\s+/', '-', $text);
    $text = preg_replace('/[^\p{L}\p{N}\-]/u', '', $text);
    return $text;
}

/**
 * بررسی CSRF Token
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * تولید CSRF Token
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generate_token();
    }
    return $_SESSION['csrf_token'];
}

// ایجاد پوشه‌های مورد نیاز
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}
if (!file_exists(PRODUCT_IMAGE_PATH)) {
    mkdir(PRODUCT_IMAGE_PATH, 0755, true);
}

// پایان فایل
?>
