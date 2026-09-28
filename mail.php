<?php
// JOBARN Contact Form Handler - hardened professional version
declare(strict_types=1);
header('Content-Type: text/plain; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    echo 'Invalid request method.';
    exit;
}

// Honeypot anti-spam (field must exist in form as hidden "company")
if (!empty($_POST['company'] ?? '')) {
    http_response_code(200);
    echo 'Message received. Thank you.';
    exit;
}

// Simple rate-limit: max 5 submissions per IP per 10 minutes
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rateFile = sys_get_temp_dir() . '/jobarn_rate_' . md5($ip) . '.log';
$now = time();
$attempts = [];
if (file_exists($rateFile)) {
    $attempts = array_filter(array_map('intval', explode(',', (string)file_get_contents($rateFile))), fn($t) => ($now - $t) < 600);
}
if (count($attempts) >= 5) {
    http_response_code(429);
    echo 'Too many requests. Please try again in 10 minutes or call 0716 026 781.';
    exit;
}
$attempts[] = $now;
@file_put_contents($rateFile, implode(',', $attempts), LOCK_EX);

function clean_text(string $v, int $max): string {
    $v = trim($v);
    $v = str_replace(["\r", "\n", "%0a", "%0d"], ' ', $v);
    $v = strip_tags($v);
    if (mb_strlen($v) > $max) $v = mb_substr($v, 0, $max);
    return $v;
}

$name = clean_text((string)($_POST['name'] ?? ''), 100);
$emailRaw = trim((string)($_POST['email'] ?? ''));
$email = filter_var($emailRaw, FILTER_VALIDATE_EMAIL) ? $emailRaw : '';
$subject = clean_text((string)($_POST['subject'] ?? ''), 150);
$phone = clean_text((string)($_POST['phone'] ?? ''), 30);
$message = clean_text((string)($_POST['message'] ?? ''), 3000);

// Strict name/phone to prevent header injection
if (!preg_match('/^[\p{L}\p{M} .\'-]{2,100}$/u', $name)) {
    http_response_code(400);
    echo 'Please enter a valid name.';
    exit;
}
if ($phone !== '' && !preg_match('/^[0-9+\s-]{6,30}$/', $phone)) {
    http_response_code(400);
    echo 'Please enter a valid phone number.';
    exit;
}
if ($name === '' || $email === '' || $subject === '' || mb_strlen($message) < 5) {
    http_response_code(400);
    echo 'Please complete Name, valid Email, Subject and Message.';
    exit;
}

$recipient = 'info@jobarn.co.tz, contact@jobarn.co.tz';
$safeSubject = 'New Contact: ' . $subject . ' - from ' . $name;

$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^a-zA-Z0-9.\-:]/', '', (string)$_SERVER['HTTP_HOST']) : 'jobarn.co.tz';
$email_content = "New message from JOBARN website\n";
$email_content .= "============================================\n";
$email_content .= "Name: $name\nEmail: $email\nPhone: $phone\nSubject: $subject\n";
$email_content .= "Message:\n$message\n============================================\n";
$email_content .= "Site: $host\nIP: $ip\nDate: " . date('Y-m-d H:i:s') . "\n";

$email_headers = "From: JOBARN Website <noreply@jobarn.co.tz>\r\n";
$email_headers .= "Reply-To: $email\r\n";
$email_headers .= "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n";
$email_headers .= "X-Mailer: PHP/" . phpversion() . "\r\nX-Content-Type-Options: nosniff\r\n";

// Private log outside public_html when possible, fallback to messages/ (protected by .htaccess)
$baseDir = __DIR__;
$privateDir = dirname($baseDir) . '/jobarn_private';
$logDir = is_writable(dirname($baseDir)) ? $privateDir : $baseDir . '/messages';
if (!is_dir($logDir)) @mkdir($logDir, 0750, true);
$logLine = date('Y-m-d H:i:s') . " | $name <$email> | $phone | $subject | $ip\n";
@file_put_contents($logDir . '/messages.log', $logLine . $message . "\n---\n", FILE_APPEND | LOCK_EX);

$sent = @mail($recipient, $safeSubject, $email_content, $email_headers);

http_response_code(200);
$ref = date('Ymd-His');
if ($sent) {
    echo "Thank you $name. Your message has been sent. We will reply shortly. Ref: $ref";
} else {
    echo "Thank you $name. Your message has been received. We will contact you shortly. Ref: $ref";
}
