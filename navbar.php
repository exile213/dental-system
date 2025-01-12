<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect.php';

$user_type = $_SESSION['user_type'] ?? '';
$user_id = $_SESSION['user_id'] ?? '';

$notifications = [];
$unread_count = 0;

if ($user_type === 'doctor') {
    $stmt = $pdo->prepare('SELECT first_name, last_name FROM doctors WHERE user_id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    // Fetch unread notifications for the doctor
    $stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? AND is_read = 0');
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll();
    $unread_count = count($notifications);
} elseif ($user_type === 'patient') {
    $stmt = $pdo->prepare('SELECT id, first_name, last_name FROM patients WHERE user_id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if ($user) {
        // Fetch unread notifications for the patient
        $stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? AND is_read = 0');
        $stmt->execute([$user['id']]);
        $notifications = $stmt->fetchAll();
        $unread_count = count($notifications);
    }
}
$first_name = $user['first_name'] ?? '';
$last_name = $user['last_name'] ?? '';
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Medical System</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $user_type; ?>_dashboard.php">Dashboard</a>
                </li>
                <?php if ($user_type === 'doctor'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="#appointments">Appointments</a>
                </li>
                <?php elseif ($user_type === 'patient'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="#request-appointment">Request Appointment</a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="#calendar">Calendar</a>
                </li>
            </ul>
            <?php if ($user_type === 'doctor' || ($user_type === 'patient' && $user)): ?>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell"></i>
                        <?php if ($unread_count > 0): ?>
                        <span class="badge bg-danger"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <?php if ($unread_count > 0): ?>
                        <?php foreach ($notifications as $notification): ?>
                        <li>
                            <a class="dropdown-item" href="#">
                                <?php echo htmlspecialchars($notification['message']); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                        <li>
                            <a class="dropdown-item" href="mark_notifications_read.php">Mark all as read</a>
                        </li>
                        <?php else: ?>
                        <li>
                            <a class="dropdown-item" href="#">No new notifications</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
            </ul>
            <?php endif; ?>
            <span class="navbar-text me-3">
                Welcome, <?php echo htmlspecialchars($first_name . ' ' . $last_name); ?>
            </span>
            <a href="logout.php" class="btn btn-outline-danger">Logout</a>
        </div>
    </div>
</nav>
