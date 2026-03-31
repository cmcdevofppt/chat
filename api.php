<?php
// api.php - returns JSON list of messages with reply details
require 'db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $stmt = $pdo->prepare("
        SELECT 
            m.id,
            m.username,
            m.message,
            m.created_at,
            m.parent_id,
            p.username AS parent_username,
            p.message AS parent_message
        FROM messages m
        LEFT JOIN messages p ON m.parent_id = p.id
        ORDER BY m.created_at ASC
    ");
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ensure parent fields are null if parent_id is null
    foreach ($messages as &$msg) {
        if ($msg['parent_id'] === null) {
            $msg['parent_username'] = null;
            $msg['parent_message'] = null;
        }
    }

    echo json_encode(['status' => 'ok', 'messages' => $messages], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}