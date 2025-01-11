<?php
require_once 'db_connect.php';

$stmt = $pdo->prepare("
    SELECT id, appointment_date 
    FROM appointments 
    WHERE is_available = TRUE 
    ORDER BY appointment_date
");
$stmt->execute();
$available_slots = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($available_slots);

