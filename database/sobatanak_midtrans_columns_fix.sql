-- SobatAnak Midtrans Columns Hotfix v3
-- Import ke database yang SUDAH ADA: sobatanak_db
-- Fungsi: memperbaiki error "Unknown column selected_payment_method" dan menambah kolom detail pembayaran Midtrans yang dibutuhkan.
-- Aman diimport ulang karena setiap kolom dicek dulu sebelum ditambahkan.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
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
  KEY `orders_status_index` (`status`)
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
  KEY `order_items_product_id_index` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP PROCEDURE IF EXISTS add_sobatanak_col_if_missing;
DELIMITER //
CREATE PROCEDURE add_sobatanak_col_if_missing(IN tableName VARCHAR(64), IN columnName VARCHAR(64), IN columnDefinition TEXT)
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
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

CALL add_sobatanak_col_if_missing('orders', 'user_address_id', "bigint(20) UNSIGNED DEFAULT NULL AFTER `user_id`");
CALL add_sobatanak_col_if_missing('orders', 'shipping_cost', "int(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `subtotal`");
CALL add_sobatanak_col_if_missing('orders', 'payment_status', "varchar(80) DEFAULT NULL AFTER `status`");
CALL add_sobatanak_col_if_missing('orders', 'payment_type', "varchar(80) DEFAULT NULL AFTER `payment_status`");
CALL add_sobatanak_col_if_missing('orders', 'selected_payment_method', "varchar(80) DEFAULT 'all' AFTER `payment_type`");
CALL add_sobatanak_col_if_missing('orders', 'selected_payment_label', "varchar(120) DEFAULT 'Semua Metode Aktif' AFTER `selected_payment_method`");
CALL add_sobatanak_col_if_missing('orders', 'enabled_payments', "longtext DEFAULT NULL AFTER `selected_payment_label`");
CALL add_sobatanak_col_if_missing('orders', 'payment_bank', "varchar(80) DEFAULT NULL AFTER `enabled_payments`");
CALL add_sobatanak_col_if_missing('orders', 'payment_store', "varchar(80) DEFAULT NULL AFTER `payment_bank`");
CALL add_sobatanak_col_if_missing('orders', 'payment_code', "varchar(120) DEFAULT NULL AFTER `payment_store`");
CALL add_sobatanak_col_if_missing('orders', 'va_number', "varchar(120) DEFAULT NULL AFTER `payment_code`");
CALL add_sobatanak_col_if_missing('orders', 'biller_code', "varchar(80) DEFAULT NULL AFTER `va_number`");
CALL add_sobatanak_col_if_missing('orders', 'bill_key', "varchar(120) DEFAULT NULL AFTER `biller_code`");
CALL add_sobatanak_col_if_missing('orders', 'acquirer', "varchar(80) DEFAULT NULL AFTER `bill_key`");
CALL add_sobatanak_col_if_missing('orders', 'pdf_url', "text DEFAULT NULL AFTER `acquirer`");
CALL add_sobatanak_col_if_missing('orders', 'payment_detail', "longtext DEFAULT NULL AFTER `pdf_url`");
CALL add_sobatanak_col_if_missing('orders', 'fraud_status', "varchar(80) DEFAULT NULL AFTER `payment_detail`");
CALL add_sobatanak_col_if_missing('orders', 'midtrans_transaction_id', "varchar(120) DEFAULT NULL AFTER `fraud_status`");
CALL add_sobatanak_col_if_missing('orders', 'midtrans_order_id', "varchar(120) DEFAULT NULL AFTER `midtrans_transaction_id`");
CALL add_sobatanak_col_if_missing('orders', 'snap_token', "varchar(255) DEFAULT NULL AFTER `midtrans_order_id`");
CALL add_sobatanak_col_if_missing('orders', 'snap_redirect_url', "text DEFAULT NULL AFTER `snap_token`");
CALL add_sobatanak_col_if_missing('orders', 'midtrans_response', "longtext DEFAULT NULL AFTER `snap_redirect_url`");
CALL add_sobatanak_col_if_missing('orders', 'callback_payload', "longtext DEFAULT NULL AFTER `midtrans_response`");
CALL add_sobatanak_col_if_missing('orders', 'shipping_snapshot', "longtext DEFAULT NULL AFTER `callback_payload`");
CALL add_sobatanak_col_if_missing('orders', 'customer_note', "text DEFAULT NULL AFTER `shipping_snapshot`");
CALL add_sobatanak_col_if_missing('orders', 'paid_at', "timestamp NULL DEFAULT NULL AFTER `customer_note`");
CALL add_sobatanak_col_if_missing('orders', 'expired_at', "timestamp NULL DEFAULT NULL AFTER `paid_at`");

CALL add_sobatanak_col_if_missing('order_items', 'product_image', "text DEFAULT NULL AFTER `product_name`");
CALL add_sobatanak_col_if_missing('order_items', 'line_total', "int(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `quantity`");

DROP PROCEDURE IF EXISTS add_sobatanak_col_if_missing;
