<?php
session_start();
require_once 'db_connect.php';

$registration_success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_POST['email'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $address = $_POST['address'];
    $phone_number = $_POST['phone_number'];
    $emergency_contact_name = $_POST['emergency_contact_name'];
    $emergency_contact_phone = $_POST['emergency_contact_phone'];
    $medical_conditions = $_POST['medical_conditions'];
    $date_of_birth = $_POST['date_of_birth'];

    try {
        $pdo->beginTransaction();

        // Insert into users table
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, user_type) VALUES (?, ?, ?, 'patient')");
        $stmt->execute([$username, $password, $email]);
        $user_id = $pdo->lastInsertId();

        // Insert into patients table
        $stmt = $pdo->prepare("INSERT INTO patients (user_id, first_name, last_name, date_of_birth, address, phone_number, emergency_contact_name, emergency_contact_phone, medical_conditions) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $first_name, $last_name, $date_of_birth, $address, $phone_number, $emergency_contact_name, $emergency_contact_phone, $medical_conditions]);

        $pdo->commit();
        $registration_success = true;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = "Failed to register: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Medical System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Register</h2>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form method="POST" action="register.php">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" required>
            </div>
            <div class="mb-3">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" required>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <input type="text" class="form-control" id="address" name="address" required>
            </div>
            <div class="mb-3">
                <label for="phone_number" class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="phone_number" name="phone_number" required>
            </div>
            <div class="mb-3">
                <label for="emergency_contact_name" class="form-label">Emergency Contact Name</label>
                <input type="text" class="form-control" id="emergency_contact_name" name="emergency_contact_name" required>
            </div>
            <div class="mb-3">
                <label for="emergency_contact_phone" class="form-label">Emergency Contact Phone Number</label>
                <input type="text" class="form-control" id="emergency_contact_phone" name="emergency_contact_phone" required>
            </div>
            <div class="mb-3">
                <label for="medical_conditions" class="form-label">Medical Conditions or Allergies</label>
                <textarea class="form-control" id="medical_conditions" name="medical_conditions" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label for="date_of_birth" class="form-label">Date of Birth</label>
                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required>
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
    </div>

    <?php if ($registration_success): ?>
    <script>
        alert('Registration successful!');
        window.location.href = 'login.php';
    </script>
    <?php endif; ?>
</body>
</html>