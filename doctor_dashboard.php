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

        <div class="row mt-4">
            <div class="col-md-6">
                <h3>Add Available Slot</h3>
                <form id="addSlotForm">
                    <div class="mb-3">
                        <label for="slotDate" class="form-label">Date</label>
                        <input type="date" class="form-control" id="slotDate" name="slotDate" required>
                    </div>
                    <div class="mb-3">
                        <label for="slotTime" class="form-label">Time</label>
                        <input type="time" class="form-control" id="slotTime" name="slotTime">
                    </div>
                    <div class="mb-3">
                        <label for="availabilityType" class="form-label">Availability</label>
                        <select class="form-select" id="availabilityType" name="availabilityType" required>
                            <option value="available">Available</option>
                            <option value="not_available_morning">Not Available (Morning)</option>
                            <option value="not_available_afternoon">Not Available (Afternoon)</option>
                            <option value="not_available_full_day">Not Available (Full Day)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Slot</button>
                </form>
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
                            <?php if ($appointment['status'] == 'requested'): ?>
                            <button class="btn btn-success btn-sm approve-appointment"
                                data-id="<?php echo $appointment['id']; ?>">Approve</button>
                            <button class="btn btn-danger btn-sm reject-appointment"
                                data-id="<?php echo $appointment['id']; ?>">Reject</button>
                            <?php elseif ($appointment['status'] == 'available' || $appointment['status'] == 'not_available'): ?>
                            <button class="btn btn-warning btn-sm edit-slot" data-id="<?php echo $appointment['id']; ?>"
                                data-date="<?php echo $appointment['appointment_date']; ?>"
                                data-availability-type="<?php echo $appointment['availability_type']; ?>">Edit</button>
                            <button class="btn btn-danger btn-sm delete-slot"
                                data-id="<?php echo $appointment['id']; ?>">Delete</button>
                            <?php elseif ($appointment['status'] == 'scheduled'): ?>
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
                eventClick: function(info) {
                    if (info.event.extendedProps.status === 'available' || info.event.extendedProps
                        .status.includes('not_available')) {
                        openEditModal(info.event.extendedProps.appointmentId, info.event.start, info
                            .event.extendedProps.availability_type);
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

            // Handle availability type selection
            document.getElementById('availabilityType').addEventListener('change', function() {
                var selectedValue = this.value;
                var slotTimeInput = document.getElementById('slotTime');

                if (selectedValue === 'not_available_full_day') {
                    slotTimeInput.value = ''; // Clear the time input
                    slotTimeInput.disabled = true; // Disable time input
                } else {
                    slotTimeInput.disabled = false; // Enable time input
                    if (selectedValue === 'not_available_morning') {
                        slotTimeInput.setAttribute('min', '06:00');
                        slotTimeInput.setAttribute('max', '12:00');
                    } else if (selectedValue === 'not_available_afternoon') {
                        slotTimeInput.setAttribute('min', '12:00');
                        slotTimeInput.setAttribute('max', '18:00');
                    }
                }
            });

            // Add Slot Form Submission
            document.getElementById('addSlotForm').addEventListener('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                fetch('add_available_slot.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Slot added successfully!');
                            location.reload();
                        } else {
                            alert('Failed to add slot: ' + data.message);
                        }
                    })
                    .catch((error) => {
                        console.error('Error:', error);
                        alert('An error occurred while adding the slot.');
                    });
            });

            // Approve and Reject functionality
            document.querySelectorAll('.approve-appointment, .reject-appointment').forEach(button => {
                button.addEventListener('click', function() {
                    const appointmentId = this.getAttribute('data-id');
                    const status = this.classList.contains('approve-appointment') ? 'scheduled' :
                        'rejected';
                    updateAppointmentStatus(appointmentId, status);
                });
            });

            function updateAppointmentStatus(appointmentId, status) {
                fetch('update_appointment_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            appointmentId,
                            status
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Appointment status updated successfully!');
                            location.reload();
                        } else {
                            alert('Failed to update appointment status: ' + data.message);
                        }
                    })
                    .catch((error) => {
                        console.error('Error:', error);
                        alert('An error occurred while updating the appointment status.');
                    });
            }
            // Edit slot functionality
            const editSlotModal = new bootstrap.Modal(document.getElementById('editSlotModal'));

            document.querySelectorAll('.edit-slot').forEach(button => {
                button.addEventListener('click', function() {
                    const appointmentId = this.getAttribute('data-id');
                    const date = this.getAttribute('data-date');
                    const availabilityType = this.getAttribute('data-availability-type');
                    openEditModal(appointmentId, date, availabilityType);
                });
            });

            function openEditModal(appointmentId, date, availabilityType) {
                document.getElementById('editSlotId').value = appointmentId;

                document.getElementById('editSlotDate').value = new Date(date).toISOString().split('T')[0];
                document.getElementById('editAvailabilityType').value = availabilityType;
                editSlotModal.show();
            }

            document.getElementById('saveEditSlot').addEventListener('click', function() {
                const form = document.getElementById('editSlotForm');
                const formData = new FormData(form);

                fetch('edit_available_slot.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Slot updated successfully!');
                            location.reload();
                        } else {
                            alert(`Failed to update slot: ${data.message}`);
                        }
                    })
                    .catch((error) => {
                        console.error('Error:', error);
                        alert('An error occurred while updating the slot.');
                    });
            });

            // Delete slot functionality
            document.querySelectorAll('.delete-slot').forEach(button => {
                button.addEventListener('click', function() {
                    const appointmentId = this.getAttribute('data-id');
                    if (confirm('Are you sure you want to delete this slot?')) {
                        deleteSlot(appointmentId);
                    }
                });
            });

            function deleteSlot(appointmentId) {
                fetch('remove_available_slot.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `appointmentId=${appointmentId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Slot deleted successfully!');
                            location.reload();
                        } else {
                            alert(`Failed to delete slot: ${data.message}`);
                        }
                    })
                    .catch((error) => {
                        console.error('Error:', error);
                        alert('An error occurred while deleting the slot.');
                    });
            }
        });
    </script>
</body>

</html>
