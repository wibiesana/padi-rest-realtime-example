# Aplikasi Demo CRUD Real-Time PADI

Aplikasi ini adalah demo aplikasi reaktif real-time berkinerja tinggi yang dibangun menggunakan **PADI PHP Framework** pada sisi backend dan **Quasar Framework (Vue 3)** pada sisi frontend. Aplikasi ini menyinkronkan data Postingan, Komentar, dan Tag secara instan ke seluruh browser pengguna yang terhubung tanpa perlu me-refresh halaman.

---

## 🚀 Fitur Utama
* **Real-time Sync**: Sinkronisasi data instan antar-klien menggunakan Server-Sent Events (SSE) yang ditenagai oleh **Mercure Hub** di FrankenPHP.
* **Manajemen CRUD Lengkap**: Dasbor interaktif untuk mengelola data **Posts**, **Comments**, dan **Tags**.
* **Autentikasi Aman**: Registrasi, Login dengan fitur **Remember Me**, dan autentikasi berbasis JWT Token.
* **Multi-Bahasa (i18n)**: Pengalih bahasa (English & Bahasa Indonesia) global pada navigasi header.
* **Aset Desain Premium**: Antarmuka gelap (*dark mode*) yang bersih berbasis kaca (*glassmorphism*) dengan animasi mikro yang mulus.

---

## 🛠️ Tumpukan Teknologi (Tech Stack)

### Backend
* **[PADI MVC Framework](https://padisoftware.my.id/)** (PHP 8.4+)
* **[FrankenPHP](https://frankenphp.dev/)** (Server Web modern berbasis Go dengan dukungan Mercure Hub bawaan)
* **Mercure Hub** (Protokol SSE untuk real-time update)
* **MySQL / MariaDB / SQLite** (Sebagai penyimpanan data utama)

### Frontend
* **Vue 3** (Composition API)
* **Quasar Framework v2** (Untuk komponen UI premium)
* **Pinia** (State Management)
* **Vue Router** & **Vue i18n** (Lokalisasi bahasa)

---

## 📋 Prasyarat Sistem
Pastikan komputer Anda sudah terinstal:
1. **PHP 8.4 atau lebih tinggi** (Opsional untuk menjalankan web server karena FrankenPHP sudah menyertakan PHP bawaan, tetapi diperlukan jika Anda ingin menjalankan perintah CLI lokal seperti `php padi migrate` secara langsung).
2. **Composer**
3. **Node.js** (LTS / v18+)
4. **FrankenPHP** (Sudah terpasang di sistem atau berada di PATH)
5. **MySQL Server** (Atau bisa menggunakan SQLite bawaan)

---

## ⚙️ Panduan Penggunaan secara Terurut

Ikuti langkah-langkah di bawah ini secara berurutan untuk menjalankan aplikasi di komputer lokal Anda:

### Langkah 1: Konfigurasi Environment Backend
1. Buka terminal baru dan masuk ke direktori `backend/`.
2. Jalankan perintah Setup Wizard interaktif berikut untuk membuat file `.env`, mengatur koneksi database, dan men-generate JWT keys secara otomatis:
   ```bash
   php padi init
   ```
3. Ikuti petunjuk interaktif di layar terminal Anda hingga selesai.

### Langkah 2: Jalankan Migrasi Database (Opsional)
Jika Anda **belum** memilih opsi untuk menjalankan migrasi database saat menjalankan wizard `php padi init` di Langkah 1, jalankan perintah berikut di direktori `backend/` untuk membuat tabel database:
```bash
php padi migrate
```

### Langkah 3: Generate Code (Opsional)
Untuk men-generate code/resource CRUD, jalankan perintah berikut di dalam direktori `backend/`:
```bash
php padi ga
```
> [!IMPORTANT]
> Pastikan Anda memilih opsi **realtime** saat diminta selama proses generate code.

### Langkah 4: Jalankan Web Server (FrankenPHP)
Di direktori root project utama, Anda dapat langsung menjalankan file batch untuk mengaktifkan server web FrankenPHP beserta Mercure Hub:
* **Mode Normal**: Jalankan file `init_frankenphp_normal_mode.bat`.
* **Mode Worker (Performa Tinggi)**: Jalankan file `init_frankenphp_worker_mode.bat`.

*Server backend kini dapat diakses di: `http://localhost:8085`*

### Langkah 5: Jalankan Queue Worker (Opsional)
Jika Anda mengonfigurasi model aplikasi di backend untuk menggunakan antrean (Queue) alih-alih metode Direct untuk memproses broadcast real-time:
Jalankan file batch `init_queue.bat` di root direktori untuk memproses pekerjaan di latar belakang.

### Langkah 6: Setup & Jalankan Frontend
1. Buka terminal baru dan masuk ke direktori `frontend/`.
2. Instal semua dependensi Node.js:
   ```bash
   npm install
   ```
3. Jalankan server pengembangan frontend Quasar:
   ```bash
   npm run dev
   ```

*Aplikasi frontend kini dapat diakses di: `http://localhost:9000`*

---

## 💻 Cara Menguji Fitur Real-Time

Untuk menguji apakah fitur sinkronisasi real-time berjalan dengan sukses:
1. Buka aplikasi frontend (`http://localhost:9000`) pada **dua jendela browser berbeda** secara berdampingan (atau gunakan mode Penyamaran/Incognito pada salah satunya).
2. Lakukan registrasi akun baru atau login pada keduanya.
3. Masuk ke **Posts Dashboard** atau **Tags Dashboard**.
4. Cobalah menambah, mengubah, atau menghapus postingan di browser A.
5. Perhatikan bahwa browser B akan langsung memperbarui daftar data dan menampilkan notifikasi pop-up secara instan tanpa perlu me-refresh halaman!

---

## 📖 Dokumentasi
Untuk dokumentasi lengkap mengenai penggunaan PADI REST API Framework, silakan kunjungi website resmi kami di:
🔗 [PADI Software Documentation](https://padisoftware.my.id/)
