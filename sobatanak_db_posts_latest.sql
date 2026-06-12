CREATE DATABASE IF NOT EXISTS sobatanak_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sobatanak_db;
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS reward_claims;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS user_points;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS post_tags;
DROP TABLE IF EXISTS post_categories;
DROP TABLE IF EXISTS articles;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS rewards;
DROP TABLE IF EXISTS testimonials;
DROP TABLE IF EXISTS products;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE products (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(255),category VARCHAR(255),price INT UNSIGNED,badge VARCHAR(255) NULL,rating DECIMAL(2,1),sold INT UNSIGNED,stock INT UNSIGNED NOT NULL DEFAULT 20,image TEXT,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE users (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(255) NOT NULL,email VARCHAR(150) NOT NULL UNIQUE,password VARCHAR(255) NOT NULL,role VARCHAR(20) NOT NULL DEFAULT 'user',created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE post_categories (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,slug VARCHAR(255) NOT NULL,name VARCHAR(255) NOT NULL,created_at TIMESTAMP NULL DEFAULT NULL,updated_at TIMESTAMP NULL DEFAULT NULL,PRIMARY KEY (id),UNIQUE KEY post_categories_slug_unique (slug)) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE post_tags (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,slug VARCHAR(255) NOT NULL,name VARCHAR(255) NOT NULL,created_at TIMESTAMP NULL DEFAULT NULL,updated_at TIMESTAMP NULL DEFAULT NULL,PRIMARY KEY (id),UNIQUE KEY post_tags_slug_unique (slug)) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE posts (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,slug VARCHAR(255) NOT NULL,title VARCHAR(255) NOT NULL,image VARCHAR(255) DEFAULT NULL,content LONGTEXT,counter INT NOT NULL DEFAULT '0',status VARCHAR(255) NOT NULL,category_id BIGINT UNSIGNED DEFAULT NULL,tags VARCHAR(255) DEFAULT NULL,created_by BIGINT UNSIGNED NOT NULL,updated_by BIGINT UNSIGNED DEFAULT NULL,published_at TIMESTAMP NULL DEFAULT NULL,created_at TIMESTAMP NULL DEFAULT NULL,updated_at TIMESTAMP NULL DEFAULT NULL,meta_data JSON DEFAULT NULL,source VARCHAR(255) NOT NULL DEFAULT 'web',PRIMARY KEY (id),UNIQUE KEY posts_slug_unique (slug)) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE testimonials (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(255),message TEXT,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE rewards (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(255),points INT UNSIGNED,description TEXT,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE user_points (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,user_id BIGINT UNSIGNED NOT NULL,points INT NOT NULL DEFAULT 1250,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,CONSTRAINT user_points_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE cart_items (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,user_id BIGINT UNSIGNED NOT NULL,product_id BIGINT UNSIGNED NOT NULL,quantity INT NOT NULL DEFAULT 1,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,UNIQUE KEY cart_user_product_unique (user_id,product_id),CONSTRAINT cart_items_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,CONSTRAINT cart_items_product_id_foreign FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE reward_claims (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,user_id BIGINT UNSIGNED NOT NULL,reward_name VARCHAR(255) NOT NULL,points_used INT NOT NULL,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,CONSTRAINT reward_claims_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO products (name,category,price,badge,rating,sold,stock,image,created_at,updated_at) VALUES
('Botol Susu Anti-Kolik','Bayi 0–12 bln',189000,'Terlaris',4.9,3241,25,'https://img.rocket.new/generatedImages/rocket_gen_img_1b9f97762-1766130752655.png',NOW(),NOW()),
('Popok Premium Newborn','Bayi 0–12 bln',135000,'Stok Terbatas',4.8,1890,18,'https://img.rocket.new/generatedImages/rocket_gen_img_1ad59fa5f-1772142839813.png',NOW(),NOW()),
('Boneka Edukatif Sensorik','Balita 1–3 thn',215000,'Baru',4.7,987,12,'https://img.rocket.new/generatedImages/rocket_gen_img_16582a9a6-1772711604593.png',NOW(),NOW()),
('Set Baju Bayi Muslin','Pakaian',299000,NULL,4.9,2105,15,'https://img.rocket.new/generatedImages/rocket_gen_img_13ee4c0a8-1767742445786.png',NOW(),NOW()),
('Stroller Lipat Ringan','Balita 1–3 thn',1450000,'Diskon 19%',4.6,542,7,'https://img.rocket.new/generatedImages/rocket_gen_img_1ac1784c7-1771669706135.png',NOW(),NOW()),
('Set Mandi Bayi Lengkap','Perawatan',395000,'Bundle Hemat',4.8,1678,20,'https://images.unsplash.com/photo-1635874714425-c342060a4c58',NOW(),NOW()),
('Mainan Gigit Silikon','Bayi 0–12 bln',89000,NULL,4.7,4320,30,'https://img.rocket.new/generatedImages/rocket_gen_img_1e14701d0-1772100946575.png',NOW(),NOW()),
('Buku Cerita Anak Bergambar','Anak 3–12 thn',75000,NULL,4.9,5670,22,'https://images.unsplash.com/photo-1550701644-af041ecfe403',NOW(),NOW()),
('Sereal Bayi Organik','Nutrisi',125000,'Organik',4.8,2890,16,'https://img.rocket.new/generatedImages/rocket_gen_img_1920981fd-1767550823123.png',NOW(),NOW()),
('Sepatu Bayi Pertama','Pakaian',165000,'Baru',4.6,890,9,'https://images.unsplash.com/photo-1719595375830-d7503d9e72e9',NOW(),NOW()),
('Puzzle Kayu Edukatif','Anak 3–12 thn',195000,NULL,4.7,1234,14,'https://img.rocket.new/generatedImages/rocket_gen_img_1fe9f393f-1778253574216.png',NOW(),NOW()),
('Lotion Bayi Aloe Vera','Perawatan',95000,NULL,4.9,3456,24,'https://images.unsplash.com/photo-1625342000939-499ffc818b9d',NOW(),NOW());

-- Akun admin awal
-- Email: felixzanqueen@gmail.com
-- Password: 12345678
INSERT INTO users (id,name,email,password,role,created_at,updated_at) VALUES
(1,'Felix','felixzanqueen@gmail.com','$2y$12$oA1YpHl0Jajpl0rN8SirieMPkcdTUFht6P.6fOiuM90wa3j1.Ejay','admin',NOW(),NOW());

INSERT INTO post_categories (id,slug,name,created_at,updated_at) VALUES
(1,'parenting','Parenting',NOW(),NOW()),
(2,'edukasi','Edukasi',NOW(),NOW()),
(3,'mom-tips','Mom Tips',NOW(),NOW()),
(4,'baby-care','Baby Care',NOW(),NOW()),
(5,'nutrisi','Nutrisi',NOW(),NOW());

INSERT INTO post_tags (id,slug,name,created_at,updated_at) VALUES
(1,'parenting','Parenting',NOW(),NOW()),
(2,'bayi','Bayi',NOW(),NOW()),
(3,'mom-tips','Mom Tips',NOW(),NOW()),
(4,'edukasi','Edukasi',NOW(),NOW()),
(5,'nutrisi','Nutrisi',NOW(),NOW());

INSERT INTO posts (id,slug,title,image,content,counter,status,category_id,tags,created_by,updated_by,published_at,created_at,updated_at,meta_data,source) VALUES
(1,'panduan-memilih-botol-susu-aman','Panduan Memilih Botol Susu Aman','https://images.unsplash.com/photo-1555252333-9f8e92e65df9','Tips memilih botol susu anti-kolik, BPA free, dan mudah dibersihkan untuk bayi.',0,'published',1,'parenting,bayi',1,NULL,NOW(),NOW(),NOW(),'{"editor":"SobatAnak Admin","summary":"Tips memilih botol susu anti-kolik, BPA free, dan mudah dibersihkan untuk bayi."}','web'),
(2,'mainan-edukatif-untuk-tumbuh-kembang-anak','Mainan Edukatif untuk Tumbuh Kembang Anak','https://images.unsplash.com/photo-1516627145497-ae6968895b74','Rekomendasi mainan sensorik, puzzle, dan buku bergambar untuk mendukung perkembangan si kecil.',0,'published',2,'edukasi,mainan-anak',1,NULL,NOW(),NOW(),NOW(),'{"editor":"SobatAnak Admin","summary":"Rekomendasi mainan sensorik, puzzle, dan buku bergambar untuk mendukung perkembangan si kecil."}','web'),
(3,'checklist-perlengkapan-bayi-baru-lahir','Checklist Perlengkapan Bayi Baru Lahir','https://images.unsplash.com/photo-1522771930-78848d9293e8','Daftar kebutuhan newborn yang penting agar belanja lebih hemat dan tidak berlebihan.',0,'published',3,'mom-tips,newborn',1,NULL,NOW(),NOW(),NOW(),'{"editor":"SobatAnak Admin","summary":"Daftar kebutuhan newborn yang penting agar belanja lebih hemat dan tidak berlebihan."}','web'),
(4,'tips-menjaga-kulit-bayi-tetap-lembap','Tips Menjaga Kulit Bayi Tetap Lembap','https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4','Cara memilih produk perawatan bayi yang lembut dan aman untuk kulit sensitif.',0,'published',4,'baby-care,kulit-bayi',1,NULL,NOW(),NOW(),NOW(),'{"editor":"SobatAnak Admin","summary":"Cara memilih produk perawatan bayi yang lembut dan aman untuk kulit sensitif."}','web'),
(5,'mpasi-pertama-apa-yang-perlu-disiapkan','MPASI Pertama: Apa yang Perlu Disiapkan?','https://images.unsplash.com/photo-1546015720-b8b30df5aa27','Panduan sederhana menyiapkan perlengkapan MPASI dan jadwal makan awal si kecil.',0,'published',5,'nutrisi,mpasi',1,NULL,NOW(),NOW(),NOW(),'{"editor":"SobatAnak Admin","summary":"Panduan sederhana menyiapkan perlengkapan MPASI dan jadwal makan awal si kecil."}','web'),
(6,'cara-membuat-rutinitas-tidur-bayi','Cara Membuat Rutinitas Tidur Bayi','https://images.unsplash.com/photo-1546015720-8f2fbde05e46','Tips membuat jam tidur lebih teratur agar bayi dan ibu sama-sama nyaman.',0,'published',1,'parenting,tidur-bayi',1,NULL,NOW(),NOW(),NOW(),'{"editor":"SobatAnak Admin","summary":"Tips membuat jam tidur lebih teratur agar bayi dan ibu sama-sama nyaman."}','web'),
(7,'mainan-sensorik-sesuai-usia-anak','Mainan Sensorik Sesuai Usia Anak','https://images.unsplash.com/photo-1566576912321-d58ddd7a6088','Rekomendasi mainan berdasarkan usia untuk membantu motorik dan rasa ingin tahu anak.',0,'published',2,'edukasi,sensorik',1,NULL,NOW(),NOW(),NOW(),'{"editor":"SobatAnak Admin","summary":"Rekomendasi mainan berdasarkan usia untuk membantu motorik dan rasa ingin tahu anak."}','web'),
(8,'perlengkapan-traveling-dengan-bayi','Perlengkapan Traveling dengan Bayi','https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9','Checklist stroller, tas popok, botol, snack, dan barang wajib saat bepergian.',0,'published',3,'mom-tips,traveling',1,NULL,NOW(),NOW(),NOW(),'{"editor":"SobatAnak Admin","summary":"Checklist stroller, tas popok, botol, snack, dan barang wajib saat bepergian."}','web'),
(9,'memilih-pakaian-bayi-yang-nyaman','Memilih Pakaian Bayi yang Nyaman','https://images.unsplash.com/photo-1515488764276-beab7607c1e6','Bahan, ukuran, dan model pakaian yang aman untuk aktivitas harian si kecil.',0,'published',4,'baby-care,pakaian',1,NULL,NOW(),NOW(),NOW(),'{"editor":"SobatAnak Admin","summary":"Bahan, ukuran, dan model pakaian yang aman untuk aktivitas harian si kecil."}','web'),
(10,'ide-aktivitas-edukatif-di-rumah','Ide Aktivitas Edukatif di Rumah','https://images.unsplash.com/photo-1484820540004-14229fe36ca4','Aktivitas sederhana dengan buku, puzzle, dan permainan warna untuk anak.',0,'published',2,'edukasi,aktivitas-anak',1,NULL,NOW(),NOW(),NOW(),'{"editor":"SobatAnak Admin","summary":"Aktivitas sederhana dengan buku, puzzle, dan permainan warna untuk anak."}','web'),
(11,'cara-hemat-belanja-kebutuhan-bayi','Cara Hemat Belanja Kebutuhan Bayi','https://images.unsplash.com/photo-1555252333-9f8e92e65df9','Strategi membeli bundle, membuat prioritas, dan menghindari barang yang jarang dipakai.',0,'published',3,'mom-tips,hemat',1,NULL,NOW(),NOW(),NOW(),'{"editor":"SobatAnak Admin","summary":"Strategi membeli bundle, membuat prioritas, dan menghindari barang yang jarang dipakai."}','web'),
(12,'panduan-membersihkan-botol-susu','Panduan Membersihkan Botol Susu','https://images.unsplash.com/photo-1584464491033-06628f3a6b7b','Langkah mencuci, steril, dan menyimpan botol susu agar tetap higienis.',0,'published',4,'baby-care,botol-susu',1,NULL,NOW(),NOW(),NOW(),'{"editor":"SobatAnak Admin","summary":"Langkah mencuci, steril, dan menyimpan botol susu agar tetap higienis."}','web');
ALTER TABLE posts AUTO_INCREMENT=101;

INSERT INTO testimonials (name,message,created_at,updated_at) VALUES
('Nadia Putri','Produknya lengkap dan kualitasnya bagus. Anak saya suka banget sama mainan edukatifnya!',NOW(),NOW()),
('Rani Maharani','Belanja perlengkapan bayi jadi gampang. Desain websitenya lucu dan mudah dipakai.',NOW(),NOW()),
('Dewi Lestari','Mini game dan poinnya bikin anak semangat. Voucher belanjanya juga berguna.',NOW(),NOW());

INSERT INTO rewards (name,points,description,created_at,updated_at) VALUES
('Voucher Belanja Rp25.000',500,'Tukar poin untuk potongan belanja produk bayi.',NOW(),NOW()),
('Gratis Ongkir',300,'Voucher ongkir untuk pembelian berikutnya.',NOW(),NOW()),
('Mystery Gift Anak',800,'Hadiah kejutan edukatif untuk si kecil.',NOW(),NOW());

INSERT INTO user_points (user_id,points,created_at,updated_at) VALUES (1,1250,NOW(),NOW());
