<?php
session_start();
require_once 'db_connect.php'; // Use the correct db_connect

// Initialize variables
$email = '';
$password = '';
$errors = [];
$showPassword = false;
$success_message = '';
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // --- Temporary Hardcoded Login Fix ---
    // This allows you to log in without a database user.
    // Admin: admin@example.com / password123
    // Customer: customer@example.com / password123
    if ($email === 'admin@example.com' && $password === 'password123') {
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = 999; // Dummy admin ID
        $_SESSION['user_type'] = 'admin';
        $_SESSION['email'] = $email;
        header('Location: admin.php');
        exit;
    }
    if ($email === 'customer@example.com' && $password === 'password123') {
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = 1000; // Dummy customer ID
        $_SESSION['user_type'] = 'customer';
        $_SESSION['email'] = $email;
        header('Location: customer.php');
        exit;
    }
    // --- End of Temporary Fix ---

    // Validation
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters';
    }

    // If no errors, process login
    if (empty($errors)) {
        // Secure Login Logic
        $stmt = $mysqli->prepare("SELECT id, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $hashed_password);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                // Login success
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $id;
                $_SESSION['user_type'] = 'customer'; // Default role in absence of user_type column
                $_SESSION['email'] = $email;

                header('Location: customer.php');
                exit;
            } else {
                $errors['password'] = 'Invalid email or password.';
            }
        } else {
            $errors['password'] = 'Invalid email or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RK Trading - Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <!-- Animated Gradient Background -->
        <div class="animated-background"></div>

        <!-- Floating Shapes -->
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>

        <!-- Decorative Icons -->
        <div class="decorative-icons">
            <div class="icon icon-sun">
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
            <div class="icon icon-leaf">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.24 12.24a6 6 0 0 0-8.49-8.49L5 10.5V19h8.5z"></path>
                    <line x1="16" y1="8" x2="2" y2="22"></line>
                    <line x1="17.5" y1="15" x2="9" y2="15"></line>
                </svg>
            </div>
        </div>

        <!-- Login Card -->
        <div class="login-card">
            <div class="card-header">
                <div class="logo">
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
                </div>
                <div class="title">
                    <h1>RK Trading</h1>
                    <p>Affordable Solar Lights & Fans for Every Home</p>
                </div>
            </div>

            <div class="card-content">
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success" style="color: #155724; background-color: #d4edda; border-color: #c3e6cb; padding: 15px; margin-bottom: 20px; border-radius: 4px; text-align: center;">
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="login-form">
                    <!-- Email Field -->
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-group">
                            <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="Enter your email"
                                value="<?php echo htmlspecialchars($email); ?>"
                                required
                            >
                        </div>
                        <?php if (isset($errors['email'])): ?>
                            <p class="error"><?php echo $errors['email']; ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Password Field -->
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <circle cx="12" cy="16" r="1"></circle>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Enter your password"
                                value="<?php echo htmlspecialchars($password); ?>"
                                required
                            >
                            <button type="button" class="password-toggle" id="password-toggle">
                                <svg class="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                        <?php if (isset($errors['password'])): ?>
                            <p class="error"><?php echo $errors['password']; ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember">
                            <span>Remember me</span>
                        </label>
                        <a href="#" class="forgot-password">Forgot password?</a>
                    </div>

                    <!-- Sign In Button -->
                    <button type="submit" class="signin-btn">
                        <span>Sign In</span>
                    </button>

                    <!-- Divider -->
                    <div class="divider">
                        <span>or continue with</span>
                    </div>

                    <!-- Social Login Buttons -->
                    <div class="social-buttons">
                        <button type="button" class="social-btn google-btn" onclick="signInWithGoogle()">
                            <svg width="20" height="20" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                            <span>Google</span>
                        </button>
                        <button type="button" class="social-btn facebook-btn" onclick="signInWithFacebook()">
                            <svg width="20" height="20" fill="#1877F2" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                            <span>Facebook</span>
                        </button>
                    </div>

                    <!-- Sign Up Link -->
                    <div class="signup-link">
                        Don't have an account? <a href="signup.php">Sign up</a>
                    </div>
                </form>

                <!-- Powered by Renewable Energy -->
                <div class="powered-by">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.24 12.24a6 6 0 0 0-8.49-8.49L5 10.5V19h8.5z"></path>
                        <line x1="16" y1="8" x2="2" y2="22"></line>
                        <line x1="17.5" y1="15" x2="9" y2="15"></line>
                    </svg>
                    <span>Powered by Renewable Energy</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Firebase JS SDK -->
    <script type="module">
      // Import the functions you need from the SDKs you need
      import { initializeApp } from "https://www.gstatic.com/firebasejs/9.22.0/firebase-app.js";
      import { getAuth, FacebookAuthProvider, GoogleAuthProvider, signInWithPopup, linkWithCredential, fetchSignInMethodsForEmail } from "https://www.gstatic.com/firebasejs/9.22.0/firebase-auth.js";

      // Firebase configuration
      const firebaseConfig = {
        apiKey: "AIzaSyBo1bspb_x2Z4tQSCp8JfpDuRu1LVoerVw",
        authDomain: "rk-trading-cfd84.firebaseapp.com",
        projectId: "rk-trading-cfd84",
        storageBucket: "rk-trading-cfd84.firebasestorage.app",
        messagingSenderId: "569098274198",
        appId: "1:569098274198:web:5e154381975487a58049b7",
        measurementId: "G-10VCC6LJQP"
      };

      // Initialize Firebase
      const app = initializeApp(firebaseConfig);
      const auth = getAuth(app);

      // Facebook provider
      const facebookProvider = new FacebookAuthProvider();
      facebookProvider.addScope('email');
      facebookProvider.addScope('public_profile');

      // Google provider
      const googleProvider = new GoogleAuthProvider();

      // Facebook login function
      window.signInWithFacebook = function() {
        signInWithPopup(auth, facebookProvider)
          .then((result) => {
            // The signed-in user info
            const user = result.user;

            // Facebook Access Token (may not always be available)
            let accessToken = null;
            if (result.credential && result.credential.accessToken) {
              accessToken = result.credential.accessToken;
            }

            // Send user data to PHP backend
            fetch('fb_callback.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
              },
              body: JSON.stringify({
                uid: user.uid,
                name: user.displayName,
                email: user.email,
                photoURL: user.photoURL,
                accessToken: accessToken
              })
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                // Redirect based on user type or to home
                window.location.href = data.redirect || 'home.php';
              } else {
                alert('Login failed: ' + data.message);
              }
            })
            .catch(error => {
              console.error('Error:', error);
              alert('An error occurred during login');
            });
          })
          .catch((error) => {
            // Handle Errors here.
            const errorCode = error.code;
            const errorMessage = error.message;
            console.error('Facebook login error:', errorCode, errorMessage);

            if (errorCode === 'auth/account-exists-with-different-credential') {
              alert('This email is already associated with another sign-in method. Please use your Facebook account to sign in.');
            } else {
              alert('Facebook login failed: ' + errorMessage);
            }
          });
      };

      // Google login function
      window.signInWithGoogle = function() {
        signInWithPopup(auth, googleProvider)
          .then((result) => {
            // The signed-in user info
            const user = result.user;

            // Send user data to PHP backend
            fetch('google_callback.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
              },
              body: JSON.stringify({
                uid: user.uid,
                name: user.displayName,
                email: user.email,
                photoURL: user.photoURL,
                idToken: result.credential.idToken // For Google, use idToken
              })
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                // Redirect based on user type or to home
                window.location.href = data.redirect || 'home.php';
              } else {
                alert('Login failed: ' + data.message);
              }
            })
            .catch(error => {
              console.error('Error:', error);
              alert('An error occurred during login');
            });
          })
          .catch((error) => {
            console.error('Google login error:', error.code, error.message);
            alert('Google login failed: ' + error.message);
          });
      };
    </script>

    <script src="script.js"></script>
</body>

</html>
