<?php
use App\Csrf;
$csrfToken = $csrfToken ?? Csrf::generateToken();
?>

<div class="auth-card">
    <div class="brand">
        <h2>Registrasi Warga</h2>
        <p>JakartaConnect</p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="/register" class="auth-form" id="registerForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

        <div class="form-group">
            <label for="nama_lengkap">Nama Lengkap (Sesuai KTP)</label>
            <input type="text" id="nama_lengkap" name="nama_lengkap" required value="<?= htmlspecialchars($old['nama_lengkap'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-group">
            <label for="nik">NIK (16 Digit Angka)</label>
            <input type="text" id="nik" name="nik" maxlength="16" required value="<?= htmlspecialchars($old['nik'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-group">
            <label for="password">Password (Min. 8 Karakter, Kombinasi Capital, Angka & Simbol)</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="form-group-checkbox">
            <label class="checkbox-label">
                <input type="checkbox" class="toggle-password" data-target="password,confirm_password">
                <span>Tampilkan Password</span>
            </label>
        </div>

        <div class="form-group">
            <label for="confirm_password">Konfirmasi Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required placeholder="Ulangi password Anda">
        </div>

        <div class="form-group-checkbox">
            <label class="checkbox-label">
                <input type="checkbox" class="toggle-password" data-target="password,confirm_password">
                <span>Tampilkan Password</span>
            </label>
        </div>

        <div class="form-group" style="margin-top: 14px;">
            <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($recaptchaSiteKey, ENT_QUOTES, 'UTF-8') ?>"></div>
        </div>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>

        <button type="submit" class="btn btn-primary">Daftar Akun</button>
    </form>

    <p class="auth-footer">Sudah punya akun? <a href="/login">Masuk ke sistem</a></p>
</div>