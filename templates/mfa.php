<?php
use App\Csrf;
$csrfToken = $csrfToken ?? Csrf::generateToken();
?>

<div class="auth-card">
  <div class="brand">
    <h2>Verifikasi 2 Langkah</h2>
    <p>Login <?= htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8') ?> · JakartaConnect</p>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <p class="mfa-info">
    Kami mengirim kode 6 digit ke <strong><?= htmlspecialchars($maskedEmail, ENT_QUOTES, 'UTF-8') ?></strong>.
    Kode berlaku 5 menit.
  </p>

  <form method="POST" action="/mfa" class="auth-form" id="mfaForm">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

    <div class="form-group">
        <label for="otp">Kode OTP</label>
        <input type="text" id="otp" name="otp" class="otp-input"
                inputmode="numeric" pattern="[0-9]{6}" maxlength="6" minlength="6"
                autocomplete="one-time-code" placeholder="••••••" required autofocus>
    </div>

    <button type="submit" class="btn btn-primary">Verifikasi</button>
</form>

<div class="mfa-actions">
    <form method="POST" action="/mfa/resend">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
      <button type="submit" class="btn-link">Kirim ulang kode</button>
    </form>
    <form method="POST" action="/mfa/cancel">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
      <button type="submit" class="btn-link">Batal, kembali ke login</button>
    </form>
  </div>
</div>