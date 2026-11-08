<?php
require_once __DIR__ . '/auth.php';
requireAuth();
require_once __DIR__ . '/navbar.php';

$account = getAdminAccount();
$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Security token expired. Please refresh and try again.';
        $messageType = 'danger';
    } else {
        $newUsername = trim($_POST['username'] ?? '');
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($newUsername === '' || strlen($newUsername) < 3 || !preg_match('/^[a-zA-Z0-9._-]+$/', $newUsername)) {
            $message = 'Username must be at least 3 characters and use only letters, numbers, dots, underscores, or hyphens.';
            $messageType = 'danger';
        } elseif ($currentPassword === '') {
            $message = 'Please provide your current password to verify your identity.';
            $messageType = 'danger';
        } elseif (!verifyAdminCredentials($account['username'], $currentPassword)) {
            $message = 'Current password is incorrect. Please double check and try again.';
            $messageType = 'danger';
        } elseif ($newPassword === '') {
            $message = 'Please enter your new password.';
            $messageType = 'danger';
        } elseif (strlen($newPassword) < 6) {
            $message = 'New password must be at least 6 characters long.';
            $messageType = 'danger';
        } elseif ($newPassword !== $confirmPassword) {
            $message = 'New password and confirmation do not match.';
            $messageType = 'danger';
        } else {
            if (updateAdminCredentials($newUsername, $newPassword)) {
                session_regenerate_id(true);
                $_SESSION['jobarn_admin_user'] = $newUsername;
                $account = getAdminAccount();
                $message = 'Username and password updated successfully!';
                $messageType = 'success';
            } else {
                $message = 'Failed to write credentials file. Please check folder permissions on data/.';
                $messageType = 'danger';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security & Password - Jobarn Admin</title>
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .security-grid {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 24px;
            max-width: 1020px;
        }
        @media (max-width: 960px) {
            .security-grid {
                grid-template-columns: 1fr;
            }
        }
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .card-header {
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
        }
        .card-header h3 {
            font-size: 17px;
            font-weight: 800;
            color: var(--text-heading);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .card-header p {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 4px;
        }
        .form-group { margin-bottom: 18px; }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 7px;
        }
        .form-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }
        .form-input-wrap i.field-icon {
            position: absolute;
            left: 14px;
            color: var(--text-muted);
            font-size: 14px;
        }
        .form-input-wrap .form-control {
            padding-left: 40px;
            padding-right: 40px;
        }
        .toggle-pwd-btn {
            position: absolute;
            right: 12px;
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 6px;
            font-size: 14px;
        }
        .toggle-pwd-btn:hover { color: var(--text-heading); }
        .btn-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 12px 20px;
            background: var(--primary);
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 8px;
            font-family: inherit;
        }
        .btn-submit:hover {
            background: #1e293b;
            transform: translateY(-1px);
        }
        .info-pill {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px;
            border-radius: 12px;
            background: #f8fafc;
            border: 1px solid var(--border);
            margin-bottom: 14px;
        }
        .info-pill:last-child { margin-bottom: 0; }
        .info-pill i {
            font-size: 18px;
            color: var(--accent);
            margin-top: 2px;
        }
        .info-pill-text h5 {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--text-heading);
        }
        .info-pill-text p {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 3px;
            line-height: 1.4;
        }
    </style>
</head>
<body>

<?php renderAdminNav('change-password.php', 'Security & Password', 'Manage your admin login credentials'); ?>

<main class="main-content">
    <div class="admin-container">
        <?php if (!empty($message)): ?>
            <div class="alert-bar <?php echo htmlspecialchars($messageType); ?>">
                <i class="fa-solid <?php echo $messageType === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?>"></i>
                <span><?php echo htmlspecialchars($message); ?></span>
            </div>
        <?php endif; ?>

        <div class="security-grid">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fa-solid fa-key" style="color: var(--accent);"></i> Update Admin Credentials</h3>
                    <p>Change your login username and password. We recommend at least 8 characters with letters and numbers.</p>
                </div>

                <form method="POST" action="change-password.php">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="update_password" value="1">

                    <div class="form-group">
                        <label for="adminUsernameInput">Admin Username *</label>
                        <div class="form-input-wrap">
                            <i class="fa-solid fa-user field-icon"></i>
                            <input type="text" id="adminUsernameInput" name="username" class="form-control" value="<?php echo htmlspecialchars($account['username'] ?? 'admin'); ?>" required minlength="3">
                        </div>
                        <span class="form-hint">You can keep "admin" or set a custom login username.</span>
                    </div>

                    <div class="form-group">
                        <label for="current_pwd">Current Password *</label>
                        <div class="form-input-wrap">
                            <i class="fa-solid fa-lock field-icon"></i>
                            <input type="password" id="current_pwd" name="current_password" class="form-control" placeholder="Enter existing password" required>
                            <button type="button" class="toggle-pwd-btn" onclick="toggleVisibility('current_pwd', this)" aria-label="Toggle password">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="new_pwd">New Password *</label>
                        <div class="form-input-wrap">
                            <i class="fa-solid fa-key field-icon"></i>
                            <input type="password" id="new_pwd" name="new_password" class="form-control" placeholder="Enter new strong password" required minlength="6">
                            <button type="button" class="toggle-pwd-btn" onclick="toggleVisibility('new_pwd', this)" aria-label="Toggle password">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_pwd">Confirm New Password *</label>
                        <div class="form-input-wrap">
                            <i class="fa-solid fa-check-double field-icon"></i>
                            <input type="password" id="confirm_pwd" name="confirm_password" class="form-control" placeholder="Re-type new password" required minlength="6">
                            <button type="button" class="toggle-pwd-btn" onclick="toggleVisibility('confirm_pwd', this)" aria-label="Toggle password">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-shield-halved"></i> Save New Credentials
                    </button>
                </form>
            </div>

            <div>
                <div class="card" style="margin-bottom: 20px;">
                    <div class="card-header">
                        <h3><i class="fa-solid fa-circle-info" style="color: var(--accent);"></i> Account Info</h3>
                        <p>Current administrative session</p>
                    </div>

                    <div class="info-pill">
                        <i class="fa-solid fa-user-shield"></i>
                        <div class="info-pill-text">
                            <h5>Active User</h5>
                            <p><strong><?php echo htmlspecialchars($_SESSION['jobarn_admin_user'] ?? 'admin'); ?></strong></p>
                        </div>
                    </div>

                    <div class="info-pill">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <div class="info-pill-text">
                            <h5>Last Updated</h5>
                            <p><?php echo !empty($account['updated_at']) ? htmlspecialchars($account['updated_at']) : 'Default installation credentials'; ?></p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3><i class="fa-solid fa-lightbulb" style="color: #f59e0b;"></i> Password Tips</h3>
                    </div>
                    <div style="font-size: 13px; color: var(--text-muted); line-height: 1.6;">
                        <ul style="padding-left: 18px;">
                            <li>Mix uppercase letters, lowercase letters, numbers, and symbols.</li>
                            <li>Avoid common dictionary words or predictable phrases.</li>
                            <li>Once updated, the default password is permanently disabled.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function toggleVisibility(id, btn) {
    const input = document.getElementById(id);
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
