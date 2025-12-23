# Panduan Deployment Docker (FrankenPHP)

Ikuti langkah-langkah berikut untuk menjalankan aplikasi di server Anda setelah melakukan `git clone`.

### 1. Persiapan Environment
Salin file `.env.example` menjadi `.env` dan sesuaikan konfigurasinya (Database, APP_URL, dll).
```bash
cp .env.example .env
```

### 2. Build dan Jalankan Container
Gunakan Docker Compose untuk membuild image dan menjalankan service di background.
```bash
docker compose up -d --build
```

### 3. Install Dependencies
Jalankan composer install di dalam container untuk menginstall library yang dibutuhkan.
```bash
docker compose exec surat-franken composer install --no-dev --optimize-autoloader
```

### 4. Generate Application Key
Generate key baru untuk keamanan aplikasi.
```bash
docker compose exec surat-franken php artisan key:generate
```

### 5. Jalankan Migrasi dan Seeding
Siapkan struktur database dan data klasifikasi awal.
```bash
docker compose exec surat-franken php artisan migrate --force
```

### 6. Set Permission Storage
Pastikan folder storage dan cache bisa ditulis oleh web server (www-data).
```bash
docker compose exec surat-franken chown -R www-data:www-data storage bootstrap/cache
docker compose exec surat-franken chmod -R 775 storage bootstrap/cache
```

### 7. Optimasi (Opsional tapi Disarankan)
Jalankan perintah optimasi untuk mempercepat loading aplikasi di production.
```bash
docker compose exec surat-franken php artisan config:cache
docker compose exec surat-franken php artisan route:cache
docker compose exec surat-franken php artisan view:cache
```

---
**Catatan:**
- Pastikan port `8080` di server Anda sudah dibuka atau sesuaikan di `docker-compose.yml`.
- Jika menggunakan SQLite (default), pastikan file `database/database.sqlite` sudah ada sebelum migrasi (bisa dibuat dengan `touch database/database.sqlite`).
