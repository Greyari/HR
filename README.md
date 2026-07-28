# HR — Human Resource Information System (HRIS)

Sistem informasi manajemen sumber daya manusia (HRIS) berbasis web yang dibangun dengan **Laravel 12**. Aplikasi ini dirancang untuk membantu perusahaan mengelola seluruh siklus kepegawaian dalam satu platform terintegrasi — mulai dari data karyawan, absensi, penggajian, hingga rekrutmen.

## ✨ Fitur

- **Manajemen Karyawan** — pengelolaan data pribadi, dokumen, dan struktur organisasi karyawan
- **Absensi & Kehadiran** — pencatatan jam masuk/keluar, izin, cuti, dan lembur
- **Payroll & Penggajian** — perhitungan gaji, potongan, dan slip gaji otomatis
- **Log Aktivitas** — pencatatan audit trail setiap perubahan data (menggunakan Spatie Activity Log)
- **Export/Import Excel** — laporan data karyawan, absensi, dan payroll ke format Excel (PhpSpreadsheet)
- **Notifikasi** — pengiriman notifikasi real-time (Firebase) dan email (Sendinblue)
- **Penyimpanan File** — upload foto profil dan dokumen karyawan ke cloud (Cloudinary)
- **Integrasi Google** — konektivitas dengan layanan Google API
- **API Ready** — autentikasi API menggunakan Laravel Sanctum untuk kebutuhan integrasi mobile/eksternal

## 🛠️ Tech Stack

| Kategori | Teknologi |
|---|---|
| Framework | Laravel 12 |
| Bahasa | PHP ^8.2 |
| Autentikasi API | Laravel Sanctum |
| Frontend Build | Vite, Tailwind CSS |
| Database | MySQL / SQLite (sesuai konfigurasi) |
| File Storage | Cloudinary |
| Notifikasi Push | Firebase (Kreait Laravel Firebase) |
| Email | Sendinblue (Brevo) API |
| Export Excel | PhpOffice/PhpSpreadsheet |
| Audit Log | Spatie Laravel Activity Log |
| Testing | Pest PHP |
| Containerization | Docker |

## 📋 Prasyarat

Sebelum instalasi, pastikan sistem kamu sudah punya:

- PHP >= 8.2
- Composer
- Node.js & npm
- MySQL / SQLite
- (Opsional) Docker & Docker Compose

## 🚀 Instalasi

1. **Clone repository**
   ```bash
   git clone https://github.com/Greyari/HR.git
   cd HR
   ```

2. **Install dependency PHP**
   ```bash
   composer install
   ```

3. **Install dependency JavaScript**
   ```bash
   npm install
   ```

4. **Salin file environment**
   ```bash
   cp .env.example .env
   ```

5. **Generate application key**
   ```bash
   php artisan key:generate
   ```

6. **Konfigurasi `.env`**

   Sesuaikan koneksi database dan kredensial layanan pihak ketiga di file `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=hr_db
   DB_USERNAME=root
   DB_PASSWORD=

   CLOUDINARY_URL=

   FIREBASE_CREDENTIALS=

   GOOGLE_CLIENT_ID=
   GOOGLE_CLIENT_SECRET=

   SENDINBLUE_API_KEY=
   ```

7. **Jalankan migrasi & seeder**
   ```bash
   php artisan migrate --seed
   ```

8. **Jalankan aplikasi**
   ```bash
   composer run dev
   ```
   Perintah ini akan menjalankan server, queue listener, log viewer (Pail), dan Vite secara bersamaan.

   Atau jalankan secara terpisah:
   ```bash
   php artisan serve
   ```

   Aplikasi bisa diakses di `http://localhost:8000`.

## 🐳 Menjalankan dengan Docker

Project ini sudah menyediakan `Dockerfile` untuk deployment berbasis container:

```bash
docker build -t hr-app .
docker run -p 8000:8000 --env-file .env hr-app
```

## 🧪 Testing

Project ini menggunakan **Pest PHP** untuk testing:

```bash
composer test
```

atau

```bash
php artisan test
```

## 📁 Struktur Direktori

```
HR/
├── app/           # Logic aplikasi (Models, Controllers, Services)
├── bootstrap/     # File bootstrap Laravel
├── config/        # File konfigurasi
├── database/      # Migrations, seeders, factories
├── public/        # Entry point & asset publik
├── resources/     # Views, CSS, JS
├── routes/        # Definisi route (web & API)
├── storage/       # File upload, log, cache
├── tests/         # Unit & feature test (Pest)
├── Dockerfile
└── composer.json
```

## 🤝 Kontribusi

Kontribusi sangat terbuka! Silakan ikuti langkah berikut:

1. Fork repository ini
2. Buat branch fitur baru (`git checkout -b fitur/nama-fitur`)
3. Commit perubahan kamu (`git commit -m 'Menambahkan fitur X'`)
4. Push ke branch (`git push origin fitur/nama-fitur`)
5. Buka Pull Request

## 📄 Lisensi

Project ini menggunakan lisensi [MIT](https://opensource.org/licenses/MIT) (mengikuti lisensi default Laravel). Silakan sesuaikan bila kamu ingin menggunakan lisensi lain.

## 📧 Kontak

Dikembangkan oleh [Greyari](https://github.com/Greyari). Untuk pertanyaan atau laporan bug, silakan hubungi tupang1017@gmail.com.
