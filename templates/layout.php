<?php
$layoutMode = $layoutMode ?? 'portal';
$activeNav  = $activeNav ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'JakartaConnect') ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="<?= $layoutMode === 'auth' ? 'layout-auth' : 'layout-portal' ?>">
<?php if ($layoutMode === 'auth'): ?>
    <div class="app-container">
        <?php include $viewFile; ?>
        <p class="auth-back-home"><a href="/">&larr; Kembali ke Beranda JakartaConnect</a></p>
    </div>
<?php else: ?>
    <?php include __DIR__ . '/partials/navbar.php'; ?>
    <main class="portal-main">
        <?php include $viewFile; ?>
    </main>
    <?php include __DIR__ . '/partials/footer.php'; ?>
<?php endif; ?>
    <script src="/js/app.js"></script>
</body>
</html>
