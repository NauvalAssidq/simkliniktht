# SimKlinik THT 🏥

**SimKlinik THT** adalah Sistem Informasi Manajemen Klinik yang dikhususkan untuk poli Spesialis Telinga Hidung Tenggorokan (THT). Aplikasi ini dirancang untuk mempermudah pendaftaran pasien, pengelolaan antrian, pencatatan rekam medis (SOAP), dan integrasi langsung dengan platform **SatuSehat** (Kemenkes RI).

![Status](https://img.shields.io/badge/Status-Development-blue) ![Laravel](https://img.shields.io/badge/Laravel-12.x-red) ![PHP](https://img.shields.io/badge/PHP-8.2-purple) ![Tailwind](https://img.shields.io/badge/Tailwind-4.0-cyan)

## 🌟 Fitur Utama

### 1. Pendaftaran & Antrian
- **Registrasi Pasien Baru**: Form pendaftaran cepat dengan validasi NIK.
- **Manajemen Antrian**: Sistem antrian otomatis per dokter/poli.
- **Display Antrian (TV)**: Tampilan layar antrian yang modern dan responsif untuk ruang tunggu.

### 2. Pemeriksaan Dokter (Cockpit THT)
- **Rekam Medis Elektronik**: Input data SOAP (Subjective, Objective, Assessment, Plan) yang terstruktur.
- **Pemeriksaan Audiologi**: Form khusus untuk input hasil tes pendengaran (Ambang Dengar & Tipe Gangguan) beserta visualisasi grafis (rencana pengembangan).
- **Diagnosis & Tindakan**: Pencarian ICD-10 dan ICD-9-CM yang cepat dengan dukungan input **Lateralitas** (Kanan/Kiri/Kedua).

### 3. Integrasi SatuSehat (Bridging) 🇮🇩
Aplikasi ini memiliki fitur bridging **Full-Cycle** ke SatuSehat:
- **Patient Search**: Pencarian data pasien berdasarkan NIK (via API SatuSehat).
- **Episode of Care & Encounter**: Pembuatan otomatis episode perawatan dan kunjungan.
- **Kondisi & Diagnosis**: Pengiriman diagnosa dengan kode ICD-10.
- **Observasi Tanda Vital**: Pengiriman data TTV (Tensi, Suhu, Nadi).
- **Observasi Audiologi**: Pengiriman hasil pemeriksaan telinga dan tes pendengaran (SNOMED CT).
- **Prosedur**: Pengiriman data tindakan medis.

## 🛠️ Persyaratan Sistem

Pastikan server atau komputer lokal Anda memenuhi spesifikasi berikut:
- **PHP**: Versi 8.2 atau lebih baru.
- **Composer**: Manajer dependensi PHP.
- **Node.js**: Versi 18+ & **NPM** (untuk build aset frontend).
- **Database**: MySQL atau SQLite.

## 🚀 Instalasi

Ikuti langkah-langkah berikut untuk menjalankan aplikasi di komputer lokal Anda:

1.  **Clone Repositori**
    ```bash
    git clone https://github.com/username/simklinik-tht.git
    cd simklinik-tht
    ```

2.  **Instal Dependensi PHP**
    ```bash
    composer install
    ```

3.  **Konfigurasi Environment**
    Salin file `.env.example` menjadi `.env`:
    ```bash
    cp .env.example .env
    ```
    Buka file `.env` dan sesuaikan konfigurasi database Anda (jika menggunakan MySQL):
    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=db_simklinik
    DB_USERNAME=root
    DB_PASSWORD=
    ```

4.  **Generate Key & Migrasi Database**
    ```bash
    php artisan key:generate
    php artisan migrate --seed
    ```
    *Perintah `--seed` akan mengisi database dengan data master (Dokter, Poli, Obat, Tindakan) untuk keperluan testing.*

5.  **Instal Dependensi Frontend**
    ```bash
    npm install
    npm run build
    ```

6.  **Jalankan Aplikasi**
    Buka dua terminal terpisah untuk menjalankan server Laravel dan Vite (untuk *Hot Module Replacement*):
    
    *Terminal 1:*
    ```bash
    php artisan serve
    ```
    
    *Terminal 2:*
    ```bash
    npm run dev
    ```

    Akses aplikasi di `http://localhost:8000`.

## 🔗 Konfigurasi SatuSehat
Untuk mengaktifkan fitur bridging, tambahkan kredensial API SatuSehat Anda di file `.env`:

```env
SATUSEHAT_ENV=dev # atau prod
SATUSEHAT_AUTH_URL=https://auth.satusehat.kemkes.go.id/oauth2/v1
SATUSEHAT_BASE_URL=https://api-satusehat.kemkes.go.id/fhir-r4/v1
SATUSEHAT_CLIENT_ID=your_client_id_here
SATUSEHAT_CLIENT_SECRET=your_client_secret_here
SATUSEHAT_ORGANIZATION_ID=your_org_id_here
```

## 📖 Panduan Penggunaan Singkat

1.  **Halaman Pendaftaran** (`/registration`): Masukkan data pasien. Jika bridging aktif, sistem akan mencoba mengambil IHS ID pasien dari SatuSehat.
2.  **Halaman Pemeriksaan** (`/pemeriksaan`): 
    - Pilih pasien dari daftar antrian di sebelah kiri.
    - Isi form SOAP, Tanda Vital, dan Audiologi.
    - Klik **Simpan Pemeriksaan**.
3.  **Kirim ke SatuSehat**:
    - Setelah pemeriksaan disimpan, data akan diproses.
    - Klik tombol **Bridging** (jika tersedia manual) atau sistem akan mengirim otomatis (tergantung konfigurasi controller) untuk sinkronisasi data ke SatuSehat.

## 🤝 Kontribusi
Silakan buat *Pull Request* atau *Issue* jika Anda menemukan bug atau ingin menambahkan fitur baru.

---
Dikembangkan dengan ❤️ menggunakan **Laravel** dan **Tailwind CSS**.
