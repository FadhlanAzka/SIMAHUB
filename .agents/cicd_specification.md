# Spesifikasi dan Panduan Replikasi CI/CD (GitHub Actions ke cPanel Non-SSH)

Dokumen ini merangkum seluruh arsitektur, alur kerja, spesifikasi teknis, serta panduan replikasi pipeline **Continuous Integration (CI)** dan **Continuous Deployment (CD)** pada aplikasi SIMAHUB.

---

## 1. Ikhtisar & Filosofi Arsitektur

Pipeline CI/CD pada aplikasi ini dirancang khusus untuk mengatasi tantangan lingkungan **Shared Hosting / cPanel yang TIDAK memiliki akses SSH atau Terminal**.

### Karakteristik Utama Arsitektur:
1. **Pemisahan Folder Aplikasi (Dual-Directory Split / Opsi B):**
   - Core Laravel diletakkan di luar root web publik (`/home/[user]/simahub_app/`) demi keamanan kode sumber dan file sensitif (`.env`, `vendor`, `database`).
   - Aset statis & file publik diletakkan di dalam subfolder web publik (`/home/[user]/public_html/simahub/`).
   - Dijembatani oleh file `index.php` kustom yang mendaftarkan custom path `usePublicPath()`.
2. **Build di Cloud (GitHub Runners):**
   - Kompilasi aset Vite (JS/CSS) dan resolusi dependensi Composer (`--no-dev --optimize-autoloader`) dilakukan langsung di GitHub Runner (Ubuntu 22.04/24.04).
   - Server hosting menerima file yang sudah terkompilasi dan siap jalan tanpa memerlukan Node.js atau Composer di server.
3. **Sinkronisasi Cerdas via FTPS (FTP over SSL):**
   - Menggunakan `SamKirkland/FTP-Deploy-Action@v4.3.5`.
   - File di-upload secara inkremental (hanya file yang berubah), memangkas waktu deploy rutin dari ~30 menit menjadi 1–2 menit.
4. **Artisan via Secure Webhook Post-Deploy (`/_deploy`):**
   - Karena server tidak memiliki terminal SSH, perintah migrasi database (`php artisan migrate --force`), seeding, dan manajemen cache dijalankan via HTTP POST endpoint internal yang dilindungi token rahasia dengan validasi `hash_equals()`.

---

## 2. Spesifikasi Teknis Workflow CI/CD

Pipeline terdiri dari dua file workflow utama di dalam folder `.github/workflows/`:

```
.github/
└── workflows/
    ├── ci.yml        # Otomasi pengujian & validasi kode (CI)
    └── deploy.yml    # Otomasi rilis & deployment ke server cPanel (CD)
```

---

### 2.1. Continuous Integration (`ci.yml`)

- **Nama Workflow:** `CI`
- **Trigger Event:** `push` pada branch:
  - `staging`
  - `main`
  - `dev/*` (misal: `dev/padlan`, `dev/akbar`)
- **Struktur Job:**

#### Job 1: `test-sqlite` (Pengujian Cepat & Linting)
- **Runner:** `ubuntu-latest`
- **Environment:** PHP 8.3 & Node.js 20
- **Langkah-Langkah:**
  1. `actions/checkout@v4`
  2. Setup PHP 8.3 dengan ekstensi lengkap (`dom, curl, libxml, mbstring, zip, pcntl, pdo, sqlite3, pdo_sqlite, bcmath, soap, intl, gd, exif, iconv, imagick, fileinfo`).
  3. Setup Node.js 20.
  4. `composer install --prefer-dist --no-progress`
  5. **Code Style Check:** `vendor/bin/pint --test` (Laravel Pint memastikan kode rapi sesuai standar PSR-12/Laravel).
  6. `npm ci` dan `npm run build` (Memastikan aset frontend bebas dari error kompilasi Vite).
  7. Menyiapkan `.env` dari `.env.example` dan generate `APP_KEY`.
  8. **Unit & Feature Testing:** Menjalankan `php artisan test` dengan database in-memory:
     - `APP_ENV=testing`
     - `DB_CONNECTION=sqlite`
     - `DB_DATABASE=:memory:`

#### Job 2: `test-mysql` (Pengujian Kompatibilitas Database Riil)
- **Runner:** `ubuntu-latest`
- **Service Container:** `mysql:8.0` (Port 3306, database: `simahub_test`, user: `root`, password: `password`).
- **Langkah-Langkah:**
  1. Setup PHP 8.3 dengan ekstensi `pdo_mysql`.
  2. `composer install`.
  3. `php artisan migrate:fresh --seed` terhadap service container MySQL riil untuk memvalidasi seluruh script migrasi dan database seeder sebelum kode digabung.

---

### 2.2. Continuous Deployment (`deploy.yml`)

- **Nama Workflow:** `Deploy to Production`
- **Trigger Event:** `workflow_dispatch` (Manual dispatch melalui tab Actions di GitHub).
- **Input Parameters:**
  - `seed` (Boolean, default: `false`): Pemicu eksekusi `ProductionSeeder` saat deploy pertama kali.
- **Environment:** `production`
- **Security Guard:** `if: github.ref == 'refs/heads/main'` (Mencegah deployment tidak sengaja dari branch selain `main`).
- **Langkah-Langkah Eksekusi:**
  1. **Checkout & Dependencies:**
     - Checkout branch `main`.
     - Setup PHP 8.3 & Node.js 20.
     - Install composer produksi: `composer install --prefer-dist --no-interaction --no-dev --optimize-autoloader`.
     - Install npm & build frontend: `npm ci && npm run build` (disertai injeksi `ASSET_URL=${{ secrets.ASSET_URL }}`).
  2. **Deploy Core Application (`simahub_app`):**
     - Menggunakan `SamKirkland/FTP-Deploy-Action@v4.3.5` via protokol `ftps`.
     - Target: Akun FTP `FTP_USERNAME_APP` yang mengarah ke `/home/[user]/simahub_app`.
     - Filter Exclude:
       ```yaml
       exclude: |
         **/.git/**
         .github/**
         .gitattributes
         **/node_modules/**
         tests/**
         .env
         .env.*
         README.md
         GIT_WORKFLOW.md
         DEPLOYMENT.md
         phpunit.xml
         serve.bat
         storage/logs/*.log
         storage/framework/sessions/*
         storage/framework/views/*.php
         storage/framework/cache/data/**
         public/**
         !storage/framework/sessions/.gitignore
       ```
  3. **Deploy Public HTML Assets (`public_html/simahub`):**
     - Menggunakan `FTP-Deploy-Action` via `ftps`.
     - Local directory: `./public/`
     - Target: Akun FTP `FTP_USERNAME_PUBLIC` yang mengarah ke `/home/[user]/public_html/simahub`.
     - Filter Exclude:
       ```yaml
       exclude: |
         **/.git/**
         index.php
         storage
         !build/**
       ```
       *(Catatan: `index.php` dan `storage` dikecualikan agar file jembatan custom dan symlink cPanel tidak tertimpa).*
  4. **Post-Deploy Webhook Execution:**
     - Mengirim HTTP POST request ke `${{ secrets.ASSET_URL }}/_deploy`.
     - Mengirim payload dummy `--data "source=github-actions"` (Krusial: web server cPanel sering menolak request POST tanpa header `Content-Length`).
     - Mengirim header otentikasi `X-Deploy-Token: ${{ secrets.DEPLOY_TOKEN }}`.
     - Mengirim header opsional `X-Deploy-Seed: 1` jika input `seed` bernilai `true`.
     - Memvalidasi HTTP status code `200` dan menampilkan output JSON log migrasi.

---

### 2.3. Endpoint Post-Deploy Internal (`routes/web.php`)

Endpoint internal dibuat khusus untuk mengeksekusi perintah Artisan tanpa terminal SSH:

```php
Route::post('/_deploy', function (Request $request) {
    $token = config('app.deploy_token');

    // 1. Validasi Token Anti Timing-Attack
    if (! $token || ! hash_equals($token, (string) $request->header('X-Deploy-Token', ''))) {
        abort(403, 'Unauthorized');
    }

    try {
        // 2. Jalankan Migrasi Database
        Artisan::call('migrate', ['--force' => true]);
        $migrateOutput = Artisan::output();

        // 3. Jalankan Seeder Produksi (Hanya jika diminta)
        $seedOutput = null;
        if ($request->header('X-Deploy-Seed') === '1') {
            Artisan::call('db:seed', [
                '--class' => ProductionSeeder::class,
                '--force' => true,
            ]);
            $seedOutput = Artisan::output();
        }

        // 4. Manajemen Cache Aplikasi
        Artisan::call('config:cache');
        Artisan::call('event:cache');
        Artisan::call('view:cache');
        Artisan::call('route:clear'); // Hindari route:cache via web request untuk mencegah korupsi cache
        $optimizeOutput = 'Config, event, view cached. Route cache cleared.';
    } catch (Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }

    return response()->json([
        'status' => 'success',
        'migrate' => $migrateOutput,
        'seed' => $seedOutput,
        'optimize' => $optimizeOutput,
    ]);
})->withoutMiddleware('web');
```

> **Catatan Teknis:** Pemanggilan `->withoutMiddleware('web')` sangat penting agar request dari curl tidak dicegat oleh verifikasi CSRF token, dan tetap dapat dieksekusi saat deploy pertama kali sebelum tabel sesi/cache database terbentuk.

---

## 3. Matriks GitHub Secrets

Konfigurasikan secrets ini di GitHub pada menu **Settings > Environments > production**:

| Nama Secret | Deskripsi | Contoh Nilai |
|---|---|---|
| `FTP_SERVER` | Hostname / IP Server FTP cPanel | `ftp.domainanda.go.id` atau `cpanelXX.namahosting.com` |
| `FTP_USERNAME_APP` | Akun FTP khusus direktori core aplikasi | `simahub_app@domainanda.go.id` |
| `FTP_PASSWORD_APP` | Password akun FTP Core App | `k0mP1ek5_P@ssw0rd!` |
| `FTP_USERNAME_PUBLIC`| Akun FTP khusus direktori web publik | `simahub_pub@domainanda.go.id` |
| `FTP_PASSWORD_PUBLIC`| Password akun FTP Public | `k0mP1ek5_P@ssw0rd2!` |
| `ASSET_URL` | URL absolut endpoint publik aplikasi (tanpa trailing slash) | `https://domainanda.go.id/simahub` |
| `DEPLOY_TOKEN` | Token acak 64 karakter (harus sama persis dengan `.env` server) | `b3f88d...92e1` |

---

## 4. Panduan Langkah demi Langkah Mereplikasi

Ikuti tahapan berikut untuk mengadopsi sistem CI/CD ini ke proyek Laravel baru atau server cPanel lainnya:

### Tahap 1: Konfigurasi di cPanel Server
1. **Struktur Folder:**
   - Masuk ke **File Manager** cPanel.
   - Buat folder aplikasi core di root home: `/home/[user]/[nama_app]_app/`.
   - Buat folder publik di dalam public_html: `/home/[user]/public_html/[subfolder_app]/` (atau gunakan root `public_html` jika aplikasi berada di domain utama).
2. **Akun FTP (2 Akun Terpisah):**
   - Buat Akun FTP 1 (App): Path direktori wajib diarahkan ke `/home/[user]/[nama_app]_app`.
   - Buat Akun FTP 2 (Public): Path direktori wajib diarahkan ke `/home/[user]/public_html/[subfolder_app]`.
3. **Database:**
   - Buat database MySQL dan user MySQL baru di cPanel, berikan hak akses penuh (*All Privileges*).
4. **File Konfigurasi `.env` Server:**
   - Buat file `.env` di dalam folder `/home/[user]/[nama_app]_app/.env`.
   - Isi konfigurasi database, `APP_KEY`, dan buat token deploy acak:
     ```bash
     # Generate token lokal:
     php -r "echo bin2hex(random_bytes(32));"
     ```
   - Masukkan token tersebut ke `DEPLOY_TOKEN="isi_token_disini"`.
5. **File Jembatan `index.php`:**
   - Buat file `index.php` di dalam `/home/[user]/public_html/[subfolder_app]/index.php`:
     ```php
     <?php
     use Illuminate\Foundation\Application;
     use Illuminate\Http\Request;

     define('LARAVEL_START', microtime(true));

     if (file_exists($maintenance = __DIR__.'/../../[nama_app]_app/storage/framework/maintenance.php')) {
         require $maintenance;
     }

     require __DIR__.'/../../[nama_app]_app/vendor/autoload.php';
     $app = require_once __DIR__.'/../../[nama_app]_app/bootstrap/app.php';

     // Arahkan path public ke lokasi file ini
     $app->usePublicPath(__DIR__);

     $app->handleRequest(Request::capture());
     ```
6. **Symlink Storage (Opsional / Jika Diperlukan):**
   - Jika aplikasi membutuhkan public disk storage, buat Cron Job sekali jalan di cPanel (per menit):
     ```bash
     ln -sfn /home/[user]/[nama_app]_app/storage/app/public /home/[user]/public_html/[subfolder_app]/storage
     ```
   - Setelah folder symlink muncul, **hapus kembali** Cron Job tersebut.

---

### Tahap 2: Konfigurasi di Repositori Laravel
1. Daftarkan `deploy_token` pada `config/app.php`:
   ```php
   'deploy_token' => env('DEPLOY_TOKEN', null),
   ```
2. Tambahkan route endpoint `/_deploy` di `routes/web.php` (sesuai spesifikasi pada Bagian 2.3).
3. Buat file `.github/workflows/ci.yml` dan `.github/workflows/deploy.yml`.
4. Sesuaikan nama folder, konfigurasi PHP/Node.js, dan path `exclude` sesuai kebutuhan proyek.

---

### Tahap 3: Konfigurasi GitHub Repository
1. Masuk ke **Settings > Environments** di repositori GitHub.
2. Tambahkan Environment bernama `production`.
3. Daftarkan ketujuh secrets sesuai tabel pada Bagian 3.
4. Buat branch protection rules di GitHub untuk branch `main` dan `staging` agar wajib melewati pull request dan status check CI hijau.

---

### Tahap 4: Prosedur Rilis & Deploy
1. **Deploy Pertama Kali:**
   - Masuk ke tab **Actions** > **Deploy to Production** > **Run workflow**.
   - Pilih branch `main`.
   - Centang opsi `seed` jika ingin membuat user/konfigurasi awal dari seeder.
   - *Catatan:* Deploy pertama membutuhkan waktu 10–30 menit karena mengunggah seluruh isi folder `vendor/`.
2. **Deploy Update Rutin:**
   - Setelah PR di-merge ke branch `main`, jalankan kembali workflow **Deploy to Production**.
   - **Jangan** centang opsi `seed`.
   - Proses update hanya memakan waktu 1–2 menit karena hanya mengunggah file yang diperbarui dan menjalankan migrasi otomatis.

---

## 5. Kumpulan Prompt Siap Pakai untuk AI Agent

Berikut adalah kumpulan prompt terstruktur yang dapat langsung disalin-tempelkan ke AI coding agent (seperti Antigravity, Claude, ChatGPT, atau Cursor) untuk mereplikasi pipeline ini ke proyek Laravel lain.

---

### Prompt 1: Replikasi Menyeluruh (Full Pipeline Scaffold)

```markdown
Saya memiliki proyek Laravel 11 (PHP 8.3 + Vite) dan ingin membangun sistem CI/CD otomatis ke server cPanel shared hosting yang TIDAK memiliki akses SSH/Terminal.

Tolong implementasikan arsitektur CI/CD berikut pada proyek ini:

1. Buat workflow CI (.github/workflows/ci.yml):
   - Trigger pada push ke branch 'main', 'staging', dan 'dev/*'.
   - Job 1 (test-sqlite): Setup PHP 8.3, Node.js 20, composer install, linting dengan Laravel Pint ('vendor/bin/pint --test'), npm run build, dan eksekusi 'php artisan test' menggunakan SQLite in-memory database.
   - Job 2 (test-mysql): Gunakan service container MySQL 8.0, setup PHP 8.3 dengan pdo_mysql, lalu jalankan 'php artisan migrate:fresh --seed' untuk menguji migrasi database.

2. Buat workflow CD (.github/workflows/deploy.yml):
   - Trigger manual via 'workflow_dispatch' dengan input boolean 'seed' (default: false).
   - Dibatasi hanya berjalan pada branch 'main' dengan environment 'production'.
   - Build frontend dengan Vite (passing environment variable ASSET_URL).
   - Install composer produksi (--no-dev --optimize-autoloader).
   - Sync file menggunakan action 'SamKirkland/FTP-Deploy-Action@v4.3.5' via protokol ftps.
   - Pisahkan sync menjadi 2 langkah:
     a. Sinkronisasi Core App ke folder root luar publik dengan exclude node_modules, tests, .env, storage cache/logs, dan folder public.
     b. Sinkronisasi Public Files dari folder ./public ke folder publik cPanel dengan exclude index.php dan storage link.
   - Panggil webhook post-deploy menggunakan curl HTTP POST ke endpoint '{ASSET_URL}/_deploy' membawa header 'X-Deploy-Token' dan data payload 'source=github-actions' untuk mencegah error 404/Content-Length pada cPanel.

3. Tambahkan Webhook Post-Deploy di Laravel (routes/web.php):
   - Buat route POST '/_deploy' dengan '->withoutMiddleware('web')'.
   - Validasi header 'X-Deploy-Token' terhadap config('app.deploy_token') menggunakan timing-attack-safe 'hash_equals'.
   - Jalankan 'Artisan::call('migrate', ['--force' => true])'.
   - Jika header 'X-Deploy-Seed' == '1', jalankan seeder produksi secara kondisional.
   - Jalankan cache optimizer: config:cache, event:cache, view:cache, dan route:clear.
   - Kembalikan respons dalam format JSON.

4. Daftarkan konfigurasi:
   - Tambahkan 'deploy_token' => env('DEPLOY_TOKEN', null) di config/app.php.

5. Buat file dokumentasi DEPLOYMENT.md yang memuat panduan setup cPanel, struktur folder dual-directory split, template file index.php jembatan untuk cPanel, dan daftar GitHub Secrets yang dibutuhkan.
```

---

### Prompt 2: Setup GitHub Workflows CI & CD Saja

```markdown
Tolong buatkan 2 file workflow GitHub Actions untuk aplikasi Laravel yang di-deploy ke cPanel tanpa SSH:

1. '.github/workflows/ci.yml':
   - Otomatis berjalan saat push ke branch 'main', 'staging', dan pattern 'dev/*'.
   - Menjalankan 2 jobs paralel di Ubuntu:
     a. SQLite Testing: PHP 8.3, Node.js 20, composer install, vendor/bin/pint --test, npm ci && npm run build, dan php artisan test (DB in-memory).
     b. MySQL Testing: Memakai service container mysql:8.0, menjalankan php artisan migrate:fresh --seed untuk memastikan migrasi MySQL valid.

2. '.github/workflows/deploy.yml':
   - Berjalan via workflow_dispatch (manual trigger) khusus dari branch 'main'.
   - Input: 'seed' (boolean, default false).
   - Environment: 'production'.
   - Steps:
     - Setup PHP 8.3 & Composer install (--no-dev --optimize-autoloader).
     - Setup Node 20 & npm run build dengan env ASSET_URL=${{ secrets.ASSET_URL }}.
     - Upload Core App ke FTP_USERNAME_APP menggunakan SamKirkland/FTP-Deploy-Action@v4.3.5 (FTPS).
     - Upload Public HTML ke FTP_USERNAME_PUBLIC menggunakan action yang sama.
     - Post-deploy webhook trigger via curl POST ke ${{ secrets.ASSET_URL }}/_deploy dengan header X-Deploy-Token: ${{ secrets.DEPLOY_TOKEN }} dan form data 'source=github-actions'.
```

---

### Prompt 3: Setup Bridge & Webhook Endpoint Laravel Saja

```markdown
Saya perlu membuat sistem post-deploy webhook dan split-directory bridge untuk Laravel 11 yang di-hosting di cPanel tanpa terminal SSH.

Tolong buatkan:
1. Endpoint Post-Deploy di 'routes/web.php':
   - URL: POST '/_deploy' tanpa middleware web (agar tidak terhambat CSRF dan session database).
   - Proteksi otentikasi menggunakan header 'X-Deploy-Token' yang dicocokkan dengan config('app.deploy_token') memakai hash_equals().
   - Menjalankan:
     - Artisan::call('migrate', ['--force' => true])
     - Artisan::call('db:seed', ['--class' => ProductionSeeder::class, '--force' => true]) HANYA jika header 'X-Deploy-Seed' bernilai '1'.
     - Artisan::call('config:cache')
     - Artisan::call('event:cache')
     - Artisan::call('view:cache')
     - Artisan::call('route:clear')
   - Tangani error dengan try-catch dan kembalikan JSON status 200/500 beserta output Artisan.

2. Konfigurasi di 'config/app.php' untuk membaca 'deploy_token' => env('DEPLOY_TOKEN', null).

3. Kode untuk file 'index.php' yang akan ditempatkan di public_html cPanel, yang berfungsi memanggil bootstrap Laravel dari folder core di luar public_html dan mengatur '$app->usePublicPath(__DIR__)'.
```

---

## 6. Tips & Troubleshooting Khusus cPanel Non-SSH

1. **Error HTTP 404 / 411 pada curl Post-Deploy:**
   - *Penyebab:* Sebagian firewall / reverse proxy cPanel (ModSecurity / Nginx / LiteSpeed) menolak request POST yang tidak memiliki header `Content-Length`.
   - *Solusi:* Selalu sertakan argumen data dummy pada curl, contoh: `--data "source=github-actions"`.
2. **Error Sertifikat TLS saat Upload FTP:**
   - *Penyebab:* Nama host `FTP_SERVER` (misal `ftp.domain.com`) tidak terdaftar dalam sertifikat SSL server cPanel.
   - *Solusi:* Gunakan nama host server cPanel utama (misal `cpanelXX.namaprovider.com`) sebagai nilai secret `FTP_SERVER`.
3. **Route Cache Korupsi (`route:cache` via HTTP):**
   - *Penyebab:* Menjalankan `Artisan::call('route:cache')` di dalam request HTTP web dapat menyebabkan route ter-serialize dalam konteks HTTP runner tertentu dan berpotensi gagal me-resolve closure atau route controller.
   - *Solusi:* Gunakan `Artisan::call('route:clear')` pada script deployment, dan biarkan route di-resolve secara dinamis atau gunakan route berbasis controller penuh jika ingin di-cache.
4. **File Vendor Hilang / Lambat:**
   - Folder `vendor/` di-upload langsung oleh GitHub Action karena server tidak memiliki Composer. Ini normal pada deploy pertama (10–30 menit), namun pada deploy kedua dan seterusnya FTP action hanya akan menyinkronkan package yang mengalami perubahan versi.
