<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Get unread notification count
    $stmt = $pdo->prepare('SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0');
    $stmt->execute([$user_id]);
    $unread_count = $stmt->fetch(PDO::FETCH_ASSOC)['unread_count'];

    // Get latest notifications
    $stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5');
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'unread_count' => $unread_count,
        'notifications' => $notifications,
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
