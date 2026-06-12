CREATE DATABASE IF NOT EXISTS sobatanak_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sobatanak_db;
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS reward_claims;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS user_points;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS rewards;
DROP TABLE IF EXISTS testimonials;
DROP TABLE IF EXISTS articles;
DROP TABLE IF EXISTS products;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE products (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(255),category VARCHAR(255),price INT UNSIGNED,badge VARCHAR(255) NULL,rating DECIMAL(2,1),sold INT UNSIGNED,image TEXT,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE articles (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,title VARCHAR(255),excerpt TEXT,category VARCHAR(255),image TEXT,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE testimonials (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(255),message TEXT,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE rewards (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(255),points INT UNSIGNED,description TEXT,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE users (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(255) NOT NULL,email VARCHAR(150) NOT NULL UNIQUE,password VARCHAR(255) NOT NULL,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE user_points (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,user_id BIGINT UNSIGNED NOT NULL,points INT NOT NULL DEFAULT 1250,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,CONSTRAINT user_points_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE cart_items (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,user_id BIGINT UNSIGNED NOT NULL,product_id BIGINT UNSIGNED NOT NULL,quantity INT NOT NULL DEFAULT 1,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,UNIQUE KEY cart_user_product_unique (user_id,product_id),CONSTRAINT cart_items_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,CONSTRAINT cart_items_product_id_foreign FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE reward_claims (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,user_id BIGINT UNSIGNED NOT NULL,reward_name VARCHAR(255) NOT NULL,points_used INT NOT NULL,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,CONSTRAINT reward_claims_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO products (name,category,price,badge,rating,sold,image,created_at,updated_at) VALUES
('Botol Susu Anti-Kolik','Bayi 0–12 bln',189000,'Terlaris',4.9,3241,'https://img.rocket.new/generatedImages/rocket_gen_img_1b9f97762-1766130752655.png',NOW(),NOW()),
('Popok Premium Newborn','Bayi 0–12 bln',135000,'Stok Terbatas',4.8,1890,'https://img.rocket.new/generatedImages/rocket_gen_img_1ad59fa5f-1772142839813.png',NOW(),NOW()),
('Boneka Edukatif Sensorik','Balita 1–3 thn',215000,'Baru',4.7,987,'https://img.rocket.new/generatedImages/rocket_gen_img_16582a9a6-1772711604593.png',NOW(),NOW()),
('Set Baju Bayi Muslin','Pakaian',299000,NULL,4.9,2105,'https://img.rocket.new/generatedImages/rocket_gen_img_13ee4c0a8-1767742445786.png',NOW(),NOW()),
('Stroller Lipat Ringan','Balita 1–3 thn',1450000,'Diskon 19%',4.6,542,'https://img.rocket.new/generatedImages/rocket_gen_img_1ac1784c7-1771669706135.png',NOW(),NOW()),
('Set Mandi Bayi Lengkap','Perawatan',395000,'Bundle Hemat',4.8,1678,'https://images.unsplash.com/photo-1635874714425-c342060a4c58',NOW(),NOW()),
('Mainan Gigit Silikon','Bayi 0–12 bln',89000,NULL,4.7,4320,'https://img.rocket.new/generatedImages/rocket_gen_img_1e14701d0-1772100946575.png',NOW(),NOW()),
('Buku Cerita Anak Bergambar','Anak 3–12 thn',75000,NULL,4.9,5670,'https://images.unsplash.com/photo-1550701644-af041ecfe403',NOW(),NOW()),
('Sereal Bayi Organik','Nutrisi',125000,'Organik',4.8,2890,'https://img.rocket.new/generatedImages/rocket_gen_img_1920981fd-1767550823123.png',NOW(),NOW()),
('Sepatu Bayi Pertama','Pakaian',165000,'Baru',4.6,890,'https://images.unsplash.com/photo-1719595375830-d7503d9e72e9',NOW(),NOW()),
('Puzzle Kayu Edukatif','Anak 3–12 thn',195000,NULL,4.7,1234,'https://img.rocket.new/generatedImages/rocket_gen_img_1fe9f393f-1778253574216.png',NOW(),NOW()),
('Lotion Bayi Aloe Vera','Perawatan',95000,NULL,4.9,3456,'https://images.unsplash.com/photo-1625342000939-499ffc818b9d',NOW(),NOW());

INSERT INTO articles (title,excerpt,category,image,created_at,updated_at) VALUES
('Panduan Memilih Botol Susu Aman','Tips memilih botol susu anti-kolik, BPA free, dan mudah dibersihkan untuk bayi.','Parenting','https://images.unsplash.com/photo-1555252333-9f8e92e65df9',NOW(),NOW()),
('Mainan Edukatif untuk Tumbuh Kembang Anak','Rekomendasi mainan sensorik, puzzle, dan buku bergambar untuk mendukung perkembangan si kecil.','Edukasi','https://images.unsplash.com/photo-1516627145497-ae6968895b74',NOW(),NOW()),
('Checklist Perlengkapan Bayi Baru Lahir','Daftar kebutuhan newborn yang penting agar belanja lebih hemat dan tidak berlebihan.','Mom Tips','https://images.unsplash.com/photo-1522771930-78848d9293e8',NOW(),NOW()),
('Tips Menjaga Kulit Bayi Tetap Lembap','Cara memilih produk perawatan bayi yang lembut dan aman untuk kulit sensitif.','Baby Care','https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4',NOW(),NOW()),
('MPASI Pertama: Apa yang Perlu Disiapkan?','Panduan sederhana menyiapkan perlengkapan MPASI dan jadwal makan awal si kecil.','Nutrisi','https://images.unsplash.com/photo-1546015720-b8b30df5aa27',NOW(),NOW()),
('Cara Membuat Rutinitas Tidur Bayi','Tips membuat jam tidur lebih teratur agar bayi dan ibu sama-sama nyaman.','Parenting','https://images.unsplash.com/photo-1546015720-8f2fbde05e46',NOW(),NOW()),
('Mainan Sensorik Sesuai Usia Anak','Rekomendasi mainan berdasarkan usia untuk membantu motorik dan rasa ingin tahu anak.','Edukasi','https://images.unsplash.com/photo-1566576912321-d58ddd7a6088',NOW(),NOW()),
('Perlengkapan Traveling dengan Bayi','Checklist stroller, tas popok, botol, snack, dan barang wajib saat bepergian.','Mom Tips','https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9',NOW(),NOW()),
('Memilih Pakaian Bayi yang Nyaman','Bahan, ukuran, dan model pakaian yang aman untuk aktivitas harian si kecil.','Baby Care','https://images.unsplash.com/photo-1515488764276-beab7607c1e6',NOW(),NOW()),
('Ide Aktivitas Edukatif di Rumah','Aktivitas sederhana dengan buku, puzzle, dan permainan warna untuk anak.','Edukasi','https://images.unsplash.com/photo-1484820540004-14229fe36ca4',NOW(),NOW()),
('Cara Hemat Belanja Kebutuhan Bayi','Strategi membeli bundle, membuat prioritas, dan menghindari barang yang jarang dipakai.','Mom Tips','https://images.unsplash.com/photo-1555252333-9f8e92e65df9',NOW(),NOW()),
('Panduan Membersihkan Botol Susu','Langkah mencuci, steril, dan menyimpan botol susu agar tetap higienis.','Baby Care','https://images.unsplash.com/photo-1584464491033-06628f3a6b7b',NOW(),NOW());

INSERT INTO testimonials (name,message,created_at,updated_at) VALUES
('Nadia Putri','Produknya lengkap dan kualitasnya bagus. Anak saya suka banget sama mainan edukatifnya!',NOW(),NOW()),
('Rani Maharani','Belanja perlengkapan bayi jadi gampang. Desain websitenya lucu dan mudah dipakai.',NOW(),NOW()),
('Dewi Lestari','Mini game dan poinnya bikin anak semangat. Voucher belanjanya juga berguna.',NOW(),NOW());

INSERT INTO rewards (name,points,description,created_at,updated_at) VALUES
('Voucher Belanja Rp25.000',500,'Tukar poin untuk potongan belanja produk bayi.',NOW(),NOW()),
('Gratis Ongkir',300,'Voucher ongkir untuk pembelian berikutnya.',NOW(),NOW()),
('Mystery Gift Anak',800,'Hadiah kejutan edukatif untuk si kecil.',NOW(),NOW());
