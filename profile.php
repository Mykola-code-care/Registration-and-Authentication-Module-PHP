<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/layout.php';

$user = requireAuth();
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf'] ?? '';
    if (!validateCsrf($csrf)) {
        $errors['form'] = 'Security error. Please refresh the page.';
    } else {
        $action = $_POST['action'] ?? 'profile';
        if ($action === 'profile') {
            $result = updateProfile($user['id'], $_POST['name'] ?? '');
            if ($result['ok']) {
                $success = 'Profile updated.';
                $user = currentUser();
            } else {
                $errors = $result['errors'];
            }
        } elseif ($action === 'password') {
            $result = changePassword(
                $user['id'],
                $_POST['current_password'] ?? '',
                $_POST['new_password'] ?? ''
            );
            if ($result['ok']) {
                $success = 'Password changed.';
            } else {
                $errors = $result['errors'];
            }
        }
    }
}

ob_start();
?>
<div class="container container--wide">
    <div class="card form-card">
        <h1>Profile</h1>
        <?php if ($success): ?>
            <p class="success-msg"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>
        <?php if (!empty($errors['form'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['form']) ?></p>
        <?php endif; ?>

        <div class="profile-info">
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?>
                <?php if ($user['email_verified_at']): ?>
                    <span class="badge badge-success">Verified</span>
                <?php else: ?>
                    <span class="badge badge-warning">Not verified</span>
                <?php endif; ?>
            </p>
            <p><strong>Registered:</strong> <?= date('d.m.Y', strtotime($user['created_at'])) ?></p>
        </div>

        <h2>Change name</h2>
        <form method="post" action="profile.php" class="auth-form">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrfToken()) ?>">
            <input type="hidden" name="action" value="profile">
            <div class="field">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                <?php if (!empty($errors['name'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['name']) ?></span>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>

        <h2>Change password</h2>
        <form method="post" action="profile.php" class="auth-form" id="profile-password-form">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrfToken()) ?>">
            <input type="hidden" name="action" value="password">
            <div class="field">
                <label for="current_password">Current password</label>
                <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
                <?php if (!empty($errors['current_password'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['current_password']) ?></span>
                <?php endif; ?>
            </div>
            <div class="field">
                <label for="new_password">New password (min. 8 characters)</label>
                <input type="password" id="new_password" name="new_password" minlength="8" autocomplete="new-password">
                <?php if (!empty($errors['new_password'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['new_password']) ?></span>
                <?php endif; ?>
            </div>
            <div class="field">
                <label for="new_password_confirm">Confirm new password</label>
                <input type="password" id="new_password_confirm" name="new_password_confirm" autocomplete="new-password">
                <span class="field-error" id="new_password_confirm_error" aria-live="polite"></span>
            </div>
            <button type="submit" class="btn btn-primary">Change password</button>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
renderLayout('Profile', $content, ['user' => $user]);
