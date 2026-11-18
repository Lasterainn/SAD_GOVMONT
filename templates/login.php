<?php
use App\Csrf;
$csrfToken = $csrfToken ?? Csrf::generateToken();
?>

<div class="auth-card">
    <div class="brand">
        <h2>JakartaConnect</h2>
        <p>Portal Pelayanan & Aspirasi Warga</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="POST" action="/login" class="auth-form" id="loginForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required placeholder="email@contoh.id">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder="Password Anda">
        </div>

        <div class="form-group-checkbox">
            <label class="checkbox-label">
                <input type="checkbox" class="toggle-password" data-target="password">
                <span>Tampilkan Password</span>
            </label>
        </div>

        <?php if (!empty($showCaptcha)): ?>
            <div class="form-group" style="margin-top: 14px;">
                <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($recaptchaSiteKey ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
            </div>
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary" style="margin-top: 14px;">Masuk</button>
    </form>

    <p class="auth-footer">Warga baru? <a href="/register">Registrasi Akun Warga</a></p>
</div>