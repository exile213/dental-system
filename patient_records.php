<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'doctor') {
    header('Location: login.php');
    exit();
}

$patient_id = $_GET['patient_id'] ?? null;

if (!$patient_id) {
    header('Location: doctor_dashboard.php');
    exit();
}

// Fetch patient information
$stmt = $pdo->prepare('SELECT * FROM patients WHERE id = ?');
$stmt->execute([$patient_id]);
$patient = $stmt->fetch();

if (!$patient) {
    header('Location: doctor_dashboard.php');
    exit();
}

// Fetch patient appointments
$stmt = $pdo->prepare('
    SELECT a.*, d.first_name AS doctor_first_name, d.last_name AS doctor_last_name 
    FROM appointments a 
    JOIN doctors d ON a.doctor_id = d.id 
    WHERE a.patient_id = ? 
    ORDER BY a.appointment_date DESC
');
$stmt->execute([$patient_id]);
$appointments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Record - Medical System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2>Patient Record</h2>
        <table class="table table-bordered">
            <tr>
                <th>Name</th>
                <td><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></td>
            </tr>
            <tr>
                <th>Date of Birth</th>
                <td><?php echo htmlspecialchars($patient['date_of_birth']); ?></td>
            </tr>
            <tr>
                <th>Address</th>
                <td><?php echo htmlspecialchars($patient['address']); ?></td>
            </tr>
            <tr>
                <th>Phone Number</th>
                <td><?php echo htmlspecialchars($patient['phone_number']); ?></td>
            </tr>
            <tr>
                <th>Emergency Contact Name</th>
                <td><?php echo htmlspecialchars($patient['emergency_contact_name']); ?></td>
            </tr>
            <tr>
                <th>Emergency Contact Phone</th>
                <td><?php echo htmlspecialchars($patient['emergency_contact_phone']); ?></td>
            </tr>
            <tr>
                <th>Medical Conditions or Allergies</th>
                <td><?php echo htmlspecialchars($patient['medical_conditions']); ?></td>
            </tr>
        </table>

        <h3>Appointment History</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Service</th>
                    <th>Doctor</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $appointment): ?>
                <tr>
                    <td><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                    <td><?php echo htmlspecialchars($appointment['service']); ?></td>
                    <td><?php echo htmlspecialchars($appointment['doctor_first_name'] . ' ' . $appointment['doctor_last_name']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="doctor_dashboard.php" class="btn btn-primary">Back to Dashboard</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
