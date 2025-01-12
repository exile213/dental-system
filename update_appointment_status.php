<?php
session_start();
require_once 'db_connect.php';
require_once 'notifications.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'doctor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $appointment_id = $data['appointmentId'];
    $status = $data['status'];

    // Define allowed status values
    $allowed_statuses = ['available', 'scheduled', 'approved', 'rejected', 'not_available', 'not_available_morning', 'not_available_afternoon', 'not_available_full_day'];

    // Validate the status value
    if (!in_array($status, $allowed_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status value']);
        exit();
    }

    // Fetch the doctor's ID from the doctors table
    $stmt = $pdo->prepare('SELECT id FROM doctors WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $doctor = $stmt->fetch();

    if (!$doctor) {
        echo json_encode(['success' => false, 'message' => 'Doctor not found']);
        exit();
    }

    $doctor_id = $doctor['id'];

    try {
        // Update the appointment status
        $stmt = $pdo->prepare('UPDATE appointments SET status = ? WHERE id = ? AND doctor_id = ?');
        $stmt->execute([$status, $appointment_id, $doctor_id]);

        if ($stmt->rowCount() > 0) {
            // Fetch patient_id for notification
            $stmt = $pdo->prepare("SELECT patient_id FROM appointments WHERE id = ?");
            $stmt->execute([$appointment_id]);
            $appointment = $stmt->fetch();
            $patient_id = $appointment['patient_id'];

            // Send notification to patient
            $message = $status == 'scheduled' ? 'Your appointment has been approved.' : 'Your appointment has been rejected.';
            addNotification($patient_id, $message);

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Appointment not found or not authorized to update']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>