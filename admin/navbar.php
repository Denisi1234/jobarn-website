<?php
/**
 * Shared Admin Navigation, Topbar & Core Layout Styles
 * Usage:
 *   require_once __DIR__ . '/navbar.php';
 *   renderAdminNav('index.php', 'Products Manager', 'Manage store inventory & products');
 */

function renderAdminNav($activePage = '', $pageTitle = 'Admin Portal', $pageSubtitle = 'Jobarn Management') {
    $user = $_SESSION['jobarn_admin_user'] ?? 'admin';
    $navItems = [
        [
            'id' => 'index.php',
            'title' => 'Products',
            'icon' => 'fa-solid fa-boxes-stacked',
            'url' => 'index.php',
            'badge' => null
        ],
        [
            'id' => 'slides.php',
            'title' => 'Slides',
            'icon' => 'fa-solid fa-images',
            'url' => 'slides.php',
            'badge' => null
        ],
        [
            'id' => 'messages.php',
            'title' => 'Messages',
            'icon' => 'fa-solid fa-envelope',
            'url' => 'messages.php',
            'badge' => null
        ],
        [
            'id' => 'settings.php',
            'title' => 'Settings',
            'icon' => 'fa-solid fa-sliders',
            'url' => 'settings.php',
            'badge' => null
        ],
        [
            'id' => 'change-password.php',
            'title' => 'Security / Password',
            'icon' => 'fa-solid fa-shield-halved',
            'url' => 'change-password.php',
            'badge' => null
        ],
    ];

    // Count unread messages
    if (function_exists('getMessages')) {
        $rawMsgs = getMessages();
        $unread = 0;
        foreach ($rawMsgs as $m) {
            if (empty($m['read'])) {
                $unread++;
            }
        }
        if ($unread > 0) {
            foreach ($navItems as &$item) {
                if ($item['id'] === 'messages.php') {
                    $item['badge'] = $unread;
                }
            }
            unset($item);
        }
    }
    ?>
    <style>
        :root {
            --sidebar-w: 260px;
            --bg-body: #f8fafc;
            --surface: #ffffff;
            --surface-card: #ffffff;
            --border: #e2e8f0;
            --border-subtle: #e2e8f0;
            --border-hover: #cbd5e1;
            --text-heading: #0f172a;
            --text-dark: #0f172a;
            --text-body: #334155;
            --text-muted: #64748b;
            --primary: #0f172a;
            --brand-primary: #0f172a;
            --accent: #2563eb;
            --brand-accent: #2563eb;
            --accent-hover: #1d4ed8;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-body);
            min-height: 100vh;
        }

        /* Topbar Header */
        .admin-topbar {
            background: #ffffff;
            border-bottom: 1px solid var(--border-subtle);
            position: sticky;
            top: 0;
            z-index: 40;
            padding: 12px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-left: var(--sidebar-w);
            transition: margin-left 0.3s ease;
        }
        .admin-topbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .admin-menu-toggle {
            display: none;
            background: #f1f5f9;
            border: 1px solid var(--border-subtle);
            color: var(--text-dark);
            border-radius: 8px;
            width: 38px;
            height: 38px;
            cursor: pointer;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: all 0.2s;
        }
        .admin-menu-toggle:hover { background: #e2e8f0; }
        .admin-breadcrumb h2 {
            font-size: 17px;
            font-weight: 800;
            color: var(--text-dark);
            line-height: 1.2;
            letter-spacing: -0.2px;
        }
        .admin-breadcrumb span {
            font-size: 12.5px;
            color: var(--text-muted);
            font-weight: 500;
        }
        .admin-topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .admin-view-site-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 14px;
            border-radius: 8px;
            background: #f8fafc;
            border: 1px solid var(--border-subtle);
            color: #334155;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }
        .admin-view-site-btn:hover { background: #f1f5f9; color: #0f172a; }
        .admin-user-badge {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 6px 12px;
            border-radius: 20px;
            background: #f1f5f9;
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
        }
        .admin-avatar {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: var(--brand-accent);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
        }

        /* Sidebar Styling */
        .admin-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: var(--sidebar-w);
            background: #0f172a;
            color: #e2e8f0;
            z-index: 50;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.05);
        }
        .admin-sidebar-header {
            padding: 20px 22px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .admin-sidebar-logo {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            background: #ffffff;
            object-fit: cover;
            padding: 2px;
        }
        .admin-sidebar-title h1 {
            font-size: 16px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.3px;
            line-height: 1.1;
        }
        .admin-sidebar-title span {
            font-size: 11px;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .admin-sidebar-nav {
            flex: 1;
            padding: 20px 14px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .admin-nav-category {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.9px;
            padding: 10px 12px 6px;
        }
        .admin-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            border-radius: 10px;
            color: #94a3b8;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s ease;
            position: relative;
        }
        .admin-nav-link i {
            width: 20px;
            font-size: 16px;
            text-align: center;
            color: #64748b;
            transition: color 0.2s;
        }
        .admin-nav-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.06);
        }
        .admin-nav-link:hover i { color: #ffffff; }
        .admin-nav-link.active {
            color: #ffffff;
            background: #2563eb;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
        }
        .admin-nav-link.active i { color: #ffffff; }
        .admin-nav-badge {
            margin-left: auto;
            background: #ef4444;
            color: #ffffff;
            font-size: 11px;
            font-weight: 800;
            padding: 2px 7px;
            border-radius: 20px;
        }
        .admin-sidebar-footer {
            padding: 16px 14px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .admin-logout-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            border-radius: 10px;
            color: #f87171;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .admin-logout-btn:hover {
            background: rgba(239, 68, 68, 0.12);
            color: #fca5a5;
        }
        .admin-logout-btn i { width: 20px; text-align: center; }
        .admin-sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 45;
        }

        /* Shared Main Content & UI Primitives */
        .main-content {
            margin-left: var(--sidebar-w);
            padding: 28px 28px 60px;
            transition: margin-left 0.3s ease;
        }
        .admin-container {
            max-width: 1320px;
            margin: 0 auto;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 9px 16px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.2s ease;
            font-family: inherit;
        }
        .btn-primary { background: var(--primary); color: #ffffff; }
        .btn-primary:hover { background: #1e293b; }
        .btn-accent { background: var(--accent); color: #ffffff; }
        .btn-accent:hover { background: var(--accent-hover); }
        .btn-outline { border-color: var(--border); color: var(--text-heading); background: #fff; }
        .btn-outline:hover { background: #f1f5f9; }
        .btn-sm { padding: 6px 12px; font-size: 12.5px; border-radius: 8px; }
        .btn-danger-ghost { color: var(--danger); background: transparent; border-color: transparent; }
        .btn-danger-ghost:hover { background: #fee2e2; }
        .btn-danger { background: var(--danger); color: #ffffff; }
        .btn-danger:hover { background: #dc2626; }

        .alert-bar {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
            padding: 13px 18px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-bar.error, .alert-bar.danger {
            background: #fef2f2;
            border-color: #fecaca;
            color: #991b1b;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            transition: all 0.2s ease;
        }
        .stat-card:hover {
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            transform: translateY(-1px);
        }
        .stat-card .val {
            font-size: 26px;
            font-weight: 800;
            color: var(--text-heading);
            margin-bottom: 4px;
            line-height: 1.1;
        }
        .stat-card .label {
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 500;
        }
        .stat-card .icon-box {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #eff6ff;
            color: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 14px;
        }
        .section-title {
            font-size: 20px;
            font-weight: 800;
            color: var(--text-heading);
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .search-filter-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 8px 14px;
            min-width: 240px;
        }
        .search-filter-bar input {
            border: none;
            outline: none;
            font-size: 13.5px;
            width: 100%;
            font-family: inherit;
            color: var(--text-heading);
            background: transparent;
        }
        .filter-select {
            padding: 8px 12px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: #ffffff;
            font-size: 13.5px;
            font-family: inherit;
            color: var(--text-heading);
            outline: none;
            cursor: pointer;
        }
        .filter-select:focus { border-color: var(--accent); }

        .table-wrap {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow-x: auto;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }
        .table-wrap table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        .table-wrap thead th {
            background: #f8fafc;
            padding: 13px 16px;
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }
        .table-wrap tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.15s ease;
        }
        .table-wrap tbody tr:last-child { border-bottom: none; }
        .table-wrap tbody tr:hover { background: #fbfcfe; }
        .table-wrap tbody td {
            padding: 14px 16px;
            vertical-align: middle;
            font-size: 14px;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }
        .badge-category { background: #f1f5f9; color: #475569; }
        .badge-sale { background: #fef2f2; color: #b91c1c; }
        .badge-instock { background: #dcfce7; color: #15803d; }
        .badge-outstock { background: #f1f5f9; color: #64748b; }
        .badge-featured { background: #fef3c7; color: #b45309; }
        .badge-active { background: #dbeafe; color: #1d4ed8; }
        .badge-inactive { background: #fee2e2; color: #991b1b; }

        .table-actions {
            display: flex;
            align-items: center;
            gap: 6px;
            justify-content: flex-end;
        }

        /* Modal styling */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 100;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-overlay.open { display: flex; }
        .modal-card {
            background: #ffffff;
            width: 100%;
            max-width: 720px;
            max-height: 90vh;
            border-radius: 20px;
            overflow-y: auto;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            display: flex;
            flex-direction: column;
        }
        .modal-header {
            padding: 18px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            background: #fff;
            z-index: 10;
        }
        .modal-header h3 {
            font-size: 18px;
            font-weight: 800;
            color: var(--text-heading);
        }
        .modal-close {
            background: transparent;
            border: none;
            font-size: 22px;
            line-height: 1;
            color: var(--text-muted);
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 8px;
        }
        .modal-close:hover { background: #f1f5f9; color: var(--text-heading); }
        .modal-body { padding: 24px; }
        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            background: #f8fafc;
            border-radius: 0 0 20px 20px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }
        .form-full { margin-bottom: 16px; }
        .form-group label, .form-full > label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 6px;
        }
        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
            font-family: inherit;
            color: var(--text-heading);
            background: #ffffff;
        }
        .form-control:focus { border-color: var(--accent); }
        textarea.form-control { min-height: 90px; resize: vertical; }
        .form-hint {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 5px;
            display: block;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 8px;
        }
        .checkbox-group input {
            width: 17px;
            height: 17px;
            accent-color: var(--accent);
            cursor: pointer;
        }
        .checkbox-group label {
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 0;
        }

        /* Responsive Layout Overrides */
        @media (max-width: 900px) {
            .admin-sidebar { transform: translateX(-100%); }
            .admin-sidebar.show-sidebar { transform: translateX(0); }
            .admin-topbar { margin-left: 0; padding: 12px 16px; }
            .admin-menu-toggle { display: inline-flex; }
            .admin-sidebar-backdrop.show-backdrop { display: block; }
            .main-content { margin-left: 0; padding: 20px 16px 60px; }
            .form-row { grid-template-columns: 1fr; }
            .admin-view-site-btn span { display: none; }
        }
    </style>

    <!-- Shared Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar-header">
            <img src="../assets/img/jobarn-logo.jpeg" alt="Jobarn Logo" class="admin-sidebar-logo" onerror="this.src='https://placehold.co/40x40/2563eb/ffffff?text=J'">
            <div class="admin-sidebar-title">
                <h1>Jobarn</h1>
                <span>Admin Portal</span>
            </div>
        </div>

        <nav class="admin-sidebar-nav">
            <div class="admin-nav-category">Main Menu</div>
            <?php foreach ($navItems as $item):
                $isActive = ($activePage === $item['id']);
            ?>
                <a href="<?php echo htmlspecialchars($item['url']); ?>" class="admin-nav-link <?php echo $isActive ? 'active' : ''; ?>">
                    <i class="<?php echo htmlspecialchars($item['icon']); ?>"></i>
                    <span><?php echo htmlspecialchars($item['title']); ?></span>
                    <?php if (!empty($item['badge'])): ?>
                        <span class="admin-nav-badge"><?php echo (int)$item['badge']; ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>

            <div class="admin-nav-category" style="margin-top: 14px;">Quick Links</div>
            <a href="../index.html" target="_blank" class="admin-nav-link">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                <span>View Storefront</span>
            </a>
            <a href="../collections/all.html" target="_blank" class="admin-nav-link">
                <i class="fa-solid fa-bag-shopping"></i>
                <span>Collections</span>
            </a>
        </nav>

        <div class="admin-sidebar-footer">
            <a href="logout.php" class="admin-logout-btn">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                <span>Sign Out</span>
            </a>
        </div>
    </aside>

    <!-- Overlay Backdrop for Mobile -->
    <div class="admin-sidebar-backdrop" id="adminSidebarBackdrop" onclick="toggleAdminSidebar()"></div>

    <!-- Shared Topbar Header -->
    <header class="admin-topbar">
        <div class="admin-topbar-left">
            <button type="button" class="admin-menu-toggle" id="adminMenuToggle" onclick="toggleAdminSidebar()" aria-label="Toggle Navigation">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="admin-breadcrumb">
                <h2><?php echo htmlspecialchars($pageTitle); ?></h2>
                <span><?php echo htmlspecialchars($pageSubtitle); ?></span>
            </div>
        </div>

        <div class="admin-topbar-right">
            <a href="../index.html" target="_blank" class="admin-view-site-btn">
                <i class="fa-solid fa-globe"></i>
                <span>Store</span>
            </a>
            <div class="admin-user-badge">
                <span class="admin-avatar"><?php echo strtoupper(substr($user, 0, 1)); ?></span>
                <span><?php echo htmlspecialchars($user); ?></span>
            </div>
        </div>
    </header>

    <script>
        function toggleAdminSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            const backdrop = document.getElementById('adminSidebarBackdrop');
            if (sidebar && backdrop) {
                sidebar.classList.toggle('show-sidebar');
                backdrop.classList.toggle('show-backdrop');
            }
        }
    </script>
    <?php
}
