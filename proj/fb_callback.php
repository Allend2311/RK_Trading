<?php
session_start();
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/fb_config.php';

// Guard: ensure Facebook SDK is available/initialized
if (!$fb || !$fb_helper || !class_exists('Facebook\\Facebook')) {
    http_response_code(500);
    echo 'Facebook SDK not initialized. Install facebook/graph-sdk via Composer and configure app id/secret.';
    exit;
}

try {
    if (!method_exists($fb_helper, 'getAccessToken')) {
        throw new Exception('Facebook helper missing getAccessToken method.');
    }

    $accessToken = $fb_helper->getAccessToken();
    if (!$accessToken) {
        $loginUrl = getFacebookLoginUrl($fb, $fb_helper, $fb_permissions, $fb_callback_url);
        header('Location: ' . $loginUrl);
        exit;
    }

    if (method_exists($accessToken, 'isLongLived') && !$accessToken->isLongLived()) {
        $oAuth2Client = $fb->getOAuth2Client();
        $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
    }

    $response = $fb->get('/me?fields=id,name,email,picture.type(large)', $accessToken);
    $fbUser = $response->getGraphUser();

    $email = $fbUser->getEmail() ?? '';
    $name = $fbUser->getName() ?? '';
    $picture = '';
    if ($fbUser->getPicture() && method_exists($fbUser->getPicture(), 'getUrl')) {
        $picture = $fbUser->getPicture()->getUrl();
    }
    $provider = 'facebook';
    $provider_id = $fbUser->getId() ?? '';

    if (!$email) {
        throw new Exception('Email permission is required from Facebook.');
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
} catch (\Facebook\Exceptions\FacebookResponseException $e) {
    http_response_code(500);
    echo 'Graph returned an error: ' . htmlspecialchars($e->getMessage());
} catch (\Facebook\Exceptions\FacebookSDKException $e) {
    http_response_code(500);
    echo 'Facebook SDK returned an error: ' . htmlspecialchars($e->getMessage());
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Authentication failed: ' . htmlspecialchars($e->getMessage());
}
