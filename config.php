<?php
/**
 * Application configuration
 * For email: use Mailtrap.io (free) or Brevo/Sendinblue SMTP
 */

define('APP_NAME', 'Auth Demo');
define('APP_URL', 'http://localhost:8080'); // Change for production

// Database (SQLite - no setup required)
define('DB_PATH', __DIR__ . '/data/auth.db');

// Optional local config (copy config.local.php.example to config.local.php)
if (is_file(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}

// Session
define('SESSION_LIFETIME', 3600 * 24 * 7); // 7 days

// Email (SMTP) — values from config.local.php or env take precedence
if (!defined('MAIL_HOST')) define('MAIL_HOST', getenv('MAIL_HOST') ?: 'smtp.mailtrap.io');
if (!defined('MAIL_PORT')) define('MAIL_PORT', (int)(getenv('MAIL_PORT') ?: 2525));
if (!defined('MAIL_USER')) define('MAIL_USER', getenv('MAIL_USER') ?: '');
if (!defined('MAIL_PASS')) define('MAIL_PASS', getenv('MAIL_PASS') ?: '');
if (!defined('MAIL_FROM_EMAIL')) define('MAIL_FROM_EMAIL', getenv('MAIL_FROM') ?: 'noreply@example.com');
if (!defined('MAIL_FROM_NAME')) define('MAIL_FROM_NAME', APP_NAME);

// Token expiry (seconds)
define('EMAIL_CONFIRM_EXPIRY', 86400);   // 24 hours
define('PASSWORD_RESET_EXPIRY', 3600);   // 1 hour

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');

// When mail fails, show confirm link on screen (for local development)
define('SHOW_CONFIRM_LINK_WHEN_MAIL_FAILS', true);

// Composer (PHPMailer)
if (is_file(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
}

session_start();
