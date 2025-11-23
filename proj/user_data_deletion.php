<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: login.php');
    exit;
}

// Get the logged-in user ID
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete user data from the users table
    $stmt = $mysqli->prepare('DELETE FROM users WHERE id = ?');
    $stmt->bind_param('i', $userId);
    if ($stmt->execute()) {
        $stmt->close();

        // Destroy the session after deletion
        session_unset();
        session_destroy();

        // Redirect to signup or home page after deletion
        header('Location: signup.php?deleted=1');
        exit;
    } else {
        $error = "Failed to delete user data. Please try again later.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Delete User Data - RK Trading</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
    <div class="container">
        <h1>Delete Your Account</h1>

        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php else: ?>
            <p>Are you sure you want to permanently delete your account and all associated data?</p>
            <form method="POST" action="">
                <button type="submit" class="btn btn-danger">Delete My Account</button>
                <a href="customer.php" class="btn btn-secondary">Cancel</a>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
