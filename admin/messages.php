<?php
require_once __DIR__ . '/auth.php';
requireAuth();
require_once __DIR__ . '/navbar.php';

$messages = getMessages();
$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Security token expired. Please refresh the page and try again.';
        $messageType = 'danger';
    } elseif (isset($_POST['toggle_read'])) {
        $targetId = trim($_POST['id'] ?? '');
        $found = false;
        $newState = 'read';
        foreach ($messages as &$m) {
            if (($m['id'] ?? '') === $targetId) {
                $m['read'] = empty($m['read']);
                $newState = $m['read'] ? 'read' : 'unread';
                $found = true;
                break;
            }
        }
        unset($m);
        if ($found && saveMessages($messages)) {
            header('Location: messages.php?msg=' . $newState);
            exit;
        }
        $message = 'Failed to update message status.';
        $messageType = 'danger';
    } elseif (isset($_POST['mark_all_read'])) {
        foreach ($messages as &$m) {
            $m['read'] = true;
        }
        unset($m);
        if (saveMessages($messages)) {
            header('Location: messages.php?msg=marked_all');
            exit;
        }
        $message = 'Failed to mark messages as read.';
        $messageType = 'danger';
    } elseif (isset($_POST['delete_message'])) {
        $targetId = trim($_POST['id'] ?? '');
        $filtered = [];
        $deleted = false;
        foreach ($messages as $m) {
            if (($m['id'] ?? '') === $targetId) {
                $deleted = true;
                continue;
            }
            $filtered[] = $m;
        }
        if ($deleted && saveMessages($filtered)) {
            header('Location: messages.php?msg=deleted');
            exit;
        }
        $message = $deleted ? 'Failed to save messages file.' : 'Message not found.';
        $messageType = 'danger';
    }
}

if ($message === '' && isset($_GET['msg'])) {
    $msgMap = [
        'read' => 'Message marked as read.',
        'unread' => 'Message marked as unread.',
        'marked_all' => 'All messages marked as read.',
        'deleted' => 'Message deleted permanently.',
    ];
    $message = $msgMap[$_GET['msg']] ?? '';
}

$unreadCount = 0;
$totalCount = count($messages);
foreach ($messages as $m) {
    if (empty($m['read'])) {
        $unreadCount++;
    }
}
$readCount = $totalCount - $unreadCount;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages Inbox - Jobarn Admin</title>
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .msg-row-unread {
            background: #f8fbff;
        }
        .msg-row-unread td:first-child {
            border-left: 3px solid var(--accent);
        }
        .msg-preview {
            color: var(--text-muted);
            font-size: 13px;
            line-height: 1.4;
            max-width: 340px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .msg-meta {
            font-size: 12px;
            color: var(--text-muted);
            display: block;
            margin-top: 2px;
        }
        .msg-detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 18px;
        }
        @media (max-width: 600px) {
            .msg-detail-grid { grid-template-columns: 1fr; }
        }
        .msg-detail-item span {
            display: block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 2px;
        }
        .msg-detail-item strong, .msg-detail-item a {
            font-size: 13.5px;
            color: var(--text-heading);
            text-decoration: none;
            word-break: break-word;
        }
        .msg-detail-item a:hover { color: var(--accent); text-decoration: underline; }
        .msg-full-body {
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 18px;
            font-size: 14px;
            line-height: 1.65;
            color: var(--text-heading);
            white-space: pre-wrap;
            word-break: break-word;
            min-height: 110px;
        }
    </style>
</head>
<body>

<?php renderAdminNav('messages.php', 'Messages Inbox', 'Customer contact form submissions & inquiries'); ?>

<main class="main-content">
    <div class="admin-container">
        <?php if ($message !== ''): ?>
            <div class="alert-bar <?php echo htmlspecialchars($messageType); ?>">
                <i class="fa-solid <?php echo $messageType === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?>"></i>
                <span><?php echo htmlspecialchars($message); ?></span>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div>
                    <div class="val"><?php echo $totalCount; ?></div>
                    <div class="label">Total Messages</div>
                </div>
                <div class="icon-box"><i class="fa-solid fa-inbox"></i></div>
            </div>
            <div class="stat-card">
                <div>
                    <div class="val" style="color: var(--danger);"><?php echo $unreadCount; ?></div>
                    <div class="label">Unread Inquiries</div>
                </div>
                <div class="icon-box" style="background: #fef2f2; color: var(--danger);"><i class="fa-solid fa-envelope-open-text"></i></div>
            </div>
            <div class="stat-card">
                <div>
                    <div class="val" style="color: var(--success);"><?php echo $readCount; ?></div>
                    <div class="label">Reviewed / Read</div>
                </div>
                <div class="icon-box" style="background: #ecfdf5; color: #10b981;"><i class="fa-solid fa-circle-check"></i></div>
            </div>
        </div>

        <div class="section-header">
            <div class="section-title">
                <i class="fa-solid fa-envelope" style="color: var(--accent);"></i>
                <span>Contact Form Submissions</span>
                <span id="visibleMsgCount" style="font-size: 13px; font-weight: 500; color: var(--text-muted);">(<?php echo $totalCount; ?>)</span>
            </div>

            <div class="toolbar-actions">
                <div class="search-filter-bar">
                    <i class="fa-solid fa-magnifying-glass" style="color: #94a3b8;"></i>
                    <input type="text" id="searchInput" placeholder="Search name, email, subject..." oninput="filterMessages()">
                </div>

                <select id="readFilter" class="filter-select" onchange="filterMessages()" aria-label="Filter messages by read status">
                    <option value="">All Messages</option>
                    <option value="unread">Unread Only</option>
                    <option value="read">Read Only</option>
                </select>

                <?php if ($unreadCount > 0): ?>
                    <form method="POST" action="messages.php" style="display:inline;">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="mark_all_read" value="1">
                        <button type="submit" class="btn btn-outline">
                            <i class="fa-solid fa-check-double"></i> Mark All Read
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="table-wrap">
            <table id="messagesTable">
                <thead>
                    <tr>
                        <th>From</th>
                        <th>Email</th>
                        <th>Subject &amp; Preview</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($messages)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 48px; color: #94a3b8;">
                                No customer messages received yet. Submissions from <code>contact.html</code> appear here automatically.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($messages as $m):
                            $mid = $m['id'] ?? '';
                            $isRead = !empty($m['read']);
                            $ts = strtotime($m['date'] ?? '');
                            $formattedDate = $ts ? date('M d, Y H:i', $ts) : ($m['date'] ?? '-');
                        ?>
                            <tr class="message-row <?php echo !$isRead ? 'msg-row-unread' : ''; ?>"
                                data-read="<?php echo $isRead ? '1' : '0'; ?>">
                                <td>
                                    <div style="font-weight: <?php echo !$isRead ? '800' : '700'; ?>; color: var(--text-heading);"><?php echo htmlspecialchars($m['name'] ?? ''); ?></div>
                                    <div class="msg-meta"><?php echo htmlspecialchars(!empty($m['phone']) ? $m['phone'] : 'No phone'); ?></div>
                                </td>
                                <td>
                                    <div style="font-size: 13.5px;"><?php echo htmlspecialchars($m['email'] ?? ''); ?></div>
                                    <div class="msg-meta"><?php echo htmlspecialchars($m['ip'] ?? ''); ?></div>
                                </td>
                                <td style="cursor: pointer;" onclick="openMessageModal(<?php echo htmlspecialchars(json_encode($mid), ENT_QUOTES, 'UTF-8'); ?>)">
                                    <div style="font-weight: <?php echo !$isRead ? '800' : '700'; ?>; color: var(--text-heading);"><?php echo htmlspecialchars($m['subject'] ?? '(no subject)'); ?></div>
                                    <div class="msg-preview"><?php echo htmlspecialchars($m['message'] ?? ''); ?></div>
                                </td>
                                <td>
                                    <div class="msg-meta" style="margin-top:0; font-weight:600; color: var(--text-body);"><?php echo htmlspecialchars($formattedDate); ?></div>
                                </td>
                                <td>
                                    <form method="POST" action="messages.php" style="display:inline;">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="toggle_read" value="1">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($mid); ?>">
                                        <button type="submit"
                                                class="btn btn-sm <?php echo $isRead ? 'btn-outline' : 'btn-accent'; ?>"
                                                title="Click to mark as <?php echo $isRead ? 'unread' : 'read'; ?>">
                                            <i class="fa-solid fa-<?php echo $isRead ? 'envelope-open' : 'envelope'; ?>"></i>
                                            <span><?php echo $isRead ? 'Read' : 'Unread'; ?></span>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <button type="button"
                                                onclick="openMessageModal(<?php echo htmlspecialchars(json_encode($mid), ENT_QUOTES, 'UTF-8'); ?>)"
                                                class="btn btn-outline btn-sm"
                                                title="Read Full Message">
                                            <i class="fa-solid fa-eye"></i> View
                                        </button>
                                        <form method="POST" action="messages.php" style="display:inline;" onsubmit="return confirm('Delete this message permanently?');">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="delete_message" value="1">
                                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($mid); ?>">
                                            <button type="submit" class="btn btn-danger-ghost btn-sm" title="Delete Message">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr id="emptyMsgFilterRow" style="display: none;">
                            <td colspan="6" style="text-align: center; padding: 36px; color: var(--text-muted);">No messages match your filter.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- VIEW MESSAGE MODAL -->
<div id="messageModal" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-header">
            <h3 id="msgModalSubject">Message Details</h3>
            <button type="button" class="modal-close" onclick="closeMessageModal()" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="msg-detail-grid">
                <div class="msg-detail-item">
                    <span>From</span>
                    <strong id="msgModalName">-</strong>
                </div>
                <div class="msg-detail-item">
                    <span>Received Date</span>
                    <strong id="msgModalDate">-</strong>
                </div>
                <div class="msg-detail-item">
                    <span>Email Address</span>
                    <a id="msgModalEmail" href="#">-</a>
                </div>
                <div class="msg-detail-item">
                    <span>Phone &amp; IP</span>
                    <strong id="msgModalPhone">-</strong>
                </div>
            </div>

            <label style="display:block; font-size:12px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:8px;">Message Content</label>
            <div id="msgModalBody" class="msg-full-body"></div>
        </div>
        <div class="modal-footer" style="justify-content: space-between; flex-wrap: wrap;">
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <a id="msgReplyEmailBtn" href="#" class="btn btn-accent btn-sm">
                    <i class="fa-solid fa-reply"></i> Reply via Email
                </a>
                <a id="msgReplyWaBtn" href="#" target="_blank" rel="noopener" class="btn btn-outline btn-sm" style="display:none; color:#15803d; border-color:#86efac;">
                    <i class="fa-brands fa-whatsapp"></i> WhatsApp
                </a>
            </div>
            <div style="display: flex; gap: 8px;">
                <form method="POST" action="messages.php" style="display:inline;">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="toggle_read" value="1">
                    <input type="hidden" name="id" id="msgModalToggleId" value="">
                    <button type="submit" id="msgModalToggleBtn" class="btn btn-outline btn-sm">
                        <i class="fa-solid fa-check"></i> Toggle Read
                    </button>
                </form>
                <button type="button" class="btn btn-primary btn-sm" onclick="closeMessageModal()">Close</button>
            </div>
        </div>
    </div>
</div>

<script id="messagesDataJson" type="application/json"><?php echo json_encode($messages, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
<script>
const MESSAGES_LIST = (() => {
    try {
        return JSON.parse(document.getElementById('messagesDataJson').textContent || '[]');
    } catch (e) {
        return [];
    }
})();

function openMessageModal(id) {
    const msg = MESSAGES_LIST.find(m => m.id === id);
    if (!msg) return;

    document.getElementById('msgModalSubject').textContent = msg.subject || '(no subject)';
    document.getElementById('msgModalName').textContent = msg.name || '-';
    document.getElementById('msgModalDate').textContent = msg.date ? new Date(msg.date).toLocaleString() : '-';

    const emailEl = document.getElementById('msgModalEmail');
    emailEl.textContent = msg.email || '-';
    emailEl.href = msg.email ? ('mailto:' + msg.email) : '#';

    const phoneText = (msg.phone || 'No phone') + (msg.ip ? (' • IP: ' + msg.ip) : '');
    document.getElementById('msgModalPhone').textContent = phoneText;
    document.getElementById('msgModalBody').textContent = msg.message || '';

    const replyBtn = document.getElementById('msgReplyEmailBtn');
    replyBtn.href = `mailto:${encodeURIComponent(msg.email || '')}?subject=${encodeURIComponent('Re: ' + (msg.subject || 'Jobarn Inquiry'))}`;

    const waBtn = document.getElementById('msgReplyWaBtn');
    const digits = String(msg.phone || '').replace(/[^\d]/g, '');
    if (digits.length >= 9) {
        const waDigits = digits.startsWith('0') ? ('255' + digits.slice(1)) : digits;
        waBtn.href = `https://wa.me/${waDigits}?text=${encodeURIComponent('Hello ' + (msg.name || '') + ', thank you for contacting Jobarn regarding: ' + (msg.subject || ''))}`;
        waBtn.style.display = 'inline-flex';
    } else {
        waBtn.style.display = 'none';
    }

    document.getElementById('msgModalToggleId').value = msg.id || '';
    const toggleBtn = document.getElementById('msgModalToggleBtn');
    toggleBtn.innerHTML = msg.read
        ? '<i class="fa-solid fa-envelope"></i> Mark Unread'
        : '<i class="fa-solid fa-envelope-open"></i> Mark Read';

    document.getElementById('messageModal').classList.add('open');
}

function closeMessageModal() {
    document.getElementById('messageModal').classList.remove('open');
}

function filterMessages() {
    const query = document.getElementById('searchInput').value.trim().toLowerCase();
    const readFilter = document.getElementById('readFilter').value;
    const rows = document.querySelectorAll('.message-row');
    let visible = 0;

    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        const isRead = row.dataset.read === '1';
        let match = !query || text.includes(query);
        if (match && readFilter === 'unread' && isRead) match = false;
        if (match && readFilter === 'read' && !isRead) match = false;

        row.style.display = match ? '' : 'none';
        if (match) visible++;
    });

    const emptyRow = document.getElementById('emptyMsgFilterRow');
    if (emptyRow) emptyRow.style.display = (rows.length > 0 && visible === 0) ? '' : 'none';

    const countEl = document.getElementById('visibleMsgCount');
    if (countEl) countEl.textContent = `(${visible})`;
}

document.getElementById('messageModal').addEventListener('click', function(e) {
    if (e.target === this) closeMessageModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeMessageModal();
});
</script>

</body>
</html>
