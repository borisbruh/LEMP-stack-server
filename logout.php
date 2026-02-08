<?php //logout.php

session_start();

// Destroy the session and all session data
session_unset();

// Set a session variable for the success message
$_SESSION['message'] = 'Logout successful';

// Redirect to the login page
header("Location: login.php");
exit;
?>
