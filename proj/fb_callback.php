<?php
session_start();
require_once __DIR__ . '/db_connect.php';

// Handle Firebase Facebook login data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        exit;
    }

    $uid = $input['uid'] ?? '';
    $name = $input['name'] ?? '';
    $email = $input['email'] ?? '';
    $photoURL = $input['photoURL'] ?? '';
    $accessToken = $input['accessToken'] ?? null;

    if (!$email || !$uid) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required user data']);
        exit;
    }

    try {
        // Check if user exists by Firebase UID or email
        $stmt = $mysqli->prepare('SELECT id FROM users WHERE provider_id = ? OR email = ? LIMIT 1');
        $stmt->bind_param('ss', $uid, $email);
        $stmt->execute();
        $stmt->bind_result($userId);
        $exists = $stmt->fetch();
        $stmt->close();

        if ($exists) {
            // Update existing user
            $stmt = $mysqli->prepare('UPDATE users SET name = ?, picture = ?, provider = ?, provider_id = ? WHERE id = ?');
            $provider = 'firebase_facebook';
            $stmt->bind_param('ssssi', $name, $photoURL, $provider, $uid, $userId);
            $stmt->execute();
            $stmt->close();
        } else {
            // Create new user
            $stmt = $mysqli->prepare('INSERT INTO users (name, email, picture, provider, provider_id) VALUES (?, ?, ?, ?, ?)');
            $provider = 'firebase_facebook';
            $stmt->bind_param('sssss', $name, $email, $photoURL, $provider, $uid);
            $stmt->execute();
            $userId = $stmt->insert_id;
            $stmt->close();
        }

        // Set session
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_picture'] = $photoURL;
        $_SESSION['user_type'] = 'customer'; // Facebook logins are customers
        $_SESSION['logged_in'] = true;

        echo json_encode(['success' => true, 'redirect' => 'customer.php']);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}

// If not POST, redirect to login
header('Location: login.php');
exit;
