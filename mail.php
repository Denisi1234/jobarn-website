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

function clean_text(string $v, int $max, bool $keepNewlines = false): string {
    $v = trim($v);
    if ($keepNewlines) {
        $v = str_replace(["\r\n", "\r"], "\n", $v);
    } else {
        $v = str_replace(["\r", "\n", "%0a", "%0d"], ' ', $v);
    }
    $v = strip_tags($v);
    if (mb_strlen($v) > $max) $v = mb_substr($v, 0, $max);
    return $v;
}

$name = clean_text((string)($_POST['name'] ?? ''), 100);
$emailRaw = str_replace(["\r", "\n", "%0a", "%0d"], '', trim((string)($_POST['email'] ?? '')));
$email = filter_var($emailRaw, FILTER_VALIDATE_EMAIL) ? $emailRaw : '';
$subject = clean_text((string)($_POST['subject'] ?? ''), 150);
$phone = clean_text((string)($_POST['phone'] ?? ''), 30);
$message = clean_text((string)($_POST['message'] ?? ''), 3000, true);

// Strict name/phone to prevent header injection
if (!preg_match('/^[\p{L}\p{M} .\'-]{2,100}$/u', $name)) {
    http_response_code(400);
    echo 'Please enter a valid name.';
    exit;
}
if ($phone !== '' && !preg_match('/^[0-9+\s()-]{6,30}$/', $phone)) {
    http_response_code(400);
    echo 'Please enter a valid phone number.';
    exit;
}
if ($name === '' || $email === '' || $subject === '' || mb_strlen($message) < 5) {
    http_response_code(400);
    echo 'Please complete Name, valid Email, Subject and Message.';
    exit;
}

// Resolve recipient email from site settings if configured
$recipient = 'info@jobarn.co.tz, contact@jobarn.co.tz';
$settingsFile = __DIR__ . '/data/settings.json';
if (file_exists($settingsFile)) {
    $settingsData = @json_decode((string)@file_get_contents($settingsFile), true);
    if (is_array($settingsData) && !empty($settingsData['contactEmail'])) {
        $cfgEmail = str_replace(["\r", "\n"], '', trim((string)$settingsData['contactEmail']));
        if (filter_var($cfgEmail, FILTER_VALIDATE_EMAIL)) {
            $recipient = $cfgEmail === 'info@jobarn.co.tz'
                ? 'info@jobarn.co.tz, contact@jobarn.co.tz'
                : $cfgEmail . ', info@jobarn.co.tz';
        }
    }
}

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

$baseDir = __DIR__;

// Save structured JSON message for Admin Inbox (messages/messages.json)
$msgsDir = $baseDir . '/messages';
if (!is_dir($msgsDir)) {
    @mkdir($msgsDir, 0755, true);
}
$msgsJsonFile = $msgsDir . '/messages.json';
$entry = [
    'id' => 'msg_' . time() . '_' . bin2hex(random_bytes(4)),
    'date' => gmdate('c'),
    'name' => $name,
    'email' => $email,
    'phone' => $phone,
    'subject' => $subject,
    'message' => $message,
    'ip' => $ip,
    'read' => false
];
$fp = @fopen($msgsJsonFile, 'c+');
if ($fp) {
    if (@flock($fp, LOCK_EX)) {
        $raw = stream_get_contents($fp);
        $list = $raw ? @json_decode($raw, true) : [];
        if (!is_array($list)) {
            $list = [];
        }
        array_unshift($list, $entry);
        $encoded = json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($encoded !== false) {
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, $encoded);
            fflush($fp);
        }
        @flock($fp, LOCK_UN);
    }
    @fclose($fp);
}

// Private log outside public_html when possible, fallback to messages/ (protected by .htaccess)
$privateDir = dirname($baseDir) . '/jobarn_private';
$logDir = is_writable(dirname($baseDir)) ? $privateDir : $msgsDir;
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
