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
            <p class="notice">Please check your email and confirm your address.</p>
            <div class="actions">
                <a href="profile.php" class="btn btn-primary">Profile</a>
                <form method="post" action="resend-confirm.php" class="btn-inline">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrfToken()) ?>">
                    <button type="submit" class="btn btn-secondary">Resend</button>
                </form>
            </div>
        <?php else: ?>
            <p>Email confirmed. <a href="profile.php">Go to profile</a></p>
        <?php endif; ?>
    </div>
</div>
<?php
if ($user && !$user['email_verified_at']):
?>
<div id="confirm-email-modal" class="modal" role="dialog" aria-labelledby="confirm-email-title" aria-hidden="false">
    <div class="modal-backdrop"></div>
    <div class="modal-content">
        <h2 id="confirm-email-title">Confirm your email</h2>
        <p class="modal-body">Please check your email and confirm your address.</p>
        <div class="modal-actions">
            <a href="profile.php" class="btn btn-primary">Profile</a>
            <form method="post" action="resend-confirm.php" class="btn-inline">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrfToken()) ?>">
                <button type="submit" class="btn btn-secondary">Resend</button>
            </form>
        </div>
        <button type="button" class="btn btn-secondary modal-close" style="margin-top: 1rem;">Close</button>
    </div>
</div>
<?php endif; ?>
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
