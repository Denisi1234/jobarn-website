<?php
require_once __DIR__ . '/auth.php';
requireAuth();
require_once __DIR__ . '/navbar.php';

$products = getProducts();
$categories = getProductCategories();
$message = '';
$messageType = 'success';
$formProduct = null;
$openModalOnLoad = false;

// Handle POST Actions (CSRF-protected)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Security token expired. Please refresh the page and try again.';
        $messageType = 'danger';
    } elseif (isset($_POST['delete_product'])) {
        $delId = trim($_POST['id'] ?? '');
        $filtered = [];
        $deleted = false;
        foreach ($products as $p) {
            if (($p['id'] ?? '') === $delId) {
                $deleted = true;
                continue;
            }
            $filtered[] = $p;
        }
        if ($deleted && saveProducts($filtered)) {
            header('Location: index.php?msg=deleted');
            exit;
        }
        $message = $deleted ? 'Failed to save catalog file. Check permissions on data/.' : 'Product not found.';
        $messageType = 'danger';
    } elseif (isset($_POST['toggle_active'])) {
        $toggleId = trim($_POST['id'] ?? '');
        $newState = 'deactivated';
        $found = false;
        foreach ($products as &$p) {
            if (($p['id'] ?? '') === $toggleId) {
                $currentStatus = isset($p['active']) ? (bool)$p['active'] : true;
                $p['active'] = !$currentStatus;
                $newState = $p['active'] ? 'activated' : 'deactivated';
                $found = true;
                break;
            }
        }
        unset($p);
        if ($found && saveProducts($products)) {
            header('Location: index.php?msg=' . $newState);
            exit;
        }
        $message = 'Failed to update product visibility.';
        $messageType = 'danger';
    } elseif (isset($_POST['toggle_stock'])) {
        $toggleId = trim($_POST['id'] ?? '');
        $newState = 'outofstock';
        $found = false;
        foreach ($products as &$p) {
            if (($p['id'] ?? '') === $toggleId) {
                $currentStock = !isset($p['inStock']) || (bool)$p['inStock'];
                $p['inStock'] = !$currentStock;
                $newState = $p['inStock'] ? 'instock' : 'outofstock';
                $found = true;
                break;
            }
        }
        unset($p);
        if ($found && saveProducts($products)) {
            header('Location: index.php?msg=' . $newState);
            exit;
        }
        $message = 'Failed to update product stock status.';
        $messageType = 'danger';
    } elseif (isset($_POST['toggle_featured'])) {
        $toggleId = trim($_POST['id'] ?? '');
        $found = false;
        foreach ($products as &$p) {
            if (($p['id'] ?? '') === $toggleId) {
                $p['isFeatured'] = empty($p['isFeatured']);
                $found = true;
                break;
            }
        }
        unset($p);
        if ($found && saveProducts($products)) {
            header('Location: index.php?msg=featured_toggled');
            exit;
        }
        $message = 'Failed to update featured status.';
        $messageType = 'danger';
    } elseif (isset($_POST['save_product'])) {
        $id = trim($_POST['id'] ?? '');
        $isNew = ($id === '');

        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? 'desks');
        if (!isset($categories[$category])) {
            $category = 'desks';
        }
        $categoryName = $categories[$category];

        $priceRaw = preg_replace('/[^0-9.]/', '', str_replace(',', '', $_POST['price'] ?? '0'));
        $price = (int)round((float)$priceRaw);
        $compareRaw = preg_replace('/[^0-9.]/', '', str_replace(',', '', $_POST['comparePrice'] ?? '0'));
        $comparePrice = (int)round((float)$compareRaw);

        $badge = trim($_POST['badge'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $inStock = isset($_POST['inStock']);
        $isFeatured = isset($_POST['isFeatured']);
        $active = isset($_POST['active']);

        // Parse specs (one per line)
        $specsRaw = trim($_POST['specs'] ?? '');
        $specs = [];
        if ($specsRaw !== '') {
            $lines = explode("\n", str_replace("\r", "", $specsRaw));
            foreach ($lines as $line) {
                $line = trim($line, " \t\n\r\0\x0B-•*");
                if ($line !== '') {
                    $specs[] = $line;
                }
            }
        }

        // Parse extra gallery URLs/paths
        $extraGalleryRaw = trim($_POST['gallery_images'] ?? '');
        $extraGallery = [];
        if ($extraGalleryRaw !== '') {
            foreach (explode("\n", str_replace("\r", "", $extraGalleryRaw)) as $gLine) {
                $gLine = trim($gLine);
                if ($gLine !== '') {
                    $extraGallery[] = $gLine;
                }
            }
        }

        // Primary image: upload takes priority, then URL/path field, then existing
        $image = trim($_POST['image_url'] ?? '');
        if ($image === '') {
            $image = trim($_POST['existing_image'] ?? '');
        }

        $uploadError = null;
        if (!empty($_FILES['image_file']['name'])) {
            $uploadRes = handleImageUpload($_FILES['image_file'], 'prod_');
            if (!$uploadRes['ok']) {
                $uploadError = $uploadRes['error'];
            } elseif (!empty($uploadRes['path'])) {
                $image = $uploadRes['path'];
            }
        }

        // Optional extra gallery file uploads
        if (!$uploadError && !empty($_FILES['gallery_files']['name']) && is_array($_FILES['gallery_files']['name'])) {
            $count = count($_FILES['gallery_files']['name']);
            for ($i = 0; $i < $count; $i++) {
                if (empty($_FILES['gallery_files']['name'][$i])) {
                    continue;
                }
                $singleFile = [
                    'name' => $_FILES['gallery_files']['name'][$i],
                    'type' => $_FILES['gallery_files']['type'][$i] ?? '',
                    'tmp_name' => $_FILES['gallery_files']['tmp_name'][$i] ?? '',
                    'error' => $_FILES['gallery_files']['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                    'size' => $_FILES['gallery_files']['size'][$i] ?? 0,
                ];
                $gRes = handleImageUpload($singleFile, 'prod_gal_');
                if (!$gRes['ok']) {
                    $uploadError = $gRes['error'];
                    break;
                } elseif (!empty($gRes['path'])) {
                    $extraGallery[] = $gRes['path'];
                }
            }
        }

        if ($title === '') {
            $message = 'Product title is required.';
            $messageType = 'danger';
        } elseif ($price < 0) {
            $message = 'Selling price cannot be negative.';
            $messageType = 'danger';
        } elseif ($uploadError !== null) {
            $message = $uploadError;
            $messageType = 'danger';
        }

        if ($messageType === 'danger') {
            $openModalOnLoad = true;
            $formProduct = [
                'id' => $id,
                'title' => $title,
                'category' => $category,
                'price' => $price,
                'comparePrice' => $comparePrice > 0 ? $comparePrice : '',
                'badge' => $badge,
                'image' => $image,
                'images' => array_values(array_unique(array_filter(array_merge([$image], $extraGallery)))),
                'description' => $description,
                'specs' => $specs,
                'inStock' => $inStock,
                'isFeatured' => $isFeatured,
                'active' => $active,
            ];
        } else {
            if ($image === '') {
                $image = 'assets/ecommerce/products/desk-140cm.jpg';
            }

            if ($isNew) {
                $slug = trim(preg_replace('/[^a-zA-Z0-9]+/', '-', substr(strtolower($title), 0, 24)), '-');
                if ($slug === '') {
                    $slug = 'item';
                }
                $existingIds = array_column($products, 'id');
                do {
                    $id = 'jobarn-' . $slug . '-' . random_int(100, 999);
                } while (in_array($id, $existingIds, true));
            }

            $allImages = array_values(array_unique(array_filter(array_merge([$image], $extraGallery))));

            $productData = [
                'id' => $id,
                'title' => $title,
                'category' => $category,
                'categoryName' => $categoryName,
                'price' => $price,
                'comparePrice' => $comparePrice > 0 ? $comparePrice : null,
                'rating' => 4.9,
                'reviewsCount' => random_int(15, 55),
                'badge' => $badge,
                'image' => $image,
                'images' => $allImages,
                'description' => $description,
                'specs' => $specs,
                'inStock' => $inStock,
                'isFeatured' => $isFeatured,
                'active' => $active
            ];

            $updatedList = [];
            $found = false;
            foreach ($products as $p) {
                if (($p['id'] ?? '') === $id) {
                    $productData['rating'] = $p['rating'] ?? 4.9;
                    $productData['reviewsCount'] = $p['reviewsCount'] ?? 25;
                    $updatedList[] = $productData;
                    $found = true;
                } else {
                    $updatedList[] = $p;
                }
            }
            if (!$found) {
                array_unshift($updatedList, $productData);
            }

            if (saveProducts($updatedList)) {
                header('Location: index.php?msg=' . ($found ? 'updated' : 'added'));
                exit;
            }
            $message = 'Failed to save products data. Please check permissions on data/ and assets/ecommerce/.';
            $messageType = 'danger';
            $openModalOnLoad = true;
            $formProduct = $productData;
        }
    }
}

// Check GET parameters for opening modal via URL (backwards compatible)
$action = $_GET['action'] ?? '';
$editId = $_GET['id'] ?? '';
if (!$openModalOnLoad && $action === 'edit' && $editId !== '') {
    foreach ($products as $p) {
        if (($p['id'] ?? '') === $editId) {
            $formProduct = $p;
            $openModalOnLoad = true;
            break;
        }
    }
} elseif (!$openModalOnLoad && ($_GET['modal'] ?? '') === 'new') {
    $openModalOnLoad = true;
}

if ($message === '' && isset($_GET['msg'])) {
    $msgMap = [
        'added' => 'Product successfully added to your store catalog!',
        'updated' => 'Product details updated and synced to storefront!',
        'deleted' => 'Product removed from catalog.',
        'activated' => 'Product activated and is now visible on the store.',
        'deactivated' => 'Product deactivated and hidden from the store.',
        'instock' => 'Product marked as In Stock.',
        'outofstock' => 'Product marked as Out of Stock.',
        'featured_toggled' => 'Product Featured / Upgrades status updated.',
    ];
    $message = $msgMap[$_GET['msg']] ?? '';
}

// Stats calculation
$activeCount = 0;
$inStockCount = 0;
$featuredCount = 0;
foreach ($products as $p) {
    if (!isset($p['active']) || !empty($p['active'])) {
        $activeCount++;
    }
    if (!isset($p['inStock']) || !empty($p['inStock'])) {
        $inStockCount++;
    }
    if (!empty($p['isFeatured'])) {
        $featuredCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Manager - Jobarn Admin</title>
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .product-cell {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 240px;
        }
        .product-thumb {
            width: 52px;
            height: 52px;
            border-radius: 10px;
            object-fit: cover;
            border: 1px solid var(--border);
            background: #f1f5f9;
            flex-shrink: 0;
        }
        .product-title-wrap h4 {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-heading);
            margin-bottom: 3px;
            max-width: 320px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .product-title-wrap p {
            font-size: 12px;
            color: var(--text-muted);
            max-width: 320px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        tr.is-inactive {
            opacity: 0.58;
            background: #f8fafc;
        }
        tr.is-inactive .product-thumb {
            filter: grayscale(80%);
        }
        .status-toggle-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid transparent;
            font-family: inherit;
            white-space: nowrap;
        }
        .status-toggle-btn.btn-is-active {
            background: #dcfce7;
            color: #15803d;
            border-color: #86efac;
        }
        .status-toggle-btn.btn-is-active:hover {
            background: #fee2e2;
            color: #dc2626;
            border-color: #fca5a5;
        }
        .status-toggle-btn.btn-is-inactive {
            background: #f1f5f9;
            color: #64748b;
            border-color: #cbd5e1;
        }
        .status-toggle-btn.btn-is-inactive:hover {
            background: #dcfce7;
            color: #15803d;
            border-color: #86efac;
        }
        .stock-toggle-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 9px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border: none;
            cursor: pointer;
            font-family: inherit;
            white-space: nowrap;
        }
        .stock-toggle-btn.in { background: #dcfce7; color: #15803d; }
        .stock-toggle-btn.out { background: #f1f5f9; color: #64748b; }
        .stock-toggle-btn:hover { filter: brightness(0.95); }

        .featured-toggle-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: #ffffff;
            color: #cbd5e1;
            cursor: pointer;
            transition: all 0.15s;
        }
        .featured-toggle-btn.is-featured {
            background: #fef3c7;
            border-color: #fcd34d;
            color: #d97706;
        }
        .featured-toggle-btn:hover { transform: scale(1.06); }

        .price-tzs {
            font-weight: 800;
            color: var(--text-heading);
            font-size: 14px;
            white-space: nowrap;
        }
        .compare-tzs {
            font-size: 12px;
            color: #94a3b8;
            text-decoration: line-through;
            display: block;
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
            width: 56px;
            height: 56px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background: #fff;
        }
        .empty-filter-row td {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-muted);
        }
    </style>
</head>
<body>

<?php renderAdminNav('index.php', 'Products Manager', 'Manage store inventory, prices, stock & descriptions'); ?>

<main class="main-content">
    <div class="admin-container">
        <?php if ($message !== ''): ?>
            <div class="alert-bar <?php echo htmlspecialchars($messageType); ?>">
                <i class="fa-solid <?php echo $messageType === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?>"></i>
                <span><?php echo htmlspecialchars($message); ?></span>
            </div>
        <?php endif; ?>

        <!-- Summary stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div>
                    <div class="val"><?php echo $activeCount; ?></div>
                    <div class="label">Active on Store</div>
                </div>
                <div class="icon-box"><i class="fa-solid fa-eye"></i></div>
            </div>
            <div class="stat-card">
                <div>
                    <div class="val"><?php echo $inStockCount; ?></div>
                    <div class="label">In Stock Items</div>
                </div>
                <div class="icon-box" style="background: #ecfdf5; color: #10b981;"><i class="fa-solid fa-check-circle"></i></div>
            </div>
            <div class="stat-card">
                <div>
                    <div class="val"><?php echo $featuredCount; ?></div>
                    <div class="label">Featured / Upgrades</div>
                </div>
                <div class="icon-box" style="background: #fffbeb; color: #d97706;"><i class="fa-solid fa-star"></i></div>
            </div>
            <div class="stat-card">
                <div>
                    <div class="val"><?php echo count($products); ?></div>
                    <div class="label">Total in Catalog</div>
                </div>
                <div class="icon-box" style="background: #fdf2f8; color: #db2777;"><i class="fa-solid fa-layer-group"></i></div>
            </div>
        </div>

        <div class="section-header">
            <div class="section-title">
                <i class="fa-solid fa-boxes-stacked" style="color: var(--accent);"></i>
                <span>Products Catalog</span>
                <span id="visibleCountLabel" style="font-size: 13px; font-weight: 500; color: var(--text-muted);">(<?php echo count($products); ?> items)</span>
            </div>

            <div class="toolbar-actions">
                <div class="search-filter-bar">
                    <i class="fa-solid fa-magnifying-glass" style="color: #94a3b8;"></i>
                    <input type="text" id="searchInput" placeholder="Search name, category, badge..." oninput="filterProducts()">
                </div>

                <select id="categoryFilter" class="filter-select" onchange="filterProducts()" aria-label="Filter by category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $catKey => $catLabel): ?>
                        <option value="<?php echo htmlspecialchars($catKey); ?>"><?php echo htmlspecialchars($catLabel); ?></option>
                    <?php endforeach; ?>
                </select>

                <select id="statusFilter" class="filter-select" onchange="filterProducts()" aria-label="Filter by status">
                    <option value="">All Statuses</option>
                    <option value="active">Active Only</option>
                    <option value="inactive">Hidden / Inactive</option>
                    <option value="instock">In Stock</option>
                    <option value="outstock">Out of Stock</option>
                    <option value="featured">Featured / Upgrades</option>
                    <option value="sale">On Sale (Deals)</option>
                </select>

                <button type="button" onclick="openAddProductModal()" class="btn btn-accent">
                    <i class="fa-solid fa-plus"></i> Add New Product
                </button>
            </div>
        </div>

        <!-- Products Table -->
        <div class="table-wrap">
            <table id="productsTable">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price (TZS)</th>
                        <th>Visibility</th>
                        <th>Stock</th>
                        <th>Featured</th>
                        <th>Badge</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 48px; color: #94a3b8;">
                                No products in catalog yet. Click <strong>Add New Product</strong> to get started.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $p):
                            $pid = $p['id'] ?? '';
                            $isActive = !isset($p['active']) || (bool)$p['active'];
                            $inStock = !isset($p['inStock']) || (bool)$p['inStock'];
                            $isFeatured = !empty($p['isFeatured']);
                            $isSale = !empty($p['comparePrice']) && (float)$p['comparePrice'] > (float)($p['price'] ?? 0);
                            $catKey = $p['category'] ?? '';
                        ?>
                            <tr class="product-row <?php echo !$isActive ? 'is-inactive' : ''; ?>"
                                data-category="<?php echo htmlspecialchars($catKey); ?>"
                                data-active="<?php echo $isActive ? '1' : '0'; ?>"
                                data-stock="<?php echo $inStock ? '1' : '0'; ?>"
                                data-featured="<?php echo $isFeatured ? '1' : '0'; ?>"
                                data-sale="<?php echo $isSale ? '1' : '0'; ?>">
                                <td>
                                    <div class="product-cell">
                                        <img src="<?php echo htmlspecialchars(adminAssetSrc($p['image'] ?? '')); ?>"
                                             alt="<?php echo htmlspecialchars($p['title'] ?? ''); ?>"
                                             class="product-thumb"
                                             onerror="this.onerror=null; this.src='https://placehold.co/100x100?text=No+Image';">
                                        <div class="product-title-wrap">
                                            <h4><?php echo htmlspecialchars($p['title'] ?? ''); ?></h4>
                                            <p><?php echo htmlspecialchars($p['description'] ?? ''); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-category"><?php echo htmlspecialchars($p['categoryName'] ?? ($categories[$catKey] ?? ucfirst($catKey))); ?></span>
                                </td>
                                <td>
                                    <span class="price-tzs"><?php echo number_format((float)($p['price'] ?? 0)); ?></span>
                                    <?php if ($isSale): ?>
                                        <span class="compare-tzs"><?php echo number_format((float)$p['comparePrice']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" action="index.php" style="display:inline;">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="toggle_active" value="1">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($pid); ?>">
                                        <button type="submit"
                                                class="status-toggle-btn <?php echo $isActive ? 'btn-is-active' : 'btn-is-inactive'; ?>"
                                                title="Click to <?php echo $isActive ? 'Deactivate (Hide from store)' : 'Activate (Show on store)'; ?>">
                                            <i class="fa-solid <?php echo $isActive ? 'fa-toggle-on' : 'fa-toggle-off'; ?>"></i>
                                            <span><?php echo $isActive ? 'Active' : 'Hidden'; ?></span>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" action="index.php" style="display:inline;">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="toggle_stock" value="1">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($pid); ?>">
                                        <button type="submit"
                                                class="stock-toggle-btn <?php echo $inStock ? 'in' : 'out'; ?>"
                                                title="Click to toggle stock availability">
                                            <?php echo $inStock ? 'In Stock' : 'Out of Stock'; ?>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" action="index.php" style="display:inline;">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="toggle_featured" value="1">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($pid); ?>">
                                        <button type="submit"
                                                class="featured-toggle-btn <?php echo $isFeatured ? 'is-featured' : ''; ?>"
                                                title="<?php echo $isFeatured ? 'Featured in Upgrades (click to remove)' : 'Not Featured (click to feature)'; ?>">
                                            <i class="fa-solid fa-star"></i>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <?php if (!empty($p['badge'])): ?>
                                        <span class="badge badge-sale"><?php echo htmlspecialchars($p['badge']); ?></span>
                                    <?php else: ?>
                                        <span style="color: #cbd5e1;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <button type="button"
                                                onclick="openEditProductModal(<?php echo htmlspecialchars(json_encode($pid), ENT_QUOTES, 'UTF-8'); ?>)"
                                                class="btn btn-outline btn-sm"
                                                title="Edit Product">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </button>
                                        <form method="POST" action="index.php" style="display:inline;" onsubmit="return confirm('Delete this product permanently?');">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="delete_product" value="1">
                                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($pid); ?>">
                                            <button type="submit" class="btn btn-danger-ghost btn-sm" title="Delete Product">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr id="emptyFilterRow" class="empty-filter-row" style="display: none;">
                            <td colspan="8">No products match your search or selected filters.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- ADD / EDIT PRODUCT MODAL -->
<?php
$modalTitle = !empty($formProduct['id']) ? 'Edit Product' : 'Add New Product';
$currCat = $formProduct['category'] ?? 'desks';
$currImage = $formProduct['image'] ?? '';
$extraGalleryList = [];
if (!empty($formProduct['images']) && is_array($formProduct['images'])) {
    foreach ($formProduct['images'] as $gImg) {
        if ($gImg !== $currImage) {
            $extraGalleryList[] = $gImg;
        }
    }
}
?>
<div id="productModal" class="modal-overlay <?php echo $openModalOnLoad ? 'open' : ''; ?>">
    <div class="modal-card">
        <div class="modal-header">
            <h3 id="productModalHeading"><?php echo htmlspecialchars($modalTitle); ?></h3>
            <button type="button" class="modal-close" onclick="closeProductModal()" aria-label="Close modal">&times;</button>
        </div>
        <form method="POST" action="index.php" enctype="multipart/form-data" id="productForm">
            <?php echo csrfField(); ?>
            <input type="hidden" name="save_product" value="1">
            <input type="hidden" name="id" id="prodIdInput" value="<?php echo htmlspecialchars($formProduct['id'] ?? ''); ?>">
            <input type="hidden" name="existing_image" id="prodExistingImgInput" value="<?php echo htmlspecialchars($currImage); ?>">

            <div class="modal-body">
                <div class="form-full">
                    <label for="prodTitleInput">Product Title / Name *</label>
                    <input type="text" id="prodTitleInput" name="title" class="form-control" placeholder="e.g. Jobarn Dual-Motor Electric Standing Desk" required value="<?php echo htmlspecialchars($formProduct['title'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="prodCatInput">Category *</label>
                        <select id="prodCatInput" name="category" class="form-control">
                            <?php foreach ($categories as $key => $name): ?>
                                <option value="<?php echo htmlspecialchars($key); ?>" <?php echo $currCat === $key ? 'selected' : ''; ?>><?php echo htmlspecialchars($name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="prodBadgeInput">Badge (Optional tag)</label>
                        <input type="text" id="prodBadgeInput" name="badge" class="form-control" placeholder="e.g. Best Seller, Hot, Sale -20%" value="<?php echo htmlspecialchars($formProduct['badge'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="prodPriceInput">Selling Price (TZS) *</label>
                        <input type="number" id="prodPriceInput" name="price" class="form-control" min="0" step="1" placeholder="e.g. 1450000" required value="<?php echo htmlspecialchars((string)($formProduct['price'] ?? '')); ?>">
                    </div>
                    <div class="form-group">
                        <label for="prodCompareInput">Compare / Original Price (TZS)</label>
                        <input type="number" id="prodCompareInput" name="comparePrice" class="form-control" min="0" step="1" placeholder="e.g. 1750000 (shows strikeout & Deals)" value="<?php echo htmlspecialchars((string)($formProduct['comparePrice'] ?? '')); ?>">
                    </div>
                </div>

                <div class="form-full">
                    <label>Primary Product Image</label>
                    <div id="prodImagePreviewWrap" class="image-preview-box" style="<?php echo $currImage !== '' ? '' : 'display:none;'; ?>">
                        <img id="prodImagePreview" src="<?php echo htmlspecialchars($currImage !== '' ? adminAssetSrc($currImage) : ''); ?>" alt="Product preview" onerror="this.src='https://placehold.co/100x100?text=Preview';">
                        <div>
                            <span style="font-size: 12px; font-weight: 700; color: var(--text-heading); display: block;">Selected Image Preview</span>
                            <span id="prodImagePreviewPath" style="font-size: 11.5px; color: var(--text-muted); font-family: monospace; word-break: break-all;"><?php echo htmlspecialchars($currImage); ?></span>
                        </div>
                    </div>

                    <div class="form-row" style="margin-bottom: 6px;">
                        <div class="form-group">
                            <input type="file" id="prodFileInput" name="image_file" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif" onchange="previewUploadedImage(this)">
                            <span class="form-hint">Upload JPG, PNG, WEBP, or GIF (max 5 MB).</span>
                        </div>
                        <div class="form-group">
                            <input type="text" id="prodImgUrlInput" name="image_url" class="form-control" placeholder="Or enter path/URL e.g. assets/ecommerce/products/..." value="<?php echo htmlspecialchars($currImage); ?>" oninput="previewImageUrl(this.value)">
                            <span class="form-hint">Path relative to site root or full https:// URL.</span>
                        </div>
                    </div>
                </div>

                <div class="form-full">
                    <label for="prodDescInput">Product Description</label>
                    <textarea id="prodDescInput" name="description" class="form-control" placeholder="Write a clean, compelling description for customers..."><?php echo htmlspecialchars($formProduct['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-full">
                    <label for="prodSpecsInput">Key Specifications (One bullet point per line)</label>
                    <textarea id="prodSpecsInput" name="specs" class="form-control" placeholder="Height Range: 62cm - 128cm&#10;Quiet dual motors (&lt;45dB)&#10;3 Years Warranty"><?php
                        if (!empty($formProduct['specs']) && is_array($formProduct['specs'])) {
                            echo htmlspecialchars(implode("\n", $formProduct['specs']));
                        }
                    ?></textarea>
                </div>

                <div class="form-full">
                    <label for="prodGalleryInput">Extra Gallery Images (Optional, one path/URL per line or upload additional files)</label>
                    <textarea id="prodGalleryInput" name="gallery_images" class="form-control" style="min-height: 64px; margin-bottom: 8px;" placeholder="assets/ecommerce/products/variant-2.jpg"><?php echo htmlspecialchars(implode("\n", $extraGalleryList)); ?></textarea>
                    <input type="file" id="prodGalleryFilesInput" name="gallery_files[]" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif" multiple>
                </div>

                <div class="form-row">
                    <div class="checkbox-group">
                        <input type="checkbox" id="activeCheck" name="active" <?php echo (!isset($formProduct) || !isset($formProduct['active']) || !empty($formProduct['active'])) ? 'checked' : ''; ?>>
                        <label for="activeCheck">Active (Visible in Store)</label>
                    </div>
                    <div class="checkbox-group">
                        <input type="checkbox" id="inStockCheck" name="inStock" <?php echo (!isset($formProduct) || !isset($formProduct['inStock']) || !empty($formProduct['inStock'])) ? 'checked' : ''; ?>>
                        <label for="inStockCheck">In Stock (Available to Order)</label>
                    </div>
                </div>
                <div class="form-row" style="margin-bottom: 0;">
                    <div class="checkbox-group">
                        <input type="checkbox" id="featuredCheck" name="isFeatured" <?php echo (!empty($formProduct['isFeatured'])) ? 'checked' : ''; ?>>
                        <label for="featuredCheck">Featured (Show in Upgrades Collection)</label>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeProductModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Save Product
                </button>
            </div>
        </form>
    </div>
</div>

<script id="productsDataJson" type="application/json"><?php echo json_encode($products, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
<script>
const PRODUCTS_LIST = (() => {
    try {
        return JSON.parse(document.getElementById('productsDataJson').textContent || '[]');
    } catch (e) {
        return [];
    }
})();

function resolvePreviewSrc(path) {
    if (!path) return '';
    if (/^(https?:\/\/|data:|\/)/i.test(path)) return path;
    return '../' + path.replace(/^\.?\//, '');
}

function openAddProductModal() {
    document.getElementById('productModalHeading').textContent = 'Add New Product';
    document.getElementById('productForm').reset();
    document.getElementById('prodIdInput').value = '';
    document.getElementById('prodExistingImgInput').value = '';
    document.getElementById('prodImgUrlInput').value = '';
    document.getElementById('prodCatInput').value = 'desks';
    document.getElementById('prodGalleryInput').value = '';
    document.getElementById('activeCheck').checked = true;
    document.getElementById('inStockCheck').checked = true;
    document.getElementById('featuredCheck').checked = false;
    document.getElementById('prodImagePreviewWrap').style.display = 'none';
    document.getElementById('productModal').classList.add('open');
}

function openEditProductModal(id) {
    const prod = PRODUCTS_LIST.find(p => p.id === id);
    if (!prod) {
        window.location.href = 'index.php?action=edit&id=' + encodeURIComponent(id);
        return;
    }
    document.getElementById('productModalHeading').textContent = 'Edit Product';
    document.getElementById('prodFileInput').value = '';
    document.getElementById('prodGalleryFilesInput').value = '';
    document.getElementById('prodIdInput').value = prod.id || '';
    document.getElementById('prodTitleInput').value = prod.title || '';
    document.getElementById('prodCatInput').value = prod.category || 'desks';
    document.getElementById('prodBadgeInput').value = prod.badge || '';
    document.getElementById('prodPriceInput').value = prod.price ?? '';
    document.getElementById('prodCompareInput').value = prod.comparePrice ?? '';
    document.getElementById('prodDescInput').value = prod.description || '';
    document.getElementById('prodSpecsInput').value = Array.isArray(prod.specs) ? prod.specs.join('\n') : '';

    const primaryImg = prod.image || '';
    document.getElementById('prodExistingImgInput').value = primaryImg;
    document.getElementById('prodImgUrlInput').value = primaryImg;
    previewImageUrl(primaryImg);

    const extraImgs = Array.isArray(prod.images) ? prod.images.filter(img => img && img !== primaryImg) : [];
    document.getElementById('prodGalleryInput').value = extraImgs.join('\n');

    document.getElementById('activeCheck').checked = (prod.active === undefined || prod.active === true);
    document.getElementById('inStockCheck').checked = (prod.inStock === undefined || prod.inStock === true);
    document.getElementById('featuredCheck').checked = Boolean(prod.isFeatured);

    document.getElementById('productModal').classList.add('open');
}

function closeProductModal() {
    document.getElementById('productModal').classList.remove('open');
    if (window.location.search.includes('action=edit') || window.location.search.includes('modal=new')) {
        window.history.replaceState({}, '', 'index.php');
    }
}

function previewUploadedImage(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    const reader = new FileReader();
    reader.onload = function(e) {
        const wrap = document.getElementById('prodImagePreviewWrap');
        const img = document.getElementById('prodImagePreview');
        const pathLabel = document.getElementById('prodImagePreviewPath');
        img.src = e.target.result;
        pathLabel.textContent = 'New upload: ' + file.name;
        wrap.style.display = 'flex';
    };
    reader.readAsDataURL(file);
}

function previewImageUrl(val) {
    const fileInput = document.getElementById('prodFileInput');
    if (fileInput && fileInput.files && fileInput.files.length > 0) return;
    const trimmed = (val || '').trim();
    const wrap = document.getElementById('prodImagePreviewWrap');
    const img = document.getElementById('prodImagePreview');
    const pathLabel = document.getElementById('prodImagePreviewPath');
    if (!trimmed) {
        wrap.style.display = 'none';
        return;
    }
    img.src = resolvePreviewSrc(trimmed);
    pathLabel.textContent = trimmed;
    wrap.style.display = 'flex';
}

function filterProducts() {
    const query = document.getElementById('searchInput').value.trim().toLowerCase();
    const cat = document.getElementById('categoryFilter').value;
    const status = document.getElementById('statusFilter').value;
    const rows = document.querySelectorAll('.product-row');
    let visible = 0;

    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        const rowCat = row.dataset.category || '';
        const isActive = row.dataset.active === '1';
        const inStock = row.dataset.stock === '1';
        const isFeatured = row.dataset.featured === '1';
        const isSale = row.dataset.sale === '1';

        let match = !query || text.includes(query);
        if (match && cat && rowCat !== cat) match = false;
        if (match && status) {
            if (status === 'active' && !isActive) match = false;
            else if (status === 'inactive' && isActive) match = false;
            else if (status === 'instock' && !inStock) match = false;
            else if (status === 'outstock' && inStock) match = false;
            else if (status === 'featured' && !isFeatured) match = false;
            else if (status === 'sale' && !isSale) match = false;
        }

        row.style.display = match ? '' : 'none';
        if (match) visible++;
    });

    const emptyRow = document.getElementById('emptyFilterRow');
    if (emptyRow) emptyRow.style.display = (rows.length > 0 && visible === 0) ? '' : 'none';

    const countEl = document.getElementById('visibleCountLabel');
    if (countEl) countEl.textContent = `(${visible} ${visible === 1 ? 'item' : 'items'})`;
}

document.getElementById('productModal').addEventListener('click', function(e) {
    if (e.target === this) closeProductModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeProductModal();
});
</script>

</body>
</html>
