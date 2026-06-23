-- SobatAnak Midtrans Payment Update v2
-- Import file ini ke database yang SUDAH ADA: sobatanak_db
-- Tidak membuat database baru. Hanya menambah/menyesuaikan tabel orders dan order_items.
-- Aman diimport ulang: tabel dibuat jika belum ada, kolom baru ditambah jika belum ada.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `orders` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `user_address_id` bigint(20) UNSIGNED DEFAULT NULL,
  `order_number` varchar(50) NOT NULL,
  `subtotal` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `shipping_cost` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `total_amount` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` varchar(40) NOT NULL DEFAULT 'pending',
  `payment_status` varchar(80) DEFAULT NULL,
  `payment_type` varchar(80) DEFAULT NULL,
  `selected_payment_method` varchar(80) DEFAULT 'all',
  `selected_payment_label` varchar(120) DEFAULT 'Semua Metode Aktif',
  `enabled_payments` longtext DEFAULT NULL,
  `payment_bank` varchar(80) DEFAULT NULL,
  `payment_store` varchar(80) DEFAULT NULL,
  `payment_code` varchar(120) DEFAULT NULL,
  `va_number` varchar(120) DEFAULT NULL,
  `biller_code` varchar(80) DEFAULT NULL,
  `bill_key` varchar(120) DEFAULT NULL,
  `acquirer` varchar(80) DEFAULT NULL,
  `pdf_url` text DEFAULT NULL,
  `payment_detail` longtext DEFAULT NULL,
  `fraud_status` varchar(80) DEFAULT NULL,
  `midtrans_transaction_id` varchar(120) DEFAULT NULL,
  `midtrans_order_id` varchar(120) DEFAULT NULL,
  `snap_token` varchar(255) DEFAULT NULL,
  `snap_redirect_url` text DEFAULT NULL,
  `midtrans_response` longtext DEFAULT NULL,
  `callback_payload` longtext DEFAULT NULL,
  `shipping_snapshot` longtext DEFAULT NULL,
  `customer_note` text DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `expired_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_number_unique` (`order_number`),
  KEY `orders_user_id_index` (`user_id`),
  KEY `orders_user_address_id_index` (`user_address_id`),
  KEY `orders_status_index` (`status`),
  KEY `orders_midtrans_transaction_id_index` (`midtrans_transaction_id`),
  KEY `orders_midtrans_order_id_index` (`midtrans_order_id`),
  KEY `orders_paid_at_index` (`paid_at`),
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_user_address_id_foreign` FOREIGN KEY (`user_address_id`) REFERENCES `user_addresses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_image` text DEFAULT NULL,
  `price` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `line_total` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_index` (`order_id`),
  KEY `order_items_product_id_index` (`product_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;

DELIMITER //
CREATE PROCEDURE add_sobatanak_midtrans_column_if_missing(IN tableName VARCHAR(64), IN columnName VARCHAR(64), IN columnDefinition TEXT)
BEGIN
  IF NOT EXISTS (
    SELECT 1
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = tableName
      AND COLUMN_NAME = columnName
  ) THEN
    SET @sql = CONCAT('ALTER TABLE `', tableName, '` ADD COLUMN `', columnName, '` ', columnDefinition);
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
  END IF;
END//
DELIMITER ;

CALL add_sobatanak_midtrans_column_if_missing('orders', 'selected_payment_method', "varchar(80) DEFAULT 'all' AFTER `payment_type`");
CALL add_sobatanak_midtrans_column_if_missing('orders', 'selected_payment_label', "varchar(120) DEFAULT 'Semua Metode Aktif' AFTER `selected_payment_method`");
CALL add_sobatanak_midtrans_column_if_missing('orders', 'enabled_payments', "longtext DEFAULT NULL AFTER `selected_payment_label`");
CALL add_sobatanak_midtrans_column_if_missing('orders', 'payment_bank', "varchar(80) DEFAULT NULL AFTER `enabled_payments`");
CALL add_sobatanak_midtrans_column_if_missing('orders', 'payment_store', "varchar(80) DEFAULT NULL AFTER `payment_bank`");
CALL add_sobatanak_midtrans_column_if_missing('orders', 'payment_code', "varchar(120) DEFAULT NULL AFTER `payment_store`");
CALL add_sobatanak_midtrans_column_if_missing('orders', 'va_number', "varchar(120) DEFAULT NULL AFTER `payment_code`");
CALL add_sobatanak_midtrans_column_if_missing('orders', 'biller_code', "varchar(80) DEFAULT NULL AFTER `va_number`");
CALL add_sobatanak_midtrans_column_if_missing('orders', 'bill_key', "varchar(120) DEFAULT NULL AFTER `biller_code`");
CALL add_sobatanak_midtrans_column_if_missing('orders', 'acquirer', "varchar(80) DEFAULT NULL AFTER `bill_key`");
CALL add_sobatanak_midtrans_column_if_missing('orders', 'pdf_url', "text DEFAULT NULL AFTER `acquirer`");
CALL add_sobatanak_midtrans_column_if_missing('orders', 'payment_detail', "longtext DEFAULT NULL AFTER `pdf_url`");

DROP PROCEDURE IF EXISTS add_sobatanak_midtrans_column_if_missing;
