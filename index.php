<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/layout.php';

$user = currentUser();

ob_start();
if ($user):
?>
<div class="container">
    <div class="card welcome-card">
        <h1>Welcome, <?= htmlspecialchars($user['name']) ?>!</h1>
        <p>You are logged in.</p>
        <?php if (!$user['email_verified_at']): ?>
            <p class="notice">Please check your email and confirm your address. <a href="profile.php">Profile</a></p>
        <?php else: ?>
            <p>Email confirmed. <a href="profile.php">Go to profile</a></p>
        <?php endif; ?>
    </div>
</div>
<?php
else:
?>
<div class="container">
    <div class="card welcome-card">
        <h1>Welcome</h1>
        <p>Log in or sign up to continue.</p>
        <div class="actions">
            <a href="login.php" class="btn btn-primary">Log in</a>
            <a href="register.php" class="btn btn-secondary">Sign up</a>
        </div>
    </div>
</div>
<?php
endif;
$content = ob_get_clean();
renderLayout('Home', $content, ['user' => $user]);
