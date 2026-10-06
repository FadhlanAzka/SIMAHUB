# Panduan Deployment SIMAHUB ke cPanel

Dokumen ini memuat langkah-langkah *setup* pertama kali dan panduan deployment ke server cPanel yang **tidak memiliki akses SSH/Terminal**. Arsitektur yang digunakan adalah **Opsi B (Subfolder public_html)**.

## 1. Arsitektur Folder di Server

Aplikasi akan dibagi menjadi dua bagian:
- `/home/[user]/simahub_app/` → Berisi seluruh *core* aplikasi (vendor, config, resources, app, dll). File ini tidak bisa diakses langsung dari web.
- `/home/[user]/public_html/simahub/` → Berisi file publik (hasil build Vite, gambar, css, js). File ini yang diakses pengguna.

## 2. Persiapan Server (Sekali Saja)

1. **Buat Folder di File Manager cPanel:**
   - Buat folder `simahub_app` sejajar dengan folder `public_html` (di root `/home/[user]/`).
   - Buat folder `simahub` di dalam folder `public_html/`.

2. **Buat Akun FTP:**
   - **Akun App:** Buat akun FTP baru (misal: `app_simahub@domain.com`). Set *Directory* ke `/home/[user]/simahub_app`.
   - **Akun Public:** Buat akun FTP baru (misal: `public_simahub@domain.com`). Set *Directory* ke `/home/[user]/public_html/simahub`.

3. **Buat Database:**
   - Buka menu *MySQL Databases*. Buat database baru (misal: `db_simahub`).
   - Buat user baru, berikan password, lalu *Add User to Database* (centang *All Privileges*).

4. **Buat File `.env` di `/home/[user]/simahub_app/.env`:**
   Buat file baru, lalu copy isi `.env.example` dari repo (file ini tidak ikut ter-upload) dan ubah nilainya menjadi:
   ```env
   APP_NAME="SIMAHUB"
   APP_ENV=production
   APP_KEY="base64:TULIS_APP_KEY_YANG_VALID_DISINI"
   APP_DEBUG=false
   APP_URL=https://domain.go.id/simahub
   ASSET_URL=https://domain.go.id/simahub

   LOG_LEVEL=error

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=nama_database_yang_dibuat
   DB_USERNAME=user_database_yang_dibuat
   DB_PASSWORD=password_database_yang_dibuat

   DEPLOY_TOKEN="BUAT_TOKEN_ACAK_PANJANG_DISINI_TANPA_SPASI"

   # Password awal akun admin & admins (dipakai ProductionSeeder saat deploy pertama)
   SEED_ADMIN_PASSWORD="PASSWORD_KUAT_SEMENTARA"
   ```
   *(Penting: Samakan nilai `DEPLOY_TOKEN` dengan yang ada di GitHub Secrets).*

   **Cara membuat `APP_KEY` dan `DEPLOY_TOKEN` (di komputer lokal, karena server tanpa SSH):**
   ```bash
   php artisan key:generate --show      # hasil: base64:xxxx  → tempel ke APP_KEY
   php -r "echo bin2hex(random_bytes(32));"   # hasil: 64 karakter hex → tempel ke DEPLOY_TOKEN
   ```
   *`APP_KEY` jangan pernah diganti setelah aplikasi live (sesi & data terenkripsi akan rusak).*

5. **Buat File `index.php` Khusus di `/home/[user]/public_html/simahub/index.php`:**
   Ini berfungsi sebagai jembatan dari folder publik ke folder *core* aplikasi.
   Buat file baru bernama `index.php`, lalu isikan:
   ```php
   <?php
   use Illuminate\Foundation\Application;
   use Illuminate\Http\Request;

   define('LARAVEL_START', microtime(true));

   if (file_exists($maintenance = __DIR__.'/../../simahub_app/storage/framework/maintenance.php')) {
       require $maintenance;
   }

   require __DIR__.'/../../simahub_app/vendor/autoload.php';
   $app = require_once __DIR__.'/../../simahub_app/bootstrap/app.php';

   // Beritahu Laravel bahwa folder public ada di sini (bukan di dalam simahub_app/public)
   $app->usePublicPath(__DIR__);

   $app->handleRequest(Request::capture());
   ```

6. **(Opsional) Buat Symlink Storage lewat Cron Job:**
   > Saat ini SIMAHUB **belum memakai** disk `public` (tidak ada upload file yang ditampilkan ke publik), jadi langkah ini boleh dilewati. Lakukan hanya jika nanti ada fitur upload foto/dokumen yang perlu diakses lewat URL.

   Karena aplikasi dan folder publik dipisah, perintah `php artisan storage:link` standar akan salah sasaran. Gunakan perintah `ln` bawaan Linux lewat menu **Cron Jobs** di cPanel:
   - Tambahkan Cron Job baru, set waktunya ke *Once Per Minute* (bintang semua).
   - Command: `ln -sfn /home/[user]/simahub_app/storage/app/public /home/[user]/public_html/simahub/storage`
     *(flag `-sfn` membuat perintah aman walau sempat berjalan lebih dari sekali)*
   - Simpan, tunggu 1-2 menit hingga folder `storage` dengan ikon panah (symlink) muncul di dalam `public_html/simahub`.
   - **HAPUS kembali Cron Job tersebut** agar tidak berjalan terus-menerus.

## 3. Konfigurasi GitHub Secrets

Di repositori GitHub, buka **Settings > Environments**.
Buat Environment baru dengan nama `production`. Tambahkan *Environment Secrets*:

| Nama Secret | Contoh Nilai |
|---|---|
| `FTP_SERVER` | `ftp.domain.go.id` |
| `FTP_USERNAME_APP` | Akun FTP untuk folder `simahub_app` |
| `FTP_PASSWORD_APP` | Password akun FTP App |
| `FTP_USERNAME_PUBLIC` | Akun FTP untuk folder `public_html/simahub` |
| `FTP_PASSWORD_PUBLIC` | Password akun FTP Public |
| `ASSET_URL` | `https://domain.go.id/simahub` (tanpa slash di akhir) |
| `DEPLOY_TOKEN` | Token rahasia yang persis sama dengan di `.env` server |

## 4. Cara Deploy

1. Pastikan semua perubahan sudah di-merge ke branch `main`.
2. Buka tab **Actions** → workflow **CI** → pastikan run terakhir untuk branch `main` berstatus **hijau**. *(Workflow deploy tidak otomatis menunggu CI, jadi cek ini manual.)*
3. Buka workflow **Deploy to Production**.
4. Klik tombol **Run workflow**, pilih branch `main`. *(Jika dijalankan dari branch lain, job akan berstatus **skipped**/abu-abu, bukan error.)*
5. Tunggu proses selesai. Jika hijau (Success), maka kode, migrasi, dan *build* frontend sudah berhasil diterapkan ke server cPanel. Output migrasi bisa dilihat di log step **Execute Post-Deploy Commands**.

### Catatan Deploy Pertama
- Saat klik **Run workflow** pertama kali, **centang opsi `seed`**. Ini menjalankan `ProductionSeeder` yang membuat pengaturan kantor default + akun `admin` dan `admins` dengan password dari `SEED_ADMIN_PASSWORD`. Data intern/mentor dummy dan absensi contoh **tidak** ikut dibuat.
- Seeder ini aman jika tidak sengaja dicentang lagi: hanya membuat data yang belum ada, tidak menimpa pengaturan kantor dan tidak mereset password.
- Setelah berhasil login, **segera ganti password** kedua akun lewat menu Profil, lalu hapus baris `SEED_ADMIN_PASSWORD` dari `.env` server.
- Upload pertama akan **lama** (bisa 10–30+ menit) karena seluruh folder `vendor/` dikirim lewat FTP. Deploy berikutnya hanya mengirim file yang berubah.
- Jika step FTP gagal dengan error sertifikat TLS, kemungkinan `FTP_SERVER` tidak cocok dengan sertifikat server. Coba gunakan hostname server cPanel (lihat di cPanel → *FTP Accounts* → *Configure FTP Client*).
- Setelah sukses, buka `https://domain.go.id/simahub/up` → harus tampil halaman "Application up".
