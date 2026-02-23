<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/layout.php';

requireGuest();

$token = trim($_GET['token'] ?? '');
$errors = [];
$success = false;

if ($token === '') {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Missing password reset link.'];
    header('Location: forgot-password.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf'] ?? '';
    if (!validateCsrf($csrf)) {
        $errors['form'] = 'Security error. Please refresh the page.';
    } else {
        $password = $_POST['password'] ?? '';
        $result = resetPassword($token, $password);
        if ($result['ok']) {
            $success = true;
        } else {
            $errors = $result['errors'];
        }
    }
}

ob_start();
?>
<div class="container">
    <div class="card form-card">
        <h1>New password</h1>
        <?php if ($success): ?>
            <p class="success-msg">Password changed. You can now log in.</p>
            <a href="login.php" class="btn btn-primary">Log in</a>
        <?php else: ?>
            <?php if (!empty($errors['form'])): ?>
                <p class="form-error"><?= htmlspecialchars($errors['form']) ?></p>
            <?php endif; ?>
            <form method="post" action="reset-password.php?token=<?= htmlspecialchars(urlencode($token)) ?>" class="auth-form" id="reset-form">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrfToken()) ?>">
                <div class="field">
                    <label for="password">New password (min. 8 characters)</label>
                    <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password">
                    <?php if (!empty($errors['password'])): ?>
                        <span class="field-error"><?= htmlspecialchars($errors['password']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($errors['token'])): ?>
                        <span class="field-error"><?= htmlspecialchars($errors['token']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="field">
                    <label for="password_confirm">Confirm password</label>
                    <input type="password" id="password_confirm" name="password_confirm" required autocomplete="new-password">
                    <span class="field-error" id="password_confirm_error" aria-live="polite"></span>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Change password</button>
            </form>
        <?php endif; ?>
        <p class="form-footer"><a href="login.php" class="link">Back to log in</a></p>
    </div>
</div>
<?php
$content = ob_get_clean();
renderLayout('New password', $content);
