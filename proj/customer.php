<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: login.php');
    exit;
}

// Check if user is customer (not admin)
if ($_SESSION['user_type'] !== 'customer') {
    header('Location: admin.php');
    exit;
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'add_to_cart' && isset($_POST['product_id'])) {
            $productId = (int)$_POST['product_id'];
            $product = getProductById($productId);

            if ($product) {
                $existingItem = findCartItem($productId);
                if ($existingItem !== false) {
                    $_SESSION['cart'][$existingItem]['quantity'] += 1;
                    $message = "Added another {$product['name']} to cart";
                } else {
                    $_SESSION['cart'][] = [
                        'id' => $product['id'],
                        'name' => $product['name'],
                        'price' => $product['price'],
                        'description' => $product['description'],
                        'category' => $product['category'],
                        'image' => $product['image'],
                        'rating' => $product['rating'],
                        'quantity' => 1
                    ];
                    $message = "{$product['name']} added to cart";
                }
            }
        } elseif ($action === 'remove_from_cart' && isset($_POST['product_id'])) {
            $productId = (int)$_POST['product_id'];
            $existingItem = findCartItem($productId);
            if ($existingItem !== false) {
                $removedItem = $_SESSION['cart'][$existingItem];
                unset($_SESSION['cart'][$existingItem]);
                $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex array
                $message = "{$removedItem['name']} removed from cart";
            }
        } elseif ($action === 'update_quantity' && isset($_POST['product_id']) && isset($_POST['delta'])) {
            $productId = (int)$_POST['product_id'];
            $delta = (int)$_POST['delta'];
            $existingItem = findCartItem($productId);
            if ($existingItem !== false) {
                $_SESSION['cart'][$existingItem]['quantity'] = max(1, $_SESSION['cart'][$existingItem]['quantity'] + $delta);
            }
        } elseif ($action === 'checkout') {
            if (empty($_SESSION['cart'])) {
                $error = "Your cart is empty";
            } else {
                $message = "Order placed successfully!";
                $_SESSION['cart'] = [];
            }
        }
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Mock product data
$products = [
    [
        'id' => 1,
        'name' => 'Solar LED Bulb 12W',
        'price' => 499,
        'description' => 'Energy-efficient LED bulb with built-in solar charging',
        'category' => 'light',
        'stock' => 50,
        'image' => 'https://images.unsplash.com/photo-1703956807427-035f294e1467?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxzb2xhciUyMGxpZ2h0JTIwYnVsYnxlbnwxfHx8fDE3NjIxMjgyNTd8MA&ixlib=rb-4.1.0&q=80&w=1080',
        'rating' => 4.5,
    ],
    [
        'id' => 2,
        'name' => 'Outdoor Solar Garden Light',
        'price' => 699,
        'description' => 'Waterproof solar garden stake lights, set of 4',
        'category' => 'light',
        'stock' => 30,
        'image' => 'https://images.unsplash.com/photo-1629794773534-48e0d6061c2e?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxzb2xhciUyMG91dGRvb3IlMjBsaWdodHN8ZW58MXx8fHwxNzYyMTI4MjU3fDA&ixlib=rb-4.1.0&q=80&w=1080',
        'rating' => 4.8,
    ],
    [
        'id' => 3,
        'name' => 'Solar Rechargeable Fan 10"',
        'price' => 1299,
        'description' => 'Portable fan with solar panel and USB charging',
        'category' => 'fan',
        'stock' => 25,
        'image' => 'https://images.unsplash.com/photo-1523437345381-db5ee4df9c04?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxwb3J0YWJsZSUyMGZhbnxlbnwxfHx8fDE3NjIxMjgyNTd8MA&ixlib=rb-4.1.0&q=80&w=1080',
        'rating' => 4.3,
    ],
    [
        'id' => 4,
        'name' => 'Solar Panel Emergency Light',
        'price' => 899,
        'description' => 'Multi-mode emergency light with 6-hour backup',
        'category' => 'light',
        'stock' => 40,
        'image' => 'https://images.unsplash.com/flagged/photo-1566838616631-f2618f74a6a2?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxzb2xhciUyMHBhbmVsJTIwaG9tZXxlbnwxfHx8fDE3NjIwMDU2MzZ8MA&ixlib=rb-4.1.0&q=80&w=1080',
        'rating' => 4.6,
    ],
    [
        'id' => 5,
        'name' => 'Solar Ceiling Fan with Remote',
        'price' => 2499,
        'description' => '16" ceiling fan with solar panel kit and remote control',
        'category' => 'fan',
        'stock' => 15,
        'image' => 'https://images.unsplash.com/photo-1523437345381-db5ee4df9c04?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxwb3J0YWJsZSUyMGZhbnxlbnwxfHx8fDE3NjIxMjgyNTd8MA&ixlib=rb-4.1.0&q=80&w=1080',
        'rating' => 4.7,
    ],
    [
        'id' => 6,
        'name' => 'Solar LED Strip Lights 5m',
        'price' => 799,
        'description' => 'Flexible waterproof LED strips with solar controller',
        'category' => 'light',
        'stock' => 35,
        'image' => 'https://images.unsplash.com/photo-1629794773534-48e0d6061c2e?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxzb2xhciUyMG91dGRvb3IlMjBsaWdodHN8ZW58MXx8fHwxNzYyMTI4MjU3fDA&ixlib=rb-4.1.0&q=80&w=1080',
        'rating' => 4.4,
    ],
];

function getProductById($id) {
    global $products;
    foreach ($products as $product) {
        if ($product['id'] == $id) {
            return $product;
        }
    }
    return null;
}

function findCartItem($productId) {
    if (!isset($_SESSION['cart'])) return false;
    foreach ($_SESSION['cart'] as $index => $item) {
        if ($item['id'] == $productId) {
            return $index;
        }
    }
    return false;
}

// Calculate cart totals
$totalAmount = 0;
$totalItems = 0;
foreach ($_SESSION['cart'] as $item) {
    $totalAmount += $item['price'] * $item['quantity'];
    $totalItems += $item['quantity'];
}

// Handle search and filter
$searchQuery = $_GET['search'] ?? '';
$categoryFilter = $_GET['category'] ?? 'all';

// Filter products
$filteredProducts = array_filter($products, function($product) use ($searchQuery, $categoryFilter) {
    $matchesSearch = empty($searchQuery) || stripos($product['name'], $searchQuery) !== false;
    $matchesCategory = $categoryFilter === 'all' || $product['category'] === $categoryFilter;
    return $matchesSearch && $matchesCategory;
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RK Trading - Customer Dashboard</title>
    <link rel="stylesheet" href="customer-style.css">
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
                        <p>Customer Portal</p>
                    </div>
                </div>
                <div class="header-actions">
                    <a href="order-tracking.php" class="btn btn-outline">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.83z"></path>
                            <line x1="7" y1="7" x2="7" y2="7"></line>
                        </svg>
                        Track Order
                    </a>
                    <button class="btn btn-outline btn-red logout-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16,17 21,12 16,7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </header>

    <div class="dashboard-container">
        <!-- Messages -->
        <?php if (isset($message)): ?>
        <div class="message success-message">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22,4 12,14.01 9,11.01"></polyline>
            </svg>
            <span><?php echo $message; ?></span>
        </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
        <div class="message error-message">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
            <span><?php echo $error; ?></span>
        </div>
        <?php endif; ?>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab-btn active" onclick="showTab('products')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.24 12.24a6 6 0 0 0-8.49-8.49L5 10.5V19h8.5z"></path>
                    <line x1="16" y1="8" x2="2" y2="22"></line>
                    <line x1="17.5" y1="15" x2="9" y2="15"></line>
                </svg>
                Products
            </button>
            <button class="tab-btn" onclick="showTab('cart')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="8" cy="21" r="1"></circle>
                    <circle cx="19" cy="21" r="1"></circle>
                    <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
                </svg>
                Cart <?php if ($totalItems > 0): ?>(<?php echo $totalItems; ?>)<?php endif; ?>
            </button>
        </div>

        <!-- Products Tab -->
        <div id="products" class="tab-content active">
            <!-- Search and Filter -->
            <div class="search-filter-card">
                <form method="GET" action="" class="search-form">
                    <div class="search-input-group">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="M21 21l-4.35-4.35"></path>
                        </svg>
                        <input
                            type="text"
                            name="search"
                            placeholder="Search products..."
                            value="<?php echo htmlspecialchars($searchQuery); ?>"
                        >
                    </div>
                    <div class="filter-buttons">
                        <button type="submit" name="category" value="all" class="filter-btn <?php echo $categoryFilter === 'all' ? 'active' : ''; ?>">
                            All
                        </button>
                        <button type="submit" name="category" value="light" class="filter-btn <?php echo $categoryFilter === 'light' ? 'active' : ''; ?>">
                            Lights
                        </button>
                        <button type="submit" name="category" value="fan" class="filter-btn <?php echo $categoryFilter === 'fan' ? 'active' : ''; ?>">
                            Fans
                        </button>
                    </div>
                </form>
            </div>

            <!-- Products Grid -->
            <div class="products-grid">
                <?php if (empty($filteredProducts)): ?>
                <div class="empty-state">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="M21 21l-4.35-4.35"></path>
                    </svg>
                    <h3>No products found</h3>
                    <p>Try adjusting your search or filter</p>
                </div>
                <?php else: ?>
                <?php foreach ($filteredProducts as $index => $product): ?>
                <div class="product-card" style="animation-delay: <?php echo $index * 0.05; ?>s">
                    <div class="product-image">
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <div class="stock-badge">In Stock: <?php echo $product['stock']; ?></div>
                    </div>
                    <div class="product-content">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p><?php echo htmlspecialchars($product['description']); ?></p>
                        <div class="product-footer">
                            <span class="price">₱<?php echo number_format($product['price']); ?></span>
                            <span class="rating">⭐ <?php echo $product['rating']; ?></span>
                        </div>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="add_to_cart">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" class="btn btn-primary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="8" cy="21" r="1"></circle>
                                    <circle cx="19" cy="21" r="1"></circle>
                                    <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
                                </svg>
                                Add to Cart
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Cart Tab -->
        <div id="cart" class="tab-content">
            <?php if (empty($_SESSION['cart'])): ?>
            <div class="empty-cart">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="8" cy="21" r="1"></circle>
                    <circle cx="19" cy="21" r="1"></circle>
                    <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
                </svg>
                <h3>Your cart is empty</h3>
                <p>Start shopping to add items to your cart</p>
            </div>
            <?php else: ?>
            <div class="cart-layout">
                <div class="cart-items">
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                    <div class="cart-item">
                        <div class="item-image">
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        </div>
                        <div class="item-details">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p><?php echo htmlspecialchars($item['description']); ?></p>
                            <p class="item-price">₱<?php echo number_format($item['price']); ?></p>
                        </div>
                        <div class="item-actions">
                            <form method="POST" action="" class="remove-form">
                                <input type="hidden" name="action" value="remove_from_cart">
                                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="btn btn-outline btn-sm btn-red">Remove</button>
                            </form>
                            <div class="quantity-controls">
                                <form method="POST" action="" class="quantity-form">
                                    <input type="hidden" name="action" value="update_quantity">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="delta" value="-1">
                                    <button type="submit" class="quantity-btn">-</button>
                                </form>
                                <span class="quantity"><?php echo $item['quantity']; ?></span>
                                <form method="POST" action="" class="quantity-form">
                                    <input type="hidden" name="action" value="update_quantity">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="delta" value="1">
                                    <button type="submit" class="quantity-btn">+</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Order Summary -->
                <div class="order-summary">
                    <div class="summary-card">
                        <h3>Order Summary</h3>
                        <div class="summary-details">
                            <div class="summary-row">
                                <span>Subtotal:</span>
                                <span>₱<?php echo number_format($totalAmount); ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Shipping:</span>
                                <span class="free-shipping">FREE</span>
                            </div>
                            <div class="summary-divider"></div>
                            <div class="summary-row total-row">
                                <span>Total:</span>
                                <span>₱<?php echo number_format($totalAmount); ?></span>
                            </div>
                        </div>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="checkout">
                            <button type="submit" class="btn btn-primary btn-full">Checkout</button>
                        </form>
                        <p class="secure-payment">🔒 Secure payment powered by renewable energy</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="customer-script.js"></script>
    <script src="modal-script.js"></script>
</body>
</html>
