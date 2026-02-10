-- =====================================================
-- فروشگاه پوشاک - دیتابیس کامل (نسخه 2.0)
-- معماری: EAV Model با امنیت کامل
-- تعداد جداول: 19
-- طراح: محمد مزروعی
-- تاریخ: 1404/11/21
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- =====================================================
-- 1. جدول کاربران (users)
-- =====================================================
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','customer') DEFAULT 'customer',
  `is_active` tinyint(1) DEFAULT 1,
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(100) DEFAULT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. جدول دسته‌بندی‌ها (categories)
-- =====================================================
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text,
  `parent_id` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `parent_id` (`parent_id`),
  KEY `idx_active` (`is_active`),
  KEY `idx_sort` (`sort_order`),
  FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. جدول برندها (brands)
-- =====================================================
CREATE TABLE `brands` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text,
  `logo` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. جدول محصولات (products)
-- =====================================================
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text,
  `short_description` varchar(500) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `low_stock_threshold` int(11) DEFAULT 5,
  `weight` decimal(8,2) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `views` int(11) DEFAULT 0,
  `sold_count` int(11) DEFAULT 0,
  `rating_avg` decimal(3,2) DEFAULT 0.00,
  `rating_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `sku` (`sku`),
  KEY `category_id` (`category_id`),
  KEY `brand_id` (`brand_id`),
  KEY `idx_price` (`price`),
  KEY `idx_featured` (`is_featured`),
  KEY `idx_active` (`is_active`),
  FULLTEXT KEY `ft_search` (`name`,`description`),
  FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. جدول ویژگی‌های محصول (product_attributes)
-- معماری EAV - برای ویژگی‌های پویا
-- =====================================================
CREATE TABLE `product_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `type` enum('text','number','color','size','boolean','select') DEFAULT 'text',
  `is_filterable` tinyint(1) DEFAULT 0,
  `is_visible` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_filterable` (`is_filterable`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. جدول مقادیر ویژگی‌ها (product_attribute_values)
-- معماری EAV - ذخیره مقادیر واقعی
-- =====================================================
CREATE TABLE `product_attribute_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `attribute_id` int(11) NOT NULL,
  `value` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `attribute_id` (`attribute_id`),
  KEY `idx_product_attr` (`product_id`,`attribute_id`),
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`attribute_id`) REFERENCES `product_attributes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. جدول تصاویر محصول (product_images)
-- =====================================================
CREATE TABLE `product_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `idx_primary` (`is_primary`),
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. جدول نظرات (reviews)
-- =====================================================
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(1) NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
  `title` varchar(255) DEFAULT NULL,
  `comment` text,
  `is_verified_purchase` tinyint(1) DEFAULT 0,
  `is_approved` tinyint(1) DEFAULT 0,
  `helpful_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_approved` (`is_approved`),
  KEY `idx_rating` (`rating`),
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 9. جدول کوپن‌های تخفیف (coupons)
-- =====================================================
CREATE TABLE `coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `discount_type` enum('percentage','fixed') DEFAULT 'percentage',
  `discount_value` decimal(10,2) NOT NULL,
  `min_purchase` decimal(10,2) DEFAULT 0.00,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `usage_count` int(11) DEFAULT 0,
  `user_limit` int(11) DEFAULT 1,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_active` (`is_active`),
  KEY `idx_dates` (`start_date`,`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 10. جدول آدرس‌ها (addresses)
-- =====================================================
CREATE TABLE `addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `province` varchar(50) NOT NULL,
  `city` varchar(50) NOT NULL,
  `address` text NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_default` (`is_default`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 11. جدول سبد خرید (cart)
-- =====================================================
CREATE TABLE `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  KEY `idx_session` (`session_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 12. جدول علاقه‌مندی‌ها (wishlists)
-- =====================================================
CREATE TABLE `wishlists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_product` (`user_id`,`product_id`),
  KEY `product_id` (`product_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 13. جدول سفارشات (orders)
-- =====================================================
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled','refunded') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `transaction_id` varchar(100) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) DEFAULT 0.00,
  `shipping_cost` decimal(10,2) DEFAULT 0.00,
  `discount` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `coupon_code` varchar(50) DEFAULT NULL,
  `shipping_address` text,
  `billing_address` text,
  `notes` text,
  `tracking_number` varchar(100) DEFAULT NULL,
  `shipped_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_payment` (`payment_status`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 14. جدول آیتم‌های سفارش (order_items)
-- =====================================================
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_sku` varchar(100) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 15. جدول نظرات عمومی (comments)
-- برای صفحه درباره ما و غیره
-- =====================================================
CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `page` varchar(50) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `is_replied` tinyint(1) DEFAULT 0,
  `reply` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_read` (`is_read`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 16. جدول لاگ فعالیت‌ها (activity_logs)
-- برای ردیابی تغییرات و امنیت
-- =====================================================
CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `description` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_entity` (`entity_type`,`entity_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 17. جدول تنظیمات (settings)
-- =====================================================
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `value` text,
  `type` varchar(50) DEFAULT 'string',
  `group` varchar(50) DEFAULT 'general',
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`),
  KEY `idx_group` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 18. جدول سشن‌ها (sessions)
-- برای مدیریت نشست‌ها
-- =====================================================
CREATE TABLE `sessions` (
  `id` varchar(128) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `payload` text NOT NULL,
  `last_activity` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_last_activity` (`last_activity`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 19. جدول تلاش‌های ورود ناموفق (failed_login_attempts)
-- برای جلوگیری از Brute Force
-- =====================================================
CREATE TABLE `failed_login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `attempt_count` int(11) DEFAULT 1,
  `blocked_until` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ip` (`ip_address`),
  KEY `idx_blocked` (`blocked_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- درج داده‌های نمونه
-- =====================================================

-- 1. کاربران نمونه
INSERT INTO `users` (`username`, `email`, `password`, `full_name`, `phone`, `role`, `is_active`, `email_verified`) VALUES
('admin', 'admin@shop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'مدیر سایت', '09121234567', 'admin', 1, 1),
('customer1', 'customer1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'علی احمدی', '09129876543', 'customer', 1, 1),
('customer2', 'customer2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'سارا محمدی', '09359876543', 'customer', 1, 1);

-- 2. دسته‌بندی‌ها
INSERT INTO `categories` (`name`, `slug`, `description`, `parent_id`, `sort_order`, `is_active`) VALUES
('کفش', 'shoes', 'انواع کفش‌های ورزشی و رسمی', NULL, 1, 1),
('پوشاک مردانه', 'mens-clothing', 'لباس و پوشاک مردانه', NULL, 2, 1),
('پوشاک زنانه', 'womens-clothing', 'لباس و پوشاک زنانه', NULL, 3, 1),
('اکسسوری', 'accessories', 'کیف، کمربند و اکسسوری', NULL, 4, 1),
('کفش ورزشی', 'sport-shoes', 'کفش‌های ورزشی', 1, 1, 1),
('کفش رسمی', 'formal-shoes', 'کفش‌های رسمی', 1, 2, 1),
('پیراهن', 'shirts', 'پیراهن مردانه', 2, 1, 1),
('شلوار', 'pants', 'شلوار مردانه', 2, 2, 1),
('مانتو', 'manteau', 'مانتو و روپوش', 3, 1, 1),
('کیف', 'bags', 'انواع کیف', 4, 1, 1);

-- 3. برندها
INSERT INTO `brands` (`name`, `slug`, `description`, `is_active`) VALUES
('Nike', 'nike', 'برند ورزشی نایک', 1),
('Adidas', 'adidas', 'برند ورزشی آدیداس', 1),
('Zara', 'zara', 'برند پوشاک زارا', 1),
('H&M', 'hm', 'برند پوشاک اچ اند ام', 1),
('Mango', 'mango', 'برند پوشاک منگو', 1),
('Puma', 'puma', 'برند ورزشی پوما', 1),
('Reebok', 'reebok', 'برند ورزشی ریباک', 1),
('New Balance', 'new-balance', 'برند ورزشی نیوبالانس', 1),
('LC Waikiki', 'lc-waikiki', 'برند پوشاک ال سی وایکیکی', 1),
('Pull & Bear', 'pull-bear', 'برند پوشاک پول اند بیر', 1);

-- 4. ویژگی‌های محصول (EAV)
INSERT INTO `product_attributes` (`name`, `slug`, `type`, `is_filterable`, `is_visible`, `sort_order`) VALUES
('سایز', 'size', 'select', 1, 1, 1),
('رنگ', 'color', 'color', 1, 1, 2),
('جنس', 'material', 'text', 1, 1, 3),
('نوع پارچه', 'fabric-type', 'select', 1, 1, 4),
('طول آستین', 'sleeve-length', 'select', 1, 1, 5),
('مناسب برای', 'suitable-for', 'select', 1, 1, 6),
('کشور سازنده', 'country', 'text', 0, 1, 7),
('ضد آب', 'waterproof', 'boolean', 1, 1, 8),
('قابل شستشو', 'washable', 'boolean', 0, 1, 9),
('وزن', 'weight', 'number', 0, 1, 10);

-- 5. محصولات نمونه (30 محصول)
INSERT INTO `products` (`category_id`, `brand_id`, `name`, `slug`, `description`, `short_description`, `price`, `sale_price`, `sku`, `stock`, `is_featured`, `is_active`) VALUES
-- کفش ورزشی
(5, 1, 'کفش ورزشی نایک Air Max', 'nike-air-max', 'کفش ورزشی مردانه نایک ایرمکس با تکنولوژی جذب ضربه', 'کفش ورزشی راحت و با دوام', 3500000, 2980000, 'NK-AM-001', 25, 1, 1),
(5, 2, 'کفش ورزشی آدیداس Ultraboost', 'adidas-ultraboost', 'کفش ورزشی با فناوری بوست برای راحتی بیشتر', 'بهترین انتخاب برای دویدن', 4200000, 3780000, 'AD-UB-001', 18, 1, 1),
(5, 6, 'کفش ورزشی پوما RS-X', 'puma-rsx', 'کفش ورزشی با طراحی مدرن و رنگ‌بندی متنوع', 'سبک و انعطاف‌پذیر', 2800000, 2450000, 'PM-RSX-001', 30, 0, 1),
(5, 7, 'کفش ورزشی ریباک Classic', 'reebok-classic', 'کفش ورزشی کلاسیک مناسب برای استفاده روزمره', 'طراحی کلاسیک و زیبا', 2200000, NULL, 'RB-CL-001', 40, 0, 1),
(5, 8, 'کفش ورزشی نیوبالانس 574', 'new-balance-574', 'کفش ورزشی با کفی راحت و زیره مقاوم', 'کیفیت عالی و قیمت مناسب', 2900000, 2610000, 'NB-574-001', 22, 1, 1),

-- کفش رسمی
(6, 3, 'کفش رسمی مردانه زارا', 'zara-formal-shoes', 'کفش رسمی چرم طبیعی مناسب مهمانی', 'شیک و باکلاس', 3200000, 2880000, 'ZR-FM-001', 15, 0, 1),
(6, 4, 'کفش رسمی اچ‌اندام Derby', 'hm-derby-shoes', 'کفش رسمی با طراحی کلاسیک', 'مناسب محیط کار', 2600000, NULL, 'HM-DB-001', 20, 0, 1),

-- پیراهن مردانه
(7, 3, 'پیراهن مردانه زارا آبی', 'zara-blue-shirt', 'پیراهن مردانه آستین بلند آبی روشن', 'پارچه نخی با کیفیت', 980000, 784000, 'ZR-SH-001', 50, 1, 1),
(7, 4, 'پیراهن مردانه اچ‌اندام چهارخانه', 'hm-checked-shirt', 'پیراهن چهارخانه کژوال', 'راحت و شیک', 750000, NULL, 'HM-SH-001', 60, 0, 1),
(7, 5, 'پیراهن مردانه منگو سفید', 'mango-white-shirt', 'پیراهن رسمی سفید آستین بلند', 'مناسب مهمانی و محیط کار', 1100000, 990000, 'MG-SH-001', 35, 0, 1),
(7, 9, 'پیراهن مردانه ال‌سی وایکیکی', 'lcw-casual-shirt', 'پیراهن کتان مردانه', 'عالی برای تابستان', 650000, NULL, 'LCW-SH-001', 45, 0, 1),

-- شلوار مردانه
(8, 3, 'شلوار جین زارا Slim Fit', 'zara-slim-jeans', 'شلوار جین مردانه اسلیم فیت', 'پارچه کشسان و راحت', 1450000, 1305000, 'ZR-JN-001', 40, 1, 1),
(8, 4, 'شلوار پارچه‌ای اچ‌اندام', 'hm-fabric-pants', 'شلوار پارچه‌ای رسمی مردانه', 'مناسب محیط کار', 1200000, NULL, 'HM-PT-001', 30, 0, 1),
(8, 10, 'شلوار اسلش پول‌اندبیر', 'pull-bear-slash', 'شلوار اسلش مردانه با پارچه نازک', 'مد روز', 980000, 882000, 'PB-SL-001', 25, 0, 1),

-- مانتو
(9, 3, 'مانتو زارا بلند', 'zara-long-manteau', 'مانتو بلند زنانه با طرح ساده', 'شیک و رسمی', 2200000, 1980000, 'ZR-MN-001', 20, 1, 1),
(9, 5, 'مانتو کوتاه منگو', 'mango-short-manteau', 'مانتو کوتاه اسپرت', 'راحت و جذاب', 1800000, NULL, 'MG-MN-001', 28, 0, 1),
(9, 4, 'مانتو اچ‌اندام طرح دار', 'hm-patterned-manteau', 'مانتو با طرح گل‌دار', 'مناسب بهار', 1950000, 1755000, 'HM-MN-001', 15, 0, 1),

-- کیف
(10, 3, 'کیف دستی زارا', 'zara-handbag', 'کیف دستی زنانه چرم مصنوعی', 'جادار و شیک', 1600000, 1440000, 'ZR-BG-001', 18, 1, 1),
(10, 5, 'کیف دوشی منگو', 'mango-shoulder-bag', 'کیف دوشی با بند بلند', 'سبک و کاربردی', 1350000, NULL, 'MG-BG-001', 22, 0, 1),
(10, 4, 'کوله پشتی اچ‌اندام', 'hm-backpack', 'کوله پشتی مناسب دانشجویان', 'فضای داخلی مناسب', 890000, 801000, 'HM-BP-001', 35, 0, 1),

-- محصولات بیشتر
(5, 1, 'کفش ورزشی نایک Revolution', 'nike-revolution', 'کفش ورزشی با قیمت مناسب', 'ورزش روزانه', 2400000, NULL, 'NK-RV-001', 32, 0, 1),
(5, 2, 'کفش ورزشی آدیداس Superstar', 'adidas-superstar', 'کفش ورزشی کلاسیک آدیداس', 'آیکونیک و پرطرفدار', 3100000, 2790000, 'AD-SS-001', 27, 1, 1),
(7, 3, 'پیراهن زارا مشکی', 'zara-black-shirt', 'پیراهن مشکی کلاسیک', 'همیشه مد', 1050000, NULL, 'ZR-SH-002', 42, 0, 1),
(8, 3, 'شلوار جین زارا Straight', 'zara-straight-jeans', 'شلوار جین استریت فیت', 'راحت و کلاسیک', 1380000, 1242000, 'ZR-JN-002', 38, 0, 1),
(9, 5, 'مانتو منگو کرمی', 'mango-cream-manteau', 'مانتو کرمی با دکمه‌های طلایی', 'لوکس و باکیفیت', 2400000, 2160000, 'MG-MN-002', 12, 1, 1),
(10, 3, 'کیف کلاچ زارا', 'zara-clutch', 'کیف کلاچ شب مناسب مهمانی', 'کوچک و شیک', 950000, NULL, 'ZR-CL-001', 25, 0, 1),
(5, 6, 'کفش ورزشی پوما Suede', 'puma-suede', 'کفش ورزشی سوئد پوما', 'کلاسیک و راحت', 2650000, 2385000, 'PM-SD-001', 19, 0, 1),
(7, 4, 'پیراهن اچ‌اندام راه راه', 'hm-striped-shirt', 'پیراهن راه راه کژوال', 'مد تابستان', 820000, NULL, 'HM-SH-002', 55, 0, 1),
(8, 10, 'شلوار کتان پول‌اندبیر', 'pull-bear-linen-pants', 'شلوار کتان سبک تابستانی', 'خنک و راحت', 1100000, 990000, 'PB-PT-001', 28, 0, 1),
(10, 5, 'کیف چرم منگو', 'mango-leather-bag', 'کیف چرم طبیعی زنانه', 'باکیفیت و مقاوم', 2800000, NULL, 'MG-BG-002', 10, 1, 1);

-- 6. مقادیر ویژگی‌ها (نمونه برای چند محصول)
INSERT INTO `product_attribute_values` (`product_id`, `attribute_id`, `value`) VALUES
-- کفش نایک Air Max
(1, 1, '41,42,43,44'),
(1, 2, 'مشکی,سفید,قرمز'),
(1, 3, 'چرم مصنوعی'),
(1, 6, 'ورزش,پیاده‌روی'),
(1, 7, 'ویتنام'),
-- کفش آدیداس Ultraboost
(2, 1, '40,41,42,43,44,45'),
(2, 2, 'مشکی,سفید,آبی'),
(2, 3, 'مش و Boost'),
(2, 6, 'دویدن,ورزش'),
(2, 7, 'چین'),
-- پیراهن زارا
(8, 1, 'S,M,L,XL,XXL'),
(8, 2, 'آبی روشن'),
(8, 3, 'نخ'),
(8, 4, 'پنبه 100%'),
(8, 5, 'آستین بلند'),
(8, 9, '1'),
-- مانتو زارا
(15, 1, 'S,M,L,XL'),
(15, 2, 'مشکی,خاکستری,سرمه‌ای'),
(15, 3, 'کرپ'),
(15, 4, 'پلی‌استر'),
(15, 9, '1');

-- 7. تصاویر محصولات (نمونه)
INSERT INTO `product_images` (`product_id`, `image_path`, `alt_text`, `is_primary`, `sort_order`) VALUES
(1, '/images/products/nike-airmax-1.jpg', 'کفش نایک ایرمکس نمای جلو', 1, 1),
(1, '/images/products/nike-airmax-2.jpg', 'کفش نایک ایرمکس نمای پهلو', 0, 2),
(2, '/images/products/adidas-ultraboost-1.jpg', 'کفش آدیداس الترابوست', 1, 1),
(8, '/images/products/zara-shirt-1.jpg', 'پیراهن زارا آبی', 1, 1);

-- 8. کوپن‌های تخفیف
INSERT INTO `coupons` (`code`, `description`, `discount_type`, `discount_value`, `min_purchase`, `max_discount`, `usage_limit`, `start_date`, `end_date`, `is_active`) VALUES
('WELCOME10', 'تخفیف 10 درصد برای خریداران جدید', 'percentage', 10.00, 500000, 200000, 100, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 1),
('WINTER25', 'تخفیف ویژه زمستان 25 درصد', 'percentage', 25.00, 1000000, 500000, 50, '2026-02-01 00:00:00', '2026-03-20 23:59:59', 1),
('FREEPOST', 'ارسال رایگان', 'fixed', 50000, 300000, NULL, 200, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 1),
('SPRING15', 'جشنواره بهار 15 درصد', 'percentage', 15.00, 800000, 300000, NULL, '2026-03-21 00:00:00', '2026-06-21 23:59:59', 1);

-- 9. نظرات نمونه
INSERT INTO `reviews` (`product_id`, `user_id`, `rating`, `title`, `comment`, `is_verified_purchase`, `is_approved`) VALUES
(1, 2, 5, 'عالی و راحت', 'کفش خیلی راحت و با کیفیته. قیمتش هم مناسبه.', 1, 1),
(1, 3, 4, 'خوب بود', 'کفش خوبیه ولی یکم سایزبندیش کوچیکه', 1, 1),
(2, 2, 5, 'بهترین خرید', 'واقعا ارزش خریدش رو داره. پیشنهاد می‌کنم', 1, 1),
(8, 3, 5, 'پارچه فوق‌العاده', 'پیراهن خیلی خوبی بود. پارچه‌اش عالیه', 1, 1);

-- 10. تنظیمات سایت
INSERT INTO `settings` (`key`, `value`, `type`, `group`, `description`) VALUES
('site_name', 'فروشگاه پوشاک', 'string', 'general', 'نام سایت'),
('site_email', 'info@shop.com', 'string', 'general', 'ایمیل سایت'),
('site_phone', '021-12345678', 'string', 'general', 'تلفن سایت'),
('tax_rate', '9', 'number', 'shop', 'نرخ مالیات (درصد)'),
('free_shipping_threshold', '500000', 'number', 'shop', 'حداقل خرید برای ارسال رایگان'),
('currency', 'تومان', 'string', 'shop', 'واحد پول'),
('products_per_page', '12', 'number', 'shop', 'تعداد محصولات در هر صفحه'),
('allow_registration', '1', 'boolean', 'users', 'امکان ثبت‌نام'),
('require_email_verification', '0', 'boolean', 'users', 'نیاز به تایید ایمیل');

COMMIT;

-- =====================================================
-- یادداشت: رمزهای عبور
-- =====================================================
-- تمام رمزهای عبور نمونه: Admin@2026
-- برای ادمین: username = admin, password = Admin@2026
-- =====================================================
