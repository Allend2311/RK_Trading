<?php
session_start();

// Auth checks
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: login.php');
    exit;
}
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: home.php');
    exit;
}

// Handle logout via dedicated endpoint (logout.php)
// Removed inline GET logout handling to centralize logic.

require_once __DIR__ . '/db_connect.php';

// Helpers
function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function generateSku($name) {
    $base = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $name));
    $base = substr($base, 0, 8);
    return 'SKU-' . ($base ?: 'ITEM') . '-' . substr((string)time(), -5);
}

$errors = [];
$successes = [];

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    try {
        if ($action === 'add_product') {
            $name = trim($_POST['name'] ?? '');
            $price = trim($_POST['price'] ?? '');
            $category = trim($_POST['category'] ?? ''); // not stored (no column), kept for future
            $stock = trim($_POST['stock'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $image = trim($_POST['image'] ?? ''); // not stored (no column), kept for future

            // Validation
            if ($name === '' || strlen($name) < 3) {
                throw new Exception('Product name must be at least 3 characters.');
            }
            if (!is_numeric($price) || (float)$price <= 0) {
                throw new Exception('Price must be greater than 0.');
            }
            if (!is_numeric($stock) || (int)$stock < 0) {
                throw new Exception('Stock cannot be negative.');
            }
            if ($description === '' || strlen($description) < 10) {
                throw new Exception('Description must be at least 10 characters.');
            }

            $sku = generateSku($name);
            $currency = 'PHP';
            $is_active = 1;

            $stmt = $mysqli->prepare('INSERT INTO products (sku, name, description, price, currency, stock, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('ssssiii', $sku, $name, $description, $price, $currency, $stock, $is_active);
            $stmt->execute();
            $stmt->close();

            $successes[] = "Product '" . h($name) . "' added with SKU " . h($sku) . ".";
        }

        if ($action === 'update_stock') {
            $product_id = (int)($_POST['product_id'] ?? 0);
            $new_stock = trim($_POST['new_stock'] ?? '');
            if ($product_id <= 0) { throw new Exception('Invalid product selection.'); }
            if (!is_numeric($new_stock) || (int)$new_stock < 0) { throw new Exception('Stock must be 0 or greater.'); }

            $stmt = $mysqli->prepare('UPDATE products SET stock = ? WHERE id = ?');
            $iv = (int)$new_stock;
            $stmt->bind_param('ii', $iv, $product_id);
            $stmt->execute();
            $stmt->close();

            $successes[] = 'Stock updated successfully.';
        }

        if ($action === 'delete_product') {
            $product_id = (int)($_POST['product_id'] ?? 0);
            if ($product_id <= 0) { throw new Exception('Invalid product selection.'); }
            // Soft delete: mark inactive to avoid FK issues with order_items
            $stmt = $mysqli->prepare('UPDATE products SET is_active = 0 WHERE id = ?');
            $stmt->bind_param('i', $product_id);
            $stmt->execute();
            $stmt->close();
            $successes[] = 'Product archived (soft deleted).';
        }

        if ($action === 'restore_product') {
            $product_id = (int)($_POST['product_id'] ?? 0);
            if ($product_id <= 0) { throw new Exception('Invalid product selection.'); }
            $stmt = $mysqli->prepare('UPDATE products SET is_active = 1 WHERE id = ?');
            $stmt->bind_param('i', $product_id);
            $stmt->execute();
            $stmt->close();
            $successes[] = 'Product restored.';
        }

        if ($action === 'update_order_status') {
            $order_id = (int)($_POST['order_id'] ?? 0);
            $new_status = trim($_POST['new_status'] ?? '');
            $note = trim($_POST['note'] ?? '');
            $allowed = ['pending','paid','shipped','delivered','cancelled','refunded'];
            if ($order_id <= 0) { throw new Exception('Invalid order selection.'); }
            if (!in_array($new_status, $allowed, true)) { throw new Exception('Invalid status.'); }

            // Update orders table
            $stmt = $mysqli->prepare('UPDATE orders SET status = ? WHERE id = ?');
            $stmt->bind_param('si', $new_status, $order_id);
            $stmt->execute();
            $stmt->close();

            // Insert into order_status_history
            $stmt = $mysqli->prepare('INSERT INTO order_status_history (order_id, status, note, changed_at) VALUES (?, ?, ?, NOW())');
            $stmt->bind_param('iss', $order_id, $new_status, $note);
            $stmt->execute();
            $stmt->close();

            $successes[] = 'Order status updated.';
        }
    } catch (Throwable $e) {
        $errors[] = $e->getMessage();
    }
}

// Data for UI
// Products list
$products = [];
if ($res = $mysqli->query("SELECT id, sku, name, price, stock, is_active FROM products ORDER BY created_at DESC LIMIT 100")) {
    while ($row = $res->fetch_assoc()) { $products[] = $row; }
    $res->close();
}

// Orders list (latest 50)
$orders = [];
$sql = "SELECT o.id, o.status, o.total, c.first_name, c.last_name,
        (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS items_count
        FROM orders o
        LEFT JOIN customers c ON c.id = o.customer_id
        ORDER BY o.created_at DESC
        LIMIT 50";
if ($res = $mysqli->query($sql)) {
    while ($row = $res->fetch_assoc()) { $orders[] = $row; }
    $res->close();
}

// Static dashboard sample data (visuals only)
$stats = [
    [ 'title' => 'Total Sales', 'value' => '₱124,500', 'change' => '+12.5%', 'icon' => 'dollar-sign', 'bgColor' => 'bg-green', 'iconColor' => 'text-green', 'borderColor' => 'border-green' ],
    [ 'title' => 'Active Orders', 'value' => '34', 'change' => '+8.2%', 'icon' => 'package', 'bgColor' => 'bg-blue', 'iconColor' => 'text-blue', 'borderColor' => 'border-blue' ],
    [ 'title' => 'Total Customers', 'value' => '1,284', 'change' => '+23.1%', 'icon' => 'users', 'bgColor' => 'bg-purple', 'iconColor' => 'text-purple', 'borderColor' => 'border-purple' ],
    [ 'title' => 'Products Listed', 'value' => (string)count($products), 'change' => '+5.4%', 'icon' => 'trending-up', 'bgColor' => 'bg-orange', 'iconColor' => 'text-orange', 'borderColor' => 'border-orange' ],
];

$recentOrders = [];
$topProducts = [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RK Trading - Admin Dashboard</title>
    <link rel="stylesheet" href="admin-style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <div class="header-content">
                <div class="logo-section">
                    <div class="logo-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="5"></circle>
                            <line x1="12" y1="1" x2="12" y2="3"></line>
                            <line x1="12" y1="21" x2="12" y2="23"></line>
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                            <line x1="1" y1="12" x2="3" y2="12"></line>
                            <line x1="21" y1="12" x2="23" y2="12"></line>
                            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                        </svg>
                    </div>
                    <div>
                        <h1>RK Trading</h1>
                        <p>Admin Portal</p>
                    </div>
                </div>
                <a class="logout-btn" href="logout.php">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16,17 21,12 16,7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    Logout
                </a>
            </div>
        </div>
    </header>

    <div class="dashboard-container">
        <div class="tabs">
            <button class="tab-btn active" onclick="showTab('dashboard')">Dashboard</button>
            <button class="tab-btn" onclick="showTab('addProduct')">Add Product</button>
            <button class="tab-btn" onclick="showTab('manageProducts')">Manage Products</button>
            <button class="tab-btn" onclick="showTab('orders')">Manage Orders</button>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="error-alert">
                <?php foreach ($errors as $e): ?>
                    <div><?php echo h($e); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($successes)): ?>
            <div class="success-alert">
                <?php foreach ($successes as $s): ?>
                    <div><?php echo h($s); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Dashboard Tab -->
        <div id="dashboard" class="tab-content active">
            <div class="section-header">
                <h2>Analytics Overview</h2>
            </div>

            <div class="stats-grid">
                <?php foreach ($stats as $stat): ?>
                <div class="stat-card <?php echo h($stat['borderColor']); ?>">
                    <div class="stat-content">
                        <div class="stat-icon <?php echo h($stat['bgColor']); ?>">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <?php if ($stat['icon'] === 'dollar-sign'): ?>
                                    <line x1="12" y1="1" x2="12" y2="23"></line>
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                <?php elseif ($stat['icon'] === 'package'): ?>
                                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.83z"></path>
                                    <line x1="7" y1="7" x2="7" y2="7"></line>
                                <?php elseif ($stat['icon'] === 'users'): ?>
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                <?php elseif ($stat['icon'] === 'trending-up'): ?>
                                    <polyline points="23 6 13.5 15.5 9.5 11.5 1 20"></polyline>
                                    <polyline points="17 6 23 6 23 12"></polyline>
                                <?php endif; ?>
                            </svg>
                        </div>
                        <div class="stat-info">
                            <p class="stat-title"><?php echo h($stat['title']); ?></p>
                            <p class="stat-value"><?php echo h($stat['value']); ?></p>
                        </div>
                        <div class="stat-badge <?php echo h($stat['bgColor']); ?>">
                            <?php echo h($stat['change']); ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="dashboard-sections">
                <div class="section-card">
                    <div class="section-header">
                        <h3>Recent Orders</h3>
                        <p>Latest customer orders</p>
                    </div>
                    <div class="orders-list">
                        <?php foreach (array_slice($orders, 0, 3) as $order): ?>
                        <div class="order-item">
                            <div class="order-info">
                                <p class="order-id">#<?php echo h($order['id']); ?></p>
                                <p class="order-customer"><?php echo h(trim(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? '')) ?: 'Customer'); ?></p>
                            </div>
                            <div class="order-details">
                                <p class="order-amount">₱<?php echo number_format((float)($order['total'] ?? 0), 2); ?></p>
                                <span class="status-badge status-<?php echo strtolower(h($order['status'] ?? 'pending')); ?>">
                                    <?php echo h($order['status'] ?? 'pending'); ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="section-card">
                    <div class="section-header">
                        <h3>Top Products</h3>
                        <p>Best selling items this month</p>
                    </div>
                    <div class="products-list">
                        <?php foreach (array_slice($products, 0, 3) as $p): ?>
                        <div class="product-item">
                            <div class="product-info">
                                <p class="product-name"><?php echo h($p['name']); ?></p>
                                <span class="sales-badge"><?php echo h($p['sku']); ?></span>
                            </div>
                            <p class="product-revenue">Stock: <?php echo (int)$p['stock']; ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Product Tab -->
        <div id="addProduct" class="tab-content">
            <div class="form-container">
                <div class="form-header">
                    <h2>Add New Product</h2>
                    <p>Fill in the details to add a new product to your inventory</p>
                </div>

                <div class="info-alert">
                    💡 All fields marked with * are required
                </div>

                <form method="POST" action="" class="product-form">
                    <input type="hidden" name="action" value="add_product">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Product Name *</label>
                            <input type="text" id="name" name="name" placeholder="e.g., Solar LED Bulb 12W" required>
                        </div>
                        <div class="form-group">
                            <label for="price">Price (₱) *</label>
                            <input type="number" id="price" name="price" step="0.01" placeholder="0.00" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="category">Category *</label>
                            <select id="category" name="category" required>
                                <option value="">Select category</option>
                                <option value="light">Solar Lights</option>
                                <option value="fan">Fans</option>
                                <option value="accessory">Accessories</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="stock">Stock Quantity *</label>
                            <input type="number" id="stock" name="stock" placeholder="0" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Product Description *</label>
                        <textarea id="description" name="description" placeholder="Describe the product features and benefits..." rows="4" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="image">Image URL (Optional)</label>
                        <input type="url" id="image" name="image" placeholder="https://example.com/image.jpg">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            Add Product
                        </button>
                        <button type="reset" class="btn btn-secondary">Clear Form</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Manage Products Tab -->
        <div id="manageProducts" class="tab-content">
            <div class="section-header">
                <h2>Manage Products</h2>
                <p>Update stock or archive products</p>
            </div>

            <div class="orders-container">
                <?php if (empty($products)): ?>
                    <p>No products yet.</p>
                <?php endif; ?>

                <?php foreach ($products as $p): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-meta">
                            <h3><?php echo h($p['name']); ?> <small>(<?php echo h($p['sku']); ?>)</small></h3>
                            <p>Price: ₱<?php echo number_format((float)$p['price'], 2); ?> • Stock: <?php echo (int)$p['stock']; ?> • Status: <?php echo ((int)$p['is_active'] ? 'Active' : 'Archived'); ?></p>
                        </div>
                        <div class="order-actions">
                            <form method="POST" action="" style="display:inline-flex; gap:8px; align-items:center;">
                                <input type="hidden" name="action" value="update_stock">
                                <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                                <input type="number" name="new_stock" min="0" value="<?php echo (int)$p['stock']; ?>" style="width:100px;">
                                <button class="btn btn-outline btn-sm" type="submit">Update Stock</button>
                            </form>

                            <?php if ((int)$p['is_active']): ?>
                            <form method="POST" action="" style="display:inline; margin-left:10px;">
                                <input type="hidden" name="action" value="delete_product">
                                <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                                <button class="btn btn-outline btn-sm" type="submit" onclick="return confirm('Archive this product?')">Archive</button>
                            </form>
                            <?php else: ?>
                            <form method="POST" action="" style="display:inline; margin-left:10px;">
                                <input type="hidden" name="action" value="restore_product">
                                <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                                <button class="btn btn-outline btn-sm" type="submit">Restore</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Orders Tab -->
        <div id="orders" class="tab-content">
            <div class="section-header">
                <h2>Order Management</h2>
                <p>View and update order statuses</p>
            </div>

            <div class="orders-container">
                <?php if (empty($orders)): ?>
                    <p>No orders found.</p>
                <?php endif; ?>

                <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-meta">
                            <h3>#<?php echo (int)$order['id']; ?></h3>
                            <p><?php echo h(trim(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? '')) ?: 'Customer'); ?> • <?php echo (int)($order['items_count'] ?? 0); ?> item(s)</p>
                            <p class="order-amount">₱<?php echo number_format((float)($order['total'] ?? 0), 2); ?></p>
                        </div>
                        <div class="order-actions">
                            <form method="POST" action="" style="display:flex; gap:8px; align-items:center;">
                                <input type="hidden" name="action" value="update_order_status">
                                <input type="hidden" name="order_id" value="<?php echo (int)$order['id']; ?>">
                                <select name="new_status">
                                    <?php
                                        $statuses = ['pending','paid','shipped','delivered','cancelled','refunded'];
                                        foreach ($statuses as $st):
                                            $sel = (isset($order['status']) && $order['status'] === $st) ? 'selected' : '';
                                            echo '<option value="' . h($st) . '" ' . $sel . '>' . ucfirst($st) . '</option>';
                                        endforeach;
                                    ?>
                                </select>
                                <input type="text" name="note" placeholder="Optional note" style="width:200px;" />
                                <button class="btn btn-outline btn-sm" type="submit">Update Status</button>
                            </form>
                            <span class="status-badge status-<?php echo strtolower(h($order['status'] ?? 'pending')); ?>">
                                <?php echo h($order['status'] ?? 'pending'); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="admin-script.js"></script>
    <script src="modal-script.js"></script>
</body>
</html>
