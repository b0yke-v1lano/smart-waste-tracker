# Smart Waste Tracker 🌿
Sistem Pencatatan dan Analisis Limbah Makanan (Food Waste) untuk Efisiensi Operasional.

## 🚀 Fitur Utama
*   **Dashboard Analytics**: Visualisasi data kerugian menggunakan Chart.js (Bar & Pie Chart).
*   **Multi-Role Access**: Pemisahan fitur antara **Staff** (Input Data) dan **Supervisor** (Persetujuan & Laporan).
*   **Approval System**: Laporan dari staff harus diperiksa dan disetujui oleh supervisor.
*   **PDF Reporting**: Kemampuan untuk mengekspor laporan bulanan ke format PDF secara otomatis.
*   **Waste Tracking**: Pencatatan detail kategori bahan, departemen, dan total nilai kerugian.

## 🛠️ Teknologi yang Digunakan
*   **Backend**: PHP Native
*   **Database**: MySQL
*   **Frontend**: HTML5, CSS3, JavaScript (SB Admin 2 Template)
*   **Library**: Chart.js, DataTables, FontAwesome.

## ⚙️ Cara Instalasi
1.  Clone repositori ini atau download file ZIP-nya.
2.  Pindahkan folder ke dalam direktori `xampp/htdocs/`.
3.  Impor database `waste_tracker.sql` (pastikan Anda sudah menyediakannya) ke dalam phpMyAdmin Anda.
4.  Salin `config/koneksi.php.example` menjadi `config/koneksi.php` dan sesuaikan kredensial database Anda.
5.  Akses melalui browser di `http://localhost/smart-waste-tracker`.

## 📂 Struktur Folder
*   **auth/**: Modul login dan logout.
*   **staff/**: Fitur khusus untuk input dan pengelolaan data oleh staff.
*   **supervisor/**: Fitur persetujuan laporan dan dashboard analisis untuk supervisor.
*   **config/**: Konfigurasi database.
*   **utils/**: Skrip pembantu/utility.

---
Copyright © 2026 Smart Waste Tracker.
