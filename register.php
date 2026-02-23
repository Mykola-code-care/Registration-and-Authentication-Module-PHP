<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/mailer.php';

requireGuest();

$errors = [];
$email = $name = '';
$agreed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf'] ?? '';
    if (!validateCsrf($csrf)) {
        $errors['form'] = 'Security error. Please refresh the page.';
    } else {
        $email = $_POST['email'] ?? '';
        $name = $_POST['name'] ?? '';
        $agreed = !empty($_POST['agreement']);
        $result = registerUser($email, $_POST['password'] ?? '', $name, $agreed);
        if ($result['ok']) {
            $confirmUrl = APP_URL . '/confirm.php?token=' . urlencode($result['confirm_token']);
            $sent = sendConfirmEmail($email, $name, $result['confirm_token']);
            if ($sent) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Registration successful. Please check your email to confirm.'];
            } elseif (defined('SHOW_CONFIRM_LINK_WHEN_MAIL_FAILS') && SHOW_CONFIRM_LINK_WHEN_MAIL_FAILS) {
                $_SESSION['flash'] = [
                    'type' => 'warning',
                    'message' => 'Registration successful, but the email could not be sent. Confirm your email using the link below.',
                    'confirm_link' => $confirmUrl,
                ];
            } else {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Registration successful. Please check your email to confirm.'];
            }
            header('Location: login.php');
            exit;
        }
        $errors = $result['errors'];
    }
}

ob_start();
?>
<div class="container">
    <div class="card form-card">
        <h1>Sign up</h1>
        <?php if (!empty($errors['form'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['form']) ?></p>
        <?php endif; ?>
        <form method="post" action="register.php" class="auth-form" id="register-form">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrfToken()) ?>">
            <div class="field">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required autocomplete="name">
                <?php if (!empty($errors['name'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['name']) ?></span>
                <?php endif; ?>
            </div>
            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required autocomplete="email">
                <?php if (!empty($errors['email'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['email']) ?></span>
                <?php endif; ?>
            </div>
            <div class="field">
                <label for="password">Password (min. 8 characters)</label>
                <input type="password" id="password" name="password" required autocomplete="new-password" minlength="8">
                <?php if (!empty($errors['password'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['password']) ?></span>
                <?php endif; ?>
            </div>
            <div class="field">
                <label for="password_confirm">Confirm password</label>
                <input type="password" id="password_confirm" name="password_confirm" required autocomplete="new-password">
                <span class="field-error" id="password_confirm_error" aria-live="polite"></span>
            </div>
            <div class="field checkbox-field">
                <label class="checkbox-label">
                    <input type="checkbox" name="agreement" id="agreement" <?= $agreed ? 'checked' : '' ?> required>
                    I agree to the <a href="#" class="link" id="agreement-link">terms of use</a> and privacy policy
                </label>
                <?php if (!empty($errors['agreement'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['agreement']) ?></span>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary btn-block" id="register-submit">Sign up</button>
        </form>
        <p class="form-footer">Already have an account? <a href="login.php">Log in</a></p>
    </div>
</div>
<div id="agreement-modal" class="modal" role="dialog" aria-labelledby="modal-title" aria-hidden="true">
    <div class="modal-backdrop"></div>
    <div class="modal-content">
        <h2 id="modal-title">Terms of use</h2>
        <div class="modal-body">
            <p>By signing up on this site, you agree to keep your data confidential and not to violate community guidelines. We only store the data necessary to run the service and do not share it with third parties without your consent.</p>
            <p>You agree to provide accurate information when registering and confirming your email.</p>
        </div>
        <button type="button" class="btn btn-primary modal-close">Close</button>
    </div>
</div>
<?php
$content = ob_get_clean();
renderLayout('Sign up', $content);
