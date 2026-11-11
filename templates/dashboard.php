<div class="dashboard-card">
    <h2>Login Berhasil</h2>
    <div class="user-info">
        <p>Selamat datang di JakartaConnect,</p>
        <h3><?= htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8') ?></h3>
        <p class="role-tag">Role: <strong><?= htmlspecialchars(strtoupper($_SESSION['user_role']), ENT_QUOTES, 'UTF-8') ?></strong></p>
    </div>

    <form method="POST" action="/logout" style="margin-top: 24px;">
        <button type="submit" class="btn btn-secondary">Logout</button>
    </form>
</div>