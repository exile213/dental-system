<?php
function addNotification($user_id, $message)
{
    global $pdo;
    $stmt = $pdo->prepare('INSERT INTO notifications (user_id, message, is_read) VALUES (?, ?, 0)');
    $stmt->execute([$user_id, $message]);
}

function getNotifications($user_id)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function markNotificationAsRead($notification_id)
{
    global $pdo;
    $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE id = ?');
    $stmt->execute([$notification_id]);
}
?>