-- =====================================================================
-- Jakarta Connect - Skema Database PostgreSQL
-- Minimal PostgreSQL 13 (gen_random_uuid() bawaan)
-- =====================================================================

BEGIN;

CREATE EXTENSION IF NOT EXISTS pgcrypto;  -- digest() untuk hash chain log
CREATE EXTENSION IF NOT EXISTS citext;    -- email case-insensitive

-- ---------------------------------------------------------------------
-- 0. Skema & tipe enum
-- ---------------------------------------------------------------------
CREATE SCHEMA IF NOT EXISTS jakarta_connect;
SET search_path = jakarta_connect, public;

CREATE TYPE role_user        AS ENUM ('admin', 'pemerintah', 'warga');
CREATE TYPE status_akun      AS ENUM ('aktif', 'nonaktif', 'terkunci');
CREATE TYPE status_proyek    AS ENUM ('direncanakan', 'dalam_pengerjaan', 'selesai');
CREATE TYPE status_konfirmasi AS ENUM ('draft_admin', 'menunggu_konfirmasi', 'terkonfirmasi');
CREATE TYPE kategori_forum   AS ENUM ('infrastruktur', 'transportasi', 'lingkungan',
                                      'fasilitas_publik', 'acara_jakarta');

-- Trigger umum untuk updated_at
CREATE OR REPLACE FUNCTION set_updated_at() RETURNS trigger AS $$
BEGIN
    NEW.updated_at := now();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- ---------------------------------------------------------------------
-- 1. tbl_warga
--    - ID UUID (bukan auto increment)
--    - NIK dienkripsi AES-256-GCM di aplikasi (kunci di env var terisolasi)
--    - nik_hash = HMAC-SHA256(NIK, pepper) -> blind index agar 1 NIK = 1 akun
--      tanpa perlu mendekripsi (mitigasi Spoofing lewat registrasi NIK)
--    - password_hash = argon2id (salt sudah ada di dalam string hash)
-- ---------------------------------------------------------------------
CREATE TABLE tbl_warga (
    id                  uuid         PRIMARY KEY DEFAULT gen_random_uuid(),
    nik_encrypted       bytea        NOT NULL,            -- nonce(12) || ciphertext || tag(16)
    nik_hash            bytea        NOT NULL UNIQUE,     -- HMAC-SHA256, 32 byte
    nama_lengkap        varchar(150) NOT NULL,
    email               citext       NOT NULL UNIQUE,
    password_hash       text         NOT NULL,
    status              status_akun  NOT NULL DEFAULT 'aktif',
    jumlah_gagal_login  smallint     NOT NULL DEFAULT 0,
    terkunci_sampai     timestamptz,
    login_terakhir_at   timestamptz,
    created_at          timestamptz  NOT NULL DEFAULT now(),
    updated_at          timestamptz  NOT NULL DEFAULT now(),

    CONSTRAINT chk_warga_hash_argon2id CHECK (password_hash LIKE '$argon2id$%'),
    CONSTRAINT chk_warga_nik_hash_len  CHECK (octet_length(nik_hash) = 32),
    CONSTRAINT chk_warga_gagal_login   CHECK (jumlah_gagal_login BETWEEN 0 AND 3),
    CONSTRAINT chk_warga_email_format  CHECK (email ~* '^[^@\s]+@[^@\s]+\.[^@\s]+$')
);
CREATE TRIGGER trg_warga_updated BEFORE UPDATE ON tbl_warga
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

-- ---------------------------------------------------------------------
-- 2. tbl_pemerintah  (wajib MFA - OTP via email)
-- ---------------------------------------------------------------------
CREATE TABLE tbl_pemerintah (
    id                  uuid         PRIMARY KEY DEFAULT gen_random_uuid(),
    nama_lengkap        varchar(150) NOT NULL,
    instansi            varchar(200) NOT NULL,            -- mis. Dinas Bina Marga DKI Jakarta
    email               citext       NOT NULL UNIQUE,
    password_hash       text         NOT NULL,
    mfa_wajib           boolean      NOT NULL DEFAULT true,
    otp_hash            text,                             -- hash OTP, bukan OTP asli
    otp_kedaluwarsa_at  timestamptz,
    otp_percobaan       smallint     NOT NULL DEFAULT 0,
    status              status_akun  NOT NULL DEFAULT 'aktif',
    jumlah_gagal_login  smallint     NOT NULL DEFAULT 0,
    terkunci_sampai     timestamptz,
    login_terakhir_at   timestamptz,
    created_at          timestamptz  NOT NULL DEFAULT now(),
    updated_at          timestamptz  NOT NULL DEFAULT now(),

    CONSTRAINT chk_pem_hash_argon2id CHECK (password_hash LIKE '$argon2id$%'),
    CONSTRAINT chk_pem_mfa_wajib     CHECK (mfa_wajib = true),
    CONSTRAINT chk_pem_gagal_login   CHECK (jumlah_gagal_login BETWEEN 0 AND 3),
    CONSTRAINT chk_pem_otp_percobaan CHECK (otp_percobaan BETWEEN 0 AND 3),
    CONSTRAINT chk_pem_email_format  CHECK (email ~* '^[^@\s]+@[^@\s]+\.[^@\s]+$')
);
CREATE TRIGGER trg_pemerintah_updated BEFORE UPDATE ON tbl_pemerintah
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

-- ---------------------------------------------------------------------
-- 3. tbl_admin  (wajib MFA - OTP via email)
-- ---------------------------------------------------------------------
CREATE TABLE tbl_admin (
    id                  uuid         PRIMARY KEY DEFAULT gen_random_uuid(),
    nama_lengkap        varchar(150) NOT NULL,
    email               citext       NOT NULL UNIQUE,
    password_hash       text         NOT NULL,
    mfa_wajib           boolean      NOT NULL DEFAULT true,
    otp_hash            text,
    otp_kedaluwarsa_at  timestamptz,
    otp_percobaan       smallint     NOT NULL DEFAULT 0,
    status              status_akun  NOT NULL DEFAULT 'aktif',
    jumlah_gagal_login  smallint     NOT NULL DEFAULT 0,
    terkunci_sampai     timestamptz,
    login_terakhir_at   timestamptz,
    created_at          timestamptz  NOT NULL DEFAULT now(),
    updated_at          timestamptz  NOT NULL DEFAULT now(),

    CONSTRAINT chk_adm_hash_argon2id CHECK (password_hash LIKE '$argon2id$%'),
    CONSTRAINT chk_adm_mfa_wajib     CHECK (mfa_wajib = true),
    CONSTRAINT chk_adm_gagal_login   CHECK (jumlah_gagal_login BETWEEN 0 AND 3),
    CONSTRAINT chk_adm_otp_percobaan CHECK (otp_percobaan BETWEEN 0 AND 3),
    CONSTRAINT chk_adm_email_format  CHECK (email ~* '^[^@\s]+@[^@\s]+\.[^@\s]+$')
);
CREATE TRIGGER trg_admin_updated BEFORE UPDATE ON tbl_admin
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

-- ---------------------------------------------------------------------
-- 4. tbl_proyek
--    - Admin upload proyek kerangka (status 'draft_admin')
--    - Pemerintah melengkapi detail (biaya, estimasi tahap) & konfirmasi
--    - Gambar: hanya jpg/png/webp, maks 10 MB, nama file UUID, MIME divalidasi
-- ---------------------------------------------------------------------
CREATE TABLE tbl_proyek (
    id                      uuid              PRIMARY KEY DEFAULT gen_random_uuid(),
    judul                   varchar(200)      NOT NULL,
    deskripsi               text,
    kategori                varchar(80),
    lokasi_kelurahan        varchar(120),
    lokasi_kota             varchar(120)      DEFAULT 'Jakarta',
    instansi_pelaksana      varchar(200),

    -- Detail penuh (diisi pemerintah)
    biaya_pengadaan         numeric(18,2),
    tanggal_mulai           date,
    tanggal_target_selesai  date,
    tahap_pengerjaan        jsonb,   -- [{"tahap":"Persiapan","mulai":"2026-01-01","selesai":"2026-02-01","estimasi_hari":31}, ...]
    status_proyek           status_proyek     NOT NULL DEFAULT 'direncanakan',
    progres_persen          smallint          NOT NULL DEFAULT 0,

    -- Gambar
    gambar_uuid             uuid,
    gambar_ekstensi         varchar(4),
    gambar_mime             varchar(30),
    gambar_ukuran_byte      integer,

    -- Kepemilikan & alur konfirmasi
    status_konfirmasi       status_konfirmasi NOT NULL DEFAULT 'draft_admin',
    dibuat_oleh_admin_id    uuid              REFERENCES tbl_admin(id)      ON DELETE RESTRICT,
    pemilik_pemerintah_id   uuid              REFERENCES tbl_pemerintah(id) ON DELETE RESTRICT,
    dikonfirmasi_at         timestamptz,
    created_at              timestamptz       NOT NULL DEFAULT now(),
    updated_at              timestamptz       NOT NULL DEFAULT now(),

    CONSTRAINT chk_proyek_progres   CHECK (progres_persen BETWEEN 0 AND 100),
    CONSTRAINT chk_proyek_biaya     CHECK (biaya_pengadaan IS NULL OR biaya_pengadaan >= 0),
    CONSTRAINT chk_proyek_tanggal   CHECK (tanggal_target_selesai IS NULL OR tanggal_mulai IS NULL
                                           OR tanggal_target_selesai >= tanggal_mulai),
    CONSTRAINT chk_proyek_tahap     CHECK (tahap_pengerjaan IS NULL OR jsonb_typeof(tahap_pengerjaan) = 'array'),
    CONSTRAINT chk_proyek_selesai   CHECK (status_proyek <> 'selesai' OR progres_persen = 100),
    CONSTRAINT chk_proyek_gbr_ext   CHECK (gambar_ekstensi IS NULL OR gambar_ekstensi IN ('jpg','png','webp')),
    CONSTRAINT chk_proyek_gbr_mime  CHECK (gambar_mime IS NULL OR gambar_mime IN ('image/jpeg','image/png','image/webp')),
    CONSTRAINT chk_proyek_gbr_size  CHECK (gambar_ukuran_byte IS NULL OR gambar_ukuran_byte BETWEEN 1 AND 10485760),
    CONSTRAINT chk_proyek_gbr_lengkap CHECK (
        (gambar_uuid IS NULL AND gambar_ekstensi IS NULL AND gambar_mime IS NULL AND gambar_ukuran_byte IS NULL)
     OR (gambar_uuid IS NOT NULL AND gambar_ekstensi IS NOT NULL AND gambar_mime IS NOT NULL AND gambar_ukuran_byte IS NOT NULL)),
    -- Proyek terkonfirmasi wajib punya pemilik pemerintah & detail utama
    CONSTRAINT chk_proyek_konfirmasi CHECK (
        status_konfirmasi <> 'terkonfirmasi'
        OR (pemilik_pemerintah_id IS NOT NULL AND dikonfirmasi_at IS NOT NULL
            AND biaya_pengadaan IS NOT NULL AND tahap_pengerjaan IS NOT NULL))
);
CREATE INDEX idx_proyek_status        ON tbl_proyek (status_proyek);
CREATE INDEX idx_proyek_konfirmasi    ON tbl_proyek (status_konfirmasi);
CREATE INDEX idx_proyek_pemilik       ON tbl_proyek (pemilik_pemerintah_id);
CREATE TRIGGER trg_proyek_updated BEFORE UPDATE ON tbl_proyek
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

-- ---------------------------------------------------------------------
-- 5. tbl_forum  = topik/ruang diskusi (mis. "Akses pejalan kaki di Stasiun Manggarai")
--    tbl_chat   = pesan-pesan di dalam sebuah forum
--    - Hanya warga yang bisa membuat topik & mengirim chat, hanya teks
--    - Admin menghapus chat lewat soft delete -> tampil "Chat ini dihapus karena ..."
-- ---------------------------------------------------------------------
CREATE TABLE tbl_forum (
    id          uuid            PRIMARY KEY DEFAULT gen_random_uuid(),
    warga_id    uuid            NOT NULL REFERENCES tbl_warga(id) ON DELETE RESTRICT,  -- pembuat topik
    proyek_id   uuid            REFERENCES tbl_proyek(id) ON DELETE SET NULL,          -- opsional: diskusi terkait proyek
    kategori    kategori_forum  NOT NULL,
    judul       varchar(200)    NOT NULL,
    deskripsi   varchar(1000),
    created_at  timestamptz     NOT NULL DEFAULT now(),
    updated_at  timestamptz     NOT NULL DEFAULT now(),
    CONSTRAINT chk_forum_judul CHECK (char_length(btrim(judul)) >= 3)
);
CREATE INDEX idx_forum_kategori ON tbl_forum (kategori, created_at DESC);
CREATE INDEX idx_forum_warga    ON tbl_forum (warga_id);
CREATE INDEX idx_forum_proyek   ON tbl_forum (proyek_id);
CREATE TRIGGER trg_forum_updated BEFORE UPDATE ON tbl_forum
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

CREATE TABLE tbl_chat (
    id                  uuid         PRIMARY KEY DEFAULT gen_random_uuid(),
    forum_id            uuid         NOT NULL REFERENCES tbl_forum(id) ON DELETE RESTRICT,
    warga_id            uuid         NOT NULL REFERENCES tbl_warga(id) ON DELETE RESTRICT,
    balasan_ke_chat_id  uuid         REFERENCES tbl_chat(id) ON DELETE RESTRICT,  -- opsional: membalas chat tertentu
    isi                 text         NOT NULL,
    dihapus             boolean      NOT NULL DEFAULT false,
    alasan_hapus        varchar(500),
    dihapus_oleh_admin  uuid         REFERENCES tbl_admin(id) ON DELETE RESTRICT,
    dihapus_at          timestamptz,
    created_at          timestamptz  NOT NULL DEFAULT now(),
    updated_at          timestamptz  NOT NULL DEFAULT now(),

    CONSTRAINT chk_chat_isi_len CHECK (char_length(btrim(isi)) BETWEEN 1 AND 2000),
    CONSTRAINT chk_chat_hapus   CHECK (
        (dihapus = false AND alasan_hapus IS NULL AND dihapus_oleh_admin IS NULL AND dihapus_at IS NULL)
     OR (dihapus = true  AND alasan_hapus IS NOT NULL AND dihapus_oleh_admin IS NOT NULL AND dihapus_at IS NOT NULL))
);
CREATE INDEX idx_chat_forum ON tbl_chat (forum_id, created_at);
CREATE INDEX idx_chat_warga ON tbl_chat (warga_id, created_at DESC);  -- dasar rate limiting anti-spam
CREATE TRIGGER trg_chat_updated BEFORE UPDATE ON tbl_chat
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

-- ---------------------------------------------------------------------
-- 6-8. Tabel LOG (append-only, tidak bisa diubah/dihapus)
--      Dilengkapi hash chain (prev_hash -> row_hash) agar manipulasi
--      terdeteksi (mitigasi Repudiation & Tampering).
--      Tidak memakai FK ke tabel user/proyek agar log tetap utuh
--      walaupun data sumber berubah, dan agar percobaan login dengan
--      email yang tidak dikenal tetap tercatat.
-- ---------------------------------------------------------------------
CREATE TABLE tbl_log_login (
    id              uuid         PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id         uuid,                         -- NULL jika email tidak dikenal
    role            role_user,
    email_percobaan citext       NOT NULL,
    tahap           varchar(10)  NOT NULL,        -- 'password' | 'otp' | 'logout'
    berhasil        boolean      NOT NULL,
    alasan_gagal    varchar(100),                 -- salah_password, captcha_gagal, akun_terkunci, otp_salah, ...
    ip_address      inet,
    user_agent      varchar(500),
    prev_hash       bytea,
    row_hash        bytea,
    created_at      timestamptz  NOT NULL DEFAULT now(),
    CONSTRAINT chk_log_login_tahap CHECK (tahap IN ('password','otp','logout'))
);
CREATE INDEX idx_log_login_user  ON tbl_log_login (user_id, created_at DESC);
CREATE INDEX idx_log_login_ip    ON tbl_log_login (ip_address, created_at DESC);
CREATE INDEX idx_log_login_waktu ON tbl_log_login (created_at DESC);

CREATE TABLE tbl_log_forum (
    id           uuid         PRIMARY KEY DEFAULT gen_random_uuid(),
    forum_id     uuid         NOT NULL,
    chat_id      uuid,                           -- NULL untuk aksi level forum
    aktor_id     uuid         NOT NULL,
    aktor_role   role_user    NOT NULL,
    aksi         varchar(20)  NOT NULL,          -- buat_forum | buat_chat | hapus_chat
    alasan       varchar(500),
    isi_snapshot text,                           -- salinan isi chat saat dihapus (bukti moderasi)
    ip_address   inet,
    prev_hash    bytea,
    row_hash     bytea,
    created_at   timestamptz  NOT NULL DEFAULT now(),
    CONSTRAINT chk_log_forum_aksi CHECK (aksi IN ('buat_forum','buat_chat','hapus_chat'))
);
CREATE INDEX idx_log_forum_forum ON tbl_log_forum (forum_id, created_at DESC);
CREATE INDEX idx_log_forum_chat  ON tbl_log_forum (chat_id);
CREATE INDEX idx_log_forum_aktor ON tbl_log_forum (aktor_id, created_at DESC);

CREATE TABLE tbl_log_proyek (
    id          uuid         PRIMARY KEY DEFAULT gen_random_uuid(),
    proyek_id   uuid         NOT NULL,
    aktor_id    uuid         NOT NULL,
    aktor_role  role_user    NOT NULL,
    aksi        varchar(20)  NOT NULL,            -- buat | ubah | konfirmasi | hapus
    data_lama   jsonb,                            -- jejak perubahan (mitigasi Tampering)
    data_baru   jsonb,
    ip_address  inet,
    prev_hash   bytea,
    row_hash    bytea,
    created_at  timestamptz  NOT NULL DEFAULT now(),
    CONSTRAINT chk_log_proyek_aksi CHECK (aksi IN ('buat','ubah','konfirmasi','hapus'))
);
CREATE INDEX idx_log_proyek_proyek ON tbl_log_proyek (proyek_id, created_at DESC);
CREATE INDEX idx_log_proyek_aktor  ON tbl_log_proyek (aktor_id, created_at DESC);

-- Hash chain: row_hash = SHA256(prev_hash || isi baris tanpa row_hash)
CREATE OR REPLACE FUNCTION log_hash_chain() RETURNS trigger AS $$
DECLARE
    v_prev bytea;
BEGIN
    -- serialisasi insert per tabel agar rantai konsisten
    PERFORM pg_advisory_xact_lock(hashtext(TG_TABLE_NAME));
    EXECUTE format('SELECT row_hash FROM %I.%I ORDER BY created_at DESC, id DESC LIMIT 1',
                   TG_TABLE_SCHEMA, TG_TABLE_NAME) INTO v_prev;
    NEW.prev_hash := v_prev;
    NEW.row_hash  := NULL;
    NEW.row_hash  := digest(coalesce(v_prev, '\x'::bytea) || convert_to(to_jsonb(NEW)::text, 'UTF8'), 'sha256');
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Larang UPDATE / DELETE / TRUNCATE pada tabel log
CREATE OR REPLACE FUNCTION log_immutable() RETURNS trigger AS $$
BEGIN
    RAISE EXCEPTION 'Tabel % bersifat append-only: % tidak diizinkan', TG_TABLE_NAME, TG_OP
        USING ERRCODE = 'insufficient_privilege';
END;
$$ LANGUAGE plpgsql;

DO $$
DECLARE t text;
BEGIN
    FOREACH t IN ARRAY ARRAY['tbl_log_login','tbl_log_forum','tbl_log_proyek'] LOOP
        EXECUTE format('CREATE TRIGGER trg_%s_chain BEFORE INSERT ON %I
                        FOR EACH ROW EXECUTE FUNCTION log_hash_chain()', t, t);
        EXECUTE format('CREATE TRIGGER trg_%s_nomod BEFORE UPDATE OR DELETE ON %I
                        FOR EACH ROW EXECUTE FUNCTION log_immutable()', t, t);
        EXECUTE format('CREATE TRIGGER trg_%s_notrunc BEFORE TRUNCATE ON %I
                        FOR EACH STATEMENT EXECUTE FUNCTION log_immutable()', t, t);
    END LOOP;
END $$;

-- ---------------------------------------------------------------------
-- View dashboard
-- ---------------------------------------------------------------------
-- Dashboard proyek (pemerintah & beranda warga)
CREATE VIEW v_dashboard_proyek AS
SELECT
    count(*)                                              AS total_proyek,
    count(*) FILTER (WHERE status_proyek = 'dalam_pengerjaan') AS dalam_pengerjaan,
    count(*) FILTER (WHERE status_proyek = 'selesai')     AS selesai,
    count(*) FILTER (WHERE status_proyek <> 'selesai')    AS belum_selesai
FROM tbl_proyek;

-- Dashboard website (admin): jumlah user & aktivitas login sebagai proxy traffic
CREATE VIEW v_dashboard_admin AS
SELECT
    (SELECT count(*) FROM tbl_warga)      AS jumlah_warga,
    (SELECT count(*) FROM tbl_pemerintah) AS jumlah_pemerintah,
    (SELECT count(*) FROM tbl_admin)      AS jumlah_admin,
    (SELECT count(*) FROM tbl_log_login
      WHERE berhasil AND tahap IN ('password','otp') AND created_at >= now() - interval '24 hours') AS login_sukses_24jam,
    (SELECT count(*) FROM tbl_log_login
      WHERE NOT berhasil AND created_at >= now() - interval '24 hours') AS login_gagal_24jam,
    (SELECT count(*) FROM tbl_chat
      WHERE created_at >= now() - interval '24 hours') AS chat_24jam;

-- Chat: teks yang ditampilkan ke warga (pesan pengganti bila dihapus)
CREATE VIEW v_chat_tampil AS
SELECT id, forum_id, warga_id, balasan_ke_chat_id,
       CASE WHEN dihapus THEN 'Chat ini dihapus karena ' || alasan_hapus ELSE isi END AS isi_tampil,
       dihapus, created_at
FROM tbl_chat;

-- Verifikasi kesinambungan hash chain log (prev_hash harus = row_hash baris sebelumnya)
CREATE VIEW v_verifikasi_log_login AS
SELECT id, prev_hash IS NOT DISTINCT FROM lag(row_hash) OVER (ORDER BY created_at, id) AS rantai_valid
FROM tbl_log_login;
CREATE VIEW v_verifikasi_log_forum AS
SELECT id, prev_hash IS NOT DISTINCT FROM lag(row_hash) OVER (ORDER BY created_at, id) AS rantai_valid
FROM tbl_log_forum;
CREATE VIEW v_verifikasi_log_proyek AS
SELECT id, prev_hash IS NOT DISTINCT FROM lag(row_hash) OVER (ORDER BY created_at, id) AS rantai_valid
FROM tbl_log_proyek;

-- ---------------------------------------------------------------------
-- Hak akses database (Deny by Default)
-- Aplikasi PHP memakai role app_jc, BUKAN owner/superuser.
-- Otorisasi per-peran (admin/pemerintah/warga) & cek kepemilikan ID
-- tetap diterapkan di sisi server aplikasi (WHERE id = :id AND warga_id = :session_user).
-- ---------------------------------------------------------------------
REVOKE ALL ON SCHEMA jakarta_connect FROM PUBLIC;
REVOKE ALL ON ALL TABLES IN SCHEMA jakarta_connect FROM PUBLIC;

DO $$
BEGIN
    IF NOT EXISTS (SELECT FROM pg_roles WHERE rolname = 'app_jc') THEN
        CREATE ROLE app_jc LOGIN PASSWORD 'GANTI_DENGAN_PASSWORD_KUAT' NOSUPERUSER NOCREATEDB NOCREATEROLE;
    END IF;
END $$;

GRANT USAGE ON SCHEMA jakarta_connect TO app_jc;
GRANT SELECT, INSERT, UPDATE ON tbl_warga, tbl_pemerintah, tbl_admin, tbl_proyek, tbl_forum, tbl_chat TO app_jc;
-- Tidak ada DELETE: penghapusan forum = soft delete pada tbl_chat; akun dinonaktifkan lewat kolom status.
GRANT SELECT, INSERT ON tbl_log_login, tbl_log_forum, tbl_log_proyek TO app_jc;  -- append-only
GRANT SELECT ON v_dashboard_proyek, v_dashboard_admin, v_chat_tampil TO app_jc;

COMMIT;