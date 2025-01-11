<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'patient') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch patient information
$stmt = $pdo->prepare('SELECT * FROM patients WHERE user_id = ?');
$stmt->execute([$user_id]);
$patient = $stmt->fetch();

// Fetch upcoming appointments
$stmt = $pdo->prepare("
    SELECT a.*, d.first_name AS doctor_first_name, d.last_name AS doctor_last_name, d.specialization 
    FROM appointments a 
    JOIN doctors d ON a.doctor_id = d.id 
    WHERE a.patient_id = ? AND a.status = 'scheduled' 
    ORDER BY a.appointment_date
");
$stmt->execute([$patient['id']]);
$appointments = $stmt->fetchAll();

// Fetch all appointments (including those made by other patients)
$stmt = $pdo->prepare("
    SELECT a.*, d.first_name AS doctor_first_name, d.last_name AS doctor_last_name, d.specialization 
    FROM appointments a 
    JOIN doctors d ON a.doctor_id = d.id 
    ORDER BY a.appointment_date
");
$stmt->execute();
$all_appointments = $stmt->fetchAll();

// Fetch all doctors
$stmt = $pdo->prepare('SELECT * FROM doctors ORDER BY last_name, first_name');
$stmt->execute();
$doctors = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard - Medical System</title>
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
        <div class="row mt-4">
            <div id="request-appointment" class="col-md-6">
                <h3>Request Appointment</h3>
                <form id="appointmentRequestForm">
                    <div class="mb-3">
                        <label for="doctor" class="form-label">Select Doctor</label>
                        <select class="form-select" id="doctor" name="doctor" required>
                            <option value="">Choose a doctor</option>
                            <?php foreach ($doctors as $doctor): ?>
                            <option value="<?php echo $doctor['id']; ?>">
                                Dr. <?php echo htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']); ?>
                                (<?php echo htmlspecialchars($doctor['specialization']); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="appointmentDate" class="form-label">Preferred Date</label>
                        <input type="date" class="form-control" id="appointmentDate" name="appointmentDate" required>
                    </div>
                    <div class="mb-3">
                        <label for="appointmentTime" class="form-label">Preferred Time</label>
                        <input type="time" class="form-control" id="appointmentTime" name="appointmentTime" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Request Appointment</button>
                </form>
            </div>
            <div id="calendar" class="col-md-6">
                <h3>Appointment Calendar</h3>
                <div id="calendar"></div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <h3>Your Upcoming Appointments</h3>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Doctor</th>
                            <th>Specialization</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['doctor_first_name'] . ' ' . $appointment['doctor_last_name']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['specialization']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['status']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
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
                    <?php foreach ($all_appointments as $appointment): ?> {
                        title: <?php
                        if ($appointment['status'] == 'available') {
                            echo "'Available'";
                        } elseif (strpos($appointment['status'], 'not_available') !== false) {
                            echo "'N/A'";
                        } elseif ($appointment['patient_id'] == $patient['id']) {
                            echo "'Your Appointment'";
                        } else {
                            echo "'N/A'";
                        }
                        ?>,
                        start: '<?php echo $appointment['appointment_date']; ?>',
                        color: <?php
                        if ($appointment['status'] == 'available') {
                            echo "'green'";
                        } elseif (strpos($appointment['status'], 'not_available') !== false) {
                            echo "'red'";
                        } elseif ($appointment['patient_id'] == $patient['id']) {
                            echo "'blue'";
                        } else {
                            echo "'red'";
                        }
                        ?>,
                        extendedProps: {
                            doctor_id: <?php echo $appointment['doctor_id']; ?>,
                            appointment_id: <?php echo $appointment['id']; ?>,
                            status: '<?php echo $appointment['status']; ?>',
                            availability_type: '<?php echo $appointment['availability_type']; ?>',
                            fullTitle: <?php
                            if ($appointment['status'] == 'available') {
                                echo "'Available: Dr. " . addslashes($appointment['doctor_last_name']) . "'";
                            } elseif ($appointment['status'] == 'not_available_morning') {
                                echo "'Not Available (Morning): Dr. " . addslashes($appointment['doctor_last_name']) . "'";
                            } elseif ($appointment['status'] == 'not_available_afternoon') {
                                echo "'Not Available (Afternoon): Dr. " . addslashes($appointment['doctor_last_name']) . "'";
                            } elseif ($appointment['status'] == 'not_available_full_day') {
                                echo "'Not Available (Full Day): Dr. " . addslashes($appointment['doctor_last_name']) . "'";
                            } elseif ($appointment['patient_id'] == $patient['id']) {
                                echo "'Your Appointment: Dr. " . addslashes($appointment['doctor_last_name']) . "'";
                            } else {
                                echo "'Not Available: Dr. " . addslashes($appointment['doctor_last_name']) . "'";
                            }
                            ?>
                        }
                    },
                    <?php endforeach; ?>
                ],
                eventClick: function(info) {
                    if (info.event.extendedProps.status === 'available') {
                        if (confirm('Would you like to book this appointment with ' + info.event
                                .extendedProps.fullTitle + '?')) {
                            bookAppointment(info.event.extendedProps.appointment_id);
                        }
                    } else if (info.event.extendedProps.status.includes('not_available')) {
                        alert('This slot is not available for booking: ' + info.event.extendedProps
                            .fullTitle);
                    }
                },
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

            function bookAppointment(appointmentId) {
                fetch('book_appointment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'appointmentId=' + appointmentId
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Appointment booked successfully!');
                            location.reload();
                        } else {
                            alert('Failed to book appointment: ' + data.message);
                        }
                    })
                    .catch((error) => {
                        console.error('Error:', error);
                        alert('An error occurred while booking the appointment.');
                    });
            }

            document.getElementById('appointmentRequestForm').addEventListener('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                fetch('request_appointment.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Appointment request submitted successfully!');
                            location.reload();
                        } else {
                            alert('Failed to submit appointment request: ' + data.message);
                        }
                    })
                    .catch((error) => {
                        console.error('Error:', error);
                        alert('An error occurred while submitting the appointment request.');
                    });
            });
        });
    </script>
</body>

</html>
