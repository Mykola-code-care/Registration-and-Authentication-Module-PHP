<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$user = requireAuth();

if (!validateCsrf($_POST['csrf'] ?? '')) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Security error. Please try again.'];
    header('Location: index.php');
    exit;
}

$result = resendConfirmEmail($user['id']);
if ($result['ok'] && !empty($result['confirm_token'])) {
    $sent = sendConfirmEmail($result['email'], $result['name'], $result['confirm_token']);
    if ($sent) {
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Confirmation email sent again. Please check your inbox.'];
    } else {
        $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Could not send the email. You can try again or use the link from the previous email.'];
    }
} else {
    $_SESSION['flash'] = ['type' => 'info', 'message' => $result['message'] ?? 'Email already verified.'];
}
$redirect = $_POST['redirect'] ?? 'index.php';
header('Location: ' . (in_array($redirect, ['index.php', 'profile.php'], true) ? $redirect : 'index.php'));
exit;
