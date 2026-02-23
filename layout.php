<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

function renderLayout(string $title, string $content, array $vars = []): void {
    extract($vars);
    $user = $user ?? currentUser();
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    ?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> — <?= htmlspecialchars(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container header-inner">
            <a href="index.php" class="logo"><?= htmlspecialchars(APP_NAME) ?></a>
            <nav>
                <?php if ($user): ?>
                    <a href="profile.php">Profile</a>
                    <a href="logout.php">Log out</a>
                <?php else: ?>
                    <a href="login.php">Log in</a>
                    <a href="register.php">Sign up</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <?php if ($flash): ?>
        <div class="flash flash-<?= htmlspecialchars($flash['type'] ?? 'info') ?>">
            <div class="container">
                <?= htmlspecialchars($flash['message']) ?>
                <?php if (!empty($flash['confirm_link'])): ?>
                    <p class="flash-confirm-link"><a href="<?= htmlspecialchars($flash['confirm_link']) ?>">Confirm email now</a></p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    <main class="main">
        <?= $content ?>
    </main>
    <footer class="footer">
        <div class="container">&copy; <?= date('Y') ?> <?= htmlspecialchars(APP_NAME) ?></div>
    </footer>
    <script src="assets/js/app.js"></script>
</body>
</html><?php
}
