-- ============================================================
-- PATCH: Tambah tabel product_reviews dan user_addresses
-- Import file ini di phpMyAdmin -> sobatanak_db -> Import
-- ============================================================

USE sobatanak_db;

-- Tabel ulasan produk
CREATE TABLE IF NOT EXISTS product_reviews (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id  BIGINT UNSIGNED NOT NULL,
    user_id     BIGINT UNSIGNED NOT NULL,
    rating      TINYINT UNSIGNED NOT NULL COMMENT '1-5',
    body        TEXT NOT NULL,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL,
    UNIQUE KEY review_user_product_unique (product_id, user_id),
    CONSTRAINT product_reviews_product_id_foreign
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT product_reviews_user_id_foreign
        FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel alamat pengiriman pengguna
CREATE TABLE IF NOT EXISTS user_addresses (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    label           VARCHAR(100)  NOT NULL DEFAULT 'Rumah',
    recipient_name  VARCHAR(255)  NOT NULL,
    phone           VARCHAR(20)   NOT NULL,
    address         TEXT          NOT NULL,
    city            VARCHAR(100)  NOT NULL,
    province        VARCHAR(100)  NOT NULL,
    postal_code     VARCHAR(10)   NOT NULL,
    is_default      TINYINT(1)    NOT NULL DEFAULT 0,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    CONSTRAINT user_addresses_user_id_foreign
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'Tabel product_reviews dan user_addresses berhasil dibuat!' AS status;
