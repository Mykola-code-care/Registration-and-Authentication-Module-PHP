<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/layout.php';

requireGuest();

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf'] ?? '';
    if (!validateCsrf($csrf)) {
        $errors['form'] = 'Security error. Please refresh the page.';
    } else {
        $result = loginUser($_POST['email'] ?? '', $_POST['password'] ?? '');
        if ($result['ok']) {
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'You have logged in successfully.'];
            header('Location: index.php');
            exit;
        }
        $errors = $result['errors'];
        $email = $_POST['email'] ?? '';
    }
}

ob_start();
?>
<div class="container">
    <div class="card form-card">
        <h1>Log in</h1>
        <?php if (!empty($errors['form'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['form']) ?></p>
        <?php endif; ?>
        <form method="post" action="login.php" class="auth-form" id="login-form">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrfToken()) ?>">
            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required autocomplete="email">
                <?php if (!empty($errors['email'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['email']) ?></span>
                <?php endif; ?>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
                <?php if (!empty($errors['password'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['password']) ?></span>
                <?php endif; ?>
            </div>
            <div class="field">
                <a href="forgot-password.php" class="link">Forgot password?</a>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Log in</button>
        </form>
        <p class="form-footer">Don't have an account? <a href="register.php">Sign up</a></p>
    </div>
</div>
<?php
$content = ob_get_clean();
renderLayout('Log in', $content);
