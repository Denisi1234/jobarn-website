<?php
require_once __DIR__ . '/auth.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$submittedUsername = '';

// Rate-limiting by IP (max 5 failed attempts in 15 minutes)
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$lockKey = 'login_attempts_' . md5($ip);
$attempts = $_SESSION[$lockKey] ?? ['count' => 0, 'first_attempt' => time()];

if ((time() - $attempts['first_attempt']) >= 900) {
    $attempts = ['count' => 0, 'first_attempt' => time()];
    unset($_SESSION[$lockKey]);
}

if ($attempts['count'] >= 5 && (time() - $attempts['first_attempt']) < 900) {
    $remaining = ceil((900 - (time() - $attempts['first_attempt'])) / 60);
    $error = "Too many failed login attempts. Please wait {$remaining} minutes before trying again.";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $submittedUsername = $username;

    if (verifyAdminCredentials($username, $password)) {
        unset($_SESSION[$lockKey]);
        session_regenerate_id(true);
        $_SESSION['jobarn_admin_logged'] = true;
        $_SESSION['jobarn_admin_user'] = $username;
        getCsrfToken();
        header('Location: index.php');
        exit;
    } else {
        if ($attempts['count'] === 0) {
            $attempts['first_attempt'] = time();
        }
        $attempts['count']++;
        $_SESSION[$lockKey] = $attempts;
        $remainingTries = max(0, 5 - $attempts['count']);
        $error = $remainingTries > 0
            ? "Invalid credentials. {$remainingTries} attempts remaining before temporary lockout."
            : "Too many failed attempts. Account locked for 15 minutes.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Jobarn Admin Portal - Login</title>
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-primary: #111827;
            --brand-accent: #2563eb;
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: var(--card-bg);
            border-radius: 20px;
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.08), 0 0 1px 1px rgba(0,0,0,0.03);
            width: 100%;
            max-width: 440px;
            padding: 40px 36px;
        }
        .logo-wrap {
            text-align: center;
            margin-bottom: 28px;
        }
        .logo-wrap img {
            max-height: 54px;
            width: auto;
            border-radius: 8px;
        }
        .portal-title {
            font-size: 22px;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.5px;
            margin-top: 14px;
        }
        .portal-sub {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 6px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
        }
        .input-group {
            position: relative;
        }
        .input-group > i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 15px;
        }
        .input-field {
            width: 100%;
            padding: 13px 42px 13px 40px;
            font-size: 14px;
            border: 1.5px solid var(--border-color);
            border-radius: 12px;
            background: #ffffff;
            color: var(--text-main);
            outline: none;
            transition: all 0.2s ease;
            font-family: inherit;
        }
        .input-field:focus {
            border-color: var(--brand-accent);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }
        .toggle-pwd {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 6px;
            font-size: 14px;
        }
        .toggle-pwd:hover { color: #334155; }
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: #0f172a;
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 24px;
            font-family: inherit;
        }
        .btn-submit:hover {
            background: #1e293b;
            transform: translateY(-1px);
            box-shadow: 0 10px 20px -5px rgba(15, 23, 42, 0.25);
        }
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fee2e2;
            color: #b91c1c;
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #64748b;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
        }
        .back-link:hover { color: #0f172a; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="logo-wrap">
        <img src="../assets/img/jobarn-logo.jpeg" alt="Jobarn Logo" onerror="this.src='https://placehold.co/180x50?text=JOBARN'">
        <h1 class="portal-title">Jobarn Admin Portal</h1>
        <p class="portal-sub">Manage products, slides, messages & store settings</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert-error">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label class="form-label" for="loginUsername">Username</label>
            <div class="input-group">
                <i class="fa-solid fa-user"></i>
                <input type="text" id="loginUsername" name="username" class="input-field" placeholder="Enter username" required autofocus value="<?php echo htmlspecialchars($submittedUsername); ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="loginPassword">Password</label>
            <div class="input-group">
                <i class="fa-solid fa-lock"></i>
                <input type="password" id="loginPassword" name="password" class="input-field" placeholder="Enter password" required>
                <button type="button" class="toggle-pwd" onclick="toggleLoginPassword(this)" aria-label="Show password">
                    <i class="fa-regular fa-eye"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="btn-submit">
            <span>Sign In to Portal</span>
            <i class="fa-solid fa-arrow-right"></i>
        </button>
    </form>

    <a href="../index.html" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> Back to Jobarn Website
    </a>
</div>

<script>
function toggleLoginPassword(btn) {
    const input = document.getElementById('loginPassword');
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>

</body>
</html>
