<?php
session_start();

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    // Destroy the session specific to the current user
    session_destroy();
}

// Redirect to the login page
header("Location: login.php");
exit();
?>