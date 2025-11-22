<?php
// Simple example login buttons snippet
// Ensure composer dependencies are installed and IDs are configured in google_config.php and fb_config.php
require_once __DIR__ . '/google_config.php';
require_once __DIR__ . '/fb_config.php';

$googleLoginUrl = getGoogleLoginUrl($googleClient);
$facebookLoginUrl = getFacebookLoginUrl($fb, $fb_helper, $fb_permissions, $fb_callback_url);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login</title>
  <style>
    .oauth-btn { display:inline-block; padding:10px 16px; margin:6px; border-radius:4px; color:#fff; text-decoration:none; font-weight:600; }
    .google { background:#db4437; }
    .facebook { background:#1877f2; }
  </style>
</head>
<body>
  <h1>Login</h1>
  <a class="oauth-btn google" href="<?= htmlspecialchars($googleLoginUrl) ?>">Login with Google</a>
  <a class="oauth-btn facebook" href="<?= htmlspecialchars($facebookLoginUrl) ?>">Login with Facebook</a>
</body>
</html>
