<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'staff') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $slot_date = $_POST['slotDate'];
    $slot_time = $_POST['slotTime'] ?? null;
    $availability_type = $_POST['availabilityType'];

    // Set appointment_date based on availability type
    if ($availability_type == 'not_available_full_day') {
        $appointment_date = $slot_date; // Only date for full day
    } else {
        $appointment_date = $slot_date . ' ' . $slot_time;
    }

    // Use a fixed doctor_id of 1
    $doctor_id = 1;

    try {
        // Determine the status based on availability type
        if ($availability_type == 'available') {
            $status = 'available';
            $availability = 'full_day';
        } elseif ($availability_type == 'not_available_morning') {
            $status = 'not_available';
            $availability = 'morning';
        } elseif ($availability_type == 'not_available_afternoon') {
            $status = 'not_available';
            $availability = 'afternoon';
        } elseif ($availability_type == 'not_available_full_day') {
            $status = 'not_available';
            $availability = 'full_day';
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid availability type']);
            exit();
        }

        // Insert the new slot into the appointments table
        $stmt = $pdo->prepare('INSERT INTO appointments (doctor_id, appointment_date, status, availability_type) VALUES (?, ?, ?, ?)');
        $stmt->execute([$doctor_id, $appointment_date, $status, $availability]);

        echo json_encode(['success' => true, 'message' => 'Slot added successfully']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to add slot: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
