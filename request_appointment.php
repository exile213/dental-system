<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'patient') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $doctor_id = $_POST['doctor'];
    $appointment_date = $_POST['appointmentDate'] . ' ' . $_POST['appointmentTime'];
    $user_id = $_SESSION['user_id'];

    try {
        // First, get the patient_id from the patients table
        $stmt = $pdo->prepare('SELECT id FROM patients WHERE user_id = ?');
        $stmt->execute([$user_id]);
        $patient = $stmt->fetch();

        if (!$patient) {
            throw new Exception('Patient not found');
        }

        $patient_id = $patient['id'];

        // Now insert the appointment with the correct patient_id and status 'requested'
        $stmt = $pdo->prepare("INSERT INTO appointments (doctor_id, patient_id, appointment_date, status) VALUES (?, ?, ?, 'requested')");
        $stmt->execute([$doctor_id, $patient_id, $appointment_date]);

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
