<?php
$user_type = $_SESSION['user_type'] ?? '';
$first_name = $user_type === 'doctor' ? $doctor['first_name'] : $patient['first_name'];
$last_name = $user_type === 'doctor' ? $doctor['last_name'] : $patient['last_name'];
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Medical System</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
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
            <span class="navbar-text me-3">
                Welcome, <?php echo htmlspecialchars($first_name . ' ' . $last_name); ?>
            </span>
            <a href="logout.php" class="btn btn-outline-danger">Logout</a>
        </div>
    </div>
</nav>

