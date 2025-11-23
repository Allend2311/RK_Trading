<?php
// Facebook Data Deletion Endpoint for Compliance

// This script handles Facebook's Data Deletion Request callback.
// Facebook sends a POST with a 'signed_request' parameter.
// You must verify the signed_request, parse user ID, and delete user data.

// Load your Facebook app secret from config
$fbAppSecret = 'FACEBOOK_APP_SECRET'; // Replace with your app secret

// Function to parse and verify Facebook signed_request
function parseSignedRequest($signedRequest, $secret) {
    list($encodedSig, $payload) = explode('.', $signedRequest, 2);
    // decode the data
    $sig = base64UrlDecode($encodedSig);
    $data = json_decode(base64UrlDecode($payload), true);

    if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
        error_log('Unknown algorithm. Expected HMAC-SHA256');
        return null;
    }

    // check sig
    $expectedSig = hash_hmac('sha256', $payload, $secret, true);
    if ($sig !== $expectedSig) {
        error_log('Bad Signed JSON signature!');
        return null;
    }

    return $data;
}

function base64UrlDecode($input) {
    return base64_decode(strtr($input, '-_', '+/'));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo 'Method Not Allowed';
    exit;
}

if (empty($_POST['signed_request'])) {
    http_response_code(400); // Bad Request
    echo 'Missing signed_request';
    exit;
}

$signedRequest = $_POST['signed_request'];
$data = parseSignedRequest($signedRequest, $fbAppSecret);

if (!$data || empty($data['user_id'])) {
    http_response_code(400);
    echo 'Invalid signed_request';
    exit;
}

$userId = $data['user_id'];

// Connect to database
require_once 'db_connect.php';

// Delete user data by Facebook user id (assuming provider_id stores fb user id)
$stmt = $mysqli->prepare('DELETE FROM users WHERE provider_id = ?');
$stmt->bind_param('s', $userId);
if (!$stmt->execute()) {
    http_response_code(500);
    echo 'Error deleting user data';
    exit;
}
$stmt->close();

// Respond to Facebook with deletion confirmation URL or empty body for success
// You can provide a URL where user can get info about deletion.
// For now, just respond with HTTP 200 and empty body:
http_response_code(200);
exit;
?>
