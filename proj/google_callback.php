<?php
session_start();
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/google_config.php';

// Guard: ensure Google Client is available/initialized
if (!$googleClient || !class_exists('Google_Client')) {
    http_response_code(500);
    echo 'Google SDK not initialized. Install google/apiclient via Composer and configure GOOGLE_CLIENT_ID/SECRET.';
    exit;
}

try {
    // If no OAuth code is present, start the auth flow safely
    if (!isset($_GET['code'])) {
        if (method_exists($googleClient, 'createAuthUrl')) {
            header('Location: ' . $googleClient->createAuthUrl());
            exit;
        }
        throw new Exception('Google client not ready to create auth URL.');
    }

    // Exchange code for token
    if (!method_exists($googleClient, 'fetchAccessTokenWithAuthCode')) {
        throw new Exception('Google client is missing required method fetchAccessTokenWithAuthCode.');
    }

    $token = $googleClient->fetchAccessTokenWithAuthCode($_GET['code']);
    if (isset($token['error'])) {
        $desc = isset($token['error_description']) ? $token['error_description'] : $token['error'];
        throw new Exception('Google OAuth error: ' . $desc);
    }

    $googleClient->setAccessToken($token);

    if (!class_exists('Google_Service_Oauth2')) {
        throw new Exception('Google_Service_Oauth2 class not found. Ensure google/apiclient is installed.');
    }

    $oauth2 = new Google_Service_Oauth2($googleClient);
    $googleUser = $oauth2->userinfo->get();

    $email = $googleUser->email ?? '';
    $name = $googleUser->name ?? '';
    $picture = $googleUser->picture ?? '';
    $provider = 'google';
    $provider_id = $googleUser->id ?? '';

    if (!$email) {
        throw new Exception('Email not provided by Google.');
    }

    // Upsert user using prepared statements
    $userId = null;

    // Find existing by email
    $stmt = $mysqli->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->bind_result($foundId);
    if ($stmt->fetch()) {
        $userId = (int)$foundId;
    }
    $stmt->close();

    if ($userId) {
        $stmt = $mysqli->prepare('UPDATE users SET name = ?, picture = ?, provider = ?, provider_id = ? WHERE id = ?');
        $stmt->bind_param('ssssi', $name, $picture, $provider, $provider_id, $userId);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $mysqli->prepare('INSERT INTO users (name, email, picture, provider, provider_id) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sssss', $name, $email, $picture, $provider, $provider_id);
        $stmt->execute();
        $userId = $stmt->insert_id;
        $stmt->close();
    }

    // Set session and redirect
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_picture'] = $picture;

    header('Location: /proj/proj/home.php');
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Authentication failed: ' . htmlspecialchars($e->getMessage());
}
