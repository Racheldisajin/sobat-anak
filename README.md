# SobatAnak.com Laravel 13 + MySQL

Project ini adalah versi Laravel 13 dari desain SobatAnak yang kamu kirim. Logo sudah diganti memakai file `logo-sobat-anak.png` dan tagline sudah dipasang: **Mom & Baby Care**.

## Cara running

1. Extract zip ini.
2. Buka terminal di folder project:
   ```bash
   cd sobatanak_laravel13
   ```
3. Install dependency Laravel:
   ```bash
   composer install
   ```
   Jika sebelumnya sempat error `laravel/tinker`, file ini sudah diperbaiki dengan menghapus package tinker yang belum cocok dengan Laravel 13.
4. Buat file `.env`:
   ```bash
   copy .env.example .env
   ```
   Untuk Git Bash/Linux gunakan:
   ```bash
   cp .env.example .env
   ```
5. Generate key:
   ```bash
   php artisan key:generate
   ```
6. Buat database MySQL di phpMyAdmin dengan nama:
   ```text
   sobatanak_db
   ```
7. Atur `.env`:
   ```env
   DB_DATABASE=sobatanak_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```
8. Import database. Pilih salah satu:
   - Import file `sobatanak_db.sql` lewat phpMyAdmin, atau
   - Jalankan migration + seeder:
     ```bash
     php artisan migrate --seed
     ```
9. Jalankan web:
   ```bash
   php artisan serve
   ```
10. Buka:
   ```text
   http://127.0.0.1:8000
   ```

## Fitur yang tersedia

- Home page dengan hero carousel, produk unggulan, points rewards, testimoni, dan artikel.
- Katalog produk dengan search, filter kategori, sort harga/rating/terlaris, tombol beli, dan cart counter localStorage.
- Artikel parenting.
- Mini game: Puzzle Edukatif, Memory Card, TapTap Kuman, sistem poin localStorage, dan tukar reward.
- Database MySQL: `products`, `articles`, `testimonials`, `rewards`.

## Catatan

Folder `vendor` tidak disertakan supaya zip ringan. Jalankan `composer install` setelah extract.
