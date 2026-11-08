<?php
require_once __DIR__ . '/auth.php';
requireAuth();
require_once __DIR__ . '/navbar.php';

$settings = getSettings();
$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Security token expired. Please refresh and try again.';
        $messageType = 'danger';
    } else {
        $siteName = trim($_POST['siteName'] ?? '');
        $siteTagline = trim($_POST['siteTagline'] ?? '');
        $contactEmail = trim($_POST['contactEmail'] ?? '');
        $contactPhone = trim($_POST['contactPhone'] ?? '');
        $contactAddress = trim($_POST['contactAddress'] ?? '');
        $footerText = trim($_POST['footerText'] ?? '');
        $whatsapp = preg_replace('/[^0-9]/', '', trim($_POST['whatsapp'] ?? ''));
        if (strlen($whatsapp) === 10 && str_starts_with($whatsapp, '0')) {
            $whatsapp = '255' . substr($whatsapp, 1);
        }
        $facebook = trim($_POST['facebook'] ?? '');
        $instagram = trim($_POST['instagram'] ?? '');
        $twitter = trim($_POST['twitter'] ?? '');
        $youtube = trim($_POST['youtube'] ?? '');
        $linkedin = trim($_POST['linkedin'] ?? '');

        if ($siteName === '') {
            $message = 'Site name cannot be empty.';
            $messageType = 'danger';
        } elseif ($contactEmail !== '' && !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            $message = 'Please enter a valid contact email address.';
            $messageType = 'danger';
        } else {
            $newSettings = [
                'siteName' => $siteName,
                'siteTagline' => $siteTagline,
                'contactEmail' => $contactEmail,
                'contactPhone' => $contactPhone,
                'contactAddress' => $contactAddress,
                'footerText' => $footerText,
                'whatsapp' => $whatsapp,
                'facebook' => $facebook,
                'instagram' => $instagram,
                'twitter' => $twitter,
                'youtube' => $youtube,
                'linkedin' => $linkedin,
            ];
            if (saveSettings($newSettings)) {
                header('Location: settings.php?msg=saved');
                exit;
            }
            $settings = array_merge($settings, $newSettings);
            $message = 'Failed to save settings file. Please check permissions on data/ and assets/ecommerce/.';
            $messageType = 'danger';
        }
    }
}

if ($message === '' && ($_GET['msg'] ?? '') === 'saved') {
    $message = 'Settings saved and synced to the live website!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Settings - Jobarn Admin</title>
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .settings-grid {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 24px;
            max-width: 1120px;
            align-items: start;
        }
        @media (max-width: 960px) {
            .settings-grid { grid-template-columns: 1fr; }
        }
        .settings-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }
        .settings-group {
            margin-bottom: 28px;
            padding-bottom: 22px;
            border-bottom: 1px solid #f1f5f9;
        }
        .settings-group:last-of-type {
            border-bottom: none;
            margin-bottom: 20px;
            padding-bottom: 0;
        }
        .group-label {
            font-size: 12.5px;
            font-weight: 800;
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .settings-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            padding-top: 16px;
            border-top: 1px solid var(--border);
        }
        .side-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 22px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }
        .side-card h4 {
            font-size: 15px;
            font-weight: 800;
            color: var(--text-heading);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .side-card p {
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.55;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>

<?php renderAdminNav('settings.php', 'Site Settings', 'Configure store contact details, branding & social channels'); ?>

<main class="main-content">
    <div class="admin-container">
        <?php if ($message !== ''): ?>
            <div class="alert-bar <?php echo htmlspecialchars($messageType); ?>">
                <i class="fa-solid <?php echo $messageType === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?>"></i>
                <span><?php echo htmlspecialchars($message); ?></span>
            </div>
        <?php endif; ?>

        <div class="settings-grid">
            <div class="settings-card">
                <form method="POST" action="settings.php">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="save_settings" value="1">

                    <div class="settings-group">
                        <div class="group-label"><i class="fa-solid fa-store"></i> Brand &amp; Footer</div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="siteName">Site Name *</label>
                                <input type="text" id="siteName" name="siteName" class="form-control" placeholder="JOBARN" required value="<?php echo htmlspecialchars($settings['siteName'] ?? 'JOBARN'); ?>">
                            </div>
                            <div class="form-group">
                                <label for="siteTagline">Store Tagline</label>
                                <input type="text" id="siteTagline" name="siteTagline" class="form-control" placeholder="Professional Tech, Ergonomics, Photo & Video Store Tanzania" value="<?php echo htmlspecialchars($settings['siteTagline'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-full" style="margin-bottom: 0;">
                            <label for="footerText">Footer Copyright Text</label>
                            <input type="text" id="footerText" name="footerText" class="form-control" placeholder="© 2026 Jobarn General Trading Company LTD, All Rights Reserved" value="<?php echo htmlspecialchars($settings['footerText'] ?? ''); ?>">
                            <span class="form-hint">Displayed in the bottom bar of the website footer.</span>
                        </div>
                    </div>

                    <div class="settings-group">
                        <div class="group-label"><i class="fa-solid fa-address-book"></i> Contact &amp; WhatsApp Ordering</div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="contactEmail">Contact Email</label>
                                <input type="email" id="contactEmail" name="contactEmail" class="form-control" placeholder="info@jobarn.co.tz" value="<?php echo htmlspecialchars($settings['contactEmail'] ?? ''); ?>">
                                <span class="form-hint">Used on footer &amp; contact form notifications.</span>
                            </div>
                            <div class="form-group">
                                <label for="contactPhone">Display Phone Number</label>
                                <input type="tel" id="contactPhone" name="contactPhone" class="form-control" placeholder="+255 716-026-781" value="<?php echo htmlspecialchars($settings['contactPhone'] ?? ''); ?>">
                                <span class="form-hint">Shown in header ("Ask Our Experts") &amp; footer.</span>
                            </div>
                        </div>
                        <div class="form-full">
                            <label for="whatsapp">WhatsApp Order &amp; Chat Number</label>
                            <input type="tel" id="whatsapp" name="whatsapp" class="form-control" placeholder="255716026781" value="<?php echo htmlspecialchars($settings['whatsapp'] ?? ''); ?>">
                            <span class="form-hint">International format without + (e.g. <code>255716026781</code>). Used for Cart Checkout &amp; WhatsApp chat widget.</span>
                        </div>
                        <div class="form-full" style="margin-bottom: 0;">
                            <label for="contactAddress">Showroom / Office Address</label>
                            <textarea id="contactAddress" name="contactAddress" class="form-control" style="min-height: 76px;" placeholder="SkyCity Mall, 1st Floor&#10;Off Mlimani City, University Road&#10;Dar es Salaam, Tanzania"><?php echo htmlspecialchars($settings['contactAddress'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="settings-group">
                        <div class="group-label"><i class="fa-solid fa-share-nodes"></i> Social Media Links</div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="facebook">Facebook URL</label>
                                <input type="url" id="facebook" name="facebook" class="form-control" placeholder="https://facebook.com/jobarn" value="<?php echo htmlspecialchars($settings['facebook'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="instagram">Instagram URL</label>
                                <input type="url" id="instagram" name="instagram" class="form-control" placeholder="https://instagram.com/jobarn" value="<?php echo htmlspecialchars($settings['instagram'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="youtube">YouTube URL</label>
                                <input type="url" id="youtube" name="youtube" class="form-control" placeholder="https://youtube.com/@jobarn" value="<?php echo htmlspecialchars($settings['youtube'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="linkedin">LinkedIn URL</label>
                                <input type="url" id="linkedin" name="linkedin" class="form-control" placeholder="https://linkedin.com/company/jobarn" value="<?php echo htmlspecialchars($settings['linkedin'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-full" style="margin-bottom: 0;">
                            <label for="twitter">Twitter / X URL</label>
                            <input type="url" id="twitter" name="twitter" class="form-control" placeholder="https://x.com/jobarn" value="<?php echo htmlspecialchars($settings['twitter'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="settings-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i> Save All Settings
                        </button>
                    </div>
                </form>
            </div>

            <div>
                <div class="side-card" style="margin-bottom: 18px;">
                    <h4><i class="fa-solid fa-shield-halved" style="color: var(--accent);"></i> Admin Security</h4>
                    <p>Need to update your admin login username or password? Manage credentials safely on the dedicated Security page.</p>
                    <a href="change-password.php" class="btn btn-outline" style="width: 100%;">
                        <i class="fa-solid fa-key"></i> Security / Password
                    </a>
                </div>

                <div class="side-card">
                    <h4><i class="fa-solid fa-bolt" style="color: #f59e0b;"></i> Live Sync</h4>
                    <p style="margin-bottom: 0;">Saving settings updates <code>data/settings.json</code> and syncs immediately to the storefront header, WhatsApp checkout, and footer across all pages.</p>
                </div>
            </div>
        </div>
    </div>
</main>

</body>
</html>
