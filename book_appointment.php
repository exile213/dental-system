<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'patient') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $appointment_id = $_POST['appointmentId'];
    $user_id = $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        // Check if the appointment is still available
        $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ? AND status = 'available'");
        $stmt->execute([$appointment_id]);
        $appointment = $stmt->fetch();

        if (!$appointment) {
            throw new Exception('Appointment is no longer available');
        }

        // Fetch the patient ID from the patients table
        $stmt = $pdo->prepare('SELECT id FROM patients WHERE user_id = ?');
        $stmt->execute([$user_id]);
        $patient = $stmt->fetch();

        if (!$patient) {
            throw new Exception('Patient not found');
        }

        $patient_id = $patient['id'];

        // Update the appointment status and assign it to the patient
        $stmt = $pdo->prepare("UPDATE appointments SET patient_id = ?, status = 'scheduled', is_available = FALSE WHERE id = ?");
        $stmt->execute([$patient_id, $appointment_id]);

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
