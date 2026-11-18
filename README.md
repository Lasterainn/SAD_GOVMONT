# JakartaConnect

Platform portal transparansi pembangunan daerah dan ruang partisipasi publik berbasis web untuk Provinsi DKI Jakarta. Aplikasi ini menghubungkan **Masyarakat**, **Instansi Pemerintah**, dan **Administrator** dalam pemantauan progres proyek pembangunan, nilai pengadaan (RAB), serta forum diskusi warga dengan pencatatan audit log yang terverifikasi.

---

## Daftar Isi
- [Fitur Utama & Pembagian Peran](#fitur-utama--pembagian-peran)
- [Arsitektur Keamanan](#arsitektur-keamanan)
- [Teknologi yang Digunakan](#teknologi-yang-digunakan)
- [Struktur Direktori](#struktur-direktori)
- [Prasyarat Sistem](#prasyarat-sistem)
- [Panduan Instalasi & Menjalankan Aplikasi](#panduan-instalasi--menjalankan-aplikasi)
- [Pengujian & Pipeline Keamanan CI/CD](#pengujian--pipeline-keamanan-cicd)

---

## Fitur Utama & Pembagian Peran

| Peran | Hak Akses & Fungsionalitas |
| :--- | :--- |
| **Publik (Tanpa Login)** | Melihat beranda statistik kota, menjelajahi proyek berstatus **Terkonfirmasi** dalam tampilan **Grid** maupun **Peta Interaktif DKI Jakarta**, memfilter/mengurutkan proyek, membaca detail proyek & tahapan pengerjaan, membaca utas diskusi, serta pencarian cepat global (`Ctrl + K`). |
| **Warga** | Registrasi akun dengan validasi NIK 16 digit, membuat topik diskusi baru (dapat ditautkan ke proyek tertentu), mengirim dan mengedit komentar bertingkat (*nested replies*). |
| **Instansi Pemerintah** | Login dengan verifikasi **MFA (OTP Email)**, melengkapi biaya pengadaan (RAB), jadwal, progres fisik, tahapan pengerjaan, serta mengonfirmasi publikasi proyek yang ditugaskan ke instansinya. |
| **Administrator** | Login dengan verifikasi **MFA (OTP Email)**, mengelola kerangka proyek (Tambah, Edit, Hapus), menugaskan instansi pelaksana, melakukan moderasi komentar forum (*soft-delete* beserta alasan), dan memantau validitas *hash chain* log audit di dashboard. |

---

## Arsitektur Keamanan

Aplikasi ini dibangun dengan pendekatan *security-by-design* yang mengacu pada standar **OWASP Top 10:2025**:

- **Kriptografi & Perlindungan Data Sensitif:**
  - *Password hashing* menggunakan **Argon2id**.
  - Nomor Induk Kependudukan (NIK) dienkripsi saat disimpan (*at rest*) menggunakan **AES-256-GCM** (`APP_KEY`) dan diindeks untuk pencarian unik menggunakan **HMAC-SHA256** (`NIK_PEPPER`).
  - Kode OTP MFA di-hash menggunakan **HMAC-SHA256** (`OTP_PEPPER`).
- **Autentikasi & Manajemen Sesi:**
  - *Multi-Factor Authentication* (MFA) berbasis OTP 6 digit untuk seluruh akun **Admin** dan **Pemerintah**.
  - Pembatasan percobaan login berbasis IP (*progressive delay*, pemicu CAPTCHA, dan blokir sementara HTTP `429` melalui `LoginThrottle`) serta integrasi **Google reCAPTCHA v2**.
  - Cookie sesi dikonfigurasi dengan `HttpOnly`, `SameSite=Lax`, `Secure` (saat HTTPS aktif), regenerasi ID sesi pasca-login, serta *idle timeout* otomatis.
- **Integritas Data & Audit Log *Append-Only*:**
  - Seluruh aktivitas login (`tbl_log_login`), perubahan/penghapusan proyek (`tbl_log_proyek`), dan moderasi forum (`tbl_log_forum`) dicatat menggunakan rantai hash kriptografis yang diverifikasi melalui *database view* (`v_verifikasi_log_*`).
  - Pemisahan hak akses database dengan role aplikasi berhak istimewa minimal (`app_jc`).
- **Pertahanan Aplikasi Web:**
  - Perlindungan **CSRF** pada seluruh form mutasi data (`POST`), kueri SQL berbasis **PDO Prepared Statements**, validasi MIME file unggahan menggunakan `finfo_file()` dengan penamaan UUID acak, serta header keamanan HTTP (`Content-Security-Policy`, `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`, dan `HSTS`).

---

## Teknologi yang Digunakan

- **Backend:** PHP 8.2+ Native (PSR-4 Autoloader tanpa dependensi eksternal)
- **Database:** PostgreSQL 15+
- **Frontend:** HTML5, Vanilla CSS, Vanilla JavaScript
- **Web Server / Reverse Proxy (Lokal):** PHP Built-in Server + Caddy (HTTPS lokal)
- **CI/CD Security:** GitHub Actions (Gitleaks, Semgrep, PostgreSQL Schema Check)

---

## Struktur Direktori

```text
jakartaconnect/
├── .github/
│   └── workflows/
│       └── security.yml         # Pipeline CI/CD pemindaian keamanan & skema DB
├── config/
│   └── config.php               # Pengaturan konfigurasi dasar aplikasi
├── database/
│   └── jakarta_connect.sql      # Skema PostgreSQL, enum, view hash-chain & role app_jc
├── public/
│   ├── css/style.css            # Desain sistem antarmuka portal & autentikasi
│   ├── js/app.js                # Interaksi peta, filter, modal, & pencarian Ctrl+K
│   ├── uploads/proyek/          # Penyimpanan gambar proyek berformat UUID
│   ├── .htaccess                # Aturan rewrite & proteksi file sensitif
│   └── index.php                # Front controller & definisi rute GET/POST
├── script/
│   ├── hashPassword.php         # Utilitas pembuatan hash password Argon2id
│   └── seedDatabase.php         # Seeder data awal akun, proyek & diskusi
├── src/
│   ├── Controllers/             # AuthController, PageController, ProyekController, ForumController
│   ├── Models/                  # User, ProyekModel, ForumModel (transaksi & audit log)
│   ├── auth.php                 # Logika autentikasi, registrasi NIK & verifikasi MFA
│   ├── bootstrap.php            # Autoloader, security headers, & manajemen sesi
│   ├── csrf.php                 # Generator & validator token CSRF
│   ├── database.php             # Koneksi PDO PostgreSQL
│   ├── env.php                  # Pemuat & validator variabel lingkungan
│   ├── helpers.php              # Helper escaping HTML, ikon SVG, & flash message
│   ├── LoginThrottle.php        # Pembatasan rate-limit login berbasis IP
│   ├── mailer.php               # Pengirim email OTP (SMTP TLS / log development)
│   ├── recaptcha.php            # Verifikasi sisi server Google reCAPTCHA
│   ├── router.php               # Router HTTP dengan dukungan parameter dinamis
│   └── validator.php            # Validasi format email, NIK 16 digit & kompleksitas password
├── templates/
│   ├── partials/                # Komponen reusable (navbar, footer, project_card, discussion_item)
│   └── *.php                    # Halaman beranda, proyek, diskusi, dashboard, login, register, mfa
├── .env.example                 # Templat konfigurasi variabel lingkungan
└── Caddyfile                    # Konfigurasi reverse proxy HTTPS lokal
```

---

## Prasyarat Sistem

- **PHP >= 8.2** dengan ekstensi aktif: `pdo_pgsql`, `openssl`, `curl`, `mbstring`, `fileinfo`
- **PostgreSQL >= 15**
- **Caddy** (opsional, untuk menjalankan HTTPS lokal di `https://jakartaconnect.local`)

---

## Panduan Instalasi & Menjalankan Aplikasi

### 1. Kloning Repositori & Konfigurasi `.env`
```bash
git clone <url-repositori-anda> jakartaconnect
cd jakartaconnect
cp .env.example .env
```

Buat tiga kunci kriptografi acak 32-byte (64 karakter heksadesimal) yang berbeda untuk `APP_KEY`, `NIK_PEPPER`, dan `OTP_PEPPER`:
```bash
openssl rand -hex 32
openssl rand -hex 32
openssl rand -hex 32
```
Masukkan ketiga hasil tersebut beserta kredensial database PostgreSQL ke dalam file `.env`.

### 2. Inisialisasi Database & Data Awal
Jalankan skema SQL untuk membuat database, tabel, *view* audit log, serta role aplikasi `app_jc`:
```bash
psql -U postgres -f database/jakarta_connect.sql
```

Isi data awal (akun demo, proyek pembangunan, dan diskusi warga) menggunakan seeder:
```bash
php script/seedDatabase.php
```

### 3. Menjalankan Server Lokal

**Opsi A — Mode HTTP Lokal (Development):**
```bash
php -S 127.0.0.1:8000 -t public
```
Akses melalui `http://127.0.0.1:8000`.

**Opsi B — Mode HTTPS Lokal dengan Caddy (`https://jakartaconnect.local`):**
1. Tambahkan `127.0.0.1 jakartaconnect.local` ke `/etc/hosts`.
2. Jalankan PHP server pada terminal pertama:
   ```bash
   php -S 127.0.0.1:8000 -t public
   ```
3. Jalankan Caddy pada terminal kedua:
   ```bash
   sudo caddy run --config Caddyfile
   ```
4. Akses melalui `https://jakartaconnect.local`.

> **Catatan OTP Development:** Apabila `MAIL_DRIVER=log` digunakan di `.env`, kode OTP 6 digit untuk login Admin dan Pemerintah akan dicetak langsung pada output terminal PHP (`error_log`).

---

## Pengujian & Pipeline Keamanan CI/CD

Setiap `push` maupun `pull_request` ke branch utama menjalankan pemeriksaan otomatis melalui `.github/workflows/security.yml`:
1. **Gitleaks:** Mendeteksi kebocoran kredensial atau kunci kriptografi di riwayat commit.
2. **PHP Lint & Semgrep S