<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/mailer.php';

requireGuest();

$errors = [];
$done = false;
$emailFound = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf'] ?? '';
    if (!validateCsrf($csrf)) {
        $errors['form'] = 'Security error. Please refresh the page.';
    } else {
        $email = $_POST['email'] ?? '';
        $result = requestPasswordReset($email);
        if (!empty($result['token']) && !empty($result['email'])) {
            sendPasswordResetEmail($result['email'], $result['name'] ?? '', $result['token']);
            $emailFound = true;
        }
        $done = true;
    }
}

ob_start();
?>
<div class="container">
    <div class="card form-card">
        <h1>Reset password</h1>
        <?php if ($done): ?>
            <?php if ($emailFound): ?>
                <p class="success-msg">If an account exists with this email, you will receive a link to reset your password. Please check your inbox.</p>
            <?php else: ?>
                <p class="form-error">No account found with this email address.</p>
            <?php endif; ?>
            <p><a href="login.php" class="btn btn-secondary">Back to log in</a></p>
        <?php else: ?>
            <?php if (!empty($errors['form'])): ?>
                <p class="form-error"><?= htmlspecialchars($errors['form']) ?></p>
            <?php endif; ?>
            <form method="post" action="forgot-password.php" class="auth-form">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrfToken()) ?>">
                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required autocomplete="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Send link</button>
            </form>
        <?php endif; ?>
        <p class="form-footer"><a href="login.php" class="link">Back to log in</a></p>
    </div>
</div>
<?php
$content = ob_get_clean();
renderLayout('Reset password', $content);
