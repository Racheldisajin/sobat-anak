# Setup Midtrans Payment SobatAnak

## 1. Import database update
Import file ini ke database yang sama:

`database/sobatanak_midtrans_update.sql`

Database tetap `sobatanak_db`. Tidak perlu bikin database baru. File SQL ini akan membuat/menyesuaikan:

- `orders`
- `order_items`
- kolom metode pembayaran Midtrans di tabel `orders`

Metode yang sudah disiapkan di checkout:

- Semua metode aktif Midtrans
- Semua Virtual Account
- BCA VA
- BNI VA
- BRI VA
- Permata VA
- Mandiri Bill Payment / e-channel
- CIMB Niaga VA
- Danamon VA
- BSI VA
- Bank lainnya
- QRIS
- GoPay
- ShopeePay
- Alfamart / Indomaret
- Kartu kredit / debit

Catatan: metode yang benar-benar muncul tetap mengikuti payment channel yang aktif di dashboard Midtrans kamu. Untuk mode Sandbox biasanya metode testing tersedia lebih lengkap.

## 2. Isi `.env`
Tambahkan/isi key berikut di file `.env` lokal kamu:

```env
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxxxxxxxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxxxxxxxxxx
MIDTRANS_MERCHANT_ID=Gxxxxxxxxx
MIDTRANS_IS_PRODUCTION=false
```

Untuk production nanti ubah:

```env
MIDTRANS_IS_PRODUCTION=true
```

Lalu jalankan:

```bash
php artisan config:clear
php artisan cache:clear
```

## 3. Jalankan flow
1. Login user.
2. Tambah produk ke cart.
3. Masuk `/cart`.
4. Klik `Checkout Sekarang`.
5. Isi alamat.
6. Pilih metode bayar, misalnya BCA VA, QRIS, GoPay, ShopeePay, atau Semua Metode Aktif.
7. Klik `Buat Order & Lanjut Bayar`.
8. Klik `Bayar dengan Midtrans`.
9. Selesaikan pembayaran di popup Snap.

## 4. Callback / Notification URL Midtrans
Masukkan URL ini di dashboard Midtrans:

```text
https://DOMAIN-KAMU.com/midtrans/notification
```

Kalau masih lokal, pakai ngrok, contoh:

```text
https://xxxx.ngrok-free.app/midtrans/notification
```

Setelah ngrok aktif, pastikan `APP_URL` di `.env` juga pakai URL ngrok agar callback finish dan tombol redirect benar:

```env
APP_URL=https://xxxx.ngrok-free.app
```

Lalu jalankan ulang:

```bash
php artisan config:clear
```

## 5. Catatan stok
Stok produk baru dikurangi setelah Midtrans mengirim status sukses (`settlement` atau `capture` yang aman). Kalau status masih pending, stok belum dikurangi.

## 6. Jika payment method tertentu tidak muncul
Buka dashboard Midtrans, lalu cek menu Snap Preferences / Payment Channels. Aktifkan channel yang kamu mau, misalnya QRIS, GoPay, ShopeePay, VA bank, atau retail.
