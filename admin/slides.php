<?php
require_once __DIR__ . '/auth.php';
requireAuth();
require_once __DIR__ . '/navbar.php';

$slides = getSlides();
$message = '';
$messageType = 'success';
$formSlide = null;
$openModalOnLoad = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Security token expired. Please refresh the page and try again.';
        $messageType = 'danger';
    } elseif (isset($_POST['delete_slide'])) {
        $delId = trim($_POST['id'] ?? '');
        $filtered = [];
        $deleted = false;
        foreach ($slides as $s) {
            if (($s['id'] ?? '') === $delId) {
                $deleted = true;
                continue;
            }
            $filtered[] = $s;
        }
        if ($deleted && saveSlides($filtered)) {
            header('Location: slides.php?msg=deleted');
            exit;
        }
        $message = $deleted ? 'Failed to save slides file. Check permissions on data/.' : 'Slide not found.';
        $messageType = 'danger';
    } elseif (isset($_POST['toggle_active'])) {
        $toggleId = trim($_POST['id'] ?? '');
        $found = false;
        $newState = 'deactivated';
        foreach ($slides as &$s) {
            if (($s['id'] ?? '') === $toggleId) {
                $currentStatus = isset($s['active']) ? (bool)$s['active'] : true;
                $s['active'] = !$currentStatus;
                $newState = $s['active'] ? 'activated' : 'deactivated';
                $found = true;
                break;
            }
        }
        unset($s);
        if ($found && saveSlides($slides)) {
            header('Location: slides.php?msg=' . $newState);
            exit;
        }
        $message = 'Failed to toggle slide visibility.';
        $messageType = 'danger';
    } elseif (isset($_POST['save_slide'])) {
        $id = trim($_POST['id'] ?? '');
        $isNew = ($id === '');

        $title = trim($_POST['title'] ?? '');
        $subtitle = trim($_POST['subtitle'] ?? '');
        $buttonText = trim($_POST['buttonText'] ?? '');
        $buttonLink = trim($_POST['buttonLink'] ?? '#');
        $order = (int)($_POST['order'] ?? 0);
        $active = isset($_POST['active']);

        $image = trim($_POST['image_url'] ?? '');
        if ($image === '') {
            $image = trim($_POST['existing_image'] ?? '');
        }

        $uploadError = null;
        if (!empty($_FILES['image_file']['name'])) {
            $uploadRes = handleImageUpload($_FILES['image_file'], 'slide_');
            if (!$uploadRes['ok']) {
                $uploadError = $uploadRes['error'];
            } elseif (!empty($uploadRes['path'])) {
                $image = $uploadRes['path'];
            }
        }

        if ($title === '') {
            $message = 'Slide title is required.';
            $messageType = 'danger';
        } elseif ($uploadError !== null) {
            $message = $uploadError;
            $messageType = 'danger';
        }

        if ($messageType === 'danger') {
            $openModalOnLoad = true;
            $formSlide = [
                'id' => $id,
                'title' => $title,
                'subtitle' => $subtitle,
                'buttonText' => $buttonText,
                'buttonLink' => $buttonLink,
                'image' => $image,
                'order' => $order,
                'active' => $active
            ];
        } else {
            if ($image === '') {
                $image = 'assets/img/home-banner1.jpeg';
            }
            if ($isNew) {
                $id = 'slide-' . time() . '-' . random_int(100, 999);
            }

            $slideData = [
                'id' => $id,
                'title' => $title,
                'subtitle' => $subtitle,
                'buttonText' => $buttonText !== '' ? $buttonText : 'Shop Now',
                'buttonLink' => $buttonLink !== '' ? $buttonLink : 'collections/all.html',
                'image' => $image,
                'order' => $order,
                'active' => $active
            ];

            $updatedList = [];
            $found = false;
            foreach ($slides as $s) {
                if (($s['id'] ?? '') === $id) {
                    $updatedList[] = $slideData;
                    $found = true;
                } else {
                    $updatedList[] = $s;
                }
            }
            if (!$found) {
                $updatedList[] = $slideData;
            }

            if (saveSlides($updatedList)) {
                header('Location: slides.php?msg=' . ($found ? 'updated' : 'added'));
                exit;
            }
            $message = 'Failed to save slides file. Check permissions on data/ and assets/ecommerce/.';
            $messageType = 'danger';
            $openModalOnLoad = true;
            $formSlide = $slideData;
        }
    }
}

// Backwards-compatible GET modal triggers
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';
if (!$openModalOnLoad && $action === 'edit' && $id !== '') {
    foreach ($slides as $s) {
        if (($s['id'] ?? '') === $id) {
            $formSlide = $s;
            $openModalOnLoad = true;
            break;
        }
    }
} elseif (!$openModalOnLoad && ($_GET['modal'] ?? '') === 'new') {
    $openModalOnLoad = true;
}

if ($message === '' && isset($_GET['msg'])) {
    $msgMap = [
        'added' => 'Slide added successfully!',
        'updated' => 'Slide updated successfully!',
        'deleted' => 'Slide removed.',
        'activated' => 'Slide activated and will appear on the homepage hero banner.',
        'deactivated' => 'Slide deactivated and hidden from the homepage.',
    ];
    $message = $msgMap[$_GET['msg']] ?? '';
}

$activeCount = 0;
foreach ($slides as $s) {
    if (!isset($s['active']) || !empty($s['active'])) {
        $activeCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slides Manager - Jobarn Admin</title>
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .slide-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .slide-thumb {
            width: 84px;
            height: 48px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid var(--border);
            background: #f1f5f9;
            flex-shrink: 0;
        }
        .slide-path {
            font-size: 11.5px;
            color: var(--text-muted);
            font-family: monospace;
            max-width: 180px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        tr.is-inactive {
            opacity: 0.58;
            background: #f8fafc;
        }
        .info-banner {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .image-preview-box {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
            background: #f8fafc;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid var(--border);
        }
        .image-preview-box img {
            width: 96px;
            height: 54px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
        }
    </style>
</head>
<body>

<?php renderAdminNav('slides.php', 'Slides Manager', 'Manage homepage hero banner slides & promotional links'); ?>

<main class="main-content">
    <div class="admin-container">
        <?php if ($message !== ''): ?>
            <div class="alert-bar <?php echo htmlspecialchars($messageType); ?>">
                <i class="fa-solid <?php echo $messageType === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?>"></i>
                <span><?php echo htmlspecialchars($message); ?></span>
            </div>
        <?php endif; ?>

        <div class="info-banner">
            <i class="fa-solid fa-circle-info"></i>
            <span>
                <?php if ($activeCount > 0): ?>
                    <strong><?php echo $activeCount; ?> active <?php echo $activeCount === 1 ? 'slide' : 'slides'; ?></strong> currently displayed in the homepage hero carousel.
                <?php else: ?>
                    No active custom slides right now — the homepage is displaying the default showroom video banner. Add &amp; activate a slide below to switch to the hero carousel.
                <?php endif; ?>
            </span>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div>
                    <div class="val"><?php echo count($slides); ?></div>
                    <div class="label">Total Slides</div>
                </div>
                <div class="icon-box"><i class="fa-solid fa-images"></i></div>
            </div>
            <div class="stat-card">
                <div>
                    <div class="val"><?php echo $activeCount; ?></div>
                    <div class="label">Active on Store</div>
                </div>
                <div class="icon-box" style="background: #ecfdf5; color: #10b981;"><i class="fa-solid fa-eye"></i></div>
            </div>
            <div class="stat-card">
                <div>
                    <div class="val"><?php echo count($slides) - $activeCount; ?></div>
                    <div class="label">Hidden / Inactive</div>
                </div>
                <div class="icon-box" style="background: #fef2f2; color: #ef4444;"><i class="fa-solid fa-eye-slash"></i></div>
            </div>
        </div>

        <div class="section-header">
            <div class="section-title">
                <i class="fa-solid fa-images" style="color: var(--accent);"></i>
                <span>Homepage Slider</span>
            </div>

            <div class="toolbar-actions">
                <div class="search-filter-bar">
                    <i class="fa-solid fa-magnifying-glass" style="color: #94a3b8;"></i>
                    <input type="text" id="searchInput" placeholder="Search slides..." oninput="filterSlides()">
                </div>

                <button type="button" onclick="openAddSlideModal()" class="btn btn-accent">
                    <i class="fa-solid fa-plus"></i> Add New Slide
                </button>
            </div>
        </div>

        <div class="table-wrap">
            <table id="slidesTable">
                <thead>
                    <tr>
                        <th>Preview</th>
                        <th>Title &amp; Subtitle</th>
                        <th>Button CTA</th>
                        <th>Order</th>
                        <th>Visibility</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($slides)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 48px; color: #94a3b8;">
                                No custom slides added yet. Click <strong>Add New Slide</strong> to create your first hero banner.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($slides as $s):
                            $sid = $s['id'] ?? '';
                            $isActive = !isset($s['active']) || (bool)$s['active'];
                        ?>
                            <tr class="slide-row <?php echo !$isActive ? 'is-inactive' : ''; ?>">
                                <td>
                                    <div class="slide-cell">
                                        <img src="<?php echo htmlspecialchars(adminAssetSrc($s['image'] ?? '')); ?>"
                                             alt="Slide preview"
                                             class="slide-thumb"
                                             onerror="this.onerror=null; this.src='https://placehold.co/120x68?text=Slide';">
                                        <div class="slide-path" title="<?php echo htmlspecialchars($s['image'] ?? ''); ?>"><?php echo htmlspecialchars($s['image'] ?? ''); ?></div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--text-heading);"><?php echo htmlspecialchars($s['title'] ?? ''); ?></div>
                                    <div style="font-size: 12.5px; color: var(--text-muted); margin-top: 2px;"><?php echo htmlspecialchars($s['subtitle'] ?? ''); ?></div>
                                </td>
                                <td>
                                    <div style="font-size: 13px; font-weight: 600;"><?php echo htmlspecialchars($s['buttonText'] ?? 'Shop Now'); ?></div>
                                    <div style="font-size: 11.5px; color: var(--text-muted);"><?php echo htmlspecialchars($s['buttonLink'] ?? '#'); ?></div>
                                </td>
                                <td><strong>#<?php echo (int)($s['order'] ?? 0); ?></strong></td>
                                <td>
                                    <form method="POST" action="slides.php" style="display:inline;">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="toggle_active" value="1">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($sid); ?>">
                                        <button type="submit"
                                                class="btn btn-sm <?php echo $isActive ? 'btn-outline' : 'btn-accent'; ?>"
                                                title="Click to <?php echo $isActive ? 'Deactivate' : 'Activate'; ?>">
                                            <i class="fa-solid fa-<?php echo $isActive ? 'eye' : 'eye-slash'; ?>"></i>
                                            <span><?php echo $isActive ? 'Active' : 'Inactive'; ?></span>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <button type="button"
                                                onclick="openEditSlideModal(<?php echo htmlspecialchars(json_encode($sid), ENT_QUOTES, 'UTF-8'); ?>)"
                                                class="btn btn-outline btn-sm"
                                                title="Edit Slide">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </button>
                                        <form method="POST" action="slides.php" style="display:inline;" onsubmit="return confirm('Delete this slide?');">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="delete_slide" value="1">
                                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($sid); ?>">
                                            <button type="submit" class="btn btn-danger-ghost btn-sm" title="Delete Slide">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr id="emptySlideFilterRow" style="display: none;">
                            <td colspan="6" style="text-align: center; padding: 36px; color: var(--text-muted);">No slides match your search.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- ADD / EDIT SLIDE MODAL -->
<?php
$currSlideImg = $formSlide['image'] ?? '';
?>
<div id="slideModal" class="modal-overlay <?php echo $openModalOnLoad ? 'open' : ''; ?>">
    <div class="modal-card">
        <div class="modal-header">
            <h3 id="slideModalHeading"><?php echo !empty($formSlide['id']) ? 'Edit Slide' : 'Add New Slide'; ?></h3>
            <button type="button" class="modal-close" onclick="closeSlideModal()" aria-label="Close modal">&times;</button>
        </div>
        <form method="POST" action="slides.php" enctype="multipart/form-data" id="slideForm">
            <?php echo csrfField(); ?>
            <input type="hidden" name="save_slide" value="1">
            <input type="hidden" name="id" id="slideIdInput" value="<?php echo htmlspecialchars($formSlide['id'] ?? ''); ?>">
            <input type="hidden" name="existing_image" id="slideExistingImgInput" value="<?php echo htmlspecialchars($currSlideImg); ?>">

            <div class="modal-body">
                <div class="form-full">
                    <label for="slideTitleInput">Slide Title *</label>
                    <input type="text" id="slideTitleInput" name="title" class="form-control" placeholder="e.g. Workspace Upgrade Sale 2026" required value="<?php echo htmlspecialchars($formSlide['title'] ?? ''); ?>">
                </div>

                <div class="form-full">
                    <label for="slideSubtitleInput">Subtitle</label>
                    <input type="text" id="slideSubtitleInput" name="subtitle" class="form-control" placeholder="e.g. Up to 25% off dual-motor standing desks and ergonomic chairs" value="<?php echo htmlspecialchars($formSlide['subtitle'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="slideBtnTextInput">Button Text</label>
                        <input type="text" id="slideBtnTextInput" name="buttonText" class="form-control" placeholder="Shop Now" value="<?php echo htmlspecialchars($formSlide['buttonText'] ?? 'Shop Now'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="slideBtnLinkInput">Button Link</label>
                        <input type="text" id="slideBtnLinkInput" name="buttonLink" class="form-control" placeholder="collections/standing-desks.html" value="<?php echo htmlspecialchars($formSlide['buttonLink'] ?? 'collections/all.html'); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="slideOrderInput">Display Order</label>
                        <input type="number" id="slideOrderInput" name="order" class="form-control" placeholder="0" value="<?php echo htmlspecialchars((string)($formSlide['order'] ?? count($slides) + 1)); ?>">
                    </div>
                    <div class="checkbox-group" style="margin-top: 26px;">
                        <input type="checkbox" id="activeCheck" name="active" <?php echo (!isset($formSlide) || !isset($formSlide['active']) || !empty($formSlide['active'])) ? 'checked' : ''; ?>>
                        <label for="activeCheck">Active (Visible on Store)</label>
                    </div>
                </div>

                <div class="form-full" style="margin-bottom: 0;">
                    <label>Slide Banner Image</label>
                    <div id="slideImagePreviewWrap" class="image-preview-box" style="<?php echo $currSlideImg !== '' ? '' : 'display:none;'; ?>">
                        <img id="slideImagePreview" src="<?php echo htmlspecialchars($currSlideImg !== '' ? adminAssetSrc($currSlideImg) : ''); ?>" alt="Current slide" onerror="this.src='https://placehold.co/120x68?text=Slide';">
                        <div>
                            <span style="font-size: 12px; font-weight: 700; color: var(--text-heading); display: block;">Selected Image Preview</span>
                            <span id="slideImagePreviewPath" style="font-size: 11.5px; color: var(--text-muted); font-family: monospace; word-break: break-all;"><?php echo htmlspecialchars($currSlideImg); ?></span>
                        </div>
                    </div>

                    <div class="form-row" style="margin-bottom: 0;">
                        <div class="form-group">
                            <input type="file" id="slideFileInput" name="image_file" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif" onchange="previewUploadedSlide(this)">
                            <span class="form-hint">Upload JPG, PNG, or WEBP (recommended 1920x700px, max 5 MB).</span>
                        </div>
                        <div class="form-group">
                            <input type="text" id="slideImgUrlInput" name="image_url" class="form-control" placeholder="Or path/URL e.g. assets/img/home-banner1.jpeg" value="<?php echo htmlspecialchars($currSlideImg); ?>" oninput="previewSlideUrl(this.value)">
                            <span class="form-hint">Path relative to root or full https:// URL.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeSlideModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Save Slide
                </button>
            </div>
        </form>
    </div>
</div>

<script id="slidesDataJson" type="application/json"><?php echo json_encode($slides, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
<script>
const SLIDES_LIST = (() => {
    try {
        return JSON.parse(document.getElementById('slidesDataJson').textContent || '[]');
    } catch (e) {
        return [];
    }
})();

function resolveSlidePreviewSrc(path) {
    if (!path) return '';
    if (/^(https?:\/\/|data:|\/)/i.test(path)) return path;
    return '../' + path.replace(/^\.?\//, '');
}

function openAddSlideModal() {
    document.getElementById('slideModalHeading').textContent = 'Add New Slide';
    document.getElementById('slideForm').reset();
    document.getElementById('slideIdInput').value = '';
    document.getElementById('slideExistingImgInput').value = '';
    document.getElementById('slideImgUrlInput').value = '';
    document.getElementById('slideBtnTextInput').value = 'Shop Now';
    document.getElementById('slideBtnLinkInput').value = 'collections/all.html';
    document.getElementById('slideOrderInput').value = String(SLIDES_LIST.length + 1);
    document.getElementById('activeCheck').checked = true;
    document.getElementById('slideImagePreviewWrap').style.display = 'none';
    document.getElementById('slideModal').classList.add('open');
}

function openEditSlideModal(id) {
    const slide = SLIDES_LIST.find(s => s.id === id);
    if (!slide) {
        window.location.href = 'slides.php?action=edit&id=' + encodeURIComponent(id);
        return;
    }
    document.getElementById('slideModalHeading').textContent = 'Edit Slide';
    document.getElementById('slideFileInput').value = '';
    document.getElementById('slideIdInput').value = slide.id || '';
    document.getElementById('slideTitleInput').value = slide.title || '';
    document.getElementById('slideSubtitleInput').value = slide.subtitle || '';
    document.getElementById('slideBtnTextInput').value = slide.buttonText || 'Shop Now';
    document.getElementById('slideBtnLinkInput').value = slide.buttonLink || '#';
    document.getElementById('slideOrderInput').value = slide.order ?? 0;
    document.getElementById('activeCheck').checked = (slide.active === undefined || slide.active === true);

    const imgPath = slide.image || '';
    document.getElementById('slideExistingImgInput').value = imgPath;
    document.getElementById('slideImgUrlInput').value = imgPath;
    previewSlideUrl(imgPath);

    document.getElementById('slideModal').classList.add('open');
}

function closeSlideModal() {
    document.getElementById('slideModal').classList.remove('open');
    if (window.location.search.includes('action=edit') || window.location.search.includes('modal=new')) {
        window.history.replaceState({}, '', 'slides.php');
    }
}

function previewUploadedSlide(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    const reader = new FileReader();
    reader.onload = function(e) {
        const wrap = document.getElementById('slideImagePreviewWrap');
        const img = document.getElementById('slideImagePreview');
        const pathLabel = document.getElementById('slideImagePreviewPath');
        img.src = e.target.result;
        pathLabel.textContent = 'New upload: ' + file.name;
        wrap.style.display = 'flex';
    };
    reader.readAsDataURL(file);
}

function previewSlideUrl(val) {
    const fileInput = document.getElementById('slideFileInput');
    if (fileInput && fileInput.files && fileInput.files.length > 0) return;
    const trimmed = (val || '').trim();
    const wrap = document.getElementById('slideImagePreviewWrap');
    const img = document.getElementById('slideImagePreview');
    const pathLabel = document.getElementById('slideImagePreviewPath');
    if (!trimmed) {
        wrap.style.display = 'none';
        return;
    }
    img.src = resolveSlidePreviewSrc(trimmed);
    pathLabel.textContent = trimmed;
    wrap.style.display = 'flex';
}

function filterSlides() {
    const query = document.getElementById('searchInput').value.trim().toLowerCase();
    const rows = document.querySelectorAll('.slide-row');
    let visible = 0;
    rows.forEach(row => {
        const match = !query || row.innerText.toLowerCase().includes(query);
        row.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    const emptyRow = document.getElementById('emptySlideFilterRow');
    if (emptyRow) emptyRow.style.display = (rows.length > 0 && visible === 0) ? '' : 'none';
}

document.getElementById('slideModal').addEventListener('click', function(e) {
    if (e.target === this) closeSlideModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeSlideModal();
});
</script>

</body>
</html>
