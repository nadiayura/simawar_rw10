# Sistem Informasi Pengelolaan Layanan Warga RW 010 Kelurahan Tanah Baru

![Laravel](https://img.shields.io/badge/Laravel-v12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-v8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-v4.1-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-v8.4-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

Sistem Informasi Pengelolaan Layanan Warga berbasis website yang terintegrasi dengan **WhatsApp Gateway** untuk efisiensi, transparansi, dan otomatisasi administrasi serta kegiatan sosial di lingkungan RW 010, Kelurahan Tanah Baru, Kecamatan Beji, Kota Depok.

---

## Latar Belakang & Permasalahan

Pelayanan administrasi dan sosial pada skala Rukun Warga (RW) seperti pengaduan lingkungan, pembayaran iuran bulanan, pengajuan surat keterangan, pencatatan kegiatan sosial (Posyandu, Posbindu, Karang Taruna), serta rekapitulasi keuangan umumnya masih dilakukan secara manual. Hal ini berpotensi menyebabkan:

1.Laporan pengaduan warga sering tidak terdokumentasi dan terlewat.

2.Pembayaran iuran dan pencatatan kas RT/RW rentan terhadap kesalahan rekapitulasi dan duplikasi data.

3.Informasi status permohonan surat maupun pengaduan tidak tersampaikan secara *real-time*.

Aplikasi ini dikembangkan untuk mengintegrasikan seluruh alur layanan warga ke dalam **satu platform terpusat berbasis web** dengan **notifikasi otomatis via WhatsApp Gateway**.

---

## Fitur Utama

Sistem ini terbagi menjadi 4 modul terpadu:
### 1. Modul Manajemen Data & Hak Akses
* Pembagian akses untuk **Admin RW**, **Admin RT**, dan **Warga**.
* Registrasi mandiri warga yang tervalidasi oleh Ketua RT.
* Pengelolaan struktur organisasi RT dan RW.

### 2. Modul Layanan Warga
* Pengajuan laporan masalah lingkungan dilengkapi dengan pelacakan status *real-time* dan *feedback* pasca-layanan.
* Pendataan dan permohonan surat keterangan warga (Domisili, Pengantar RT/RW, dll.).
* Pengumuman & rekapitulasi kegiatan Karang Taruna, Posyandu, dan Posbindu.

### 3. Modul Keuangan & Pembayaran
* Pembuatan tagihan iuran otomatis per periode.
* Pembayaran iuran secara tunai *offline* maupun *online* via Payment Gateway (**Midtrans**).
* Mencegah pembayaran loncat bulan jika terdapat tunggakan.
* Sinkronisasi otomatis pemasukan iuran, pencatatan pengeluaran, serta ekspor Laporan Keuangan ke format PDF.

### 4. Modul Integrasi WhatsApp Gateway (Fonnte)
* Pemberitahuan otomatis ketika status laporan atau pengajuan surat diperbarui.
* Pengingat tagihan iuran bulanan langsung ke WhatsApp warga.
* Verifikasi keamanan kata sandi melalui kode OTP via WhatsApp.

---

## Tech Stack & Spesifikasi

* **Framework Backend**: Laravel v12.x
* **Bahasa Pemrograman**: PHP v8.4.13
* **Database**: MySQL v8.4.3
* **Frontend**: Tailwind CSS v4.1, HTML5, JavaScript
* **Database Client**: DBeaver
* **Server Environment**: Laragon v8.0.0 / Localhost
* **Third-Party Integrations**:
  * **WhatsApp Gateway**: Fonnte API
  * **Payment Gateway**: Midtrans API
  * **Testing Method**: Blackbox Testing

---

## Arsitektur & Metodologi Sistem

Penelitian dan pengembangan aplikasi ini menggunakan metode **Rapid Application Development (RAD)** yang terdiri dari 5 fase:
1. **Pemodelan Bisnis**: Observasi dan wawancara alur layanan RW 010.
2. **Pemodelan Data**: Perancangan ERD (*Entity Relationship Diagram*) & Class Diagram.
3. **Pemodelan Proses**: Perancangan UML (*Use Case, Activity Diagram, Sequence Diagram*).
4. **Pembentukan Aplikasi**: Pembangunan modul web Laravel & integrasi WhatsApp API.
5. **Pengujian & Turnover**: Pengujian fungsi menggunakan **Blackbox Testing**.

---

## Cara Instalasi & Menjalankan Project

### Prasyarat:
* PHP >= 8.2
* Composer
* MySQL / MariaDB
* Node.js & NPM

### Langkah-langkah:

1. **Clone Repositori**
   ```bash
   git clone [https://github.com/username/sistem-layanan-warga-rw010.git](https://github.com/username/sistem-layanan-warga-rw010.git)
   cd sistem-layanan-warga-rw010
2. **Install Dependensi PHP & Frontend**
    ```bash
    composer install
    npm install && npm run build
3. **Konfigurasi Environment (.env)**
    ```bash
    Buat file .env
    Atur koneksi database dan kredensial API pada file .env:
4. **Generate Application Key & Migrasi Database**
    ```bash
    php artisan key:generate
    php artisan migrate --seed
5. **Jalankan Development Server**
    ```bash
    php artisan serve
