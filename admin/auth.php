<?php
// Session-based authentication & shared data helpers for Jobarn Admin
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    ]);
    session_start();
}

define('DATA_FILE', __DIR__ . '/../data/products.json');
define('SLIDES_FILE', __DIR__ . '/../data/slides.json');
define('SETTINGS_FILE', __DIR__ . '/../data/settings.json');
define('MESSAGES_FILE', __DIR__ . '/../messages/messages.json');
define('ADMIN_CREDENTIALS_FILE', __DIR__ . '/../data/admin.json');

// Default fallback credentials used ONLY before custom credentials are saved
define('DEFAULT_ADMIN_USER', 'admin');
define('DEFAULT_ADMIN_PASS', 'jobarn2026!');

/**
 * Check whether custom admin credentials file exists and is valid
 */
function hasCustomAdminCredentials() {
    if (!file_exists(ADMIN_CREDENTIALS_FILE)) {
        return false;
    }
    $raw = @file_get_contents(ADMIN_CREDENTIALS_FILE);
    $data = @json_decode((string)$raw, true);
    return is_array($data) && !empty($data['username']) && !empty($data['password_hash']);
}

/**
 * Retrieve current admin account details
 */
function getAdminAccount() {
    if (file_exists(ADMIN_CREDENTIALS_FILE)) {
        $raw = @file_get_contents(ADMIN_CREDENTIALS_FILE);
        $data = @json_decode((string)$raw, true);
        if (is_array($data) && !empty($data['username'])) {
            return $data;
        }
    }
    return [
        'username' => DEFAULT_ADMIN_USER,
        'password_hash' => password_hash(DEFAULT_ADMIN_PASS, PASSWORD_DEFAULT),
        'updated_at' => null
    ];
}

/**
 * Verify given credentials against current admin account
 */
function verifyAdminCredentials($username, $password) {
    $account = getAdminAccount();
    if (!hash_equals((string)$account['username'], (string)$username)) {
        return false;
    }

    if (hasCustomAdminCredentials()) {
        return password_verify((string)$password, (string)$account['password_hash']);
    }

    // First-time default login before admin.json is created
    if (!empty($account['password_hash']) && password_verify((string)$password, (string)$account['password_hash'])) {
        return true;
    }
    return $username === DEFAULT_ADMIN_USER && hash_equals(DEFAULT_ADMIN_PASS, (string)$password);
}

/**
 * Update admin credentials securely
 */
function updateAdminCredentials($newUsername, $newPassword) {
    $dir = dirname(ADMIN_CREDENTIALS_FILE);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $data = [
        'username' => trim($newUsername),
        'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return false;
    }
    return @file_put_contents(ADMIN_CREDENTIALS_FILE, $json, LOCK_EX) !== false;
}

function isLoggedIn() {
    return isset($_SESSION['jobarn_admin_logged']) && $_SESSION['jobarn_admin_logged'] === true;
}

function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * CSRF protection helpers
 */
function getCsrfToken() {
    if (empty($_SESSION['jobarn_csrf_token'])) {
        $_SESSION['jobarn_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['jobarn_csrf_token'];
}

function verifyCsrfToken($token) {
    if (empty($_SESSION['jobarn_csrf_token']) || !is_string($token)) {
        return false;
    }
    return hash_equals($_SESSION['jobarn_csrf_token'], $token);
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(getCsrfToken(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Helper to write JSON file safely with optional storefront mirror
 */
function writeJsonFile($path, $data, $mirrorPath = null) {
    $dir = dirname($path);
    if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
        return false;
    }
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        return false;
    }
    $res = @file_put_contents($path, $json, LOCK_EX);
    if ($res === false) {
        return false;
    }
    if ($mirrorPath) {
        $mirrorDir = dirname($mirrorPath);
        if (!is_dir($mirrorDir)) {
            @mkdir($mirrorDir, 0755, true);
        }
        @file_put_contents($mirrorPath, $json, LOCK_EX);
    }
    return true;
}

function readJsonArray($path, $fallbackPath = null) {
    $target = file_exists($path) ? $path : ($fallbackPath && file_exists($fallbackPath) ? $fallbackPath : null);
    if (!$target) {
        return [];
    }
    $raw = @file_get_contents($target);
    if ($raw === false) {
        return [];
    }
    $data = @json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/**
 * Canonical Store Product Categories
 */
function getProductCategories() {
    return [
        'photography' => 'Photography & Cameras',
        'pro-video' => 'Pro Video & Cinema',
        'camcorders' => 'Camcorders',
        'drones' => 'Drones & Aerial',
        'lighting' => 'Studio & Lighting',
        'optics' => 'Optics & Scopes',
        'computers' => 'Computers & Laptops',
        'monitors' => 'Monitors & Displays',
        'keyboards' => 'Keyboards & Peripherals',
        'desks' => 'Standing Desks',
        'chairs' => 'Ergonomic Chairs',
        'accessories' => 'Accessories & Gadgets',
        'audio' => 'Pro Audio & Microphones',
        'security' => 'Surveillance & CCTV',
        'networking' => 'Networking & IT Infrastructure',
        'gaming' => 'Gaming Gear',
        'used' => 'Certified Used Gear'
    ];
}

function getProducts() {
    return readJsonArray(DATA_FILE, __DIR__ . '/../assets/ecommerce/products.json');
}

function saveProducts($products) {
    return writeJsonFile(DATA_FILE, array_values($products), __DIR__ . '/../assets/ecommerce/products.json');
}

function getSlides() {
    $slides = readJsonArray(SLIDES_FILE, __DIR__ . '/../assets/ecommerce/slides.json');
    usort($slides, function ($a, $b) {
        return (int)($a['order'] ?? 0) - (int)($b['order'] ?? 0);
    });
    return $slides;
}

function saveSlides($slides) {
    return writeJsonFile(SLIDES_FILE, array_values($slides), __DIR__ . '/../assets/ecommerce/slides.json');
}

function getDefaultSettings() {
    return [
        'siteName' => 'JOBARN',
        'siteTagline' => 'Professional Tech, Ergonomics, Photo & Video Store Tanzania',
        'contactEmail' => 'info@jobarn.co.tz',
        'contactPhone' => '+255 716-026-781',
        'contactAddress' => "SkyCity Mall, 1st Floor\nOff Mlimani City, University Road\nDar es Salaam, Tanzania",
        'footerText' => '© 2026 Jobarn General Trading Company LTD, All Rights Reserved',
        'facebook' => '',
        'instagram' => '',
        'twitter' => '',
        'whatsapp' => '255716026781',
        'youtube' => '',
        'linkedin' => ''
    ];
}

function getSettings() {
    $defaults = getDefaultSettings();
    $data = readJsonArray(SETTINGS_FILE, __DIR__ . '/../assets/ecommerce/settings.json');
    return array_merge($defaults, $data);
}

function saveSettings($settings) {
    $merged = array_merge(getDefaultSettings(), $settings);
    return writeJsonFile(SETTINGS_FILE, $merged, __DIR__ . '/../assets/ecommerce/settings.json');
}

function getMessages() {
    $messages = readJsonArray(MESSAGES_FILE);
    foreach ($messages as $idx => &$m) {
        if (empty($m['id'])) {
            $seed = ($m['date'] ?? '') . '|' . ($m['email'] ?? '') . '|' . $idx;
            $m['id'] = 'msg_' . substr(sha1($seed), 0, 12);
        }
    }
    unset($m);
    usort($messages, function ($a, $b) {
        return strtotime($b['date'] ?? '0') - strtotime($a['date'] ?? '0');
    });
    return $messages;
}

function saveMessages($messages) {
    return writeJsonFile(MESSAGES_FILE, array_values($messages));
}

/**
 * Resolve asset URL cleanly inside /admin/
 */
function adminAssetSrc($path) {
    $path = trim((string)$path);
    if ($path === '') {
        return 'https://placehold.co/120x120?text=No+Image';
    }
    if (preg_match('#^(https?://|data:|/)#i', $path)) {
        return $path;
    }
    return '../' . ltrim($path, './');
}

/**
 * Validate and save an uploaded image into uploads/
 * Returns ['ok' => bool, 'path' => string|null, 'error' => string|null]
 */
function handleImageUpload($file, $prefix = 'img_', $maxBytes = 5242880) {
    if (!isset($file) || !isset($file['error']) || is_array($file['error'])) {
        return ['ok' => false, 'path' => null, 'error' => 'Invalid file upload payload.'];
    }
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => true, 'path' => null, 'error' => null];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errMap = [
            UPLOAD_ERR_INI_SIZE => 'Uploaded image exceeds server upload limit.',
            UPLOAD_ERR_FORM_SIZE => 'Uploaded image exceeds form size limit.',
            UPLOAD_ERR_PARTIAL => 'Image upload was interrupted. Please try again.',
            UPLOAD_ERR_NO_TMP_DIR => 'Server temporary upload folder is missing.',
            UPLOAD_ERR_CANT_WRITE => 'Server failed to write uploaded image to disk.',
        ];
        return ['ok' => false, 'path' => null, 'error' => $errMap[$file['error']] ?? 'Image upload failed (code ' . $file['error'] . ').'];
    }
    if (($file['size'] ?? 0) > $maxBytes) {
        return ['ok' => false, 'path' => null, 'error' => 'Image file is too large (maximum 5 MB).'];
    }

    $allowedExts = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
        'gif' => 'image/gif'
    ];
    $ext = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
    if (!isset($allowedExts[$ext])) {
        return ['ok' => false, 'path' => null, 'error' => 'Unsupported image format. Please upload JPG, PNG, WEBP, or GIF.'];
    }

    $imgInfo = @getimagesize($file['tmp_name']);
    if ($imgInfo === false || !in_array($imgInfo['mime'] ?? '', array_values($allowedExts), true)) {
        return ['ok' => false, 'path' => null, 'error' => 'The uploaded file is not a valid image.'];
    }

    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir) && !@mkdir($uploadDir, 0755, true)) {
        return ['ok' => false, 'path' => null, 'error' => 'Could not create uploads directory.'];
    }

    $newFileName = $prefix . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $targetPath = $uploadDir . $newFileName;
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['ok' => false, 'path' => null, 'error' => 'Failed to save uploaded image to uploads/.'];
    }

    return ['ok' => true, 'path' => 'uploads/' . $newFileName, 'error' => null];
}
