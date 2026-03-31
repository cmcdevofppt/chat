<?php
// send_message.php - AJAX endpoint to post a new message
session_start();
if (!isset($_SESSION['username']) && !isset($_COOKIE['username'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

if (!isset($_SESSION['username']) && isset($_COOKIE['username'])) {
    $_SESSION['username'] = $_COOKIE['username'];
}

require 'db.php';
$username = $_SESSION['username'];

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Read JSON input (if sent as JSON) or fallback to form data
$input = json_decode(file_get_contents('php://input'), true);
if ($input && isset($input['message'])) {
    $message = trim($input['message']);
    $parent_id = isset($input['parent_id']) ? (int)$input['parent_id'] : null;
} else {
    $message = trim($_POST['message'] ?? '');
    $parent_id = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? (int)$_POST['parent_id'] : null;
}

if ($message === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Message cannot be empty']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO messages (username, message, parent_id) VALUES (?, ?, ?)");
    $stmt->execute([$username, $message, $parent_id]);
    echo json_encode(['status' => 'ok', 'id' => $pdo->lastInsertId()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}