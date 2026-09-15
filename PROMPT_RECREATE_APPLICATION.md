# Prompt untuk Re-create Aplikasi ePegawai

Buatkan aplikasi manajemen pegawai (ePegawai) dengan fitur lengkap berikut:

## Tech Stack
- PHP 7.4+
- MySQL/MariaDB
- Bootstrap 5
- jQuery
- PHPWord (untuk generate dokumen Word)
- FullCalendar (untuk kalender)

## Struktur Database

### Tabel users
- id (INT, PK, AI)
- username (VARCHAR, unique)
- password (VARCHAR, hashed)
- nama_lengkap (VARCHAR)
- role (ENUM: admin, user)
- created_at (DATETIME)
- updated_at (DATETIME)

### Tabel pegawai
- id (INT, PK, AI)
- nip (VARCHAR, unique)
- nama (VARCHAR)
- tempat_lahir (VARCHAR)
- tanggal_lahir (DATE)
- jenis_kelamin (ENUM: L, P)
- agama (VARCHAR)
- no_hp (VARCHAR)
- email (VARCHAR)
- alamat (TEXT)
- status_kepegawaian (ENUM: PNS, PPPK)
- golongan_ruangan (VARCHAR)
- jabatan (VARCHAR)
- unit_kerja (VARCHAR)
- status_pegawai (ENUM: aktif, nonaktif, pensiun)
- pendidikan_terakhir (VARCHAR)
- jurusan (VARCHAR)
- tahun_lulus (INT)
- foto (VARCHAR)
- cuti_n (INT, default 12)
- cuti_n_minus_1 (INT, default 0)
- cuti_n_minus_2 (INT, default 0)
- tanggal_masuk (DATE)
- created_at (DATETIME)
- updated_at (DATETIME)

### Tabel cuti
- id (INT, PK, AI)
- nomor_surat_cuti (VARCHAR)
- id_pegawai (INT, FK)
- nip (VARCHAR)
- nama_pegawai (VARCHAR)
- jenis_cuti (ENUM: Cuti Tahunan, Cuti Sakit, Cuti Melahirkan, Cuti Besar, Cuti Alasan Penting)
- tanggal_pengajuan (DATE)
- tanggal_mulai (DATE)
- tanggal_selesai (DATE)
- lama_cuti (INT)
- alasan_cuti (TEXT)
- alamat_cuti (TEXT)
- no_telepon_cuti (VARCHAR)
- cuti_n_minus_2 (INT)
- cuti_n_minus_1 (INT)
- cuti_n (INT)
- atasan_id (INT, FK)
- atasan_nama (VARCHAR)
- atasan_nip (VARCHAR)
- pejabat_id (INT, FK)
- pejabat_nama (VARCHAR)
- pejabat_nip (VARCHAR)
- status_cuti (ENUM: Pending, Disetujui, Ditolak)
- file_surat (VARCHAR)
- created_at (DATETIME)
- updated_at (DATETIME)

### Tabel surat_tugas
- id (INT, PK, AI)
- nomor_surat (VARCHAR)
- tentang (VARCHAR)
- tanggal_surat_tugas (DATE)
- hari (VARCHAR)
- hari_awal (DATE)
- hari_akhir (DATE)
- tempat (TEXT)
- dasar (TEXT)
- tujuan (TEXT)
- transportasi (TEXT)
- file_surat (VARCHAR)
- status_surat (ENUM: Pending, Disetujui, Ditolak)
- created_at (DATETIME)
- updated_at (DATETIME)

### Tabel surat_tugas_pegawai
- id (INT, PK, AI)
- id_surat_tugas (INT, FK)
- id_pegawai (INT, FK)

### Tabel holidays
- id (INT, PK, AI)
- tanggal (DATE)
- nama_libur (VARCHAR)
- jenis_libur (VARCHAR)
- tahun (INT)
- keterangan (TEXT)
- created_at (DATETIME)
- updated_at (DATETIME)

### Tabel activity_log
- id (INT, PK, AI)
- user_id (INT, FK)
- action (VARCHAR)
- description (TEXT)
- created_at (DATETIME)

## Fitur Utama

### 1. Authentication & Authorization
- Login/logout
- Role-based access control (admin/user)
- Session management

### 2. Manajemen Pegawai
- CRUD data pegawai
- Upload foto pegawai
- Search & filter pegawai
- View detail pegawai
- Cuti quota rotation (otomatis saat admin login di awal tahun)

### 3. Manajemen Cuti
- Pengajuan cuti (Cuti Tahunan, Cuti Sakit, Cuti Melahirkan, Cuti Besar, Cuti Alasan Penting)
- Perhitungan lama cuti:
  - Cuti Tahunan: hanya hitung hari kerja (exclude weekend & holidays)
  - Cuti Melahirkan: hitung semua hari (termasuk weekend & holidays)
- Validasi kuota cuti (max 24 hari)
- Approval cuti (Atasan & Pejabat)
- Generate surat cuti (PHPWord template)
- Riwayat cuti pegawai

### 4. Manajemen Surat Tugas
- Buat surat tugas untuk multiple pegawai
- Approval surat tugas
- Generate surat tugas (PHPWord template)
- Riwayat surat tugas

### 5. Manajemen Hari Libur
- CRUD hari libur nasional
- Calendar view (FullCalendar)
- Modal form untuk tambah/edit hari libur

### 6. Fitur Tambahan
- Masa kerja pegawai (real-time calculation dari tanggal_masuk)
- Auto-fill data pegawai saat pilih pegawai di form cuti
- Flash messages untuk notifikasi
- Activity logging
- Responsive design (Bootstrap 5)

## Struktur File

```
epegawai/
├── config.php
├── index.php (dashboard)
├── login.php
├── logout.php
├── data_pegawai.php (CRUD pegawai)
├── cuti.php (CRUD cuti)
├── surat_tugas.php (CRUD surat tugas)
├── holidays.php (CRUD hari libur)
├── calendar_holidays.php (calendar view)
├── includes/
│   ├── navbar.php
│   ├── pegawai_form.php
│   ├── pegawai_detail.php
│   ├── cuti_form.php
│   └── functions.php
├── download/
│   ├── download_surat_cuti.php
│   └── download_surat_tugas.php
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   ├── script.js
│   │   └── live-search.js
│   └── images/
├── templates/
│   ├── template_surat_cuti_pns.docx
│   ├── template_surat_cuti_pppk.docx
│   └── template_surat_tugas.docx
└── uploads/
    └── files/
```

## Khusus: Perhitungan Cuti

### Cuti Tahunan
- Hanya hitung hari kerja (Senin-Jumat)
- Exclude hari libur nasional
- Max kuota: 24 hari (dari 3 tahun: N, N-1, N-2)
- Validasi jika melebihi kuota

### Cuti Melahirkan
- Hitung semua hari (termasuk Sabtu, Minggu, dan hari libur)
- Tidak ada batasan kuota
- Biasanya 3 bulan (±90 hari)

## Khusus: Masa Kerja
- Hitung real-time dari tanggal_masuk
- Format: "X Tahun Y Bulan"
- Ditampilkan di detail pegawai dan placeholder Word (${masa_kerja})

## Khusus: Rotasi Kuota Cuti
- Jalan otomatis saat admin login di awal tahun (Januari)
- cuti_n_minus_2 = 0
- cuti_n_minus_1 = cuti_n
- cuti_n = 12 (reset)

## Placeholder Word Template
- ${nomor_surat_cuti}
- ${nama_pegawai}
- ${nip}
- ${jabatan}
- ${unit_kerja}
- ${status_kepegawaian}
- ${golongan_ruangan}
- ${alamat}
- ${no_hp}
- ${jenis_cuti}
- ${tanggal_mulai}
- ${tanggal_selesai}
- ${lama_cuti}
- ${alasan_cuti}
- ${alamat_cuti}
- ${no_telepon_cuti}
- ${tanggal_pengajuan}
- ${nomor_surat}
- ${tanggal_sekarang}
- ${approved_by}
- ${tempat}
- ${atasan_nama}
- ${atasan_nip}
- ${pejabat_nama}
- ${pejabat_nip}
- ${cuti_n_minus_2}
- ${cuti_n_minus_1}
- ${cuti_n}
- ${sisa_cuti_n}
- ${sisa_cuti_n1}
- ${sisa_cuti_n2}
- ${total_sisa_cuti}
- ${dipakai_n}
- ${dipakai_n1}
- ${dipakai_n2}
- ${total_dipakai}
- ${masa_kerja}
- ${chk_ct} (checkbox Cuti Tahunan)
- ${chk_cs} (checkbox Cuti Sakit)
- ${chk_cm} (checkbox Cuti Melahirkan)
- ${chk_cb} (checkbox Cuti Besar)
- ${chk_cap} (checkbox Cuti Alasan Penting)

## Security
- Password hashing (password_hash)
- SQL injection prevention (prepared statements)
- XSS prevention (htmlspecialchars)
- CSRF protection (session tokens)
- File upload validation
- Login required for all pages except login

Buatkan aplikasi dengan struktur dan fitur di atas. Pastikan semua fitur berfungsi sesuai deskripsi.
