-- SobatAnak Midtrans 6 Menit + Share Lokasi Update
-- Import ke database yang SUDAH ADA: sobatanak_db
-- Fungsi: menambah kolom lokasi rumah user di tabel user_addresses.
-- Aman diimport ulang karena setiap kolom dicek dulu sebelum ditambahkan.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

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

CALL add_sobatanak_col_if_missing('user_addresses', 'location_url', "text DEFAULT NULL AFTER `postal_code`");
CALL add_sobatanak_col_if_missing('user_addresses', 'latitude', "decimal(10,7) DEFAULT NULL AFTER `location_url`");
CALL add_sobatanak_col_if_missing('user_addresses', 'longitude', "decimal(10,7) DEFAULT NULL AFTER `latitude`");

DROP PROCEDURE IF EXISTS add_sobatanak_col_if_missing;
