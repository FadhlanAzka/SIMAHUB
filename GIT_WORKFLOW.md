# Standar Operasional Prosedur (SOP) Git Workflow - SIMAHUB

Dokumen ini mengatur alur kerja dan pengelolaan kolaborasi kode (*version control*) untuk proyek SIMAHUB menggunakan Git dan GitHub.

## 1. Struktur Branch (Cabang)
*   **`main`**: Merupakan *branch production*. Berisi kode yang sudah final, stabil, dan siap atau sedang digunakan oleh pengguna (*live*).
*   **`staging`**: Merupakan *branch* integrasi dan tahap pengujian (*pre-production*). Semua fitur baru dari para *developer* digabungkan ke sini untuk diuji bersama.
*   **`dev/[nama-developer]`**: Merupakan *branch* kerja pribadi masing-masing *developer* (contoh: `dev/padlan`, `dev/akbar`). Segala bentuk *coding* harian wajib dilakukan di *branch* masing-masing.

## 2. Aturan Pembatasan Akses (Access Restriction)
1.  **Dilarang Push Langsung (No Direct Pushes):** Tidak ada *developer* (termasuk Admin) yang diizinkan untuk melakukan `git push` secara langsung ke *branch* `main` dan `staging`.
2.  **Batas Ruang Kerja:** Setiap *developer* hanya diperbolehkan melakukan *push* ke *branch* pribadi mereka masing-masing.
3.  **Metode Penggabungan (Merge):**
    *   **Dari Dev ke Staging:** Wajib melalui **Pull Request (PR)** di GitHub. Kode akan digabungkan setelah lolos tinjauan (*code review*).
    *   **Dari Staging ke Main:** Wajib melalui **Pull Request (PR)** dengan metode *Merge Commit* saat rilis. *Staging* dilarang melakukan aksi *push* sepihak ke `main`.

## 3. Alur Kerja Harian (Daily Workflow)

### A. Memulai Fitur Baru / Bekerja Harian
1. Pastikan Anda berada di *branch* Anda (misal: `dev/padlan`):
   ```bash
   git checkout dev/padlan
   ```
2. Selalu tarik kode terbaru dari `staging` agar fondasi kode Anda up-to-date:
   ```bash
   git pull origin staging
   ```
3. Lakukan *coding*, lalu *commit* perubahan Anda:
   ```bash
   git add .
   git commit -m "feat: deskripsi fitur yang dibuat"
   ```
4. *Push* ke *branch* Anda di server:
   ```bash
   git push origin dev/padlan
   ```

### B. Menggabungkan Kode ke Staging (Uji Coba)
1. Buka repositori SIMAHUB di GitHub.
2. Buat **Pull Request** baru.
   * **Base:** `staging`
   * **Compare:** `dev/padlan`
3. Minta anggota tim lain untuk meninjau (*review*).
4. Jika disetujui (Approved), klik **Merge Pull Request**.

### C. Rilis ke Production (Main)
*Syarat: Kode di staging sudah stabil dan teruji tanpa bug.*
1. Buka repositori SIMAHUB di GitHub.
2. Buat **Pull Request** baru.
   * **Base:** `main`
   * **Compare:** `staging`
3. Klik **Merge Pull Request** (Pilih opsi *Create a merge commit* agar riwayat rilis tercatat rapi).