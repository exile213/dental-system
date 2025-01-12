<?php
session_start();
require_once 'db_connect.php';
require_once 'notifications.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'patient') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $appointment_id = $_POST['appointmentId'];

    try {
        $pdo->beginTransaction();

        // Fetch the appointment details
        $stmt = $pdo->prepare('SELECT * FROM appointments WHERE id = ?');
        $stmt->execute([$appointment_id]);
        $appointment = $stmt->fetch();

        if (!$appointment) {
            throw new Exception('Appointment not found');
        }

        // Update the appointment status to 'canceled'
        $stmt = $pdo->prepare("UPDATE appointments SET status = 'canceled' WHERE id = ?");
        $stmt->execute([$appointment_id]);

        // Fetch the user_id associated with the doctor_id
        $stmt = $pdo->prepare('SELECT user_id FROM doctors WHERE id = ?');
        $stmt->execute([$appointment['doctor_id']]);
        $doctor = $stmt->fetch();

        if (!$doctor) {
            throw new Exception('Doctor not found');
        }

        // Send notification to the doctor
        $doctor_user_id = $doctor['user_id'];
        addNotification($doctor_user_id, 'A patient has canceled their appointment.');

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
