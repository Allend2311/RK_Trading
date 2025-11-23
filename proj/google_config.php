<?php
// Google OAuth configuration using google/apiclient
// Install via Composer in your project root: composer require google/apiclient:^2.16

// Try to load Composer autoload. If the Google API client is not installed
// we avoid fatal/type errors and provide helpful fallbacks.
$vendorAutoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
}

$googleClient = null;
// Disable Google OAuth as no credentials provided
//$google_callback = 'https://rk-trade2025.infinityfreeapp.com/proj/google_callback.php';

/*
if (class_exists('Google_Client')) {
    $googleClass = 'Google_Client';
    $googleClient = new $googleClass();
    $googleClient->setClientId('GOOGLE_CLIENT_ID');
    $googleClient->setClientSecret('GOOGLE_CLIENT_SECRET');
    $googleClient->setRedirectUri($google_callback);
    $googleClient->addScope(['email', 'profile']);
    $googleClient->setAccessType('offline');
    $googleClient->setPrompt('select_account consent');
}
*/

// Expose a helper to build login URL. Returns '#' if the client isn't available.
function getGoogleLoginUrl($client): string {
    if ($client && is_object($client) && method_exists($client, 'createAuthUrl')) {
        return $client->createAuthUrl();
    }
    return '#';
}
