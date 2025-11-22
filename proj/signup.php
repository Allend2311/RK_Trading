<?php
session_start();
require_once 'db_connect.php';

$errors = [];
$name = ''; // To repopulate the form on error
$email = ''; // To repopulate the form on error

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password']; // No trim on passwords
    $confirm_password = $_POST['confirm_password']; // No trim on passwords

    // 1. Validate for empty fields
    if (empty($name)) {
        $errors[] = "Full Name is required.";
    }
    if (empty($email)) {
        $errors[] = "Email is required.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    // 3. Validate email format and existence
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } else {
        // Check if email already exists
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?"); // Use $mysqli
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result(); // Needed to check num_rows
        if ($stmt->num_rows > 0) {
            $errors[] = "An account with this email already exists.";
        }
        $stmt->close();
    }

    // 2. Validate password match
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // If there are no errors, proceed with registration
    if (empty($errors)) {
        // 4. Hash the password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // 5. Insert data into the database (users table has no user_type column)
        $stmt = $mysqli->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)"); // Use $mysqli
        $stmt->bind_param("sss", $name, $email, $hashed_password);

        if ($stmt->execute()) {
            // 6. Redirect to login page with a success message
            $_SESSION['success_message'] = "Signup successful! You can now log in.";
            header("Location: login.php");
            exit();
        } else {
            $errors[] = "Error during registration. Please try again later.";
        }
        $stmt->close();
    }
    $mysqli->close(); // Close connection
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RK Trading - Sign Up</title>
    <link rel="stylesheet" href="style.css">
    <!-- Additional styles for signup page -->
    <link rel="stylesheet" href="signup-style.css">
</head>
<body>
    <div class="login-container">
        <!-- Animated Background and Shapes from login style -->
        <div class="animated-background"></div>
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>

        <div class="login-card">
            <div class="card-header">
                <div class="logo">
                    <div class="logo-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="8.5" cy="7" r="4"></circle>
                            <polyline points="17 11 19 13 23 9"></polyline>
                        </svg>
                    </div>
                </div>
                <div class="title">
                    <h1>Create Account</h1>
                    <p>Join RK Trading for a sustainable future</p>
                </div>
            </div>

            <div class="card-content">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="signup.php" method="post" class="login-form">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <div class="input-group">
                             <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <input type="text" id="name" name="name" placeholder="Enter your full name" value="<?php echo htmlspecialchars($name); ?>" required autocomplete="name">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-group">
                            <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($email); ?>" required autocomplete="email">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            <input type="password" id="password" name="password" placeholder="Create a password" required autocomplete="new-password">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <div class="input-group">
                            <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required autocomplete="new-password">
                        </div>
                    </div>
                    <button type="submit" class="signin-btn">Sign Up</button>
                </form>

                <div class="divider">
                    <span></span>
                </div>

                <div class="signup-link">
                    Already have an account? <a href="login.php">Log In</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>