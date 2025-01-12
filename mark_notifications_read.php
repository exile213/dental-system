<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?');
    $stmt->execute([$user_id]);
    if ($_SESSION['user_type'] == 'patient') {
        header('Location: patient_dashboard.php');
    } elseif ($_SESSION['user_type'] == 'doctor') {
        header('Location: doctor_dashboard.php');
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
