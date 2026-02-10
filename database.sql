-- ==========================================
-- 🗄️ دیتابیس فروشگاه پوشاک لوکس - نسخه کامل حرفه‌ای
-- معماری: EAV (Entity-Attribute-Value)
-- امنیت: Bcrypt Password + Login Attempts Protection
-- نسخه: 2.0
-- تاریخ: 1404/11/20 (2026-02-10)
-- طراح: محمد مزروعی
-- ==========================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ساخت دیتابیس
CREATE DATABASE IF NOT EXISTS `fashion_shop_db` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `fashion_shop_db`;

-- ==========================================
-- 🔐 بخش امنیتی و کاربران
-- ==========================================

-- ==========================================
-- 👤 جدول کاربران (Users)
-- ==========================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) DEFAULT NULL,
  `mobile` VARCHAR(15) DEFAULT NULL,
  `national_code` VARCHAR(10) DEFAULT NULL,
  `role` ENUM('customer', 'admin') DEFAULT 'customer',
  `status` ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
  `avatar` VARCHAR(255) DEFAULT NULL,
  `email_verified` TINYINT(1) DEFAULT 0,
  `mobile_verified` TINYINT(1) DEFAULT 0,
  `two_factor_secret` VARCHAR(255) DEFAULT NULL,
  `last_login` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_username` (`username`),
  KEY `idx_email` (`email`),
  KEY `idx_mobile` (`mobile`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- درج کاربر ادمین پیش‌فرض
-- نام کاربری: admin | رمز عبور: Admin@2026
INSERT INTO `users` (
  `username`, 
  `email`, 
  `password`, 
  `full_name`, 
  `mobile`, 
  `role`, 
  `status`, 
  `email_verified`, 
  `mobile_verified`,
  `last_login`,
  `created_at`
) VALUES (
  'admin',
  'admin@fashionshop.com',
  '$2y$12$LzR5K8JxPqY3nV7mF9wQ8.vX6hN2sT4jP1qW8yH5rE3dA9cM6bU0G',
  'مدیر سیستم',
  '09123456789',
  'admin',
  'active',
  1,
  1,
  NOW(),
  NOW()
);

-- درج کاربران تست
INSERT INTO `users` (`username`, `email`, `password`, `full_name`, `mobile`, `role`, `email_verified`, `mobile_verified`) VALUES
('ali_ahmadi', 'ali@example.com', '$2y$12$LzR5K8JxPqY3nV7mF9wQ8.vX6hN2sT4jP1qW8yH5rE3dA9cM6bU0G', 'علی احمدی', '09121234567', 'customer', 1, 1),
('sara_mohammadi', 'sara@example.com', '$2y$12$LzR5K8JxPqY3nV7mF9wQ8.vX6hN2sT4jP1qW8yH5rE3dA9cM6bU0G', 'سارا محمدی', '09127654321', 'customer', 1, 1);

-- ==========================================
-- 🚫 جدول تلاش‌های ناموفق ورود (Login Attempts)
-- ==========================================
DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE `login_attempts` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) DEFAULT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `attempted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_username` (`username`),
  KEY `idx_ip` (`ip_address`),
  KEY `idx_attempted_at` (`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 📝 جدول لاگ ورود (Login Logs)
-- ==========================================
DROP TABLE IF EXISTS `login_logs`;
CREATE TABLE `login_logs` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `login_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `logout_time` TIMESTAMP NULL DEFAULT NULL,
  `session_duration` INT(11) DEFAULT NULL COMMENT 'مدت زمان سشن به ثانیه',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_login_time` (`login_time`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 🔄 جدول بازیابی رمز عبور (Password Resets)
-- ==========================================
DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `token` VARCHAR(100) NOT NULL UNIQUE,
  `expires_at` DATETIME NOT NULL,
  `used` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_token` (`token`),
  KEY `idx_user_id` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 📊 جدول فعالیت‌های کاربران (Activity Logs)
-- ==========================================
DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) UNSIGNED DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `entity_type` VARCHAR(50) DEFAULT NULL,
  `entity_id` INT(11) UNSIGNED DEFAULT NULL,
  `details` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 🛍️ بخش محصولات و دسته‌بندی
-- ==========================================

-- ==========================================
-- 📂 جدول دسته‌بندی‌ها (Categories)
-- ==========================================
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `parent_id` INT(11) UNSIGNED DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `icon` VARCHAR(50) DEFAULT NULL,
  `sort_order` INT(11) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_slug` (`slug`),
  KEY `idx_parent_id` (`parent_id`),
  FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 🏷️ جدول برندها (Brands)
-- ==========================================
DROP TABLE IF EXISTS `brands`;
CREATE TABLE `brands` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `logo` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 📦 جدول محصولات (Products)
-- ==========================================
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(200) NOT NULL UNIQUE,
  `sku` VARCHAR(50) NOT NULL UNIQUE,
  `category_id` INT(11) UNSIGNED NOT NULL,
  `brand_id` INT(11) UNSIGNED DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `short_description` VARCHAR(500) DEFAULT NULL,
  `base_price` DECIMAL(12,2) NOT NULL,
  `discount_price` DECIMAL(12,2) DEFAULT NULL,
  `discount_percentage` TINYINT(3) UNSIGNED DEFAULT 0,
  `stock_quantity` INT(11) UNSIGNED DEFAULT 0,
  `low_stock_threshold` INT(11) UNSIGNED DEFAULT 5,
  `main_image` VARCHAR(255) NOT NULL,
  `hover_image` VARCHAR(255) DEFAULT NULL,
  `is_featured` TINYINT(1) DEFAULT 0,
  `is_new` TINYINT(1) DEFAULT 0,
  `status` ENUM('draft', 'active', 'out_of_stock', 'discontinued') DEFAULT 'active',
  `view_count` INT(11) UNSIGNED DEFAULT 0,
  `sales_count` INT(11) UNSIGNED DEFAULT 0,
  `rating_avg` DECIMAL(3,2) DEFAULT 0.00,
  `rating_count` INT(11) UNSIGNED DEFAULT 0,
  `meta_title` VARCHAR(200) DEFAULT NULL,
  `meta_description` VARCHAR(500) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_slug` (`slug`),
  KEY `idx_sku` (`sku`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_brand_id` (`brand_id`),
  KEY `idx_price` (`base_price`),
  KEY `idx_status` (`status`),
  KEY `idx_featured` (`is_featured`),
  FULLTEXT KEY `idx_search` (`name`, `description`),
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`brand_id`) REFERENCES `brands`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 🖼️ جدول تصاویر محصولات (Product Images)
-- ==========================================
DROP TABLE IF EXISTS `product_images`;
CREATE TABLE `product_images` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) UNSIGNED NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `alt_text` VARCHAR(200) DEFAULT NULL,
  `sort_order` INT(11) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_product_id` (`product_id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 🎨 بخش ویژگی‌های پویا (EAV Model)
-- ==========================================

-- ==========================================
-- 📋 جدول ویژگی‌های پایه (Attributes)
-- ==========================================
DROP TABLE IF EXISTS `attributes`;
CREATE TABLE `attributes` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `type` ENUM('text', 'number', 'select', 'color', 'size') DEFAULT 'text',
  `is_filterable` TINYINT(1) DEFAULT 0,
  `is_required` TINYINT(1) DEFAULT 0,
  `sort_order` INT(11) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 🎯 جدول مقادیر ویژگی‌ها (Attribute Values)
-- ==========================================
DROP TABLE IF EXISTS `attribute_values`;
CREATE TABLE `attribute_values` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `attribute_id` INT(11) UNSIGNED NOT NULL,
  `value` VARCHAR(200) NOT NULL,
  `color_code` VARCHAR(7) DEFAULT NULL,
  `sort_order` INT(11) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_attribute_id` (`attribute_id`),
  FOREIGN KEY (`attribute_id`) REFERENCES `attributes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 🔗 جدول ویژگی‌های محصولات (Product Attributes)
-- ==========================================
DROP TABLE IF EXISTS `product_attributes`;
CREATE TABLE `product_attributes` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) UNSIGNED NOT NULL,
  `attribute_id` INT(11) UNSIGNED NOT NULL,
  `attribute_value_id` INT(11) UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_product_attribute` (`product_id`, `attribute_id`, `attribute_value_id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_attribute_id` (`attribute_id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`attribute_id`) REFERENCES `attributes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`attribute_value_id`) REFERENCES `attribute_values`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 🔀 جدول متغیرها (Product Variants)
-- ==========================================
DROP TABLE IF EXISTS `product_variants`;
CREATE TABLE `product_variants` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) UNSIGNED NOT NULL,
  `sku` VARCHAR(50) NOT NULL UNIQUE,
  `price` DECIMAL(12,2) NOT NULL,
  `stock_quantity` INT(11) UNSIGNED DEFAULT 0,
  `image` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_sku` (`sku`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 🏷️ جدول ویژگی‌های متغیرها (Variant Attributes)
-- ==========================================
DROP TABLE IF EXISTS `variant_attributes`;
CREATE TABLE `variant_attributes` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `variant_id` INT(11) UNSIGNED NOT NULL,
  `attribute_id` INT(11) UNSIGNED NOT NULL,
  `attribute_value_id` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_variant_attribute` (`variant_id`, `attribute_id`),
  KEY `idx_variant_id` (`variant_id`),
  FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`attribute_id`) REFERENCES `attributes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`attribute_value_id`) REFERENCES `attribute_values`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 🛒 بخش سبد خرید و علاقه‌مندی‌ها
-- ==========================================

-- ==========================================
-- 🛒 جدول سبد خرید (Cart)
-- ==========================================
DROP TABLE IF EXISTS `cart`;
CREATE TABLE `cart` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) UNSIGNED DEFAULT NULL,
  `session_id` VARCHAR(100) DEFAULT NULL COMMENT 'برای کاربران مهمان',
  `product_id` INT(11) UNSIGNED NOT NULL,
  `variant_id` INT(11) UNSIGNED DEFAULT NULL,
  `quantity` INT(11) UNSIGNED DEFAULT 1,
  `price` DECIMAL(12,2) NOT NULL,
  `reserved_until` TIMESTAMP NULL DEFAULT NULL COMMENT 'رزرو موقت موجودی',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_product_id` (`product_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- ❤️ جدول علاقه‌مندی‌ها (Wishlist)
-- ==========================================
DROP TABLE IF EXISTS `wishlist`;
CREATE TABLE `wishlist` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `product_id` INT(11) UNSIGNED NOT NULL,
  `notify_on_sale` TINYINT(1) DEFAULT 0 COMMENT 'اعلان تخفیف',
  `notify_on_restock` TINYINT(1) DEFAULT 0 COMMENT 'اعلان موجودی',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_wishlist` (`user_id`, `product_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_product_id` (`product_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 📍 جدول آدرس‌های کاربران (User Addresses)
-- ==========================================
DROP TABLE IF EXISTS `user_addresses`;
CREATE TABLE `user_addresses` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `label` VARCHAR(50) DEFAULT 'خانه',
  `full_name` VARCHAR(100) NOT NULL,
  `province` VARCHAR(100) NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `address` TEXT NOT NULL,
  `postal_code` VARCHAR(10) NOT NULL,
  `phone` VARCHAR(15) NOT NULL,
  `is_default` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 📦 بخش سفارشات
-- ==========================================

-- ==========================================
-- 📦 جدول سفارشات (Orders)
-- ==========================================
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `order_number` VARCHAR(50) NOT NULL UNIQUE,
  `total_amount` DECIMAL(12,2) NOT NULL,
  `discount_amount` DECIMAL(12,2) DEFAULT 0,
  `shipping_cost` DECIMAL(12,2) DEFAULT 0,
  `tax` DECIMAL(12,2) DEFAULT 0,
  `final_amount` DECIMAL(12,2) NOT NULL,
  `payment_method` VARCHAR(50) DEFAULT NULL,
  `payment_status` ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
  `order_status` ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
  `shipping_address_id` INT(11) UNSIGNED DEFAULT NULL,
  `shipping_address` TEXT DEFAULT NULL COMMENT 'نسخه ذخیره شده آدرس',
  `shipping_province` VARCHAR(100) DEFAULT NULL,
  `shipping_city` VARCHAR(100) DEFAULT NULL,
  `shipping_postal_code` VARCHAR(10) DEFAULT NULL,
  `shipping_phone` VARCHAR(15) DEFAULT NULL,
  `customer_note` TEXT DEFAULT NULL,
  `tracking_code` VARCHAR(50) DEFAULT NULL,
  `paid_at` TIMESTAMP NULL DEFAULT NULL,
  `shipped_at` TIMESTAMP NULL DEFAULT NULL,
  `delivered_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_order_number` (`order_number`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_order_status` (`order_status`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`shipping_address_id`) REFERENCES `user_addresses`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 📋 جدول آیتم‌های سفارش (Order Items)
-- ==========================================
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) UNSIGNED NOT NULL,
  `product_id` INT(11) UNSIGNED NOT NULL,
  `variant_id` INT(11) UNSIGNED DEFAULT NULL,
  `product_name` VARCHAR(200) NOT NULL,
  `quantity` INT(11) UNSIGNED NOT NULL,
  `price` DECIMAL(12,2) NOT NULL,
  `discount_price` DECIMAL(12,2) DEFAULT NULL,
  `subtotal` DECIMAL(12,2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_product_id` (`product_id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 🎟️ جدول کدهای تخفیف (Coupons)
-- ==========================================
DROP TABLE IF EXISTS `coupons`;
CREATE TABLE `coupons` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `type` ENUM('percentage', 'fixed') DEFAULT 'percentage',
  `value` DECIMAL(12,2) NOT NULL,
  `min_purchase` DECIMAL(12,2) DEFAULT 0,
  `max_discount` DECIMAL(12,2) DEFAULT NULL,
  `usage_limit` INT(11) UNSIGNED DEFAULT NULL,
  `usage_count` INT(11) UNSIGNED DEFAULT 0,
  `valid_from` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `valid_until` TIMESTAMP NULL DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- ⭐ جدول نظرات (Reviews)
-- ==========================================
DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) UNSIGNED NOT NULL,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `rating` TINYINT(1) UNSIGNED NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
  `title` VARCHAR(200) DEFAULT NULL,
  `comment` TEXT NOT NULL,
  `is_approved` TINYINT(1) DEFAULT 0,
  `admin_reply` TEXT DEFAULT NULL,
  `replied_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_approved` (`is_approved`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 📧 جدول پیام‌های تماس (Contact Messages)
-- ==========================================
DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(15) NOT NULL,
  `subject` ENUM('order_inquiry', 'cooperation', 'complaint', 'other') NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('new', 'read', 'replied') DEFAULT 'new',
  `admin_reply` TEXT DEFAULT NULL,
  `replied_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 📧 جدول خبرنامه (Newsletter)
-- ==========================================
DROP TABLE IF EXISTS `newsletter`;
CREATE TABLE `newsletter` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `subscribed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- ⚙️ جدول تنظیمات سایت (Settings)
-- ==========================================
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT DEFAULT NULL,
  `setting_type` VARCHAR(50) DEFAULT 'string',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 📊 درج دیتاهای پایه
-- ==========================================

-- دسته‌بندی‌های اصلی
INSERT INTO `categories` (`name`, `slug`, `parent_id`, `description`, `sort_order`, `is_active`) VALUES
('لباس', 'clothing', NULL, 'انواع پوشاک مردانه و زنانه', 1, 1),
('کیف', 'bags', NULL, 'کیف‌های دستی، کوله پشتی و چرمی', 2, 1),
('کفش', 'shoes', NULL, 'کفش‌های اسپرت، رسمی و کتانی', 3, 1),
('اکسسوری', 'accessories', NULL, 'عینک، کمربند، ساعت و لوازم جانبی', 4, 1);

-- زیر دسته‌بندی‌های لباس
INSERT INTO `categories` (`name`, `slug`, `parent_id`, `description`, `sort_order`, `is_active`) VALUES
('تیشرت', 't-shirts', 1, 'تیشرت‌های مردانه و زنانه', 1, 1),
('شلوار جین', 'jeans', 1, 'شلوار جین با کیفیت بالا', 2, 1),
('هودی', 'hoodies', 1, 'هودی و سویشرت گرم', 3, 1),
('کت و پیراهن', 'shirts-jackets', 1, 'پیراهن رسمی و کت مردانه', 4, 1);

-- زیر دسته‌بندی‌های کیف
INSERT INTO `categories` (`name`, `slug`, `parent_id`, `description`, `sort_order`, `is_active`) VALUES
('کیف دستی', 'handbags', 2, 'کیف‌های دستی زنانه لوکس', 1, 1),
('کوله پشتی', 'backpacks', 2, 'کوله پشتی مسافرتی و ورزشی', 2, 1),
('کیف لپ تاپ', 'laptop-bags', 2, 'کیف‌های مخصوص لپ تاپ', 3, 1);

-- زیر دسته‌بندی‌های کفش
INSERT INTO `categories` (`name`, `slug`, `parent_id`, `description`, `sort_order`, `is_active`) VALUES
('کفش اسپرت', 'sneakers', 3, 'کفش‌های ورزشی و اسپرت', 1, 1),
('کفش رسمی', 'formal-shoes', 3, 'کفش‌های رسمی مردانه', 2, 1),
('کتانی', 'canvas-shoes', 3, 'کفش‌های کتانی راحتی', 3, 1);

-- برندهای معتبر
INSERT INTO `brands` (`name`, `slug`, `description`, `is_active`) VALUES
('نایک', 'nike', 'برند معتبر ورزشی آمریکایی', 1),
('آدیداس', 'adidas', 'برند آلمانی ورزشی', 1),
('زارا', 'zara', 'برند اسپانیایی پوشاک', 1),
('اچ اند ام', 'h-and-m', 'برند سوئدی فست فشن', 1),
('لویی ویتون', 'louis-vuitton', 'برند لوکس فرانسوی', 1),
('گوچی', 'gucci', 'برند لوکس ایتالیایی', 1),
('پوما', 'puma', 'برند آلمانی ورزشی', 1),
('کانورس', 'converse', 'برند آمریکایی کفش کتانی', 1),
('ونس', 'vans', 'برند کفش اسکیت بورد', 1),
('نورث فیس', 'north-face', 'برند کوهنوردی و فضای باز', 1);

-- ویژگی‌های پایه (Attributes)
INSERT INTO `attributes` (`name`, `slug`, `type`, `is_filterable`, `is_required`, `sort_order`) VALUES
('سایز', 'size', 'select', 1, 1, 1),
('رنگ', 'color', 'color', 1, 1, 2),
('جنس', 'material', 'select', 1, 0, 3),
('جنسیت', 'gender', 'select', 1, 0, 4);

-- مقادیر سایز لباس
INSERT INTO `attribute_values` (`attribute_id`, `value`, `sort_order`) VALUES
(1, 'XS', 1), (1, 'S', 2), (1, 'M', 3), (1, 'L', 4), (1, 'XL', 5), (1, 'XXL', 6);

-- مقادیر سایز کفش
INSERT INTO `attribute_values` (`attribute_id`, `value`, `sort_order`) VALUES
(1, '36', 7), (1, '37', 8), (1, '38', 9), (1, '39', 10), (1, '40', 11),
(1, '41', 12), (1, '42', 13), (1, '43', 14), (1, '44', 15), (1, '45', 16);

-- مقادیر رنگ
INSERT INTO `attribute_values` (`attribute_id`, `value`, `color_code`, `sort_order`) VALUES
(2, 'مشکی', '#000000', 1), (2, 'سفید', '#FFFFFF', 2), (2, 'قرمز', '#FF0000', 3),
(2, 'آبی', '#0000FF', 4), (2, 'سبز', '#00FF00', 5), (2, 'زرد', '#FFFF00', 6),
(2, 'خاکستری', '#808080', 7), (2, 'قهوه‌ای', '#8B4513', 8), 
(2, 'صورتی', '#FFC0CB', 9), (2, 'نارنجی', '#FFA500', 10);

-- مقادیر جنس
INSERT INTO `attribute_values` (`attribute_id`, `value`, `sort_order`) VALUES
(3, 'پنبه', 1), (3, 'پلی استر', 2), (3, 'چرم', 3), (3, 'جین', 4),
(3, 'نایلون', 5), (3, 'کتان', 6), (3, 'ابریشم', 7);

-- مقادیر جنسیت
INSERT INTO `attribute_values` (`attribute_id`, `value`, `sort_order`) VALUES
(4, 'مردانه', 1), (4, 'زنانه', 2), (4, 'یونیسکس', 3);

-- ==========================================
-- 🎁 محصولات نمونه - بخش لباس (10 محصول)
-- ==========================================

-- 1. تیشرت مشکی نایک
INSERT INTO `products` (`name`, `slug`, `sku`, `category_id`, `brand_id`, `description`, `short_description`, `base_price`, `discount_price`, `discount_percentage`, `stock_quantity`, `main_image`, `hover_image`, `is_featured`, `is_new`, `status`) VALUES
('تیشرت مشکی نایک کلاسیک', 'nike-black-classic-tshirt', 'NK-TS-001', 5, 1, 'تیشرت مشکی نایک با پارچه ۱۰۰٪ پنبه، مناسب برای استفاده روزمره و ورزشی. طراحی کلاسیک و راحتی فوق‌العاده. دارای تکنولوژی Dri-FIT برای دفع رطوبت.', 'تیشرت پنبه‌ای راحت با کیفیت عالی', 450000, 360000, 20, 150, '/images/products/tshirt-nike-black.jpg', '/images/products/tshirt-nike-black-back.jpg', 1, 1, 'active');

-- 2. هودی خاکستری آدیداس
INSERT INTO `products` (`name`, `slug`, `sku`, `category_id`, `brand_id`, `description`, `short_description`, `base_price`, `discount_price`, `discount_percentage`, `stock_quantity`, `main_image`, `hover_image`, `is_featured`, `status`) VALUES
('هودی خاکستری آدیداس اسپرت', 'adidas-grey-sport-hoodie', 'AD-HD-002', 7, 2, 'هودی گرم و راحت آدیداس با جنس پلی‌استر و پنبه، مجهز به جیب‌های بزرگ و کش‌های کمر. ایده‌آل برای فصل پاییز و زمستان. داخل کرکی نرم و گرم.', 'هودی گرم با طراحی اسپرت', 850000, NULL, 0, 80, '/images/products/hoodie-adidas-grey.jpg', '/images/products/hoodie-adidas-grey-side.jpg', 1, 'active');

-- 3. شلوار جین آبی زارا
INSERT INTO `products` (`name`, `slug`, `sku`, `category_id`, `brand_id`, `description`, `short_description`, `base_price`, `discount_price`, `discount_percentage`, `stock_quantity`, `main_image`, `hover_image`, `is_new`, `status`) VALUES
('شلوار جین آبی زارا فیت اسلیم', 'zara-blue-slim-fit-jeans', 'ZR-JN-003', 6, 3, 'شلوار جین آبی تیره با برش اسلیم فیت، پارچه دنیم مرغوب با قابلیت کشسانی بالا. طراحی مدرن و شیک برای همه مناسبت‌ها. با شستشوی استون واش.', 'جین اسلیم فیت با کیفیت برتر', 650000, 520000, 20, 120, '/images/products/jeans-zara-blue.jpg', '/images/products/jeans-zara-blue-detail.jpg', 1, 'active');

-- 4. تیشرت سفید اچ اند ام
INSERT INTO `products` (`name`, `slug`, `sku`, `category_id`, `brand_id`, `description`, `short_description`, `base_price`, `stock_quantity`, `main_image`, `hover_image`, `status`) VALUES
('تیشرت سفید اچ اند ام بیسیک', 'hm-white-basic-tshirt', 'HM-TS-004', 5, 4, 'تیشرت سفید ساده و بدون چاپ، پارچه نرم و نفس‌پذیر. گزینه ایده‌آل برای ست کردن با انواع لباس‌ها. مناسب برای فصول گرم سال.', 'تیشرت سفید بیسیک روزمره', 320000, 200, '/images/products/tshirt-hm-white.jpg', '/images/products/tshirt-hm-white-back.jpg', 'active');

-- 5. هودی قرمز پوما
INSERT INTO `products` (`name`, `slug`, `sku`, `category_id`, `brand_id`, `description`, `short_description`, `base_price`, `discount_price`, `discount_percentage`, `stock_quantity`, `main_image`, `hover_image`, `is_featured`, `status`) VALUES
('هودی قرمز پوما اسپرت', 'puma-red-sport-hoodie', 'PM-HD-005', 7, 7, 'هودی قرمز پررنگ با لوگوی پوما، جنس پلی‌استر با داخل کرکی. مناسب برای ورزش و پیاده‌روی. زیپ دار با کیفیت YKK.', 'هودی گرم و راحت پوما', 780000, 624000, 20, 65, '/images/products/hoodie-puma-red.jpg', '/images/products/hoodie-puma-red-front.jpg', 1, 'active');

-- 6-10: محصولات لباس اضافی
INSERT INTO `products` (`name`, `slug`, `sku`, `category_id`, `brand_id`, `description`, `short_description`, `base_price`, `stock_quantity`, `main_image`, `is_new`, `status`) VALUES
('شلوار جین مشکی زارا رگولار فیت', 'zara-black-regular-jeans', 'ZR-JN-006', 6, 3, 'شلوار جین مشکی با برش رگولار، راحتی فوق‌العاده و پارچه با دوام', 'جین مشکی کلاسیک و شیک', 680000, 95, '/images/products/jeans-zara-black.jpg', 1, 'active'),
('تیشرت آبی نایک دری فیت', 'nike-blue-dri-fit-tshirt', 'NK-TS-007', 5, 1, 'تیشرت آبی با تکنولوژی دری فیت برای دفع رطوبت', 'تیشرت ورزشی با فناوری دری فیت', 520000, 110, '/images/products/tshirt-nike-blue.jpg', 0, 'active'),
('هودی مشکی آدیداس کلاسیک', 'adidas-black-classic-hoodie', 'AD-HD-008', 7, 2, 'هودی مشکی با لوگوی سه خط مشهور آدیداس', 'هودی کلاسیک آدیداس', 920000, 55, '/images/products/hoodie-adidas-black.jpg', 1, 'active'),
('شلوار جین خاکستری اچ اند ام', 'hm-grey-skinny-jeans', 'HM-JN-009', 6, 4, 'شلوار جین خاکستری با برش اسکینی، کشسانی عالی', 'جین اسکینی با پارچه کشی', 580000, 75, '/images/products/jeans-hm-grey.jpg', 0, 'active'),
('تیشرت سبز پوما ورزشی', 'puma-green-sport-tshirt', 'PM-TS-010', 5, 7, 'تیشرت سبز یشمی با لوگوی پوما، پارچه تنفسی و ضد تعریق', 'تیشرت ورزشی با کیفیت برتر', 480000, 130, '/images/products/tshirt-puma-green.jpg', 1, 'active');

-- ==========================================
-- 🎁 محصولات نمونه - بخش کیف (10 محصول)
-- ==========================================

INSERT INTO `products` (`name`, `slug`, `sku`, `category_id`, `brand_id`, `description`, `short_description`, `base_price`, `discount_price`, `discount_percentage`, `stock_quantity`, `main_image`, `is_featured`, `status`) VALUES
('کیف دستی مشکی لویی ویتون لوکس', 'lv-black-luxury-handbag', 'LV-HB-011', 9, 5, 'کیف دستی چرم طبیعی مشکی با لوگوی طلایی لویی ویتون', 'کیف چرمی لوکس دست‌دوز', 15500000, 13950000, 10, 12, '/images/products/bag-lv-black.jpg', 1, 'active'),
('کوله پشتی نورث فیس کوهنوردی', 'northface-grey-hiking-backpack', 'NF-BP-012', 10, 10, 'کوله پشتی ۴۰ لیتری با جنس نایلون ضد آب', 'کوله پشتی حرفه‌ای ضد آب', 3200000, NULL, 0, 45, '/images/products/backpack-northface-grey.jpg', 0, 'active'),
('کیف لپ تاپ ۱۵ اینچ اچ اند ام', 'hm-black-laptop-bag-15inch', 'HM-LB-013', 11, 4, 'کیف لپ تاپ مخصوص سایز ۱۵ اینچ، جنس نایلون با داخل خزدار', 'کیف محافظ لپ تاپ با طراحی مدرن', 850000, 680000, 20, 85, '/images/products/laptop-bag-hm-black.jpg', 0, 'active'),
('کیف دستی قرمز گوچی مارمونت', 'gucci-red-marmont-handbag', 'GC-HB-014', 9, 6, 'کیف دستی چرم قرمز با طراحی مارمونت، لوگوی دبل جی طلایی', 'کیف لوکس چرم طبیعی', 18900000, NULL, 0, 8, '/images/products/bag-gucci-red.jpg', 1, 'active'),
('کوله پشتی نایک اسپرت برازیلیا', 'nike-black-brasilia-backpack', 'NK-BP-015', 10, 1, 'کوله پشتی ورزشی با جنس پلی‌استر بادوام', 'کوله پشتی ورزشی با کیفیت نایک', 980000, 784000, 20, 95, '/images/products/backpack-nike-black.jpg', 0, 'active'),
('کیف لپ تاپ چرمی قهوه‌ای کلاسیک', 'classic-brown-leather-laptop-bag', 'CL-LB-016', 11, NULL, 'کیف لپ تاپ چرم طبیعی قهوه‌ای، طراحی کلاسیک و رسمی', 'کیف چرمی دست‌دوز رسمی', 2400000, NULL, 0, 35, '/images/products/laptop-bag-brown-leather.jpg', 1, 'active'),
('کیف دستی آبی زارا شیک', 'zara-blue-chic-handbag', 'ZR-HB-017', 9, 3, 'کیف دستی آبی کوچک با طراحی مینیمال', 'کیف کوچک مجلسی شیک', 1200000, 960000, 20, 60, '/images/products/bag-zara-blue.jpg', 0, 'active'),
('کوله پشتی آدیداس سبز کلاسیک', 'adidas-green-classic-backpack', 'AD-BP-018', 10, 2, 'کوله پشتی سبز با سه خط سفید مشهور آدیداس', 'کوله پشتی کلاسیک روزانه', 1150000, NULL, 0, 70, '/images/products/backpack-adidas-green.jpg', 0, 'active'),
('کیف لپ تاپ پوما اسپرت', 'puma-grey-sport-laptop-bag', 'PM-LB-019', 11, 7, 'کیف لپ تاپ خاکستری با طراحی اسپرت', 'کیف لپ تاپ اسپرت با جیب‌های متعدد', 920000, 736000, 20, 50, '/images/products/laptop-bag-puma-grey.jpg', 0, 'active'),
('کیف دستی صورتی اچ اند ام', 'hm-pink-trendy-handbag', 'HM-HB-020', 9, 4, 'کیف دستی صورتی پاستلی با طراحی ترندی', 'کیف دستی رنگی ترندی', 780000, NULL, 0, 90, '/images/products/bag-hm-pink.jpg', 1, 'active');

-- ==========================================
-- 🎁 محصولات نمونه - بخش کفش (10 محصول)
-- ==========================================

INSERT INTO `products` (`name`, `slug`, `sku`, `category_id`, `brand_id`, `description`, `short_description`, `base_price`, `discount_price`, `discount_percentage`, `stock_quantity`, `main_image`, `is_featured`, `is_new`, `status`) VALUES
('کفش اسپرت نایک ایر مکس مشکی', 'nike-black-air-max-sneakers', 'NK-SN-021', 12, 1, 'کفش ورزشی با تکنولوژی ایر مکس برای راحتی فوق‌العاده', 'کفش اسپرت با فناوری ایر مکس', 3500000, 2800000, 20, 80, '/images/products/shoes-nike-black-airmax.jpg', 1, 1, 'active'),
('کفش رسمی مردانه چرم قهوه‌ای', 'classic-brown-leather-formal-shoes', 'CL-FS-022', 13, NULL, 'کفش رسمی چرم طبیعی قهوه‌ای با دوخت دست', 'کفش چرمی رسمی دست‌دوز', 2100000, NULL, 0, 45, '/images/products/shoes-formal-brown-leather.jpg', 0, 0, 'active'),
('کتانی کانورس سفید کلاسیک آل استار', 'converse-white-all-star-classic', 'CV-CS-023', 14, 8, 'کتانی سفید آیکونیک کانورس با رویه کتان و زیره لاستیکی', 'کتانی کلاسیک آل استار', 1450000, 1160000, 20, 120, '/images/products/shoes-converse-white.jpg', 1, 0, 'active'),
('کفش اسپرت آدیداس اولترا بوست آبی', 'adidas-blue-ultraboost-sneakers', 'AD-SN-024', 12, 2, 'کفش ورزشی با تکنولوژی بوست برای بازگشت انرژی', 'کفش دو با فناوری بوست', 4200000, NULL, 0, 65, '/images/products/shoes-adidas-blue-ultraboost.jpg', 0, 1, 'active'),
('کفش رسمی مردانه چرم مشکی کلاسیک', 'classic-black-leather-formal-shoes', 'CL-FS-025', 13, NULL, 'کفش رسمی مشکی چرم طبیعی با طراحی آکسفورد', 'کفش چرمی رسمی آکسفورد', 2350000, 1880000, 20, 55, '/images/products/shoes-formal-black-oxford.jpg', 1, 0, 'active'),
('کتانی ونس مشکی اولد اسکول', 'vans-black-old-skool-canvas', 'VN-CS-026', 14, 9, 'کتانی مشکی با خط سفید کلاسیک ونس', 'کتانی اسکیت کلاسیک ونس', 1580000, NULL, 0, 85, '/images/products/shoes-vans-black-oldskool.jpg', 0, 0, 'active'),
('کفش اسپرت پوما سوید سبز', 'puma-green-suede-classic-sneakers', 'PM-SN-027', 12, 7, 'کفش اسپرت با رویه سوید سبز یشمی', 'کفش سوید کلاسیک پوما', 1850000, 1480000, 20, 70, '/images/products/shoes-puma-green-suede.jpg', 0, 1, 'active'),
('کفش رسمی مردانه قهوه‌ای تیره دربی', 'dark-brown-derby-formal-shoes', 'CL-FS-028', 13, NULL, 'کفش رسمی قهوه‌ای تیره با طراحی دربی', 'کفش چرمی رسمی دربی', 2200000, NULL, 0, 40, '/images/products/shoes-formal-darkbrown-derby.jpg', 0, 0, 'active'),
('کتانی کانورس قرمز آل استار', 'converse-red-all-star-high-top', 'CV-CS-029', 14, 8, 'کتانی قرمز هایتاپ کانورس با بند بلند', 'کتانی هایتاپ قرمز کلاسیک', 1520000, 1216000, 20, 95, '/images/products/shoes-converse-red-hightop.jpg', 1, 0, 'active'),
('کفش اسپرت نایک ری اکت خاکستری', 'nike-grey-react-running-shoes', 'NK-SN-030', 12, 1, 'کفش دو خاکستری با فناوری ری اکت فوم', 'کفش دو حرفه‌ای با فناوری ری اکت', 3800000, NULL, 0, 60, '/images/products/shoes-nike-grey-react.jpg', 0, 1, 'active');

-- ==========================================
-- 📊 درج ویژگی‌های محصولات (نمونه)
-- ==========================================

-- ویژگی‌های تیشرت مشکی نایک
INSERT INTO `product_attributes` (`product_id`, `attribute_id`, `attribute_value_id`) VALUES
(1, 2, 1), -- رنگ: مشکی
(1, 3, 1), -- جنس: پنبه
(1, 4, 13); -- جنسیت: یونیسکس

-- ویژگی‌های هودی خاکستری آدیداس
INSERT INTO `product_attributes` (`product_id`, `attribute_id`, `attribute_value_id`) VALUES
(2, 2, 7), -- رنگ: خاکستری
(2, 3, 2), -- جنس: پلی استر
(2, 4, 13); -- جنسیت: یونیسکس

-- ویژگی‌های کفش اسپرت نایک
INSERT INTO `product_attributes` (`product_id`, `attribute_id`, `attribute_value_id`) VALUES
(21, 2, 1), -- رنگ: مشکی
(21, 4, 13); -- جنسیت: یونیسکس

-- ==========================================
-- 📦 درج متغیرهای محصولات
-- ==========================================

-- متغیرهای تیشرت نایک (سایزهای مختلف)
INSERT INTO `product_variants` (`product_id`, `sku`, `price`, `stock_quantity`) VALUES
(1, 'NK-TS-001-S', 360000, 30),
(1, 'NK-TS-001-M', 360000, 35),
(1, 'NK-TS-001-L', 360000, 30),
(1, 'NK-TS-001-XL', 360000, 20),
(1, 'NK-TS-001-XXL', 360000, 10);

-- ویژگی‌های متغیرها
INSERT INTO `variant_attributes` (`variant_id`, `attribute_id`, `attribute_value_id`) VALUES
(1, 1, 2), -- S
(2, 1, 3), -- M
(3, 1, 4), -- L
(4, 1, 5), -- XL
(5, 1, 6); -- XXL

-- متغیرهای کفش نایک (سایزهای مختلف)
INSERT INTO `product_variants` (`product_id`, `sku`, `price`, `stock_quantity`) VALUES
(21, 'NK-SN-021-40', 2800000, 10),
(21, 'NK-SN-021-41', 2800000, 15),
(21, 'NK-SN-021-42', 2800000, 20),
(21, 'NK-SN-021-43', 2800000, 18);

INSERT INTO `variant_attributes` (`variant_id`, `attribute_id`, `attribute_value_id`) VALUES
(6, 1, 11), -- 40
(7, 1, 12), -- 41
(8, 1, 13), -- 42
(9, 1, 14); -- 43

-- ==========================================
-- ⭐ درج نظرات نمونه
-- ==========================================

INSERT INTO `reviews` (`product_id`, `user_id`, `rating`, `title`, `comment`, `is_approved`, `created_at`) VALUES
(1, 2, 5, 'کیفیت عالی', 'تیشرت خیلی راحت و با کیفیته. پارچه‌اش نرم و تنفسیه. حتماً دوباره خرید می‌کنم.', 1, '2026-01-15 10:30:00'),
(1, 3, 4, 'خوب ولی سایز کوچک', 'کیفیت خوبه اما یه سایز بزرگتر سفارش بدید.', 1, '2026-01-20 14:20:00'),
(21, 2, 5, 'بهترین کفش دو', 'برای دو عالیه. سبک و راحت. زیره‌اش خیلی خوبه.', 1, '2026-01-25 16:45:00');

-- ==========================================
-- 🎟️ درج کوپن‌های تخفیف
-- ==========================================

INSERT INTO `coupons` (`code`, `type`, `value`, `min_purchase`, `usage_limit`, `valid_until`, `is_active`) VALUES
('WELCOME20', 'percentage', 20, 500000, 100, '2026-12-31 23:59:59', 1),
('NEWYEAR2026', 'fixed', 100000, 1000000, 50, '2026-03-20 23:59:59', 1),
('FREESHIP', 'percentage', 100, 0, NULL, '2026-12-31 23:59:59', 1),
('LUXURY10', 'percentage', 10, 5000000, 30, '2026-06-30 23:59:59', 1);

-- ==========================================
-- ⚙️ درج تنظیمات سایت
-- ==========================================

INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`) VALUES
('site_name', 'فروشگاه پوشاک لوکس', 'string'),
('site_description', 'خرید آنلاین لباس و پوشاک مردانه و زنانه', 'string'),
('site_email', 'info@fashionshop.com', 'string'),
('site_phone', '021-12345678', 'string'),
('free_shipping_threshold', '500000', 'number'),
('tax_rate', '0.09', 'number'),
('maintenance_mode', '0', 'boolean'),
('currency', 'تومان', 'string'),
('items_per_page', '12', 'number');

-- ==========================================
-- ✅ فعال‌سازی Foreign Keys
-- ==========================================

SET FOREIGN_KEY_CHECKS = 1;

-- ==========================================
-- 📊 نمایش اطلاعات کلیدی
-- ==========================================

SELECT '✅ دیتابیس با موفقیت ساخته شد!' AS status;
SELECT 'تعداد جداول:' AS info, COUNT(*) AS count FROM information_schema.tables WHERE table_schema = 'fashion_shop_db';

-- نمایش اطلاعات ادمین
SELECT '🔑 اطلاعات ورود ادمین:' AS info;
SELECT username, email, full_name, mobile, role FROM users WHERE role = 'admin';

-- ==========================================
-- 📝 یادداشت‌های مهم
-- ==========================================

/*
🔐 اطلاعات ورود پنل مدیریت:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
نام کاربری: admin
رمز عبور: Admin@2026
ایمیل: admin@fashionshop.com
موبایل: 09123456789
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

⚠️ توجه: پس از اولین ورود حتماً رمز عبور را تغییر دهید!

📊 ویژگی‌های دیتابیس:
✅ معماری EAV برای ویژگی‌های پویا
✅ امنیت Bcrypt + محافظت Brute-Force
✅ Soft Deletes برای حفظ تاریخچه
✅ Full-Text Search برای جستجوی سریع
✅ Atomic Transactions برای موجودی
✅ Activity Logging کامل
✅ 30 محصول نمونه (لباس، کیف، کفش)
✅ کوپن‌های تخفیف فعال
✅ نظرات و امتیازات
✅ سیستم متغیرها (سایز، رنگ)

📦 جداول ساخته شده: 19 جدول
👤 کاربران: 3 (1 ادمین + 2 مشتری)
🏷️ برندها: 10 برند معتبر
📂 دسته‌بندی: 13 دسته (4 اصلی + 9 زیر)
🎁 محصولات: 30 محصول کامل
*/
