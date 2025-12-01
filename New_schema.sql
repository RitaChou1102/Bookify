CREATE DATABASE IF NOT EXISTS `bookify` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `bookify`;

-- Users table (包含會員、管理員、廠商的基礎欄位)
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `login_id` VARCHAR(191) NOT NULL,
  `name` VARCHAR(191) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `address` VARCHAR(500) DEFAULT NULL,
  `role` ENUM('member','business') NOT NULL DEFAULT 'member',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uq_users_email` (`email`),
  UNIQUE KEY `uq_users_login_id` (`login_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Admins table
CREATE TABLE IF NOT EXISTS `admins` (
  `admin_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `login_id` VARCHAR(191) NOT NULL,
  `name` VARCHAR(191) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY (`login_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Businesses
CREATE TABLE IF NOT EXISTS `businesses` (
  `business_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `bank_account` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`business_id`),
  UNIQUE KEY `uq_business_user` (`user_id`),
  CONSTRAINT `fk_business_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Member
CREATE TABLE IF NOT EXISTS `member` (
  `member_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`member_id`),
  UNIQUE KEY `uq_member_user` (`user_id`),
  CONSTRAINT `fk_member_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `member_id` BIGINT UNSIGNED NOT NULL,
  `business_id` BIGINT UNSIGNED NOT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `order_time` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `shipping_fee` DECIMAL(10,2) Not null,
  `payment_method` TINYINT Not null, -- 0 Cash 1 Credit card 2 Bank transfer
  `order_status` TINYINT Not null DEFAULT 0, --0 Received, 1 Processing, 2 Shipped, 3 Completed
  `coupon_id` BIGINT UNSIGNED DEFAULT NULL,
  `cart_id` BIGINT UNSIGNED Not null,
  PRIMARY KEY (`order_id`),
  KEY `idx_orders_member` (`member_id`),
  KEY `idx_orders_business` (`business_id`),
  KEY `idx_orders_cart` (`cart_id`),
  CONSTRAINT `fk_orders_member` FOREIGN KEY (`member_id`) REFERENCES `member`(`member_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_orders_business` FOREIGN KEY (`business_id`) REFERENCES `businesses`(`business_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_orders_cart` FOREIGN KEY (`cart_id`) REFERENCES `carts`(`cart_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_orders_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `coupons`(`coupon_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order details
CREATE TABLE IF NOT EXISTS `order_details` (
  `detail_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `book_id` BIGINT UNSIGNED NOT NULL,
  `quantity` INT DEFAULT 1,
  `piece_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `subtotal` DECIMAL(10,2) AS (quantity * piece_price) VIRTUAL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`detail_id`),
  KEY `idx_orderdetails_order` (`order_id`),
  KEY `idx_orderdetails_book` (`book_id`),
  CONSTRAINT `fk_orderdetails_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`order_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_orderdetails_book` FOREIGN KEY (`book_id`) REFERENCES `books`(`book_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Complaints
CREATE TABLE IF NOT EXISTS `complaints` (
  `complaint_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `content` TEXT NOT NULL,
  `complaint_time` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `complaint_status` TINYINT DEFAULT 0, -- 0 pending,1 in progress,2 resolved
  `result` TEXT DEFAULT NULL,
  PRIMARY KEY (`complaint_id`),
  KEY `idx_complaint_order` (`order_id`),
  CONSTRAINT `fk_complaint_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`order_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Books
CREATE TABLE IF NOT EXISTS `books` (
  `book_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(500) NOT NULL,
  `author_id` BIGINT UNSIGNED NOT NULL,
  `isbn` VARCHAR(50) NOT NULL,
  `publish_date` DATE NOT NULL,
  `edition` INT NOT NULL,
  `publisher` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `condition` ENUM('new','used') NOT NULL DEFAULT 'new',
  `category_id` BIGINT UNSIGNED NOT NULL,
  `business_id` BIGINT UNSIGNED NOT NULL,
  `stock` INT NOT NULL DEFAULT 0,
  `listing` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`book_id`),
  KEY `idx_books_category` (`category_id`),
  KEY `idx_books_business` (`business_id`),
  KEY `idx_books_author` (`author_id`),
  UNIQUE KEY `uq_book_selling_unit` (`isbn`, `condition`, `business_id`),
  CONSTRAINT `fk_books_author` FOREIGN KEY (`author_id`) REFERENCES `authors`(`author_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_books_category` FOREIGN KEY (`category_id`) REFERENCES `categories`(`category_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_books_business` FOREIGN KEY (`business_id`) REFERENCES `businesses`(`business_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories
CREATE TABLE IF NOT EXISTS `categories` (
  `category_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(191) NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Authors
CREATE TABLE IF NOT EXISTS `authors` (
  `author_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Images
CREATE TABLE IF NOT EXISTS `images` (
  `image_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `book_id` BIGINT UNSIGNED NOT NULL,
  `image_index` INT DEFAULT 0,
  `image_url` VARCHAR(1000) NOT NULL,
  PRIMARY KEY (`image_id`),
  KEY `idx_images_book` (`book_id`),
  UNIQUE KEY `uq_book_sequence` (`book_id`, `image_index`),
  CONSTRAINT `fk_image_book` FOREIGN KEY (`book_id`) REFERENCES `books`(`book_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `coupons` (
  `coupon_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `business_id` BIGINT UNSIGNED NOT NULL,
  `code` VARCHAR(191) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `start_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `end_date` DATETIME DEFAULT NULL,
  `discount_type` ENUM('percent', 'fixed') NOT NULL DEFAULT 'percent',
  `discount_value` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `limit_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `usage_limit` INT DEFAULT 1,
  `used_count` INT DEFAULT 0,
  `coupon_type` ENUM('shipping', 'seasonal', 'special_event') NOT NULL DEFAULT 'shipping',
  PRIMARY KEY (`coupon_id`),
  UNIQUE KEY `uq_coupon_code` (`code`),
  KEY `idx_coupon_business` (`business_id`),
  CONSTRAINT `fk_coupon_business` FOREIGN KEY (`business_id`) REFERENCES `businesses`(`business_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blacklist
CREATE TABLE IF NOT EXISTS `blacklist` (
  `blacklist_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `blocked_userid` BIGINT UNSIGNED NOT NULL,
  `reason` TEXT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `banned_by` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`blacklist_id`),
  KEY `idx_blacklist_blocked` (`blocked_userid`),
  CONSTRAINT `fk_blacklist_blocked` FOREIGN KEY (`blocked_userid`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_blacklist_bannedby` FOREIGN KEY (`banned_by`) REFERENCES `admins`(`admin_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reports
CREATE TABLE IF NOT EXISTS `reports` (
  `report_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` BIGINT UNSIGNED NOT NULL,
  `generation_date` DATE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `report_type` ENUM('sales_summary', 'inventory_status', 'user_activity', 'complaint_analysis') DEFAULT NULL,
  `time_period_start` DATE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `time_period_end` DATE NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `stats_data` TEXT DEFAULT NULL,
  PRIMARY KEY (`report_id`),
  KEY `idx_report_admin` (`admin_id`),
  CONSTRAINT `fk_report_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins`(`admin_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Carts
CREATE TABLE IF NOT EXISTS `carts` (
    `cart_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `member_id` BIGINT UNSIGNED NOT NULL,
    --`checked_out` TINYINT(1) DEFAULT 0,
    PRIMARY KEY (`cart_id`),
    KEY `idx_cart_member` (`member_id`),
    CONSTRAINT `fk_cart_member` FOREIGN KEY (`member_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cart items
CREATE TABLE IF NOT EXISTS `cart_items` (
    `cart_item_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `cart_id` BIGINT UNSIGNED NOT NULL,
    `book_id` BIGINT UNSIGNED NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    PRIMARY KEY (`cart_item_id`),
    KEY `idx_cartitems_cart` (`cart_id`),
    KEY `idx_cartitems_book` (`book_id`),
    CONSTRAINT `fk_cartitems_cart` FOREIGN KEY (`cart_id`) REFERENCES `carts`(`cart_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cartitems_book` FOREIGN KEY (`book_id`) REFERENCES `books`(`book_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reviews
CREATE TABLE IF NOT EXISTS `reviews` (
    `review_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `book_id` BIGINT UNSIGNED NOT NULL,
    `order_id` BIGINT UNSIGNED NOT NULL,
    `rating` TINYINT NOT NULL DEFAULT 5,
    `comment` TEXT DEFAULT NULL,
    `review_time` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `reply` TEXT DEFAULT NULL,
    `reply_time` DATETIME DEFAULT NULL,
    PRIMARY KEY (`review_id`),
    KEY `idx_reviews_book` (`book_id`),
    CONSTRAINT `fk_reviews_book` FOREIGN KEY (`book_id`) REFERENCES `books`(`book_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_reviews_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`order_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Search history
CREATE TABLE IF NOT EXISTS `search_histories` (
    `history_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `member_id` BIGINT UNSIGNED NOT NULL,
    `keyword` VARCHAR(500) NOT NULL,
    `search_time` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`history_id`),
    KEY `idx_search_member` (`member_id`),
    CONSTRAINT `fk_search_member` FOREIGN KEY (`member_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;