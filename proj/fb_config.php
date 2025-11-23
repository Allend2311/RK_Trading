<?php
// Facebook Login configuration using facebook/graph-sdk
// Install via Composer in your project root: composer require facebook/graph-sdk:^5.1

// Try to load Composer autoload. If the Facebook SDK is not installed
// we avoid fatal/type errors and provide helpful fallbacks.
$vendorAutoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
}

$fb = null;
$fb_helper = null;
$fb_permissions = ['email'];
$fb_callback_url = 'https://rk-trade2025.infinityfreeapp.com/proj/fb_callback.php'; // Confirmed for affinityfree hosting

// Only initialize the Facebook SDK if the class exists (i.e. package installed)
if (class_exists('Facebook\\Facebook')) {
    $fbClass = 'Facebook\\Facebook';
    $fb = new $fbClass([
        'app_id' => '820536124065197', // TODO: Replace with your real Facebook App ID
        'app_secret' => '8abe1d6c74ab924f5ca4c88b3b01827e', // TODO: Replace with your real Facebook App Secret
        'default_graph_version' => 'v19.0',
    ]);

    // getRedirectLoginHelper may throw if not available; guard with method_exists
    if (method_exists($fb, 'getRedirectLoginHelper')) {
        $fb_helper = $fb->getRedirectLoginHelper();
    }
} else {
    // Helpful runtime message for developers: install the SDK via Composer
    // composer require facebook/graph-sdk
    // We keep variables defined so other scripts can check and degrade gracefully.
}

// Return the login URL if helper is available; otherwise return a placeholder
function getFacebookLoginUrl($fb, $helper, array $permissions, string $callbackUrl): string {
    if ($helper && is_object($helper) && method_exists($helper, 'getLoginUrl')) {
        return $helper->getLoginUrl($callbackUrl, $permissions);
    }

    // Fallback: return a non-empty string so callers can avoid fatal errors.
    return '#';
}
