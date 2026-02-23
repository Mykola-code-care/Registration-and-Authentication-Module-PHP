<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/layout.php';

$token = trim($_GET['token'] ?? '');
if ($token === '') {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Missing confirmation link.'];
    header('Location: index.php');
    exit;
}

$result = confirmEmail($token);
if ($result['ok']) {
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Email confirmed. You can now log in.'];
} else {
    $_SESSION['flash'] = ['type' => 'error', 'message' => $result['message'] ?? 'Link is invalid.'];
}
header('Location: login.php');
exit;
