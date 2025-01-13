<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'staff') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['appointmentId'])) {
        echo json_encode(['success' => false, 'message' => 'Appointment ID is required']);
        exit();
    }

    $appointment_id = $_POST['appointmentId'];

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
        // First, check if the appointment exists and belongs to the doctor
        $stmt = $pdo->prepare('SELECT * FROM appointments WHERE id = ? AND doctor_id = ?');
        $stmt->execute([$appointment_id, $doctor_id]);
        $appointment = $stmt->fetch();

        if (!$appointment) {
            echo json_encode(['success' => false, 'message' => 'Slot not found or not authorized to delete']);
            exit();
        }

        // Check if the appointment status is 'available' or 'not_available'
        if ($appointment['status'] != 'available' && $appointment['status'] != 'not_available') {
            echo json_encode(['success' => false, 'message' => 'Cannot delete a slot that is not available or not available']);
            exit();
        }

        // Proceed to delete the appointment
        $stmt = $pdo->prepare('DELETE FROM appointments WHERE id = ?');
        $stmt->execute([$appointment_id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete the slot']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
