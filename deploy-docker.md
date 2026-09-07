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

### CARA UPDATE (Jika ada perubahan code)
Dengan sistem *Mounting* yang baru, Anda tidak perlu melakukan build ulang setiap kali ada perubahan kecil di tampilan (CSS/Blade).

#### Menggunakan Skrip Otomatis (Direkomendasikan)
Cukup jalankan skrip deploy berikut:
```bash
./deploy.sh
```
Skrip ini secara cerdas akan:
1. Menjalankan `git pull` untuk mengambil commit terbaru.
2. Memeriksa apakah ada file konfigurasi (`Dockerfile`, `docker-compose.yml`, `Caddyfile`), dependensi (`composer.lock`, `package.json`), atau file aset Vite (`resources/css`, `resources/js`) yang berubah:
   - Jika **ada perubahan**: menjalankan `docker compose up -d --build`.
   - Jika **hanya kode PHP/Blade/migrasi**: melewati proses build dan hanya menjalankan `docker compose up -d`.
3. Menjalankan `php artisan optimize:clear`, `migrate --force`, dan caching optimasi (`config`, `route`, `view`).
4. Me-restart `queue-worker` agar memuat logic baru.
5. Memperbarui permission direktori `storage` dan `bootstrap/cache`.

Opsi tambahan:
- `./deploy.sh --build` : Memaksa build ulang Docker image kapan pun diperlukan.
- `./deploy.sh --skip-pull` : Menjalankan alur deploy tanpa menarik kode dari git.

---

#### Manual (Alternatif)
1. **Sinkronisasi Kode**:
   ```bash
   git pull origin main
   ```
2. **Bersihkan Cache & Migrasi (Jika ada perubahan database/logic)**:
   ```bash
   docker compose exec surat-franken php artisan optimize:clear
   docker compose exec surat-franken php artisan migrate --force
   ```
3. **Rebuild Image (Hanya jika mengubah Dockerfile atau dependensi PHP/Python)**:
   ```bash
   docker compose up -d --build
   ```

---
**Catatan:**
- Pastikan port `8080` di server Anda sudah dibuka atau sesuaikan di `docker-compose.yml`.
- Jika menggunakan SQLite (default), pastikan file `database/database.sqlite` sudah ada sebelum migrasi (bisa dibuat dengan `touch database/database.sqlite`).

