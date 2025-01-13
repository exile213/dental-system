<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'doctor') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch doctor information
$stmt = $pdo->prepare('SELECT * FROM doctors WHERE user_id = ?');
$stmt->execute([$user_id]);
$doctor = $stmt->fetch();

// Fetch all appointments
$stmt = $pdo->prepare("
    SELECT a.*, p.first_name, p.last_name, p.date_of_birth, a.service 
    FROM appointments a 
    LEFT JOIN patients p ON a.patient_id = p.id 
    WHERE a.doctor_id = ?
    ORDER BY a.appointment_date
");
$stmt->execute([$doctor['id']]);
$appointments = $stmt->fetchAll();

// Fetch unread notifications for the doctor
$stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? AND is_read = 0');
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();
$unread_count = count($notifications);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - Medical System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.js'></script>
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>
    <style>
        #calendar {
            max-width: 1000px;
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>
    <div class="container mt-5">
        <div id="calendar" class="row mt-4">
            <div class="col-md-12">
                <h3>Appointment Calendar</h3>
                <div id="calendar"></div>
            </div>
        </div>

        <div id="appointments" class="row mt-4">
            <h3>Appointments</h3>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Patient</th>
                        <th>Service</th>
                        <th>Availability Type</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($appointment['appointment_date'] ?? ''); ?></td>
                        <td>
                            <?php
                            if ($appointment['status'] == 'available') {
                                echo 'Available';
                            } else {
                                echo htmlspecialchars(($appointment['first_name'] ?? '') . ' ' . ($appointment['last_name'] ?? ''));
                            }
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($appointment['service'] ?? ''); ?></td>
                        <td>
                            <?php
                            if ($appointment['status'] == 'scheduled' || $appointment['status'] == 'approved') {
                                echo 'Scheduled';
                            } elseif ($appointment['availability_type'] == 'full_day' && $appointment['status'] == 'not_available') {
                                echo 'Not Available (Full Day)';
                            } elseif ($appointment['availability_type'] == 'morning') {
                                echo 'Not Available (Morning)';
                            } elseif ($appointment['availability_type'] == 'afternoon') {
                                echo 'Not Available (Afternoon)';
                            } elseif ($appointment['availability_type'] == 'full_day' && $appointment['status'] == 'available') {
                                echo 'Available';
                            } else {
                                echo 'Unknown Availability';
                            }
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars(ucfirst($appointment['status'] ?? '')); ?></td>
                        <td>
                            <?php if ($appointment['status'] == 'scheduled'): ?>
                            <a href="view_patient_record.php?patient_id=<?php echo $appointment['patient_id']; ?>"
                                class="btn btn-primary btn-sm">View Record</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth'
                },
                events: [
                    <?php foreach ($appointments as $appointment): ?> {
                        title: '<?php
                        if ($appointment['status'] == 'available') {
                            echo 'Available';
                        } elseif ($appointment['status'] == 'not_available') {
                            echo 'N/A';
                        } else {
                            echo ucfirst(substr($appointment['status'], 0, 3));
                        }
                        ?>',
                        start: '<?php echo $appointment['appointment_date']; ?>',
                        color: '<?php
                        if ($appointment['status'] == 'available') {
                            echo 'green';
                        } elseif (strpos($appointment['status'], 'not_available') !== false) {
                            echo 'red';
                        } else {
                            echo 'gray';
                        }
                        ?>',
                        extendedProps: {
                            status: '<?php echo $appointment['status']; ?>',
                            appointmentId: <?php echo $appointment['id']; ?>,
                            availability_type: '<?php echo $appointment['availability_type']; ?>',
                            fullTitle: '<?php
                            if ($appointment['status'] == 'available') {
                                echo 'Available';
                            } elseif ($appointment['status'] == 'not_available') {
                                if ($appointment['availability_type'] == 'morning') {
                                    echo 'Not Available (Morning)';
                                } elseif ($appointment['availability_type'] == 'afternoon') {
                                    echo 'Not Available (Afternoon)';
                                } elseif ($appointment['availability_type'] == 'full_day') {
                                    echo 'Not Available (Full Day)';
                                }
                            } elseif ($appointment['status'] == 'scheduled' || $appointment['status'] == 'approved') {
                                echo 'Scheduled: ' . htmlspecialchars(($appointment['first_name'] ?? '') . ' ' . ($appointment['last_name'] ?? ''));
                            } else {
                                echo ucfirst($appointment['status']);
                            }
                            ?>'
                        }
                    },
                    <?php endforeach; ?>
                ],
                eventDidMount: function(info) {
                    tippy(info.el, {
                        content: info.event.extendedProps.fullTitle,
                    });
                },
                validRange: function(nowDate) {
                    return {
                        start: nowDate
                    };
                }
            });
            calendar.render();
        });
    </script>
</body>

</html>
