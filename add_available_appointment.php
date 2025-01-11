<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'doctor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $appointment_date = $_POST['appointmentDate'];
    $doctor_id = $_SESSION['user_id'];

    try {
        $stmt = $pdo->prepare("INSERT INTO appointments (doctor_id, appointment_date, status) VALUES (?, ?, 'available')");
        $stmt->execute([$doctor_id, $appointment_date]);

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}