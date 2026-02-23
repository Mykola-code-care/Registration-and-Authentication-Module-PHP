<?php
require_once __DIR__ . '/db.php';

function currentUser(): ?array {
    if (empty($_SESSION['user_id'])) return null;
    $pdo = getDb();
    $st = $pdo->prepare("SELECT id, email, name, email_verified_at, created_at FROM users WHERE id = ?");
    $st->execute([$_SESSION['user_id']]);
    $u = $st->fetch(PDO::FETCH_ASSOC);
    return $u ?: null;
}

function requireAuth(): array {
    $u = currentUser();
    if (!$u) {
        header('Location: login.php');
        exit;
    }
    return $u;
}

function requireGuest(): void {
    if (currentUser()) {
        header('Location: index.php');
        exit;
    }
}

function csrfToken(): string {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function validateCsrf(string $token): bool {
    return !empty($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

function registerUser(string $email, string $password, string $name, bool $agreed): array {
    $err = [];
    $email = trim(strtolower($email));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $err['email'] = 'Invalid email';
    if (strlen($password) < 8) $err['password'] = 'Password must be at least 8 characters';
    if (trim($name) === '') $err['name'] = 'Please enter your name';
    if (!$agreed) $err['agreement'] = 'You must accept the terms of use';

    if ($err) return ['ok' => false, 'errors' => $err];

    $pdo = getDb();
    $st = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $st->execute([$email]);
    if ($st->fetch()) {
        $err['email'] = 'This email is already registered';
        return ['ok' => false, 'errors' => $err];
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $agreedAt = date('Y-m-d H:i:s');
    $pdo->prepare("INSERT INTO users (email, password_hash, name, agreed_at) VALUES (?, ?, ?, ?)")
        ->execute([$email, $hash, trim($name), $agreedAt]);
    $userId = (int) $pdo->lastInsertId();

    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', time() + EMAIL_CONFIRM_EXPIRY);
    $pdo->prepare("INSERT INTO tokens (user_id, type, token, expires_at) VALUES (?, 'email_confirm', ?, ?)")
        ->execute([$userId, $token, $expires]);

    return ['ok' => true, 'user_id' => $userId, 'confirm_token' => $token];
}

function loginUser(string $email, string $password): array {
    $err = [];
    $email = trim(strtolower($email));
    if ($email === '') $err['email'] = 'Please enter your email';
    if ($password === '') $err['password'] = 'Please enter your password';
    if ($err) return ['ok' => false, 'errors' => $err];

    $pdo = getDb();
    $st = $pdo->prepare("SELECT id, password_hash FROM users WHERE email = ?");
    $st->execute([$email]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if (!$row || !password_verify($password, $row['password_hash'])) {
        $err['email'] = 'Invalid email or password';
        return ['ok' => false, 'errors' => $err];
    }

    $_SESSION['user_id'] = (int) $row['id'];
    return ['ok' => true];
}

function confirmEmail(string $token): array {
    $pdo = getDb();
    $st = $pdo->prepare("
        SELECT t.user_id FROM tokens t
        WHERE t.type = 'email_confirm' AND t.token = ? AND t.expires_at > datetime('now')
    ");
    $st->execute([$token]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if (!$row) return ['ok' => false, 'message' => 'Link is invalid or expired'];

    $pdo->prepare("UPDATE users SET email_verified_at = datetime('now') WHERE id = ?")->execute([$row['user_id']]);
    $pdo->prepare("DELETE FROM tokens WHERE type = 'email_confirm' AND user_id = ?")->execute([$row['user_id']]);
    return ['ok' => true];
}

function resendConfirmEmail(int $userId): array {
    $pdo = getDb();
    $st = $pdo->prepare("SELECT email, name, email_verified_at FROM users WHERE id = ?");
    $st->execute([$userId]);
    $u = $st->fetch(PDO::FETCH_ASSOC);
    if (!$u || $u['email_verified_at']) {
        return ['ok' => false, 'message' => 'Email already verified'];
    }
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', time() + EMAIL_CONFIRM_EXPIRY);
    $pdo->prepare("DELETE FROM tokens WHERE user_id = ? AND type = 'email_confirm'")->execute([$userId]);
    $pdo->prepare("INSERT INTO tokens (user_id, type, token, expires_at) VALUES (?, 'email_confirm', ?, ?)")
        ->execute([$userId, $token, $expires]);
    return ['ok' => true, 'email' => $u['email'], 'name' => $u['name'], 'confirm_token' => $token];
}

function requestPasswordReset(string $email): array {
    $email = trim(strtolower($email));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => true]; // Don't reveal if email exists
    }

    $pdo = getDb();
    $st = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $st->execute([$email]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if (!$row) return ['ok' => true];

    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', time() + PASSWORD_RESET_EXPIRY);
    $pdo->prepare("DELETE FROM tokens WHERE user_id = ? AND type = 'password_reset'")->execute([$row['id']]);
    $pdo->prepare("INSERT INTO tokens (user_id, type, token, expires_at) VALUES (?, 'password_reset', ?, ?)")
        ->execute([$row['id'], $token, $expires]);

    $st = $pdo->prepare("SELECT name FROM users WHERE id = ?");
    $st->execute([$row['id']]);
    $user = $st->fetch(PDO::FETCH_ASSOC);
    return ['ok' => true, 'token' => $token, 'user_id' => $row['id'], 'email' => $email, 'name' => $user['name'] ?? ''];
}

function resetPassword(string $token, string $password): array {
    $err = [];
    if (strlen($password) < 8) $err['password'] = 'Password must be at least 8 characters';
    if ($err) return ['ok' => false, 'errors' => $err];

    $pdo = getDb();
    $st = $pdo->prepare("
        SELECT user_id FROM tokens
        WHERE type = 'password_reset' AND token = ? AND expires_at > datetime('now')
    ");
    $st->execute([$token]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if (!$row) return ['ok' => false, 'errors' => ['token' => 'Link is invalid or expired']];

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE users SET password_hash = ?, updated_at = datetime('now') WHERE id = ?")
        ->execute([$hash, $row['user_id']]);
    $pdo->prepare("DELETE FROM tokens WHERE type = 'password_reset' AND user_id = ?")->execute([$row['user_id']]);
    return ['ok' => true];
}

function updateProfile(int $userId, string $name): array {
    $name = trim($name);
    if ($name === '') return ['ok' => false, 'errors' => ['name' => 'Please enter your name']];

    $pdo = getDb();
    $pdo->prepare("UPDATE users SET name = ?, updated_at = datetime('now') WHERE id = ?")->execute([$name, $userId]);
    return ['ok' => true];
}

function changePassword(int $userId, string $currentPassword, string $newPassword): array {
    $err = [];
    if (strlen($newPassword) < 8) $err['new_password'] = 'New password must be at least 8 characters';
    if ($err) return ['ok' => false, 'errors' => $err];

    $pdo = getDb();
    $st = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
    $st->execute([$userId]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if (!$row || !password_verify($currentPassword, $row['password_hash'])) {
        $err['current_password'] = 'Invalid current password';
        return ['ok' => false, 'errors' => $err];
    }

    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE users SET password_hash = ?, updated_at = datetime('now') WHERE id = ?")
        ->execute([$hash, $userId]);
    return ['ok' => true];
}
