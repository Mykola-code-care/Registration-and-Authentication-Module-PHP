<?php
/**
 * Simple mailer using PHP mail() or SMTP via PHPMailer if available.
 * For production use: composer require phpmailer/phpmailer and configure SMTP in config.
 */

function sendMail(string $to, string $subject, string $bodyHtml, string $bodyText = ''): bool {
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        return sendMailSMTP($to, $subject, $bodyHtml, $bodyText);
    }
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=utf-8',
        'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM_EMAIL . '>',
    ];
    return @mail($to, $subject, $bodyHtml, implode("\r\n", $headers));
}

function sendMailSMTP(string $to, string $subject, string $bodyHtml, string $bodyText): bool {
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USER;
        $mail->Password   = MAIL_PASS;
        $mail->SMTPSecure = MAIL_PORT === 465 ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        $mail->CharSet    = 'UTF-8';
        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body    = $bodyHtml;
        $mail->AltBody = $bodyText ?: strip_tags($bodyHtml);
        $mail->isHTML(true);
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mail error: ' . $e->getMessage());
        return false;
    }
}

function sendConfirmEmail(string $email, string $name, string $token): bool {
    $url = APP_URL . '/confirm.php?token=' . urlencode($token);
    $subject = 'Confirm your email - ' . APP_NAME;
    $body = "
    <h2>Hello, " . htmlspecialchars($name) . "!</h2>
    <p>Click the link below to confirm your email:</p>
    <p><a href=\"" . htmlspecialchars($url) . "\">Confirm email</a></p>
    <p>This link is valid for 24 hours.</p>
    <p>If you did not sign up, please ignore this email.</p>
    ";
    return sendMail($email, $subject, $body);
}

function sendPasswordResetEmail(string $email, string $name, string $token): bool {
    $url = APP_URL . '/reset-password.php?token=' . urlencode($token);
    $subject = 'Password reset - ' . APP_NAME;
    $body = "
    <h2>Password reset</h2>
    <p>Hello, " . htmlspecialchars($name) . "!</p>
    <p>Click the link below to set a new password:</p>
    <p><a href=\"" . htmlspecialchars($url) . "\">Set new password</a></p>
    <p>This link is valid for 1 hour.</p>
    <p>If you did not request a reset, please ignore this email.</p>
    ";
    return sendMail($email, $subject, $body);
}
