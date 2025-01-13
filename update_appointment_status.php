<?php
session_start();
require_once 'db_connect.php';
require_once 'notifications.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'staff') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $appointment_id = $data['appointmentId'];
    $status = $data['status'];

    // Define allowed status values
    $allowed_statuses = ['scheduled', 'rejected'];

    // Validate the status value
    if (!in_array($status, $allowed_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status value']);
        exit();
    }

    try {
        // Update the appointment status
        $stmt = $pdo->prepare('UPDATE appointments SET status = ? WHERE id = ?');
        $stmt->execute([$status, $appointment_id]);

        if ($stmt->rowCount() > 0) {
            // Fetch patient_id and user_id for notification
            $stmt = $pdo->prepare('SELECT p.id AS patient_id, u.id AS user_id FROM appointments a JOIN patients p ON a.patient_id = p.id JOIN users u ON p.user_id = u.id WHERE a.id = ?');
            $stmt->execute([$appointment_id]);
            $appointment = $stmt->fetch();
            $patient_id = $appointment['patient_id'];
            $patient_user_id = $appointment['user_id'];

            // Send notification to patient
            $message = $status == 'scheduled' ? 'Your appointment has been approved.' : 'Your appointment has been rejected.';
            addNotification($patient_user_id, $message);

            // If the status is rejected, delete the appointment
            if ($status == 'rejected') {
                $stmt = $pdo->prepare('DELETE FROM appointments WHERE id = ?');
                $stmt->execute([$appointment_id]);
            }

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
