<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: login.php');
    exit;
}

// Initialize variables
$orderNumber = '';
$trackingData = null;
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderNumber = trim($_POST['order_number'] ?? '');

    if (empty($orderNumber)) {
        $error = 'Please enter an order number';
    } else {
        $error = '';

        // Demo tracking data
        if (stripos($orderNumber, 'ord') !== false) {
            $trackingData = [
                [
                    'id' => 1,
                    'title' => 'Order Placed',
                    'description' => 'Your order has been successfully placed',
                    'timestamp' => 'Nov 1, 2025 10:30 AM',
                    'status' => 'completed',
                    'icon' => 'check-circle',
                ],
                [
                    'id' => 2,
                    'title' => 'Processing',
                    'description' => 'Your order is being prepared',
                    'timestamp' => 'Nov 1, 2025 2:15 PM',
                    'status' => 'completed',
                    'icon' => 'package',
                ],
                [
                    'id' => 3,
                    'title' => 'Out for Delivery',
                    'description' => 'Package is on its way to you',
                    'timestamp' => 'Nov 3, 2025 9:00 AM',
                    'status' => 'current',
                    'icon' => 'truck',
                ],
                [
                    'id' => 4,
                    'title' => 'Delivered',
                    'description' => 'Package will be delivered to your address',
                    'timestamp' => 'Estimated: Nov 4, 2025',
                    'status' => 'upcoming',
                    'icon' => 'home',
                ],
            ];
        } else {
            $error = 'Order not found. Try order number: ORD-2025-001';
            $trackingData = null;
        }
    }
}

// Handle back navigation
if (isset($_GET['back'])) {
    if ($_SESSION['user_type'] === 'admin') {
        header('Location: admin.php');
    } else {
        header('Location: customer.php');
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RK Trading - Order Tracking</title>
    <link rel="stylesheet" href="order-tracking-style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <div class="header-content">
                <button onclick="window.location.href='?back=1'" class="btn btn-outline back-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 18l-6-6 6-6"></path>
                    </svg>
                    Back
                </button>
                <div class="logo-section">
                    <div class="logo-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.83z"></path>
                            <line x1="7" y1="7" x2="7" y2="7"></line>
                        </svg>
                    </div>
                    <div>
                        <h1>Track Your Order</h1>
                        <p>Real-time order tracking</p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="main-container">
        <!-- Search Card -->
        <div class="search-card">
            <div class="card-header">
                <h2>Enter Order Number</h2>
                <p>Track your order by entering the order number you received</p>
            </div>
            <div class="card-content">
                <div class="alert">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    <span>Demo - Try order number: ORD-2025-001</span>
                </div>

                <form method="POST" action="" class="tracking-form">
                    <div class="form-group">
                        <label for="order_number">Order Number</label>
                        <div class="input-group">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="M21 21l-4.35-4.35"></path>
                            </svg>
                            <input
                                type="text"
                                id="order_number"
                                name="order_number"
                                placeholder="e.g., ORD-2025-001"
                                value="<?php echo htmlspecialchars($orderNumber); ?>"
                                required
                            >
                        </div>
                        <?php if ($error): ?>
                        <p class="error"><?php echo $error; ?></p>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="M21 21l-4.35-4.35"></path>
                        </svg>
                        Track Order
                    </button>
                </form>
            </div>
        </div>

        <?php if ($trackingData): ?>
        <!-- Order Info Card -->
        <div class="order-info-card">
            <div class="card-header">
                <div class="order-header">
                    <div>
                        <h2>Order #<?php echo htmlspecialchars(strtoupper($orderNumber)); ?></h2>
                        <p>Estimated Delivery: Nov 4, 2025</p>
                    </div>
                    <span class="status-badge">In Transit</span>
                </div>
            </div>
            <div class="card-content">
                <div class="order-details-grid">
                    <div class="detail-card">
                        <div class="detail-header">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <span>Delivery Address</span>
                        </div>
                        <p>123 Solar Street, Green City<br>Manila, Philippines 1000</p>
                    </div>
                    <div class="detail-card">
                        <div class="detail-header">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.83z"></path>
                                <line x1="7" y1="7" x2="7" y2="7"></line>
                            </svg>
                            <span>Items</span>
                        </div>
                        <p>Solar LED Bulb 12W × 2<br>Solar Garden Light × 1</p>
                    </div>
                    <div class="detail-card">
                        <div class="detail-header">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12,6 12,12 16,14"></polyline>
                            </svg>
                            <span>Total Amount</span>
                        </div>
                        <p>₱1,997<br><span class="free-shipping">Free Shipping</span></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline Card -->
        <div class="timeline-card">
            <div class="card-header">
                <h2>Tracking Timeline</h2>
                <p>Follow your order journey</p>
            </div>
            <div class="card-content">
                <div class="timeline">
                    <?php foreach ($trackingData as $index => $step): ?>
                    <div class="timeline-item <?php echo $step['status']; ?>" style="animation-delay: <?php echo $index * 0.1; ?>s">
                        <?php if ($index < count($trackingData) - 1): ?>
                        <div class="timeline-line <?php echo $step['status'] === 'completed' ? 'completed' : ''; ?>"></div>
                        <?php endif; ?>

                        <div class="timeline-icon <?php echo $step['status']; ?>">
                            <?php if ($step['icon'] === 'check-circle'): ?>
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22,4 12,14.01 9,11.01"></polyline>
                            </svg>
                            <?php elseif ($step['icon'] === 'package'): ?>
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.83z"></path>
                                <line x1="7" y1="7" x2="7" y2="7"></line>
                            </svg>
                            <?php elseif ($step['icon'] === 'truck'): ?>
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="1" y="3" width="15" height="13"></rect>
                                <polygon points="16,8 20,8 23,11 23,16 16,16 16,8"></polygon>
                                <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                <circle cx="18.5" cy="18.5" r="2.5"></circle>
                            </svg>
                            <?php elseif ($step['icon'] === 'home'): ?>
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                <polyline points="9,22 9,12 15,12 15,22"></polyline>
                            </svg>
                            <?php endif; ?>
                        </div>

                        <div class="timeline-content <?php echo $step['status'] === 'current' ? 'current' : ''; ?>">
                            <div class="timeline-header">
                                <h3><?php echo htmlspecialchars($step['title']); ?></h3>
                                <?php if ($step['status'] === 'current'): ?>
                                <span class="current-badge">Current</span>
                                <?php endif; ?>
                            </div>
                            <p><?php echo htmlspecialchars($step['description']); ?></p>
                            <div class="timeline-timestamp">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12,6 12,12 16,14"></polyline>
                                </svg>
                                <span><?php echo htmlspecialchars($step['timestamp']); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Contact Support -->
        <div class="support-card">
            <div class="card-content">
                <div class="support-content">
                    <div>
                        <h3>Need Help?</h3>
                        <p>Contact our support team for any questions about your order</p>
                    </div>
                    <div class="support-buttons">
                        <button class="btn btn-outline">Email Support</button>
                        <button class="btn btn-primary">Live Chat</button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!$trackingData && !$error): ?>
        <!-- Empty State -->
        <div class="empty-state">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.83z"></path>
                <line x1="7" y1="7" x2="7" y2="7"></line>
            </svg>
            <h3>Track Your Package</h3>
            <p>Enter your order number above to see tracking details</p>
        </div>
        <?php endif; ?>
    </div>

    <script src="order-tracking-script.js"></script>
</body>
</html>
